<?php
/**
 * Member ID fix.
 *
 * The original code (generate_and_schedule_member_id + process_pending_member_ids)
 * keeps the new Member ID in a 5-minute transient and only saves it once a
 * WordPress user with that email exists. With account activation the user is
 * created later than that, the transient expires and the Member ID is lost.
 *
 * This file adds, without changing the original code:
 * 1. A permanent copy of every new Member ID (keyed by email) that is assigned
 *    on 'user_register', whenever the account is actually created.
 * 2. Users > Repair Member IDs: assigns missing Member IDs to existing users
 *    from their Forminator registration entries.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMC_REGISTRATION_FORM_ID', 13233 );

function smc_pending_member_id_option( $email ) {
	return 'smc_pending_member_id_' . md5( strtolower( trim( $email ) ) );
}

/**
 * Save member-id the same way process_pending_member_ids() does (direct SQL),
 * then clear the user cache so the new value shows straight away.
 */
function smc_save_member_id( $user_id, $reg_number ) {
	global $wpdb;

	$existing = $wpdb->get_var( $wpdb->prepare(
		"SELECT umeta_id FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",
		$user_id,
		'member-id'
	) );

	if ( $existing ) {
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->usermeta} SET meta_value = %s WHERE umeta_id = %d",
			$reg_number,
			$existing
		) );
	} else {
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES (%d, %s, %s)",
			$user_id,
			'member-id',
			$reg_number
		) );
	}

	clean_user_cache( $user_id );
}

// 1a. After generate_and_schedule_member_id (priority 10) has set hidden-1,
//     keep a permanent copy of the Member ID for this email.
add_filter( 'forminator_custom_form_submit_field_data', 'smc_store_pending_member_id', 11, 2 );
function smc_store_pending_member_id( $field_data_array, $form_id ) {
	if ( intval( $form_id ) !== SMC_REGISTRATION_FORM_ID ) {
		return $field_data_array;
	}

	$email      = '';
	$reg_number = '';
	foreach ( $field_data_array as $field ) {
		if ( ! isset( $field['name'], $field['value'] ) || ! is_string( $field['value'] ) ) {
			continue;
		}
		if ( $field['name'] === 'email-1' ) {
			$email = sanitize_email( $field['value'] );
		} elseif ( $field['name'] === 'hidden-1' ) {
			$reg_number = $field['value'];
		}
	}

	if ( $email && strpos( $reg_number, 'DLXMP/' ) === 0 ) {
		update_option( smc_pending_member_id_option( $email ), $reg_number, false );
	}

	return $field_data_array;
}

// 1b. Whenever the account is created (immediately or after activation),
//     assign the stored Member ID.
add_action( 'user_register', 'smc_assign_pending_member_id', 5 );
function smc_assign_pending_member_id( $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user || empty( $user->user_email ) ) {
		return;
	}

	$option     = smc_pending_member_id_option( $user->user_email );
	$reg_number = get_option( $option, '' );
	if ( ! $reg_number ) {
		return;
	}

	if ( ! get_user_meta( $user_id, 'member-id', true ) ) {
		smc_save_member_id( $user_id, $reg_number );
	}
	delete_option( $option );
}

// 1c. If the original 5-minute path saved it first, drop the permanent copy.
add_action( 'init', 'smc_cleanup_pending_member_ids', 20 );
function smc_cleanup_pending_member_ids() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$user = wp_get_current_user();
	if ( get_user_meta( $user->ID, 'member-id', true ) ) {
		$option = smc_pending_member_id_option( $user->user_email );
		if ( get_option( $option, false ) !== false ) {
			delete_option( $option );
		}
	}
}

/**
 * 2. Repair page: find users with no Member ID and look up the Member ID that
 *    was generated in their Forminator registration entry (field hidden-1).
 */
function smc_find_member_ids_in_entries() {
	global $wpdb;

	$entry_table = $wpdb->prefix . 'frmt_form_entry';
	$meta_table  = $wpdb->prefix . 'frmt_form_entry_meta';

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $meta_table ) ) !== $meta_table ) {
		return null;
	}

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT e.entry_id, em.meta_value AS email, hm.meta_value AS reg_number
		FROM {$entry_table} e
		INNER JOIN {$meta_table} em ON em.entry_id = e.entry_id AND em.meta_key = 'email-1'
		INNER JOIN {$meta_table} hm ON hm.entry_id = e.entry_id AND hm.meta_key = 'hidden-1'
		WHERE e.form_id = %d AND hm.meta_value LIKE %s
		ORDER BY e.entry_id ASC",
		SMC_REGISTRATION_FORM_ID,
		$wpdb->esc_like( 'DLXMP/' ) . '%'
	) );

	// Latest entry per email wins.
	$by_email = array();
	foreach ( $rows as $row ) {
		$by_email[ strtolower( trim( $row->email ) ) ] = array(
			'entry_id'   => (int) $row->entry_id,
			'reg_number' => $row->reg_number,
		);
	}

	return $by_email;
}

function smc_get_repair_candidates() {
	global $wpdb;

	$entries = smc_find_member_ids_in_entries();
	if ( $entries === null ) {
		return null;
	}

	$users = get_users( array(
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => 'member-id',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => 'member-id',
				'value' => '',
			),
		),
		'role__not_in' => array( 'administrator' ),
		'orderby'      => 'registered',
		'order'        => 'DESC',
	) );

	$candidates = array();
	foreach ( $users as $user ) {
		$email = strtolower( trim( $user->user_email ) );
		$found = isset( $entries[ $email ] ) ? $entries[ $email ] : null;

		$in_use_by = 0;
		if ( $found ) {
			$in_use_by = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'member-id' AND meta_value = %s LIMIT 1",
				$found['reg_number']
			) );
		}

		$candidates[] = array(
			'user'      => $user,
			'found'     => $found,
			'in_use_by' => $in_use_by,
		);
	}

	return $candidates;
}

add_action( 'admin_menu', 'smc_add_repair_member_ids_page' );
function smc_add_repair_member_ids_page() {
	add_users_page(
		'Repair Member IDs',
		'Repair Member IDs',
		'manage_options',
		'smc-repair-member-ids',
		'smc_repair_member_ids_page'
	);
}

function smc_repair_member_ids_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$assigned = array();
	if ( isset( $_POST['smc_repair_member_ids'] ) ) {
		check_admin_referer( 'smc_repair_member_ids' );
		$candidates = smc_get_repair_candidates();
		foreach ( (array) $candidates as $c ) {
			if ( $c['found'] && ! $c['in_use_by'] ) {
				smc_save_member_id( $c['user']->ID, $c['found']['reg_number'] );
				$assigned[] = $c['user']->user_email . ' → ' . $c['found']['reg_number'];
			}
		}
	}

	$candidates = smc_get_repair_candidates();
	?>
	<div class="wrap">
		<h1>Repair Member IDs</h1>
		<p>Users (non-admin) without a Member ID. Where their Forminator registration entry (form <?php echo (int) SMC_REGISTRATION_FORM_ID; ?>) contains a generated Member ID, it can be assigned to them.</p>

		<?php if ( $assigned ) : ?>
			<div class="notice notice-success"><p><strong>Assigned:</strong><br><?php echo implode( '<br>', array_map( 'esc_html', $assigned ) ); ?></p></div>
		<?php endif; ?>

		<?php if ( $candidates === null ) : ?>
			<div class="notice notice-error"><p>Forminator entry tables were not found.</p></div>
		<?php elseif ( ! $candidates ) : ?>
			<p><strong>All users have a Member ID.</strong></p>
		<?php else : ?>
			<?php $can_fix = false; ?>
			<table class="widefat striped">
				<thead><tr><th>User</th><th>Email</th><th>Role</th><th>Registered</th><th>Member ID found in Forminator entry</th></tr></thead>
				<tbody>
				<?php foreach ( $candidates as $c ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_user_link( $c['user']->ID ) ); ?>"><?php echo esc_html( $c['user']->display_name ); ?></a></td>
						<td><?php echo esc_html( $c['user']->user_email ); ?></td>
						<td><?php echo esc_html( implode( ', ', $c['user']->roles ) ); ?></td>
						<td><?php echo esc_html( $c['user']->user_registered ); ?></td>
						<td>
							<?php
							if ( ! $c['found'] ) {
								echo '<em>No registration entry with a Member ID found for this email</em>';
							} elseif ( $c['in_use_by'] ) {
								echo esc_html( $c['found']['reg_number'] ) . ' <em>(already used by user #' . (int) $c['in_use_by'] . ', will not assign)</em>';
							} else {
								$can_fix = true;
								echo '<strong>' . esc_html( $c['found']['reg_number'] ) . '</strong> (entry #' . (int) $c['found']['entry_id'] . ')';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( $can_fix ) : ?>
				<form method="post" style="margin-top:15px;">
					<?php wp_nonce_field( 'smc_repair_member_ids' ); ?>
					<button type="submit" name="smc_repair_member_ids" value="1" class="button button-primary"
						onclick="return confirm('Assign the Member IDs shown in bold?');">Assign found Member IDs</button>
				</form>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
