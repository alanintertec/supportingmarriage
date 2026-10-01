<?php
/**
 * Plugin Name:       Supporting Marriage Custom
 * Description:       Custom functionality for supportingmarriage.com (member IDs, My Account profile tabs, secure member files, Forminator tweaks, etc.). Moved out of the child theme functions.php.
 * Version:           1.1.0
 * Author:            Intertec Data Solutions
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Load the custom code at the same point WordPress used to run it.
 *
 * The theme's functions.php is loaded just before the 'after_setup_theme'
 * action fires, so loading here (priority 0) keeps the timing identical to
 * before, e.g. WooCommerce hooks are already registered for remove_action().
 *
 * Safety guard: if the old code is still in the theme's functions.php,
 * loading it again would cause a "Cannot redeclare function" fatal error.
 * In that case we skip loading and show an admin notice instead. As soon as
 * functions.php is replaced with the slim version, the plugin takes over on
 * the next page load - no gap, no duplicate code.
 */
function smc_load_custom_functions() {
	if ( function_exists( 'generate_and_schedule_member_id' ) ) {
		add_action( 'admin_notices', 'smc_theme_code_still_present_notice' );
		return;
	}

	require_once SMC_PLUGIN_DIR . 'includes/custom-functions.php';
	require_once SMC_PLUGIN_DIR . 'includes/member-id-fix.php';
}
add_action( 'after_setup_theme', 'smc_load_custom_functions', 0 );

function smc_theme_code_still_present_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>Supporting Marriage Custom:</strong> the custom code is still in the theme\'s functions.php, so the plugin is standing by (nothing is loaded twice). Replace functions.php with the slim version to let the plugin take over.</p></div>';
}
