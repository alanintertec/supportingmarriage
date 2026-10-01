<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function infine_child_theme_enqueue_styles() {
	wp_enqueue_style( 'infine-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'infine-style' ), INFINE_THEME_VERSION ); 
}
add_action( 'wp_enqueue_scripts', 'infine_child_theme_enqueue_styles', 999 );

/*
add_action('rm_user_registered', 'generate_unique_member_id', 10, 3);

function generate_unique_member_id($user_id, $form_id, $params) {
    
    // Get gender from the correct field name: Select_46
    $gender = '';
    
    if (isset($params['Select_46'])) {
        $gender = $params['Select_46'];
    } elseif (get_user_meta($user_id, 'Select_46', true)) {
        $gender = get_user_meta($user_id, 'Select_46', true);
    }
    
    // Log for debugging
    error_log('Gender found: ' . $gender . ' for User ID: ' . $user_id);
    
    // If no gender found, log error and exit
    if (empty($gender)) {
        error_log('ERROR: No gender found for user ' . $user_id);
        return;
    }
    
    // Normalize gender value (Female or Male based on your form)
    $gender = trim($gender);
    
    // Determine prefix based on gender
    if ($gender == 'Male') {
        $prefix = 'DLEMP';
    } elseif ($gender == 'Female') {
        $prefix = 'DLEMPF';
    } else {
        error_log('ERROR: Invalid gender value: ' . $gender);
        return;
    }
    
    // Get next sequential number using database query
    global $wpdb;
    
    $last_id = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->usermeta} 
        WHERE meta_key = 'member_id' 
        AND meta_value LIKE %s 
        ORDER BY CAST(SUBSTRING_INDEX(meta_value, '-', -1) AS UNSIGNED) DESC 
        LIMIT 1",
        $prefix . '%'
    ));
    
    // Calculate next number
    if ($last_id) {
        preg_match('/-(\d+)$/', $last_id, $matches);
        $next_number = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
    } else {
        $next_number = 1;
    }
    
    // Generate unique ID
    $unique_id = $prefix . '-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
    
    // Save to user meta
    update_user_meta($user_id, 'member_id', $unique_id);
    
    // Also save gender for easier filtering later
    update_user_meta($user_id, 'gender', $gender);
    
    // Log success
    error_log('SUCCESS: Generated Member ID: ' . $unique_id . ' for User ID: ' . $user_id);
}

// Add Member ID column to users list
add_filter('manage_users_columns', 'add_member_id_column');
function add_member_id_column($columns) {
    $columns['member_id'] = 'Member ID';
    $columns['gender'] = 'Gender';
    return $columns;
}

// Populate the columns
add_action('manage_users_custom_column', 'show_member_id_column_content', 10, 3);
function show_member_id_column_content($value, $column_name, $user_id) {
    if ($column_name == 'member_id') {
        $member_id = get_user_meta($user_id, 'member_id', true);
        return $member_id ? $member_id : '—';
    }
    if ($column_name == 'gender') {
        $gender = get_user_meta($user_id, 'gender', true);
        if (empty($gender)) {
            $gender = get_user_meta($user_id, 'Select_46', true);
        }
        return $gender ? $gender : '—';
    }
    return $value;
}

// Make columns sortable
add_filter('manage_users_sortable_columns', 'make_member_columns_sortable');
function make_member_columns_sortable($columns) {
    $columns['member_id'] = 'member_id';
    $columns['gender'] = 'gender';
    return $columns;
}

function count_registrations_by_gender() {
    // Count males
    $males = get_users(array(
        'meta_key' => 'gender',
        'meta_value' => 'Male',
        'count_total' => true
    ));
    
    // Count females
    $females = get_users(array(
        'meta_key' => 'gender',
        'meta_value' => 'Female',
        'count_total' => true
    ));
    
    echo "Male Registrations: " . count($males) . "<br>";
    echo "Female Registrations: " . count($females);
}
*/

add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );

// Remove default WooCommerce breadcrumbs
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// Remove theme's default breadcrumb for WooCommerce pages
add_action('infine_action_get_breadcrumb', 'remove_default_breadcrumb_for_woocommerce', 1);
function remove_default_breadcrumb_for_woocommerce() {
    if (is_product_category() || is_product_tag() || is_shop() || is_product() || is_post_type_archive('product')) {
        // Remove all existing breadcrumb actions
        remove_all_actions('infine_action_get_breadcrumb', 10);
    }
}

// Add WooCommerce breadcrumbs
add_action('infine_action_get_breadcrumb', 'custom_woocommerce_breadcrumb_only', 20);
function custom_woocommerce_breadcrumb_only() {
    if (is_product_category() || is_product_tag() || is_shop() || is_product() || is_post_type_archive('product')) {
        if (function_exists('woocommerce_breadcrumb')) {
            woocommerce_breadcrumb(array(
                'wrap_before' => '<div role="navigation" aria-label="Breadcrumbs" class="breadcrumb-trail breadcrumbs"><ol class="trail-items">',
                'wrap_after'  => '</ol></div>',
                'before'      => '<li class="trail-item">',
                'after'       => '</li>',
                'home'        => 'Home',
				'delimiter'   => ' ',
            ));
        }
    }
}

// Remove link from "Coming Soon" button on single product page
add_action('wp_footer', 'disable_coming_soon_button_link');
function disable_coming_soon_button_link() {
    if (is_product()) {
        global $product;
        if ($product && $product->is_type('external') && $product->get_button_text() == 'Coming Soon') {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.single_add_to_cart_button').on('click', function(e) {
                    e.preventDefault();
                    return false;
                });
                $('.single_add_to_cart_button').css({
                    'pointer-events': 'none',
                    'opacity': '0.6',
                    'cursor': 'not-allowed'
                });
            });
            </script>
            <?php
        }
    }
}

// Keep normal product link for shop/archive pages
add_filter('woocommerce_product_add_to_cart_url', 'custom_coming_soon_product_url', 10, 2);
function custom_coming_soon_product_url($url, $product) {
    if (!is_product() && $product->is_type('external') && $product->get_button_text() == 'Coming Soon') {
        return get_permalink($product->get_id());
    }
    return $url;
}


// Disable comments on posts and pages, but keep them on products
add_filter('comments_open', 'enable_comments_only_on_products', 20, 2);
add_filter('pings_open', 'enable_comments_only_on_products', 20, 2);
function enable_comments_only_on_products($open, $post_id) {
    $post_type = get_post_type($post_id);
    
    // Only allow comments/reviews on products
    if ($post_type === 'product') {
        return true;
    }
    
    // Disable for everything else
    return false;
}

// Hide comment form and existing comments on non-products
add_action('template_redirect', 'hide_comments_on_non_products');
function hide_comments_on_non_products() {
    if (!is_product() && !is_singular('product')) {
        // Remove comment display
        add_filter('comments_array', '__return_empty_array', 10, 2);
    }
}

// Remove comments from bottom of product page
remove_action('woocommerce_after_single_product_summary', 'comments_template', 10);

// Ensure reviews tab exists and is properly configured
add_filter('woocommerce_product_tabs', 'fix_reviews_tab', 98);
function fix_reviews_tab($tabs) {
    // Add/fix reviews tab
    $tabs['reviews'] = array(
        'title'    => __('Reviews', 'woocommerce'),
        'priority' => 30,
        'callback' => 'comments_template',
    );
    
    return $tabs;
}

// Remove comments section from bottom of product pages
add_action('template_redirect', 'remove_product_comments_from_bottom');
function remove_product_comments_from_bottom() {
    if (is_product()) {
        // Remove comments from the page template
        add_filter('comments_template', '__return_false', 999);
        
        // But allow in tabs
        add_filter('woocommerce_product_tabs', 'restore_reviews_in_tab', 99);
    }
}

function restore_reviews_in_tab($tabs) {
    if (isset($tabs['reviews'])) {
        $tabs['reviews']['callback'] = 'comments_template';
    }
    return $tabs;
}


// Debug: Find where comments are being called on product pages
add_action('wp_footer', 'debug_product_template');
function debug_product_template() {
    if (is_product()) {
        global $template;
        ?>
        <script>
        console.log('=== Product Page Template Debug ===');
        console.log('Current template:', '<?php echo basename($template); ?>');
        console.log('Template path:', '<?php echo $template; ?>');
        </script>
        <?php
    }
}


/*
// Generate unique registration numbers - Using the WORKING hook
add_filter('forminator_custom_form_submit_field_data', 'generate_registration_number', 10, 2);

function generate_registration_number($field_data_array, $form_id) {
    // Only for test form 13374 and live form 13233
    if (!in_array(intval($form_id), array(13233, 13374))) {
        return $field_data_array;
    }
    
    // Get gender from field data
    $gender = '';
    foreach ($field_data_array as $field) {
        if (isset($field['name']) && $field['name'] === 'select-1' && isset($field['value'])) {
            $gender = strtolower(trim($field['value']));
            break;
        }
    }
    
    if (empty($gender)) {
        return $field_data_array;
    }
    
    // Determine prefix based on gender
    if ($gender === 'male') {
        $prefix = 'DLXMP/M';
        $counter_key = 'dlxmp_male_counter';
    } elseif ($gender === 'female') {
        $prefix = 'DLXMP/F';
        $counter_key = 'dlxmp_female_counter';
    } else {
        return $field_data_array;
    }
    
    // Get and increment counter
    $counter = intval(get_option($counter_key, 0)) + 1;
    update_option($counter_key, $counter);
    
    // Generate registration number
    $reg_number = $prefix . str_pad($counter, 6, '0', STR_PAD_LEFT);
    
    // Add/update the hidden-1 field in the field data array
    $found = false;
    foreach ($field_data_array as $key => $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-1') {
            $field_data_array[$key]['value'] = $reg_number;
            $found = true;
            break;
        }
    }
    
    // If hidden-1 wasn't found, add it
    if (!$found) {
        $field_data_array[] = array(
            'name'  => 'hidden-1',
            'value' => $reg_number,
        );
    }
    
    return $field_data_array;
}

// Shortcode to display registration statistics
add_shortcode('registration_stats', 'display_registration_stats');

function display_registration_stats() {
    $male_count = intval(get_option('dlxmp_male_counter', 0));
    $female_count = intval(get_option('dlxmp_female_counter', 0));
    $total = $male_count + $female_count;
    
    $ratio = '';
    if ($total > 0) {
        $male_percent = round(($male_count / $total) * 100, 1);
        $female_percent = round(($female_count / $total) * 100, 1);
        $ratio = $male_percent . '% Men, ' . $female_percent . '% Women';
    } else {
        $ratio = 'No registrations yet';
    }
    
    $output = '<div class="registration-stats" style="padding: 20px; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd;">';
    $output .= '<h3 style="margin-top: 0;">Registration Statistics</h3>';
    $output .= '<p><strong>Total Registered:</strong> ' . $total . '</p>';
    $output .= '<p><strong>Men:</strong> ' . $male_count . '</p>';
    $output .= '<p><strong>Women:</strong> ' . $female_count . '</p>';
    $output .= '<p><strong>Ratio:</strong> ' . $ratio . '</p>';
    $output .= '</div>';
    
    return $output;
}
*/

// Generate registration number AND schedule user meta save
add_filter('forminator_custom_form_submit_field_data', 'generate_and_schedule_member_id', 10, 2);
function generate_and_schedule_member_id($field_data_array, $form_id) {
    //error_log('=== Member ID Generation Started ===');
    //error_log('Form ID: ' . $form_id);
    
    // Only for live form 13233
    if (!in_array(intval($form_id), array(13233))) {
        return $field_data_array;
    }
    
    // Get gender and email from field data
    $gender = '';
    $user_email = '';
    
    foreach ($field_data_array as $field) {
        if (isset($field['name']) && $field['name'] === 'select-1' && isset($field['value'])) {
            $gender = strtolower(trim($field['value']));
        }
        if (isset($field['name']) && $field['name'] === 'email-1' && isset($field['value'])) {
            $user_email = $field['value'];
        }
    }
    
    if (empty($gender)) {
        return $field_data_array;
    }
    
    // Determine prefix based on gender
    if ($gender === 'male') {
        $prefix = 'DLXMP/M';
        $counter_key = 'dlxmp_male_counter';
    } elseif ($gender === 'female') {
        $prefix = 'DLXMP/F';
        $counter_key = 'dlxmp_female_counter';
    } else {
        return $field_data_array;
    }
    
    // Get and increment counter
    $counter = intval(get_option($counter_key, 0)) + 1;
    update_option($counter_key, $counter);
    
    // Generate registration number
    $reg_number = $prefix . str_pad($counter, 6, '0', STR_PAD_LEFT);
    
    // Add/update the hidden-1 field in the field data array
    $found = false;
    foreach ($field_data_array as $key => $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-1') {
            $field_data_array[$key]['value'] = $reg_number;
            $found = true;
            break;
        }
    }
    
    // If hidden-1 wasn't found, add it
    if (!$found) {
        $field_data_array[] = array(
            'name'  => 'hidden-1',
            'value' => $reg_number,
        );
    }
    
    // Store email and reg_number to process later
    if ($user_email) {
        set_transient('pending_member_id_' . sanitize_email($user_email), $reg_number, 300);
    }
    
    //error_log('Member ID generated: ' . $reg_number);
    //error_log('Returning field data array');
    return $field_data_array;
}

// Process member ID after user is created (runs on every page load, but checks transient)
add_action('init', 'process_pending_member_ids');
function process_pending_member_ids() {
    global $wpdb;
    
    // Get all pending member IDs from transients
    $transients = $wpdb->get_results(
        "SELECT option_name, option_value 
        FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_pending_member_id_%'"
    );
    
    if (empty($transients)) {
        return;
    }
    
    foreach ($transients as $transient) {
        $email = str_replace('_transient_pending_member_id_', '', $transient->option_name);
        $reg_number = $transient->option_value;
        
        // Check if user exists
        $user = get_user_by('email', $email);
        
        if ($user) {
            // User exists, save member-id
            $usermeta_table = $wpdb->prefix . 'usermeta';
            
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT umeta_id FROM {$usermeta_table} WHERE user_id = %d AND meta_key = %s",
                $user->ID,
                'member-id'
            ));
            
            if ($existing) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$usermeta_table} SET meta_value = %s WHERE umeta_id = %d",
                    $reg_number,
                    $existing
                ));
            } else {
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO {$usermeta_table} (user_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                    $user->ID,
                    'member-id',
                    $reg_number
                ));
            }
            
            // Delete the transient after saving
            delete_transient('pending_member_id_' . $email);
        }
    }
}

// Shortcode to display registration statistics
add_shortcode('registration_stats', 'display_registration_stats');

function display_registration_stats() {
    $male_count = intval(get_option('dlxmp_male_counter', 0));
    $female_count = intval(get_option('dlxmp_female_counter', 0));
    $total = $male_count + $female_count;
    
    $ratio = '';
    if ($total > 0) {
        $male_percent = round(($male_count / $total) * 100, 1);
        $female_percent = round(($female_count / $total) * 100, 1);
        $ratio = $male_percent . '% Men, ' . $female_percent . '% Women';
    } else {
        $ratio = 'No registrations yet';
    }
    
    $output = '<div class="registration-stats" style="padding: 20px; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd;">';
    $output .= '<h3 style="margin-top: 0;">Registration Statistics</h3>';
    $output .= '<p><strong>Total Registered:</strong> ' . $total . '</p>';
    $output .= '<p><strong>Men:</strong> ' . $male_count . '</p>';
    $output .= '<p><strong>Women:</strong> ' . $female_count . '</p>';
    $output .= '<p><strong>Ratio:</strong> ' . $ratio . '</p>';
    $output .= '</div>';
    
    return $output;
}

// Add Registration Statistics menu under Users in WordPress admin
add_action('admin_menu', 'add_registration_stats_menu');
function add_registration_stats_menu() {
    add_users_page(
        'Registration Statistics',     // Page title
        'Registration Stats',           // Menu title
        'manage_options',              // Capability (only admins)
        'registration-statistics',     // Menu slug
        'display_registration_stats_admin_page'  // Callback function
    );
}

// Display the admin page
function display_registration_stats_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    global $wpdb;
    
    // Get registration statistics - COUNT BY MEMBER-ID PREFIX
    $all_users = get_users(array(
        'meta_query' => array(
            array(
                'key' => 'member-id',
                'compare' => 'EXISTS'
            ),
            array(
                'key' => 'member-id',
                'value' => '',
                'compare' => '!='
            )
        ),
        'fields' => 'ID'
    ));

    $male_count = 0;
    $female_count = 0;

    foreach ($all_users as $user_id) {
        $member_id = get_user_meta($user_id, 'member-id', true);
        
        // Count by Member ID prefix
        if (strpos($member_id, 'DLXMP/M') === 0) {
            $male_count++;
        } elseif (strpos($member_id, 'DLXMP/F') === 0) {
            $female_count++;
        }
    }

    $total = $male_count + $female_count;
    
    $ratio = '';
    if ($total > 0) {
        $male_percent = round(($male_count / $total) * 100, 1);
        $female_percent = round(($female_count / $total) * 100, 1);
        $ratio = $male_percent . '% Men, ' . $female_percent . '% Women';
    } else {
        $ratio = 'No registrations yet';
    }
    
    // Handle search or display all users by default
    $search_results = array();
    $search_performed = false;
    
    if (isset($_GET['search_user']) && !empty($_GET['search_user'])) {
        $search_performed = true;
        $search_term = sanitize_text_field($_GET['search_user']);
        $search_field = sanitize_text_field($_GET['search_field']);
        
        // Build the search query based on field type
        if ($search_field === 'first_name' || $search_field === 'last_name' || $search_field === 'email') {
            // WordPress native fields
            $meta_query = array(
                'relation' => 'AND',
                // Require member-id to exist
                array(
                    'key' => 'member-id',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'member-id',
                    'value' => '',
                    'compare' => '!='
                )
            );
            
            if ($search_field === 'first_name') {
                $meta_query[] = array(
                    'key' => 'first_name',
                    'value' => $search_term,
                    'compare' => 'LIKE'
                );
            } elseif ($search_field === 'last_name') {
                $meta_query[] = array(
                    'key' => 'last_name',
                    'value' => $search_term,
                    'compare' => 'LIKE'
                );
            } elseif ($search_field === 'email') {
                // Search by email in user table
                $args = array(
                    'search' => '*' . $search_term . '*',
                    'search_columns' => array('user_email'),
                    'meta_query' => $meta_query
                );
                $search_results = get_users($args);
            }
            
            if ($search_field !== 'email') {
                $args = array('meta_query' => $meta_query);
                $search_results = get_users($args);
            }
            
        } else {
            // Custom meta fields (gender, member-id)
            $meta_key = $search_field === 'gender' ? 'gender' : 'member-id';
            
            $args = array(
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => $meta_key,
                        'value' => $search_term,
                        'compare' => 'LIKE'
                    ),
                    // Require member-id to exist
                    array(
                        'key' => 'member-id',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key' => 'member-id',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            $search_results = get_users($args);
        }
        
        // Additional filter: Remove users without member-id (safety check)
        $search_results = array_filter($search_results, function($user) {
            $member_id = get_user_meta($user->ID, 'member-id', true);
            return !empty($member_id);
        });
    } else {
        // Display all users with Member ID by default
        $search_results = get_users(array(
            'meta_query' => array(
                array(
                    'key' => 'member-id',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'member-id',
                    'value' => '',
                    'compare' => '!='
                )
            ),
            'orderby' => 'registered',
            'order' => 'DESC'
        ));
    }
    
    // Always show the user list (either search results or all users)
    $show_user_list = true;
    
    ?>
    <div class="wrap">
        <h1>Registration Statistics</h1>
        
        <!-- Statistics Section -->
        <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; border-bottom: 2px solid #2271b1; padding-bottom: 10px;">Overview</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Total Registered</div>
                    <div style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo $total; ?></div>
                    <div style="font-size: 11px; color: #999; margin-top: 5px;">With Member ID</div>
                </div>
                
                <div style="padding: 15px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Men</div>
                    <div style="font-size: 32px; font-weight: bold; color: #0ea5e9;"><?php echo $male_count; ?></div>
                </div>
                
                <div style="padding: 15px; background: #fef3f2; border-left: 4px solid #ef4444; border-radius: 4px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Women</div>
                    <div style="font-size: 32px; font-weight: bold; color: #ef4444;"><?php echo $female_count; ?></div>
                </div>
                
                <div style="padding: 15px; background: #f9f5ff; border-left: 4px solid #a855f7; border-radius: 4px;">
                    <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Gender Ratio</div>
                    <div style="font-size: 18px; font-weight: bold; color: #a855f7; margin-top: 8px;"><?php echo $ratio; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Search Section -->
        <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; border-bottom: 2px solid #2271b1; padding-bottom: 10px;">
                <?php echo $search_performed ? 'Search Users' : 'All Registered Users'; ?>
            </h2>
            
            <form method="get" action="" style="margin-top: 20px;">
                <input type="hidden" name="page" value="registration-statistics" />
                
                <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="search_field" style="display: block; margin-bottom: 5px; font-weight: 600;">Search By:</label>
                        <select name="search_field" id="search_field" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="first_name" <?php selected(isset($_GET['search_field']) ? $_GET['search_field'] : '', 'first_name'); ?>>First Name</option>
                            <option value="last_name" <?php selected(isset($_GET['search_field']) ? $_GET['search_field'] : '', 'last_name'); ?>>Last Name</option>
                            <option value="email" <?php selected(isset($_GET['search_field']) ? $_GET['search_field'] : '', 'email'); ?>>Email</option>
                            <option value="gender" <?php selected(isset($_GET['search_field']) ? $_GET['search_field'] : '', 'gender'); ?>>Gender</option>
                            <option value="member-id" <?php selected(isset($_GET['search_field']) ? $_GET['search_field'] : '', 'member-id'); ?>>Member ID</option>
                        </select>
                    </div>
                    
                    <div style="flex: 2; min-width: 250px;">
                        <label for="search_user" style="display: block; margin-bottom: 5px; font-weight: 600;">Search Term:</label>
                        <input type="text" name="search_user" id="search_user" 
                               value="<?php echo isset($_GET['search_user']) ? esc_attr($_GET['search_user']) : ''; ?>" 
                               placeholder="Enter search term..." 
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
                    </div>
                    
                    <div>
                        <button type="submit" class="button button-primary" style="padding: 8px 20px; height: 38px;">
                            🔍 Search
                        </button>
                        <?php if ($search_performed): ?>
                            <a href="<?php echo admin_url('users.php?page=registration-statistics'); ?>" 
                               class="button" style="padding: 8px 20px; height: 38px; margin-left: 5px;">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <!-- User List (Search Results or All Users) -->
            <?php if ($show_user_list): ?>
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px;">
                        <?php echo $search_performed ? 'Search Results' : 'User List'; ?>
                        <span style="color: #666; font-weight: normal; font-size: 14px;">
                            (<?php echo count($search_results); ?> user<?php echo count($search_results) !== 1 ? 's' : ''; ?> <?php echo $search_performed ? 'found' : 'total'; ?>)
                        </span>
                    </h3>
                    
                    <?php if (!empty($search_results)): ?>
                        <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px;">User ID</th>
                                    <th style="padding: 10px;">Member ID</th>
                                    <th style="padding: 10px;">Name</th>
                                    <th style="padding: 10px;">Email</th>
                                    <th style="padding: 10px;">Gender</th>
                                    <th style="padding: 10px;">Registration Date</th>
                                    <th style="padding: 10px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $user): ?>
                                    <tr>
                                        <td style="padding: 10px;"><?php echo $user->ID; ?></td>
                                        <td style="padding: 10px;">
                                            <?php 
                                            $member_id = get_user_meta($user->ID, 'member-id', true);
                                            echo $member_id ? esc_html($member_id) : '-';
                                            ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <?php 
                                            $first_name = get_user_meta($user->ID, 'first_name', true);
                                            $last_name = get_user_meta($user->ID, 'last_name', true);
                                            echo esc_html($first_name . ' ' . $last_name);
                                            ?>
                                        </td>
                                        <td style="padding: 10px;"><?php echo esc_html($user->user_email); ?></td>
                                        <td style="padding: 10px;">
                                            <?php 
                                            $gender = get_user_meta($user->ID, 'gender', true);
                                            echo $gender ? esc_html($gender) : '-';
                                            ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <?php 
                                            $reg_date = get_user_meta($user->ID, 'registration-date', true);
                                            echo $reg_date ? esc_html($reg_date) : date('Y-m-d', strtotime($user->user_registered));
                                            ?>
                                        </td>
                                        <td style="padding: 10px;">
                                            <a href="<?php echo get_edit_user_link($user->ID); ?>" class="button button-small" target="_blank">
                                                Edit Profile
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                            <strong>No users found</strong>
                            <p style="margin: 10px 0 0 0;">
                                <?php echo $search_performed ? 'Try adjusting your search criteria or check if users have Member IDs assigned.' : 'No registered users with Member IDs yet.'; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .wrap h1 {
            margin-bottom: 10px;
        }
        
        .wp-list-table th {
            background: #f9f9f9;
            font-weight: 600;
        }
        
        .wp-list-table tbody tr:hover {
            background: #f5f5f5;
        }
    </style>
    <?php
}


add_filter('woocommerce_default_catalog_orderby', 'custom_default_catalog_orderby');
function custom_default_catalog_orderby() {
    return 'price-desc'; // Change this to your preferred sorting
}


// Display custom fields in user profile
add_action('show_user_profile', 'display_member_profile_fields');
add_action('edit_user_profile', 'display_member_profile_fields');

function display_member_profile_fields($user) {
    ?>
    <h2>Member Information</h2>
    
    <h3>Basic Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="member-id">Member ID</label></th>
            <td>
                <input type="text" name="member-id" id="member-id" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'member-id', true)); ?>" 
                       class="regular-text" readonly />
                <p class="description">This field is read-only</p>
            </td>
        </tr>
        <tr>
            <th><label for="registration-date">Registration Date</label></th>
            <td>
                <input type="text" name="registration-date" id="registration-date" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'registration-date', true)); ?>" 
                       class="regular-text" readonly />
                <p class="description">This field is read-only</p>
            </td>
        </tr>
        <tr>
			<th><label for="gender">Gender</label></th>
			<td>
				<input type="text" name="gender" id="gender" 
					value="<?php echo esc_attr(get_user_meta($user->ID, 'gender', true)); ?>" 
					class="regular-text" readonly />
				<p class="description">This field is read-only</p>
			</td>
		</tr>
        <tr>
            <th><label for="profile-last-updated">Profile Last Updated</label></th>
            <td>
                <input type="text" name="profile-last-updated" id="profile-last-updated" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'profile-last-updated', true)); ?>" 
                    class="regular-text" readonly />
                <p class="description">This field is read-only</p>
            </td>
        </tr>
        <tr>
            <th><label for="date-of-birth">Date of Birth</label></th>
            <td>
                <?php
                $dob = get_user_meta($user->ID, 'date-of-birth', true);
                // Convert from DD/MM/YYYY to YYYY-MM-DD for HTML5 date input
                if (!empty($dob) && strpos($dob, '/') !== false) {
                    $date_parts = explode('/', $dob);
                    if (count($date_parts) === 3) {
                        $dob = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                    }
                }
                ?>
                <input type="date" name="date-of-birth" id="date-of-birth" 
                    value="<?php echo esc_attr($dob); ?>" 
                    class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="nationality">Nationality / Place of Birth</label></th>
            <td>
                <input type="text" name="nationality" id="nationality" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'nationality', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="ethnic-or-cultural-background">Ethnic and Cultural Background</label></th>
            <td>
                <input type="text" name="ethnic-or-cultural-background" id="ethnic-or-cultural-background" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'ethnic-or-cultural-background', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="valid-passports">How many Valid Passports do you have? List all countries</label></th>
            <td>
                <input type="text" name="valid-passports" id="valid-passports" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'valid-passports', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="address-country">Country of Residence</label></th>
            <td>
                <select name="address-country" id="address-country" required>
                    <option value="">Select country</option>
                    <option value="Afghanistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Afghanistan'); ?>>Afghanistan</option>
                    <option value="Albania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Albania'); ?>>Albania</option>
                    <option value="Algeria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Algeria'); ?>>Algeria</option>
                    <option value="American Samoa" <?php selected(get_user_meta($user->ID, 'address-country', true), 'American Samoa'); ?>>American Samoa</option>
                    <option value="Andorra" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Andorra'); ?>>Andorra</option>
                    <option value="Angola" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Angola'); ?>>Angola</option>
                    <option value="Anguilla" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Anguilla'); ?>>Anguilla</option>
                    <option value="Antarctica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Antarctica'); ?>>Antarctica</option>
                    <option value="Antigua and Barbuda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Antigua and Barbuda'); ?>>Antigua and Barbuda</option>
                    <option value="Argentina" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Argentina'); ?>>Argentina</option>
                    <option value="Armenia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Armenia'); ?>>Armenia</option>
                    <option value="Aruba" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Aruba'); ?>>Aruba</option>
                    <option value="Australia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Australia'); ?>>Australia</option>
                    <option value="Austria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Austria'); ?>>Austria</option>
                    <option value="Azerbaijan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Azerbaijan'); ?>>Azerbaijan</option>
                    <option value="Bahamas" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bahamas'); ?>>Bahamas</option>
                    <option value="Bahrain" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bahrain'); ?>>Bahrain</option>
                    <option value="Bangladesh" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bangladesh'); ?>>Bangladesh</option>
                    <option value="Barbados" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Barbados'); ?>>Barbados</option>
                    <option value="Belarus" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Belarus'); ?>>Belarus</option>
                    <option value="Belgium" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Belgium'); ?>>Belgium</option>
                    <option value="Belize" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Belize'); ?>>Belize</option>
                    <option value="Benin" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Benin'); ?>>Benin</option>
                    <option value="Bermuda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bermuda'); ?>>Bermuda</option>
                    <option value="Bhutan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bhutan'); ?>>Bhutan</option>
                    <option value="Bolivia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bolivia'); ?>>Bolivia</option>
                    <option value="Bosnia and Herzegovina" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bosnia and Herzegovina'); ?>>Bosnia and Herzegovina</option>
                    <option value="Botswana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Botswana'); ?>>Botswana</option>
                    <option value="Bouvet Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bouvet Island'); ?>>Bouvet Island</option>
                    <option value="Brazil" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Brazil'); ?>>Brazil</option>
                    <option value="British Indian Ocean Territory" <?php selected(get_user_meta($user->ID, 'address-country', true), 'British Indian Ocean Territory'); ?>>British Indian Ocean Territory</option>
                    <option value="Brunei" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Brunei'); ?>>Brunei</option>
                    <option value="Bulgaria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bulgaria'); ?>>Bulgaria</option>
                    <option value="Burkina Faso" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Burkina Faso'); ?>>Burkina Faso</option>
                    <option value="Burundi" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Burundi'); ?>>Burundi</option>
                    <option value="Cabo Verde" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cabo Verde'); ?>>Cabo Verde</option>
                    <option value="Cambodia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cambodia'); ?>>Cambodia</option>
                    <option value="Cameroon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cameroon'); ?>>Cameroon</option>
                    <option value="Canada" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Canada'); ?>>Canada</option>
                    <option value="Cayman Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cayman Islands'); ?>>Cayman Islands</option>
                    <option value="Central African Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Central African Republic'); ?>>Central African Republic</option>
                    <option value="Chad" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Chad'); ?>>Chad</option>
                    <option value="Chile" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Chile'); ?>>Chile</option>
                    <option value="China, People's Republic of" <?php selected(get_user_meta($user->ID, 'address-country', true), "China, People's Republic of"); ?>>China, People's Republic of</option>
                    <option value="Christmas Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Christmas Island'); ?>>Christmas Island</option>
                    <option value="Cocos Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cocos Islands'); ?>>Cocos Islands</option>
                    <option value="Colombia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Colombia'); ?>>Colombia</option>
                    <option value="Comoros" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Comoros'); ?>>Comoros</option>
                    <option value="Congo, Democratic Republic of the" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Congo, Democratic Republic of the'); ?>>Congo, Democratic Republic of the</option>
                    <option value="Congo, Republic of the" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Congo, Republic of the'); ?>>Congo, Republic of the</option>
                    <option value="Cook Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cook Islands'); ?>>Cook Islands</option>
                    <option value="Costa Rica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Costa Rica'); ?>>Costa Rica</option>
                    <option value="Croatia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Croatia'); ?>>Croatia</option>
                    <option value="Cuba" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cuba'); ?>>Cuba</option>
                    <option value="Curaçao" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Curaçao'); ?>>Curaçao</option>
                    <option value="Cyprus" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cyprus'); ?>>Cyprus</option>
                    <option value="Czech Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Czech Republic'); ?>>Czech Republic</option>
                    <option value="Côte d'Ivoire" <?php selected(get_user_meta($user->ID, 'address-country', true), "Côte d'Ivoire"); ?>>Côte d'Ivoire</option>
                    <option value="Denmark" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Denmark'); ?>>Denmark</option>
                    <option value="Djibouti" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Djibouti'); ?>>Djibouti</option>
                    <option value="Dominica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Dominica'); ?>>Dominica</option>
                    <option value="Dominican Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Dominican Republic'); ?>>Dominican Republic</option>
                    <option value="East Timor" <?php selected(get_user_meta($user->ID, 'address-country', true), 'East Timor'); ?>>East Timor</option>
                    <option value="Ecuador" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ecuador'); ?>>Ecuador</option>
                    <option value="Egypt" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Egypt'); ?>>Egypt</option>
                    <option value="El Salvador" <?php selected(get_user_meta($user->ID, 'address-country', true), 'El Salvador'); ?>>El Salvador</option>
                    <option value="Equatorial Guinea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Equatorial Guinea'); ?>>Equatorial Guinea</option>
                    <option value="Eritrea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Eritrea'); ?>>Eritrea</option>
                    <option value="Estonia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Estonia'); ?>>Estonia</option>
                    <option value="Ethiopia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ethiopia'); ?>>Ethiopia</option>
                    <option value="Falkland Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Falkland Islands'); ?>>Falkland Islands</option>
                    <option value="Faroe Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Faroe Islands'); ?>>Faroe Islands</option>
                    <option value="Fiji" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Fiji'); ?>>Fiji</option>
                    <option value="Finland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Finland'); ?>>Finland</option>
                    <option value="France" <?php selected(get_user_meta($user->ID, 'address-country', true), 'France'); ?>>France</option>
                    <option value="France, Metropolitan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'France, Metropolitan'); ?>>France, Metropolitan</option>
                    <option value="French Guiana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'French Guiana'); ?>>French Guiana</option>
                    <option value="French Polynesia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'French Polynesia'); ?>>French Polynesia</option>
                    <option value="French South Territories" <?php selected(get_user_meta($user->ID, 'address-country', true), 'French South Territories'); ?>>French South Territories</option>
                    <option value="Gabon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Gabon'); ?>>Gabon</option>
                    <option value="Gambia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Gambia'); ?>>Gambia</option>
                    <option value="Georgia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Georgia'); ?>>Georgia</option>
                    <option value="Germany" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Germany'); ?>>Germany</option>
                    <option value="Ghana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ghana'); ?>>Ghana</option>
                    <option value="Gibraltar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Gibraltar'); ?>>Gibraltar</option>
                    <option value="Greece" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Greece'); ?>>Greece</option>
                    <option value="Greenland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Greenland'); ?>>Greenland</option>
                    <option value="Grenada" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Grenada'); ?>>Grenada</option>
                    <option value="Guadeloupe" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guadeloupe'); ?>>Guadeloupe</option>
                    <option value="Guam" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guam'); ?>>Guam</option>
                    <option value="Guatemala" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guatemala'); ?>>Guatemala</option>
                    <option value="Guernsey" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guernsey'); ?>>Guernsey</option>
                    <option value="Guinea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guinea'); ?>>Guinea</option>
                    <option value="Guinea-Bissau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guinea-Bissau'); ?>>Guinea-Bissau</option>
                    <option value="Guyana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guyana'); ?>>Guyana</option>
                    <option value="Haiti" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Haiti'); ?>>Haiti</option>
                    <option value="Heard Island And Mcdonald Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Heard Island And Mcdonald Island'); ?>>Heard Island And Mcdonald Island</option>
                    <option value="Honduras" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Honduras'); ?>>Honduras</option>
                    <option value="Hong Kong" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Hong Kong'); ?>>Hong Kong</option>
                    <option value="Hungary" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Hungary'); ?>>Hungary</option>
                    <option value="Iceland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Iceland'); ?>>Iceland</option>
                    <option value="India" <?php selected(get_user_meta($user->ID, 'address-country', true), 'India'); ?>>India</option>
                    <option value="Indonesia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Indonesia'); ?>>Indonesia</option>
                    <option value="Iran" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Iran'); ?>>Iran</option>
                    <option value="Iraq" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Iraq'); ?>>Iraq</option>
                    <option value="Ireland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ireland'); ?>>Ireland</option>
                    <option value="Israel" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Israel'); ?>>Israel</option>
                    <option value="Italy" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Italy'); ?>>Italy</option>
                    <option value="Jamaica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Jamaica'); ?>>Jamaica</option>
                    <option value="Japan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Japan'); ?>>Japan</option>
                    <option value="Jersey" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Jersey'); ?>>Jersey</option>
                    <option value="Johnston Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Johnston Island'); ?>>Johnston Island</option>
                    <option value="Jordan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Jordan'); ?>>Jordan</option>
                    <option value="Kazakhstan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kazakhstan'); ?>>Kazakhstan</option>
                    <option value="Kenya" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kenya'); ?>>Kenya</option>
                    <option value="Kiribati" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kiribati'); ?>>Kiribati</option>
                    <option value="Korea, Democratic People's Republic of" <?php selected(get_user_meta($user->ID, 'address-country', true), "Korea, Democratic People's Republic of"); ?>>Korea, Democratic People's Republic of</option>
                    <option value="Korea, Republic of" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Korea, Republic of'); ?>>Korea, Republic of</option>
                    <option value="Kosovo" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kosovo'); ?>>Kosovo</option>
                    <option value="Kuwait" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kuwait'); ?>>Kuwait</option>
                    <option value="Kyrgyzstan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kyrgyzstan'); ?>>Kyrgyzstan</option>
                    <option value="Lao People's Democratic Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), "Lao People's Democratic Republic"); ?>>Lao People's Democratic Republic</option>
                    <option value="Latvia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Latvia'); ?>>Latvia</option>
                    <option value="Lebanon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Lebanon'); ?>>Lebanon</option>
                    <option value="Lesotho" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Lesotho'); ?>>Lesotho</option>
                    <option value="Liberia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Liberia'); ?>>Liberia</option>
                    <option value="Libya" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Libya'); ?>>Libya</option>
                    <option value="Liechtenstein" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Liechtenstein'); ?>>Liechtenstein</option>
                    <option value="Lithuania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Lithuania'); ?>>Lithuania</option>
                    <option value="Luxembourg" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Luxembourg'); ?>>Luxembourg</option>
                    <option value="Macau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Macau'); ?>>Macau</option>
                    <option value="Madagascar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Madagascar'); ?>>Madagascar</option>
                    <option value="Malawi" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Malawi'); ?>>Malawi</option>
                    <option value="Malaysia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Malaysia'); ?>>Malaysia</option>
                    <option value="Maldives" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Maldives'); ?>>Maldives</option>
                    <option value="Mali" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mali'); ?>>Mali</option>
                    <option value="Malta" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Malta'); ?>>Malta</option>
                    <option value="Marshall Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Marshall Islands'); ?>>Marshall Islands</option>
                    <option value="Martinique" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Martinique'); ?>>Martinique</option>
                    <option value="Mauritania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mauritania'); ?>>Mauritania</option>
                    <option value="Mauritius" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mauritius'); ?>>Mauritius</option>
                    <option value="Mayotte" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mayotte'); ?>>Mayotte</option>
                    <option value="Mexico" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mexico'); ?>>Mexico</option>
                    <option value="Micronesia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Micronesia'); ?>>Micronesia</option>
                    <option value="Moldova" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Moldova'); ?>>Moldova</option>
                    <option value="Monaco" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Monaco'); ?>>Monaco</option>
                    <option value="Mongolia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mongolia'); ?>>Mongolia</option>
                    <option value="Montenegro" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Montenegro'); ?>>Montenegro</option>
                    <option value="Montserrat" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Montserrat'); ?>>Montserrat</option>
                    <option value="Morocco" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Morocco'); ?>>Morocco</option>
                    <option value="Mozambique" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mozambique'); ?>>Mozambique</option>
                    <option value="Myanmar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Myanmar'); ?>>Myanmar</option>
                    <option value="Namibia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Namibia'); ?>>Namibia</option>
                    <option value="Nauru" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nauru'); ?>>Nauru</option>
                    <option value="Nepal" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nepal'); ?>>Nepal</option>
                    <option value="Netherlands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Netherlands'); ?>>Netherlands</option>
                    <option value="Netherlands Antilles" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Netherlands Antilles'); ?>>Netherlands Antilles</option>
                    <option value="New Caledonia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'New Caledonia'); ?>>New Caledonia</option>
                    <option value="New Zealand" <?php selected(get_user_meta($user->ID, 'address-country', true), 'New Zealand'); ?>>New Zealand</option>
                    <option value="Nicaragua" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nicaragua'); ?>>Nicaragua</option>
                    <option value="Niger" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Niger'); ?>>Niger</option>
                    <option value="Nigeria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nigeria'); ?>>Nigeria</option>
                    <option value="Niue" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Niue'); ?>>Niue</option>
                    <option value="Norfolk Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Norfolk Island'); ?>>Norfolk Island</option>
                    <option value="North Macedonia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'North Macedonia'); ?>>North Macedonia</option>
                    <option value="Northern Mariana Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Northern Mariana Islands'); ?>>Northern Mariana Islands</option>
                    <option value="Norway" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Norway'); ?>>Norway</option>
                    <option value="Oman" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Oman'); ?>>Oman</option>
                    <option value="Pakistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Pakistan'); ?>>Pakistan</option>
                    <option value="Palau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Palau'); ?>>Palau</option>
                    <option value="Palestine, State of" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Palestine, State of'); ?>>Palestine, State of</option>
                    <option value="Panama" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Panama'); ?>>Panama</option>
                    <option value="Papua New Guinea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Papua New Guinea'); ?>>Papua New Guinea</option>
                    <option value="Paraguay" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Paraguay'); ?>>Paraguay</option>
                    <option value="Peru" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Peru'); ?>>Peru</option>
                    <option value="Philippines" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Philippines'); ?>>Philippines</option>
                    <option value="Pitcairn Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Pitcairn Islands'); ?>>Pitcairn Islands</option>
                    <option value="Poland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Poland'); ?>>Poland</option>
                    <option value="Portugal" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Portugal'); ?>>Portugal</option>
                    <option value="Puerto Rico" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Puerto Rico'); ?>>Puerto Rico</option>
                    <option value="Qatar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Qatar'); ?>>Qatar</option>
                    <option value="Reunion Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Reunion Island'); ?>>Reunion Island</option>
                    <option value="Romania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Romania'); ?>>Romania</option>
                    <option value="Russia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Russia'); ?>>Russia</option>
                    <option value="Rwanda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Rwanda'); ?>>Rwanda</option>
                    <option value="Saint Helena" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Helena'); ?>>Saint Helena</option>
                    <option value="Saint Kitts and Nevis" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Kitts and Nevis'); ?>>Saint Kitts and Nevis</option>
                    <option value="Saint Lucia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Lucia'); ?>>Saint Lucia</option>
                    <option value="Saint Pierre & Miquelon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Pierre & Miquelon'); ?>>Saint Pierre & Miquelon</option>
                    <option value="Saint Vincent and the Grenadines" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Vincent and the Grenadines'); ?>>Saint Vincent and the Grenadines</option>
                    <option value="Samoa" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Samoa'); ?>>Samoa</option>
                    <option value="San Marino" <?php selected(get_user_meta($user->ID, 'address-country', true), 'San Marino'); ?>>San Marino</option>
                    <option value="Sao Tome and Principe" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sao Tome and Principe'); ?>>Sao Tome and Principe</option>
                    <option value="Saudi Arabia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saudi Arabia'); ?>>Saudi Arabia</option>
                    <option value="Senegal" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Senegal'); ?>>Senegal</option>
                    <option value="Serbia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Serbia'); ?>>Serbia</option>
                    <option value="Seychelles" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Seychelles'); ?>>Seychelles</option>
                    <option value="Sierra Leone" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sierra Leone'); ?>>Sierra Leone</option>
                    <option value="Singapore" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Singapore'); ?>>Singapore</option>
                    <option value="Sint Maarten" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sint Maarten'); ?>>Sint Maarten</option>
                    <option value="Slovakia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Slovakia'); ?>>Slovakia</option>
                    <option value="Slovenia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Slovenia'); ?>>Slovenia</option>
                    <option value="Solomon Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Solomon Islands'); ?>>Solomon Islands</option>
                    <option value="Somalia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Somalia'); ?>>Somalia</option>
                    <option value="South Africa" <?php selected(get_user_meta($user->ID, 'address-country', true), 'South Africa'); ?>>South Africa</option>
                    <option value="South Georgia and South Sandwich" <?php selected(get_user_meta($user->ID, 'address-country', true), 'South Georgia and South Sandwich'); ?>>South Georgia and South Sandwich</option>
                    <option value="Spain" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Spain'); ?>>Spain</option>
                    <option value="Sri Lanka" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sri Lanka'); ?>>Sri Lanka</option>
                    <option value="Stateless Persons" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Stateless Persons'); ?>>Stateless Persons</option>
                    <option value="Sudan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sudan'); ?>>Sudan</option>
                    <option value="Sudan, South" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sudan, South'); ?>>Sudan, South</option>
                    <option value="Suriname" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Suriname'); ?>>Suriname</option>
                    <option value="Svalbard and Jan Mayen" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Svalbard and Jan Mayen'); ?>>Svalbard and Jan Mayen</option>
                    <option value="Swaziland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Swaziland'); ?>>Swaziland</option>
                    <option value="Sweden" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sweden'); ?>>Sweden</option>
                    <option value="Switzerland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Switzerland'); ?>>Switzerland</option>
                    <option value="Syria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Syria'); ?>>Syria</option>
                    <option value="Taiwan, Republic of China" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Taiwan, Republic of China'); ?>>Taiwan, Republic of China</option>
                    <option value="Tajikistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tajikistan'); ?>>Tajikistan</option>
                    <option value="Tanzania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tanzania'); ?>>Tanzania</option>
                    <option value="Thailand" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Thailand'); ?>>Thailand</option>
                    <option value="Togo" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Togo'); ?>>Togo</option>
                    <option value="Tokelau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tokelau'); ?>>Tokelau</option>
                    <option value="Tonga" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tonga'); ?>>Tonga</option>
                    <option value="Trinidad and Tobago" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Trinidad and Tobago'); ?>>Trinidad and Tobago</option>
                    <option value="Tunisia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tunisia'); ?>>Tunisia</option>
                    <option value="Turkey" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Turkey'); ?>>Turkey</option>
                    <option value="Turkmenistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Turkmenistan'); ?>>Turkmenistan</option>
                    <option value="Turks And Caicos Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Turks And Caicos Islands'); ?>>Turks And Caicos Islands</option>
                    <option value="Tuvalu" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tuvalu'); ?>>Tuvalu</option>
                    <option value="US Minor Outlying Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'US Minor Outlying Islands'); ?>>US Minor Outlying Islands</option>
                    <option value="Uganda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Uganda'); ?>>Uganda</option>
                    <option value="Ukraine" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ukraine'); ?>>Ukraine</option>
                    <option value="United Arab Emirates" <?php selected(get_user_meta($user->ID, 'address-country', true), 'United Arab Emirates'); ?>>United Arab Emirates</option>
                    <option value="United Kingdom" <?php selected(get_user_meta($user->ID, 'address-country', true), 'United Kingdom'); ?>>United Kingdom</option>
                    <option value="United States of America (USA)" <?php selected(get_user_meta($user->ID, 'address-country', true), 'United States of America (USA)'); ?>>United States of America (USA)</option>
                    <option value="Uruguay" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Uruguay'); ?>>Uruguay</option>
                    <option value="Uzbekistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Uzbekistan'); ?>>Uzbekistan</option>
                    <option value="Vanuatu" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Vanuatu'); ?>>Vanuatu</option>
                    <option value="Vatican City" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Vatican City'); ?>>Vatican City</option>
                    <option value="Venezuela" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Venezuela'); ?>>Venezuela</option>
                    <option value="Vietnam" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Vietnam'); ?>>Vietnam</option>
                    <option value="Virgin Islands, British" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Virgin Islands, British'); ?>>Virgin Islands, British</option>
                    <option value="Virgin Islands, U.S." <?php selected(get_user_meta($user->ID, 'address-country', true), 'Virgin Islands, U.S.'); ?>>Virgin Islands, U.S.</option>
                    <option value="Wallis And Futuna Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Wallis And Futuna Islands'); ?>>Wallis And Futuna Islands</option>
                    <option value="Western Sahara" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Western Sahara'); ?>>Western Sahara</option>
                    <option value="Yemen" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Yemen'); ?>>Yemen</option>
                    <option value="Zambia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Zambia'); ?>>Zambia</option>
                    <option value="Zimbabwe" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Zimbabwe'); ?>>Zimbabwe</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="address-city">City</label></th>
            <td>
                <input type="text" name="address-city" id="address-city" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'address-city', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="how-long-lived-in-your-country">How long have you lived in your country of residence?</label></th>
            <td>
                <input type="text" name="how-long-lived-in-your-country" id="how-long-lived-in-your-country" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'how-long-lived-in-your-country', true)); ?>" 
                    class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="height">Height (in feet and inches)</label></th>
            <td>
                <input type="text" name="height" id="height" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'height', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
			<th><label for="body-type">Body Type</label></th>
			<td>
				<select name="body-type" id="body-type">
					<option value="">Select Body Type</option>
					<option value="Slender" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Slender'); ?>>Slender</option>
					<option value="Slim" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Slim'); ?>>Slim</option>
					<option value="Average" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Average'); ?>>Average</option>
					<option value="Athletic" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Athletic'); ?>>Athletic</option>
					<option value="Well-Built" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Well-Built'); ?>>Well-Built</option>
					<option value="Overweight" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Overweight'); ?>>Overweight</option>
				</select>
			</td>
		</tr>
        <tr>
            <th><label for="smoker">Smoker</label></th>
            <td>
                <select name="smoker" id="smoker" required>
                    <option value="">Select</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'smoker', true), 'No'); ?>>No</option>
                    <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'smoker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                    <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'smoker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                    <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'smoker', true), 'Yes - Social'); ?>>Yes - Social</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="drinker">Drinker</label></th>
            <td>
                <select name="drinker" id="drinker" required>
                    <option value="">Select</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'drinker', true), 'No'); ?>>No</option>
                    <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'drinker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                    <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'drinker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                    <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'drinker', true), 'Yes - Social'); ?>>Yes - Social</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="health">Health</label></th>
            <td>
                <select name="health" id="health" required>
                    <option value="">Select</option>
                    <option value="Very Good" <?php selected(get_user_meta($user->ID, 'health', true), 'Very Good'); ?>>Very Good</option>
                    <option value="Good" <?php selected(get_user_meta($user->ID, 'health', true), 'Good'); ?>>Good</option>
                    <option value="Average" <?php selected(get_user_meta($user->ID, 'health', true), 'Average'); ?>>Average</option>
                    <option value="Poor" <?php selected(get_user_meta($user->ID, 'health', true), 'Poor'); ?>>Poor</option>
                </select>
            </td>
        </tr>
    </table>

    <h3>Family & Relationships</h3>
    <table class="form-table">
        <tr>
            <th><label for="marital-status">Marital Status</label></th>
            <td>
                <select name="marital-status" id="marital-status" required>
                    <option value="">Select Marital Status</option>
                    <option value="Single/Never Married" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Single/Never Married'); ?>>Single/Never Married</option>
                    <option value="Single Parent/Never Married" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Single Parent/Never Married'); ?>>Single Parent/Never Married</option>
                    <option value="Married/Separated - Processing Divorce" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Married/Separated - Processing Divorce'); ?>>Married/Separated - Processing Divorce</option>
                    <option value="Divorced" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Divorced'); ?>>Divorced</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="number-of-children">Number of Children</label></th>
            <td>
                <input type="text" name="number-of-children" id="number-of-children" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'number-of-children', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="children-ages">Children Ages</label></th>
            <td>
                <input type="text" name="children-ages" id="children-ages" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'children-ages', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="do-you-want-children">Do you want children?</label></th>
            <td>
                <select name="do-you-want-children" id="do-you-want-children">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected(get_user_meta($user->ID, 'do-you-want-children', true), 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'do-you-want-children', true), 'No'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="how-many-children-do-you-want">How many children do you want?</label></th>
            <td>
                <input type="text" name="how-many-children-do-you-want" id="how-many-children-do-you-want" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'how-many-children-do-you-want', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
    </table>

    <h3>Faith & Religion</h3>
    <table class="form-table">
        <tr>
            <th><label for="faith-or-religion">Faith or Religion</label></th>
            <td>
                <input type="text" name="faith-or-religion" id="faith-or-religion" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'faith-or-religion', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="practising-level">Practising Level</label></th>
            <td>
                <select name="practising-level" id="practising-level">
                    <option value="">Select Practising Level</option>
                    <option value="Believer" <?php selected(get_user_meta($user->ID, 'practising-level', true), 'Believer'); ?>>Believer</option>
                    <option value="Attend Church" <?php selected(get_user_meta($user->ID, 'practising-level', true), 'Attend Church'); ?>>Attend Church</option>
                    <option value="Devout" <?php selected(get_user_meta($user->ID, 'practising-level', true), 'Devout'); ?>>Devout</option>
                </select>
            </td>
        </tr>
    </table>

    <h3>Education & Career</h3>
    <table class="form-table">
        <tr>
            <th><label for="highest-level-of-education">Highest Level of Education</label></th>
            <td>
                <input type="text" name="highest-level-of-education" id="highest-level-of-education" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'highest-level-of-education', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="field-of-study">Field of Study</label></th>
            <td>
                <input type="text" name="field-of-study" id="field-of-study" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'field-of-study', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="current-occupation-or-business">Current Occupation or Business</label></th>
            <td>
                <input type="text" name="current-occupation-or-business" id="current-occupation-or-business" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'current-occupation-or-business', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="duration-in-current-occupation-business">Duration in Current Occupation/Business</label></th>
            <td>
                <input type="text" name="duration-in-current-occupation-business" id="duration-in-current-occupation-business" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'duration-in-current-occupation-business', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
    </table>

    <h3>Financial Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="approximate-income-range-or-business-details">Approximate Income Range or Business Details</label></th>
            <td>
                <input type="text" name="approximate-income-range-or-business-details" id="approximate-income-range-or-business-details" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'approximate-income-range-or-business-details', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="property-ownership">Property Ownership</label></th>
            <td>
                <select name="property-ownership" id="property-ownership">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected(get_user_meta($user->ID, 'property-ownership', true), 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'property-ownership', true), 'No'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="car-ownership">Car Ownership</label></th>
            <td>
                <select name="car-ownership" id="car-ownership">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected(get_user_meta($user->ID, 'car-ownership', true), 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'car-ownership', true), 'No'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="savings">Savings</label></th>
            <td>
                <select name="savings" id="savings">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected(get_user_meta($user->ID, 'savings', true), 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'savings', true), 'No'); ?>>No</option>
                </select>
            </td>
        </tr>
    </table>

    <h3>Personal Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="languages-spoken-fluently">Languages Spoken Fluently</label></th>
            <td>
                <input type="text" name="languages-spoken-fluently" id="languages-spoken-fluently" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'languages-spoken-fluently', true)); ?>" 
                       class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="personality-description">Personality Description</label></th>
            <td>
                <input type="text" name="personality-description" id="personality-description" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'personality-description', true)); ?>" 
                       class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="lifestyle-description">Lifestyle Description</label></th>
            <td>
                <input type="text" name="lifestyle-description" id="lifestyle-description" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'lifestyle-description', true)); ?>" 
                       class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="hobbies-interest-passions">Hobbies, Interests & Passions</label></th>
            <td>
                <input type="text" name="hobbies-interest-passions" id="hobbies-interest-passions" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'hobbies-interest-passions', true)); ?>" 
                       class="regular-text" required />
            </td>
        </tr>
    </table>

    <h3>Relationship Experience</h3>
    <table class="form-table">
        <tr>
            <th><label for="relationship-or-marriage-experience">How many years was your longest relationship or years in Marriage</label></th>
            <td>
                <input type="text" name="relationship-or-marriage-experience" id="relationship-or-marriage-experience" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'relationship-or-marriage-experience', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="lessons-learned-from-past-relationships">Lessons Learned from Past Relationships</label></th>
            <td>
                <input type="text" name="lessons-learned-from-past-relationships" id="lessons-learned-from-past-relationships" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'lessons-learned-from-past-relationships', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
    </table>

    <h3>Partner Preferences</h3>
    <table class="form-table">
        <tr>
            <th><label for="happy-to-relocate">Happy to Relocate</label></th>
            <td>
                <select name="happy-to-relocate" id="happy-to-relocate" required>
                    <option value="">Select</option>
                    <option value="Yes" <?php selected(get_user_meta($user->ID, 'happy-to-relocate', true), 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'happy-to-relocate', true), 'No'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="write-the-countries-you-are-happy-to-relocate-to">Write the Countries You Are Happy to Relocate To</label></th>
            <td>
                <input type="text" name="write-the-countries-you-are-happy-to-relocate-to" id="write-the-countries-you-are-happy-to-relocate-to" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'write-the-countries-you-are-happy-to-relocate-to', true)); ?>" 
                    class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="age-range-preferred-from">Age Range Preferred From</label></th>
            <td>
                <input type="text" name="age-range-preferred-from" id="age-range-preferred-from" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'age-range-preferred-from', true)); ?>" 
                    class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="age-range-preferred-to">Age Range Preferred To</label></th>
            <td>
                <input type="text" name="age-range-preferred-to" id="age-range-preferred-to" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'age-range-preferred-to', true)); ?>" 
                    class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="height-range-preferred-from">Height Range Preferred From (in feet and inches)</label></th>
            <td>
                <input type="text" name="height-range-preferred-from" id="height-range-preferred-from" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'height-range-preferred-from', true)); ?>" 
                    class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="height-range-preferred-to">Height Range Preferred To (in feet and inches)</label></th>
            <td>
                <input type="text" name="height-range-preferred-to" id="height-range-preferred-to" 
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'height-range-preferred-to', true)); ?>" 
                    class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="accept-smoker">Will you accept a Smoker</label></th>
            <td>
                <select name="accept-smoker" id="accept-smoker" required>
                    <option value="">Select</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'No'); ?>>No</option>
                    <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                    <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                    <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'Yes - Social'); ?>>Yes - Social</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="accept-drinker">Will you accept a Drinker</label></th>
            <td>
                <select name="accept-drinker" id="accept-drinker" required>
                    <option value="">Select</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'No'); ?>>No</option>
                    <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                    <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                    <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'Yes - Social'); ?>>Yes - Social</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="health-preferred">Health Preferred</label></th>
            <td>
                <select name="health-preferred" id="health-preferred" required>
                    <option value="">Select</option>
                    <option value="Very Good" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Very Good'); ?>>Very Good</option>
                    <option value="Good" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Good'); ?>>Good</option>
                    <option value="Average" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Average'); ?>>Average</option>
                    <option value="Poor" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Poor'); ?>>Poor</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="important-values-in-future-spouse">Important Values in Future Spouse</label></th>
            <td>
                <input type="text" name="important-values-in-future-spouse" id="important-values-in-future-spouse" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'important-values-in-future-spouse', true)); ?>" 
                       class="regular-text" required />
            </td>
        </tr>
        <tr>
            <th><label for="description-of-future-spouse">Description of Future Spouse</label></th>
            <td>
                <textarea name="description-of-future-spouse" id="description-of-future-spouse" 
                          rows="5" cols="50" class="large-text" required><?php echo esc_textarea(get_user_meta($user->ID, 'description-of-future-spouse', true)); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="willing-to-entertain-people">Willing to Entertain People with Different Cultures and Faiths</label></th>
            <td>
                <select name="willing-to-entertain-people" id="willing-to-entertain-people" required>
                    <option value="">Select</option>
                    <option value="Yes" <?php selected(get_user_meta($user->ID, 'willing-to-entertain-people', true), 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected(get_user_meta($user->ID, 'willing-to-entertain-people', true), 'No'); ?>>No</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="conflict-handling-approach">Conflict Handling Approach</label></th>
            <td>
                <textarea name="conflict-handling-approach" id="conflict-handling-approach" 
                          rows="5" cols="50" class="large-text" required><?php echo esc_textarea(get_user_meta($user->ID, 'conflict-handling-approach', true)); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="successful-relationship-definition">Successful Relationship Definition</label></th>
            <td>
                <textarea name="successful-relationship-definition" id="successful-relationship-definition" 
                          rows="5" cols="50" class="large-text" required><?php echo esc_textarea(get_user_meta($user->ID, 'successful-relationship-definition', true)); ?></textarea>
            </td>
        </tr>
    </table>

    <h3>Additional Information / Photos</h3>
    <table class="form-table">
        <!-- PHOTOS SECTION -->
        <tr>
            <th><label for="upload-photos">Photos <span class="description"></span></label></th>
            <td>
                <?php
                $photos = get_user_meta($user->ID, 'upload-photos', true);
                
                if (!empty($photos)) {
                    preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $photos, $matches);
                    $photo_urls = isset($matches[1]) ? $matches[1] : array();
                    
                    if (!empty($photo_urls)) {
                        echo '<div style="margin-bottom: 15px;"><strong>Current Photos (' . count($photo_urls) . '/6):</strong></div>';
                        echo '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">';
                        foreach ($photo_urls as $url) {
                            $filename = basename($url);
                            $secure_url = get_secure_file_url($url); // Convert to secure URL
                            
                            echo '<div style="border: 1px solid #ddd; border-radius: 3px; padding: 5px; background: #f9f9f9; position: relative;">';
                            echo '<a href="' . esc_url($secure_url) . '" target="_blank" rel="noreferrer">';
                            echo '<img src="' . esc_url($secure_url) . '" style="width: 100%; height: 100px; object-fit: cover; display: block; margin-bottom: 5px;" />';
                            echo '</a>';
                            echo '<div style="font-size: 11px; color: #666; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 5px;" title="' . esc_attr($filename) . '">' . esc_html($filename) . '</div>';
                            echo '<label style="display: flex; align-items: center; font-size: 11px; cursor: pointer;">';
                            echo '<input type="checkbox" name="admin-delete-photos[]" value="' . esc_attr($url) . '" style="margin-right: 3px;" />';
                            echo 'Delete';
                            echo '</label>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '<p class="description">Check the boxes and click "Update User" to delete photos.</p>';
                    }
                } else {
                    echo '<p class="description" style="color: #d63638; font-weight: 500;">No photos uploaded yet. Please upload at least one photo below.</p>';
                }
                
                $current_photo_count = isset($photo_urls) ? count($photo_urls) : 0;
                if ($current_photo_count < 6) {
                    $remaining = 6 - $current_photo_count;
                    echo '<div style="margin-top: 15px; background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
                    echo '<label for="admin-new-photos" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0073aa;"><strong>Upload New Photos</strong></label>';
                    echo '<p style="margin: 0 0 10px 0; font-size: 13px;">You can upload up to <strong>' . $remaining . '</strong> more photo(s)</p>';
                    echo '<input type="file" name="admin-new-photos[]" id="admin-new-photos" accept="image/jpeg,image/jpg,image/png,image/gif" multiple />';
                    echo '<p class="description" style="margin-top: 8px;">Accepted formats: JPG, JPEG, PNG, GIF. Max size: 3MB per photo. You can select multiple photos at once (maximum ' . $remaining . ').</p>';
                    echo '</div>';
                } else {
                    echo '<p class="description" style="color: #856404; margin-top: 10px; background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;">Maximum of 6 photos reached. Delete existing photos to upload new ones.</p>';
                }
                ?>
            </td>
        </tr>

        <!-- DOCUMENTS SECTION -->
        <tr>
            <th><label for="upload-documents">Supporting Documents <span class="description">(maximum 6 documents)</span></label></th>
            <td>
                <?php
                $documents = get_user_meta($user->ID, 'upload-documents', true);
                
                if (!empty($documents)) {
                    preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $documents, $matches);
                    $document_urls = isset($matches[1]) ? $matches[1] : array();
                    
                    if (!empty($document_urls)) {
                        echo '<div style="margin-bottom: 15px;"><strong>Current Documents (' . count($document_urls) . '/6):</strong></div>';
                        echo '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">';
                        foreach ($document_urls as $url) {
                            $filename = basename($url);
                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $secure_url = get_secure_file_url($url); // Convert to secure URL
                            
                            // Determine icon and color
                            $icon = '📄';
                            $bg_color = '#e8f4f8';
                            if ($extension === 'pdf') {
                                $icon = '📕';
                                $bg_color = '#ffe8e8';
                            } elseif (in_array($extension, array('doc', 'docx'))) {
                                $icon = '📘';
                                $bg_color = '#e8f0ff';
                            } elseif (in_array($extension, array('ppt', 'pptx'))) {
                                $icon = '📙';
                                $bg_color = '#fff4e8';
                            } elseif (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                                $icon = '🖼️';
                                $bg_color = '#f0ffe8';
                            }
                            
                            echo '<div style="border: 1px solid #ddd; border-radius: 3px; padding: 5px; background: #f9f9f9; position: relative;">';
                            echo '<a href="' . esc_url($secure_url) . '" target="_blank" rel="noreferrer">';
                            
                            // Show preview for images, icon for documents
                            if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                                echo '<img src="' . esc_url($secure_url) . '" style="width: 100%; height: 100px; object-fit: cover; display: block; margin-bottom: 5px;" />';
                            } else {
                                echo '<div style="width: 100%; height: 100px; display: flex; align-items: center; justify-content: center; background: ' . $bg_color . '; font-size: 40px; margin-bottom: 5px;">' . $icon . '</div>';
                            }
                            
                            echo '</a>';
                            echo '<div style="font-size: 11px; color: #666; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 5px;" title="' . esc_attr($filename) . '">' . esc_html($filename) . '</div>';
                            echo '<label style="display: flex; align-items: center; font-size: 11px; cursor: pointer;">';
                            echo '<input type="checkbox" name="admin-delete-documents[]" value="' . esc_attr($url) . '" style="margin-right: 3px;" />';
                            echo 'Delete';
                            echo '</label>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '<p class="description">Check the boxes and click "Update User" to delete documents.</p>';
                    }
                } else {
                    echo '<p class="description" style="color: #666;">No documents uploaded yet.</p>';
                }
                
                $current_doc_count = isset($document_urls) ? count($document_urls) : 0;
                if ($current_doc_count < 6) {
                    $remaining_docs = 6 - $current_doc_count;
                    echo '<div style="margin-top: 15px; background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
                    echo '<label for="admin-new-documents" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0073aa;"><strong>Upload New Documents</strong></label>';
                    echo '<p style="margin: 0 0 10px 0; font-size: 13px;">You can upload up to <strong>' . $remaining_docs . '</strong> more document(s)</p>';
                    echo '<input type="file" name="admin-new-documents[]" id="admin-new-documents" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.ppt,.pptx" multiple />';
                    echo '<p class="description" style="margin-top: 8px;">Accepted formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, PPT, PPTX. Max size: 5MB per document. You can select multiple documents at once (maximum ' . $remaining_docs . ').</p>';
                    echo '</div>';
                } else {
                    echo '<p class="description" style="color: #856404; margin-top: 10px; background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;">Maximum of 6 documents reached. Delete existing documents to upload new ones.</p>';
                }
                ?>
            </td>
        </tr>

        <tr>
            <th><label for="social-media-and-professional-platform">Social Media & Professional Platform</label></th>
            <td>
                <textarea name="social-media-and-professional-platform" id="social-media-and-professional-platform" 
                          rows="3" cols="50" class="large-text"><?php echo esc_textarea(get_user_meta($user->ID, 'social-media-and-professional-platform', true)); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="how-did-you-learn-about-the-supporting-marriage-platform">How Did You Learn About Dr London and the Supporting Marriage Platform?</label></th>
            <td>
                <select name="how-did-you-learn-about-the-supporting-marriage-platform" id="how-did-you-learn-about-the-supporting-marriage-platform">
                    <option value="">Select</option>
                    <option value="Social Media" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Social Media'); ?>>Social Media</option>
                    <option value="Referral" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Referral'); ?>>Referral</option>
                    <option value="Advertisment" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Advertisment'); ?>>Advertisment</option>
                    <option value="Already following Dr London" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Already following Dr London'); ?>>Already following Dr London</option>
                </select>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        // Make the form support file uploads
        $('form#your-profile').attr('enctype', 'multipart/form-data');
    });
    </script>
    <?php
}

// Custom upload directory for member photos (admin upload)
function admin_custom_member_photos_upload_dir($upload) {
    // Get user ID from POST data
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($user_id > 0) {
        // Create custom subdirectory: /wp-content/uploads/member-photos/USER_ID/
        $upload['subdir'] = '/member-photos/' . $user_id;
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        
        // Create directory if it doesn't exist
        if (!file_exists($upload['path'])) {
            wp_mkdir_p($upload['path']);
        }
    }
    
    return $upload;
}

/*
// Validate admin profile updates
add_action('user_profile_update_errors', 'validate_admin_member_profile_fields', 10, 3);
function validate_admin_member_profile_fields($errors, $update, $user) {
    if (!$update) {
        return;
    }
    
    // Define required fields (same as before)
    $required_fields = array(
        'date-of-birth' => 'Date of Birth',
        'nationality' => 'Nationality / Place of Birth',
        'ethnic-or-cultural-background' => 'Ethnic and Cultural Background',
        'valid-passports' => 'How many Valid Passports do you have? List all countries',
        'address-street' => 'Address',
        'address-city' => 'City',
        'address-country' => 'Country of Residence',
        'height' => 'Height',
        'smoker' => 'Smoker',
        'drinker' => 'Drinker',
        'health' => 'Health',
        'marital-status' => 'Marital Status',
        'languages-spoken-fluently' => 'Languages Spoken Fluently',
        'personality-description' => 'Personality Description',
        'lifestyle-description' => 'Lifestyle Description',
        'hobbies-interest-passions' => 'Hobbies, Interests & Passions',
        'happy-to-relocate' => 'Happy to Relocate',
        'age-range-preferred-from' => 'Age Range Preferred From',
        'age-range-preferred-to' => 'Age Range Preferred To',
        'height-range-preferred-from' => 'Height Range Preferred From (in feet and inches)',
        'height-range-preferred-to' => 'Height Range Preferred To (in feet and inches)',
        'important-values-in-future-spouse' => 'Important Values in Future Spouse',
        'description-of-future-spouse' => 'Description of Future Spouse',
        //'willing-to-entertain-people' => 'Willing to Entertain People',
        'conflict-handling-approach' => 'Conflict Handling Approach',
        'successful-relationship-definition' => 'Successful Relationship Definition'
    );
    
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors->add('required_field_missing', '<strong>ERROR</strong>: ' . $label . ' is required.');
        }
    }
    
    if (isset($_POST['happy-to-relocate']) && $_POST['happy-to-relocate'] === 'Yes') {
        if (empty($_POST['write-the-countries-you-are-happy-to-relocate-to']) || trim($_POST['write-the-countries-you-are-happy-to-relocate-to']) === '') {
            $errors->add('required_field_missing', '<strong>ERROR</strong>: Countries You Are Happy to Relocate To is required when Happy to Relocate is Yes.');
        }
    }
    
    // Validate PHOTOS
    $existing_photos = get_user_meta($user->ID, 'upload-photos', true);
    $existing_photo_urls = array();
    
    if (!empty($existing_photos)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_photos, $matches);
        $existing_photo_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    $new_photo_uploads = 0;
    if (!empty($_FILES['admin-new-photos']['name'][0])) {
        foreach ($_FILES['admin-new-photos']['name'] as $name) {
            if (!empty($name)) {
                $new_photo_uploads++;
            }
        }
    }
    
    $photos_to_delete = isset($_POST['admin-delete-photos']) ? $_POST['admin-delete-photos'] : array();
    $remaining_photos = count($existing_photo_urls) - count($photos_to_delete) + $new_photo_uploads;
    
    if ($remaining_photos < 1) {
        $errors->add('photo_required', '<strong>ERROR</strong>: At least one photo is required. Cannot delete all photos without uploading new ones.');
    }
    
    if ($remaining_photos > 6) {
        $errors->add('photo_limit', '<strong>ERROR</strong>: Cannot have more than 6 photos total.');
    }
    
    // Validate DOCUMENTS
    $existing_documents = get_user_meta($user->ID, 'upload-documents', true);
    $existing_document_urls = array();
    
    if (!empty($existing_documents)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_documents, $matches);
        $existing_document_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    $new_doc_uploads = 0;
    if (!empty($_FILES['admin-new-documents']['name'][0])) {
        foreach ($_FILES['admin-new-documents']['name'] as $name) {
            if (!empty($name)) {
                $new_doc_uploads++;
            }
        }
    }
    
    $documents_to_delete = isset($_POST['admin-delete-documents']) ? $_POST['admin-delete-documents'] : array();
    $remaining_documents = count($existing_document_urls) - count($documents_to_delete) + $new_doc_uploads;
    
    if ($remaining_documents > 6) {
        $errors->add('document_limit', '<strong>ERROR</strong>: Cannot have more than 6 documents total.');
    }
}
*/

// Validate admin profile updates
add_action('user_profile_update_errors', 'validate_admin_member_profile_fields', 10, 3);
function validate_admin_member_profile_fields($errors, $update, $user) {
    if (!$update) {
        return;
    }
    
    // Validate PHOTOS - only check maximum limit
    $existing_photos = get_user_meta($user->ID, 'upload-photos', true);
    $existing_photo_urls = array();
    
    if (!empty($existing_photos)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_photos, $matches);
        $existing_photo_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    $new_photo_uploads = 0;
    if (!empty($_FILES['admin-new-photos']['name'][0])) {
        foreach ($_FILES['admin-new-photos']['name'] as $name) {
            if (!empty($name)) {
                $new_photo_uploads++;
            }
        }
    }
    
    $photos_to_delete = isset($_POST['admin-delete-photos']) ? $_POST['admin-delete-photos'] : array();
    $remaining_photos = count($existing_photo_urls) - count($photos_to_delete) + $new_photo_uploads;
    
    if ($remaining_photos > 6) {
        $errors->add('photo_limit', '<strong>ERROR</strong>: Cannot have more than 6 photos total.');
    }
    
    // Validate DOCUMENTS - only check maximum limit
    $existing_documents = get_user_meta($user->ID, 'upload-documents', true);
    $existing_document_urls = array();
    
    if (!empty($existing_documents)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_documents, $matches);
        $existing_document_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    $new_doc_uploads = 0;
    if (!empty($_FILES['admin-new-documents']['name'][0])) {
        foreach ($_FILES['admin-new-documents']['name'] as $name) {
            if (!empty($name)) {
                $new_doc_uploads++;
            }
        }
    }
    
    $documents_to_delete = isset($_POST['admin-delete-documents']) ? $_POST['admin-delete-documents'] : array();
    $remaining_documents = count($existing_document_urls) - count($documents_to_delete) + $new_doc_uploads;
    
    if ($remaining_documents > 6) {
        $errors->add('document_limit', '<strong>ERROR</strong>: Cannot have more than 6 documents total.');
    }
}

// Save custom fields
add_action('personal_options_update', 'save_member_profile_fields');
add_action('edit_user_profile_update', 'save_member_profile_fields');

function save_member_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    // HANDLE PHOTOS (existing code)
    $photos = get_user_meta($user_id, 'upload-photos', true);
    $existing_photo_urls = array();
    
    if (!empty($photos)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $photos, $matches);
        $existing_photo_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    if (isset($_POST['admin-delete-photos']) && is_array($_POST['admin-delete-photos'])) {
        foreach ($_POST['admin-delete-photos'] as $photo_to_delete) {
            $key = array_search($photo_to_delete, $existing_photo_urls);
            if ($key !== false) {
                unset($existing_photo_urls[$key]);
                
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $photo_to_delete);
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
                
                // *** NEW: Delete ownership record ***
                delete_file_ownership_record($photo_to_delete);
            }
        }
        $existing_photo_urls = array_values($existing_photo_urls);
    }
    
    if (!empty($_FILES['admin-new-photos']['name'][0])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        add_filter('upload_dir', 'admin_custom_member_photos_upload_dir');
        
        $files = $_FILES['admin-new-photos'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0 && !empty($files['name'][$i])) {
                $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
                $file_type = $files['type'][$i];
                $file_ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                
                if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_extensions)) {
                    continue;
                }
                
                if ($files['size'][$i] > 3145728) {
                    continue;
                }
                
                $file = array(
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                );
                
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);
                
                if ($movefile && !isset($movefile['error'])) {
                    $existing_photo_urls[] = $movefile['url'];
                    
                    // *** NEW: Track ownership ***
                    track_file_ownership($user_id, $movefile['url'], 'photos');
                }
            }
        }
        
        remove_filter('upload_dir', 'admin_custom_member_photos_upload_dir');
    }
    
    if (!empty($existing_photo_urls)) {
        $photo_links_html = '';
        foreach ($existing_photo_urls as $url) {
            $photo_links_html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noreferrer">' . basename($url) . '</a><br>';
        }
        update_user_meta($user_id, 'upload-photos', $photo_links_html);
    } else {
        update_user_meta($user_id, 'upload-photos', '');
    }
    
    // HANDLE DOCUMENTS (new code)
    $documents = get_user_meta($user_id, 'upload-documents', true);
    $existing_document_urls = array();
    
    if (!empty($documents)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $documents, $matches);
        $existing_document_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    if (isset($_POST['admin-delete-documents']) && is_array($_POST['admin-delete-documents'])) {
        foreach ($_POST['admin-delete-documents'] as $doc_to_delete) {
            $key = array_search($doc_to_delete, $existing_document_urls);
            if ($key !== false) {
                unset($existing_document_urls[$key]);
                
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $doc_to_delete);
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
                
                // *** NEW: Delete ownership record ***
                delete_file_ownership_record($doc_to_delete);
            }
        }
        $existing_document_urls = array_values($existing_document_urls);
    }
    
    if (!empty($_FILES['admin-new-documents']['name'][0])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        add_filter('upload_dir', 'admin_custom_member_documents_upload_dir');
        
        $files = $_FILES['admin-new-documents'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0 && !empty($files['name'][$i])) {
                $allowed_types = array(
                    'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                );
                $file_type = $files['type'][$i];
                $file_ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx');
                
                if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_extensions)) {
                    continue;
                }
                
                if ($files['size'][$i] > 5242880) { // 5MB
                    continue;
                }
                
                $file = array(
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                );
                
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);
                
                if ($movefile && !isset($movefile['error'])) {
                    $existing_document_urls[] = $movefile['url'];
                    
                    // *** NEW: Track ownership ***
                    track_file_ownership($user_id, $movefile['url'], 'documents');
                }
            }
        }
        
        remove_filter('upload_dir', 'admin_custom_member_documents_upload_dir');
    }
    
    if (!empty($existing_document_urls)) {
        $doc_links_html = '';
        foreach ($existing_document_urls as $url) {
            $doc_links_html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noreferrer">' . basename($url) . '</a><br>';
        }
        update_user_meta($user_id, 'upload-documents', $doc_links_html);
    } else {
        update_user_meta($user_id, 'upload-documents', '');
    }
    
    // Rest of your existing save code for other fields...
    if (isset($_POST['date-of-birth'])) {
        $dob = sanitize_text_field($_POST['date-of-birth']);
        if (!empty($dob) && strpos($dob, '-') !== false) {
            $date_parts = explode('-', $dob);
            if (count($date_parts) === 3) {
                $dob = $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
            }
        }
        update_user_meta($user_id, 'date-of-birth', $dob);
    }
    
    // Handle Address fields
    if (isset($_POST['address-country'])) {
        update_user_meta($user_id, 'address-country', sanitize_text_field($_POST['address-country']));
    }
    if (isset($_POST['address-city'])) {
        update_user_meta($user_id, 'address-city', sanitize_text_field($_POST['address-city']));
    }
    if (isset($_POST['how-long-lived-in-your-country'])) {
        update_user_meta($user_id, 'how-long-lived-in-your-country', sanitize_text_field($_POST['how-long-lived-in-your-country']));
    }
    
    // Handle Body Type
    if (isset($_POST['body-type'])) {
        update_user_meta($user_id, 'body-type', sanitize_text_field($_POST['body-type']));
    }
    
    // Text fields
    $text_fields = array(
        'nationality',
        'height',
        'ethnic-or-cultural-background',
        'valid-passports',
        'number-of-children',
        'children-ages',
        'how-many-children-do-you-want',
        'faith-or-religion',
        'highest-level-of-education',
        'field-of-study',
        'current-occupation-or-business',
        'duration-in-current-occupation-business',
        'approximate-income-range-or-business-details',
        'languages-spoken-fluently',
        'personality-description',
        'lifestyle-description',
        'hobbies-interest-passions',
        'relationship-or-marriage-experience',
        'lessons-learned-from-past-relationships',
        'important-values-in-future-spouse',
        'write-the-countries-you-are-happy-to-relocate-to',
        'age-range-preferred-from',
        'age-range-preferred-to',
        'height-range-preferred-from',
        'height-range-preferred-to'
    );
    
    // Select fields
    $select_fields = array(
        'gender',
        'marital-status',
        'do-you-want-children',
        'practising-level',
        'property-ownership',
        'car-ownership',
        'savings',
        'how-did-you-learn-about-the-supporting-marriage-platform',
        'smoker',
        'drinker',
        'health',
        'happy-to-relocate',
        'accept-smoker',
        'accept-drinker',
        'health-preferred',
        'willing-to-entertain-people'
    );
    
    // Textarea fields
    $textarea_fields = array(
        'description-of-future-spouse',
        'conflict-handling-approach',
        'successful-relationship-definition',
        'social-media-and-professional-platform'
    );
    
    // Save text fields
    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Save select fields
    foreach ($select_fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Save textarea fields
    foreach ($textarea_fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_textarea_field($_POST[$field]));
        }
    }
    
    // Note: member-id and registration-date are read-only, so we don't save them
}

// Delete file ownership record when file is deleted
function delete_file_ownership_record($file_url) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'member_file_ownership';
    
    $wpdb->delete(
        $table_name,
        array('file_url' => $file_url),
        array('%s')
    );
}

// Custom upload directory for documents (admin)
function admin_custom_member_documents_upload_dir($upload) {
    $upload['subdir'] = '/member-documents';
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}

// Filter ADMIN email - exclude payment fields from {all_fields}
add_filter('forminator_custom_form_mail_admin_message', 'customize_all_fields_output', 10, 5);
function customize_all_fields_output($message, $custom_form, $data, $entry, $cls) {
    // For live form 13233, and PDF regeneration form 14497
    if (!in_array(intval($custom_form->id), array(13233, 14497))) {
        return $message;
    }
    
    return filter_all_fields_output($message, $custom_form, $entry);
}

// Filter USER email - exclude payment fields from {all_fields}
add_filter('forminator_custom_form_mail_user_message', 'customize_all_fields_output_user', 10, 5);
function customize_all_fields_output_user($message, $custom_form, $data, $entry, $cls) {
    // For live form 13233, and PDF regeneration form 14497
    if (!in_array(intval($custom_form->id), array(13233, 14497))) {
        return $message;
    }
    
    return filter_all_fields_output($message, $custom_form, $entry);
}

// Shared function to filter {all_fields}
function filter_all_fields_output($message, $custom_form, $entry) {
    // Check if {all_fields} is in the message
    if (strpos($message, '{all_fields}') === false) {
        return $message;
    }
    
    // Fields to exclude
    $exclude_fields = array('calculation-1', 'stripe-ocs-1', 'paypal-1');
    
    // Build custom all_fields output
    $custom_output = '';
    
    if (isset($entry->meta_data) && is_array($entry->meta_data)) {
        foreach ($entry->meta_data as $field_slug => $field_data) {
            
            // Skip excluded fields
            if (in_array($field_slug, $exclude_fields)) {
                continue;
            }
            
            // Get field value
            $field_value = '';
            if (is_array($field_data) && isset($field_data['value'])) {
                $field_value = $field_data['value'];
            } elseif (!is_array($field_data)) {
                $field_value = $field_data;
            }
            
            // Handle arrays (checkboxes, multi-select)
            if (is_array($field_value)) {
                $field_value = implode(', ', $field_value);
            }
            
            // Skip empty values (optional - remove these 3 lines if you want to show empty fields)
            if (empty($field_value)) {
                continue;
            }
            
            // Get field label from form
            $field_label = $field_slug;
            $fields = $custom_form->get_fields();
            if ($fields) {
                foreach ($fields as $field) {
                    if (isset($field['element_id']) && $field['element_id'] === $field_slug) {
                        $field_label = isset($field['field_label']) ? $field['field_label'] : $field_slug;
                        break;
                    }
                }
            }
            
            // Format output with bullet point and line break
            $custom_output .= "• {$field_label}\n{$field_value}\n";
        }
    }
    
    // Only replace if we have output
    if (!empty($custom_output)) {
        $message = str_replace('{all_fields}', $custom_output, $message);
    }
    
    return $message;
}


// Add custom tabs to WooCommerce My Account
add_filter('woocommerce_account_menu_items', 'add_member_profile_tabs', 10, 1);
function add_member_profile_tabs($items) {
    // Remove logout temporarily
    $logout = $items['customer-logout'];
    unset($items['customer-logout']);
    
    // Add our custom tabs after dashboard
    $new_items = array();
    foreach ($items as $key => $item) {
        $new_items[$key] = $item;
        if ($key === 'dashboard') {
            $new_items['basic-information'] = 'Basic Information';
            $new_items['family-relationships'] = 'Family & Relationships';
            $new_items['faith-religion'] = 'Faith & Religion';
            $new_items['education-career'] = 'Education & Career';
            $new_items['financial-information'] = 'Financial Information';
            $new_items['personal-information'] = 'Personal Information';
            $new_items['relationship-experience'] = 'Relationship Experience';
            $new_items['partner-preferences'] = 'Partner Preferences';
            $new_items['additional-information'] = 'Additional Information / Photos';
        }
    }
    
    // Add logout back at the end
    $new_items['customer-logout'] = $logout;
    
    return $new_items;
}

// Register endpoints
add_action('init', 'add_member_profile_endpoints');
function add_member_profile_endpoints() {
    add_rewrite_endpoint('basic-information', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('family-relationships', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('faith-religion', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('education-career', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('financial-information', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('personal-information', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('relationship-experience', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('partner-preferences', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('additional-information', EP_ROOT | EP_PAGES);
}

// Save function for all profile data
function save_member_profile_data($user_id) {
   // Handle Date of Birth conversion
    if (isset($_POST['date-of-birth'])) {
        $dob = sanitize_text_field($_POST['date-of-birth']);
        // If it's in YYYY-MM-DD format (from date input), convert to DD/MM/YYYY
        if (!empty($dob) && strpos($dob, '-') !== false) {
            $date_parts = explode('-', $dob);
            if (count($date_parts) === 3) {
                $dob = $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
            }
        }
        update_user_meta($user_id, 'date-of-birth', $dob);
    }
    
    // Save other fields
    if (isset($_POST['address-country'])) update_user_meta($user_id, 'address-country', sanitize_text_field($_POST['address-country']));
    if (isset($_POST['address-city'])) update_user_meta($user_id, 'address-city', sanitize_text_field($_POST['address-city']));
    if (isset($_POST['how-long-lived-in-your-country'])) update_user_meta($user_id, 'how-long-lived-in-your-country', sanitize_text_field($_POST['how-long-lived-in-your-country']));
    
    // Basic Information
    if (isset($_POST['nationality'])) update_user_meta($user_id, 'nationality', sanitize_text_field($_POST['nationality']));
    if (isset($_POST['gender'])) update_user_meta($user_id, 'gender', sanitize_text_field($_POST['gender']));
    if (isset($_POST['height'])) update_user_meta($user_id, 'height', sanitize_text_field($_POST['height']));
    if (isset($_POST['body-type'])) update_user_meta($user_id, 'body-type', sanitize_text_field($_POST['body-type']));
    if (isset($_POST['ethnic-or-cultural-background'])) update_user_meta($user_id, 'ethnic-or-cultural-background', sanitize_text_field($_POST['ethnic-or-cultural-background']));
    if (isset($_POST['valid-passports'])) update_user_meta($user_id, 'valid-passports', sanitize_text_field($_POST['valid-passports']));
    if (isset($_POST['smoker'])) update_user_meta($user_id, 'smoker', sanitize_text_field($_POST['smoker']));
    if (isset($_POST['drinker'])) update_user_meta($user_id, 'drinker', sanitize_text_field($_POST['drinker']));
    if (isset($_POST['health'])) update_user_meta($user_id, 'health', sanitize_text_field($_POST['health']));
    
    // Family & Relationships
    if (isset($_POST['marital-status'])) update_user_meta($user_id, 'marital-status', sanitize_text_field($_POST['marital-status']));
    if (isset($_POST['number-of-children'])) update_user_meta($user_id, 'number-of-children', sanitize_text_field($_POST['number-of-children']));
    if (isset($_POST['children-ages'])) update_user_meta($user_id, 'children-ages', sanitize_text_field($_POST['children-ages']));
    if (isset($_POST['do-you-want-children'])) update_user_meta($user_id, 'do-you-want-children', sanitize_text_field($_POST['do-you-want-children']));
    if (isset($_POST['how-many-children-do-you-want'])) update_user_meta($user_id, 'how-many-children-do-you-want', sanitize_text_field($_POST['how-many-children-do-you-want']));

    // Faith & Religion
    if (isset($_POST['faith-or-religion'])) update_user_meta($user_id, 'faith-or-religion', sanitize_text_field($_POST['faith-or-religion']));
    if (isset($_POST['practising-level'])) update_user_meta($user_id, 'practising-level', sanitize_text_field($_POST['practising-level']));
    
    // Education & Career
    if (isset($_POST['highest-level-of-education'])) update_user_meta($user_id, 'highest-level-of-education', sanitize_text_field($_POST['highest-level-of-education']));
    if (isset($_POST['field-of-study'])) update_user_meta($user_id, 'field-of-study', sanitize_text_field($_POST['field-of-study']));
    if (isset($_POST['current-occupation-or-business'])) update_user_meta($user_id, 'current-occupation-or-business', sanitize_text_field($_POST['current-occupation-or-business']));
    if (isset($_POST['duration-in-current-occupation-business'])) update_user_meta($user_id, 'duration-in-current-occupation-business', sanitize_text_field($_POST['duration-in-current-occupation-business']));
    
    // Financial Information
    if (isset($_POST['approximate-income-range-or-business-details'])) update_user_meta($user_id, 'approximate-income-range-or-business-details', sanitize_text_field($_POST['approximate-income-range-or-business-details']));
    if (isset($_POST['property-ownership'])) update_user_meta($user_id, 'property-ownership', sanitize_text_field($_POST['property-ownership']));
    if (isset($_POST['car-ownership'])) update_user_meta($user_id, 'car-ownership', sanitize_text_field($_POST['car-ownership']));
    if (isset($_POST['savings'])) update_user_meta($user_id, 'savings', sanitize_text_field($_POST['savings']));
    
    // Personal Information
    if (isset($_POST['languages-spoken-fluently'])) update_user_meta($user_id, 'languages-spoken-fluently', sanitize_text_field($_POST['languages-spoken-fluently']));
    if (isset($_POST['personality-description'])) update_user_meta($user_id, 'personality-description', sanitize_text_field($_POST['personality-description']));
    if (isset($_POST['lifestyle-description'])) update_user_meta($user_id, 'lifestyle-description', sanitize_text_field($_POST['lifestyle-description']));
    if (isset($_POST['hobbies-interest-passions'])) update_user_meta($user_id, 'hobbies-interest-passions', sanitize_text_field($_POST['hobbies-interest-passions']));
    
    // Relationship Experience
    if (isset($_POST['relationship-or-marriage-experience'])) update_user_meta($user_id, 'relationship-or-marriage-experience', sanitize_text_field($_POST['relationship-or-marriage-experience']));
    if (isset($_POST['lessons-learned-from-past-relationships'])) update_user_meta($user_id, 'lessons-learned-from-past-relationships', sanitize_text_field($_POST['lessons-learned-from-past-relationships']));
    
    // Partner Preferences
    if (isset($_POST['happy-to-relocate'])) update_user_meta($user_id, 'happy-to-relocate', sanitize_text_field($_POST['happy-to-relocate']));
    if (isset($_POST['write-the-countries-you-are-happy-to-relocate-to'])) update_user_meta($user_id, 'write-the-countries-you-are-happy-to-relocate-to', sanitize_text_field($_POST['write-the-countries-you-are-happy-to-relocate-to']));
    if (isset($_POST['age-range-preferred-from'])) update_user_meta($user_id, 'age-range-preferred-from', sanitize_text_field($_POST['age-range-preferred-from']));
    if (isset($_POST['age-range-preferred-to'])) update_user_meta($user_id, 'age-range-preferred-to', sanitize_text_field($_POST['age-range-preferred-to']));
    if (isset($_POST['height-range-preferred-from'])) update_user_meta($user_id, 'height-range-preferred-from', sanitize_text_field($_POST['height-range-preferred-from']));
    if (isset($_POST['height-range-preferred-to'])) update_user_meta($user_id, 'height-range-preferred-to', sanitize_text_field($_POST['height-range-preferred-to']));
    if (isset($_POST['accept-smoker'])) update_user_meta($user_id, 'accept-smoker', sanitize_text_field($_POST['accept-smoker']));
    if (isset($_POST['accept-drinker'])) update_user_meta($user_id, 'accept-drinker', sanitize_text_field($_POST['accept-drinker']));
    if (isset($_POST['health-preferred'])) update_user_meta($user_id, 'health-preferred', sanitize_text_field($_POST['health-preferred']));

    if (isset($_POST['important-values-in-future-spouse'])) update_user_meta($user_id, 'important-values-in-future-spouse', sanitize_text_field($_POST['important-values-in-future-spouse']));
    if (isset($_POST['description-of-future-spouse'])) update_user_meta($user_id, 'description-of-future-spouse', sanitize_textarea_field($_POST['description-of-future-spouse']));
    if (isset($_POST['willing-to-entertain-people'])) update_user_meta($user_id, 'willing-to-entertain-people', sanitize_text_field($_POST['willing-to-entertain-people']));
    if (isset($_POST['conflict-handling-approach'])) update_user_meta($user_id, 'conflict-handling-approach', sanitize_textarea_field($_POST['conflict-handling-approach']));
    if (isset($_POST['successful-relationship-definition'])) update_user_meta($user_id, 'successful-relationship-definition', sanitize_textarea_field($_POST['successful-relationship-definition']));
    
    // Additional Information
    if (isset($_POST['social-media-and-professional-platform'])) update_user_meta($user_id, 'social-media-and-professional-platform', sanitize_textarea_field($_POST['social-media-and-professional-platform']));
    if (isset($_POST['how-did-you-learn-about-the-supporting-marriage-platform'])) update_user_meta($user_id, 'how-did-you-learn-about-the-supporting-marriage-platform', sanitize_text_field($_POST['how-did-you-learn-about-the-supporting-marriage-platform']));

    // Update last-updated timestamp at the end
    update_user_meta($user_id, 'profile-last-updated', date('Y-m-d H:i:s'));
}

// Validation function for Basic Information
function validate_basic_information() {
    $required_fields = array(
        'date-of-birth' => 'Date of Birth',
        'nationality' => 'Nationality / Place of Birth',
        'ethnic-or-cultural-background' => 'Ethnic and Cultural Background',
        'valid-passports' => 'How many Valid Passports do you have? List all countries',
        'address-country' => 'Country of Residence',
        'address-city' => 'City',
        'how-long-lived-in-your-country' => 'How long have you lived in your country of residence?',
        'height' => 'Height',
        'body-type' => 'Body Type',
        'smoker' => 'Smoker',
        'drinker' => 'Drinker',
        'health' => 'Health'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    return $errors;
}

// Helper function to display validation errors
function display_validation_errors($errors) {
    if (!empty($errors)) {
        echo '<div class="woocommerce-error" role="alert">';
        echo '<strong>Please fill in the following required fields:</strong><ul>';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul></div>';
        return true;
    }
    return false;
}

// 1. Basic Information Tab
add_action('woocommerce_account_basic-information_endpoint', 'basic_information_content');
function basic_information_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_basic_info'])) {
        $errors = validate_basic_information();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Basic Information</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="member-id">Member ID</label>
            <input type="text" class="woocommerce-Input input-text" name="member-id" id="member-id" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'member-id', true)); ?>" readonly />
            <span class="description">This field is read-only</span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="registration-date">Registration Date</label>
            <input type="text" class="woocommerce-Input input-text" name="registration-date" id="registration-date" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'registration-date', true)); ?>" readonly />
            <span class="description">This field is read-only</span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="gender">Gender</label>
			<input type="text" class="woocommerce-Input input-text" name="gender" id="gender" 
				value="<?php echo esc_attr(get_user_meta($user->ID, 'gender', true)); ?>" readonly />
			<span class="description">This field is read-only</span>
		</p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="date-of-birth">Date of Birth <span class="required">*</span></label>
            <?php
            $dob = get_user_meta($user->ID, 'date-of-birth', true);
            // Convert from DD/MM/YYYY to YYYY-MM-DD for HTML5 date input
            if (!empty($dob) && strpos($dob, '/') !== false) {
                $date_parts = explode('/', $dob);
                if (count($date_parts) === 3) {
                    $dob = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                }
            }
            ?>
            <input type="date" class="woocommerce-Input input-text" name="date-of-birth" id="date-of-birth" 
                value="<?php echo esc_attr($dob); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="nationality">Nationality / Place of Birth <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="nationality" id="nationality" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'nationality', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="ethnic-or-cultural-background">Ethnic and Cultural Background <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="ethnic-or-cultural-background" id="ethnic-or-cultural-background" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'ethnic-or-cultural-background', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="valid-passports">How many Valid Passports do you have? List all countries <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="valid-passports" id="valid-passports" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'valid-passports', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="address-country">Country of Residence <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="address-country" id="address-country" required>
                <option value="">Select country</option>
                <option value="Afghanistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Afghanistan'); ?>>Afghanistan</option>
                <option value="Albania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Albania'); ?>>Albania</option>
                <option value="Algeria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Algeria'); ?>>Algeria</option>
                <option value="American Samoa" <?php selected(get_user_meta($user->ID, 'address-country', true), 'American Samoa'); ?>>American Samoa</option>
                <option value="Andorra" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Andorra'); ?>>Andorra</option>
                <option value="Angola" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Angola'); ?>>Angola</option>
                <option value="Anguilla" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Anguilla'); ?>>Anguilla</option>
                <option value="Antarctica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Antarctica'); ?>>Antarctica</option>
                <option value="Antigua and Barbuda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Antigua and Barbuda'); ?>>Antigua and Barbuda</option>
                <option value="Argentina" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Argentina'); ?>>Argentina</option>
                <option value="Armenia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Armenia'); ?>>Armenia</option>
                <option value="Aruba" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Aruba'); ?>>Aruba</option>
                <option value="Australia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Australia'); ?>>Australia</option>
                <option value="Austria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Austria'); ?>>Austria</option>
                <option value="Azerbaijan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Azerbaijan'); ?>>Azerbaijan</option>
                <option value="Bahamas" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bahamas'); ?>>Bahamas</option>
                <option value="Bahrain" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bahrain'); ?>>Bahrain</option>
                <option value="Bangladesh" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bangladesh'); ?>>Bangladesh</option>
                <option value="Barbados" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Barbados'); ?>>Barbados</option>
                <option value="Belarus" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Belarus'); ?>>Belarus</option>
                <option value="Belgium" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Belgium'); ?>>Belgium</option>
                <option value="Belize" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Belize'); ?>>Belize</option>
                <option value="Benin" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Benin'); ?>>Benin</option>
                <option value="Bermuda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bermuda'); ?>>Bermuda</option>
                <option value="Bhutan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bhutan'); ?>>Bhutan</option>
                <option value="Bolivia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bolivia'); ?>>Bolivia</option>
                <option value="Bosnia and Herzegovina" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bosnia and Herzegovina'); ?>>Bosnia and Herzegovina</option>
                <option value="Botswana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Botswana'); ?>>Botswana</option>
                <option value="Bouvet Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bouvet Island'); ?>>Bouvet Island</option>
                <option value="Brazil" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Brazil'); ?>>Brazil</option>
                <option value="British Indian Ocean Territory" <?php selected(get_user_meta($user->ID, 'address-country', true), 'British Indian Ocean Territory'); ?>>British Indian Ocean Territory</option>
                <option value="Brunei" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Brunei'); ?>>Brunei</option>
                <option value="Bulgaria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Bulgaria'); ?>>Bulgaria</option>
                <option value="Burkina Faso" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Burkina Faso'); ?>>Burkina Faso</option>
                <option value="Burundi" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Burundi'); ?>>Burundi</option>
                <option value="Cabo Verde" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cabo Verde'); ?>>Cabo Verde</option>
                <option value="Cambodia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cambodia'); ?>>Cambodia</option>
                <option value="Cameroon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cameroon'); ?>>Cameroon</option>
                <option value="Canada" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Canada'); ?>>Canada</option>
                <option value="Cayman Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cayman Islands'); ?>>Cayman Islands</option>
                <option value="Central African Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Central African Republic'); ?>>Central African Republic</option>
                <option value="Chad" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Chad'); ?>>Chad</option>
                <option value="Chile" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Chile'); ?>>Chile</option>
                <option value="China, People's Republic of" <?php selected(get_user_meta($user->ID, 'address-country', true), "China, People's Republic of"); ?>>China, People's Republic of</option>
                <option value="Christmas Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Christmas Island'); ?>>Christmas Island</option>
                <option value="Cocos Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cocos Islands'); ?>>Cocos Islands</option>
                <option value="Colombia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Colombia'); ?>>Colombia</option>
                <option value="Comoros" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Comoros'); ?>>Comoros</option>
                <option value="Congo, Democratic Republic of the" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Congo, Democratic Republic of the'); ?>>Congo, Democratic Republic of the</option>
                <option value="Congo, Republic of the" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Congo, Republic of the'); ?>>Congo, Republic of the</option>
                <option value="Cook Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cook Islands'); ?>>Cook Islands</option>
                <option value="Costa Rica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Costa Rica'); ?>>Costa Rica</option>
                <option value="Croatia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Croatia'); ?>>Croatia</option>
                <option value="Cuba" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cuba'); ?>>Cuba</option>
                <option value="Curaçao" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Curaçao'); ?>>Curaçao</option>
                <option value="Cyprus" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Cyprus'); ?>>Cyprus</option>
                <option value="Czech Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Czech Republic'); ?>>Czech Republic</option>
                <option value="Côte d'Ivoire" <?php selected(get_user_meta($user->ID, 'address-country', true), "Côte d'Ivoire"); ?>>Côte d'Ivoire</option>
                <option value="Denmark" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Denmark'); ?>>Denmark</option>
                <option value="Djibouti" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Djibouti'); ?>>Djibouti</option>
                <option value="Dominica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Dominica'); ?>>Dominica</option>
                <option value="Dominican Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Dominican Republic'); ?>>Dominican Republic</option>
                <option value="East Timor" <?php selected(get_user_meta($user->ID, 'address-country', true), 'East Timor'); ?>>East Timor</option>
                <option value="Ecuador" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ecuador'); ?>>Ecuador</option>
                <option value="Egypt" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Egypt'); ?>>Egypt</option>
                <option value="El Salvador" <?php selected(get_user_meta($user->ID, 'address-country', true), 'El Salvador'); ?>>El Salvador</option>
                <option value="Equatorial Guinea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Equatorial Guinea'); ?>>Equatorial Guinea</option>
                <option value="Eritrea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Eritrea'); ?>>Eritrea</option>
                <option value="Estonia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Estonia'); ?>>Estonia</option>
                <option value="Ethiopia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ethiopia'); ?>>Ethiopia</option>
                <option value="Falkland Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Falkland Islands'); ?>>Falkland Islands</option>
                <option value="Faroe Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Faroe Islands'); ?>>Faroe Islands</option>
                <option value="Fiji" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Fiji'); ?>>Fiji</option>
                <option value="Finland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Finland'); ?>>Finland</option>
                <option value="France" <?php selected(get_user_meta($user->ID, 'address-country', true), 'France'); ?>>France</option>
                <option value="France, Metropolitan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'France, Metropolitan'); ?>>France, Metropolitan</option>
                <option value="French Guiana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'French Guiana'); ?>>French Guiana</option>
                <option value="French Polynesia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'French Polynesia'); ?>>French Polynesia</option>
                <option value="French South Territories" <?php selected(get_user_meta($user->ID, 'address-country', true), 'French South Territories'); ?>>French South Territories</option>
                <option value="Gabon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Gabon'); ?>>Gabon</option>
                <option value="Gambia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Gambia'); ?>>Gambia</option>
                <option value="Georgia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Georgia'); ?>>Georgia</option>
                <option value="Germany" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Germany'); ?>>Germany</option>
                <option value="Ghana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ghana'); ?>>Ghana</option>
                <option value="Gibraltar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Gibraltar'); ?>>Gibraltar</option>
                <option value="Greece" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Greece'); ?>>Greece</option>
                <option value="Greenland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Greenland'); ?>>Greenland</option>
                <option value="Grenada" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Grenada'); ?>>Grenada</option>
                <option value="Guadeloupe" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guadeloupe'); ?>>Guadeloupe</option>
                <option value="Guam" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guam'); ?>>Guam</option>
                <option value="Guatemala" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guatemala'); ?>>Guatemala</option>
                <option value="Guernsey" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guernsey'); ?>>Guernsey</option>
                <option value="Guinea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guinea'); ?>>Guinea</option>
                <option value="Guinea-Bissau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guinea-Bissau'); ?>>Guinea-Bissau</option>
                <option value="Guyana" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Guyana'); ?>>Guyana</option>
                <option value="Haiti" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Haiti'); ?>>Haiti</option>
                <option value="Heard Island And Mcdonald Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Heard Island And Mcdonald Island'); ?>>Heard Island And Mcdonald Island</option>
                <option value="Honduras" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Honduras'); ?>>Honduras</option>
                <option value="Hong Kong" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Hong Kong'); ?>>Hong Kong</option>
                <option value="Hungary" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Hungary'); ?>>Hungary</option>
                <option value="Iceland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Iceland'); ?>>Iceland</option>
                <option value="India" <?php selected(get_user_meta($user->ID, 'address-country', true), 'India'); ?>>India</option>
                <option value="Indonesia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Indonesia'); ?>>Indonesia</option>
                <option value="Iran" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Iran'); ?>>Iran</option>
                <option value="Iraq" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Iraq'); ?>>Iraq</option>
                <option value="Ireland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ireland'); ?>>Ireland</option>
                <option value="Israel" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Israel'); ?>>Israel</option>
                <option value="Italy" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Italy'); ?>>Italy</option>
                <option value="Jamaica" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Jamaica'); ?>>Jamaica</option>
                <option value="Japan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Japan'); ?>>Japan</option>
                <option value="Jersey" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Jersey'); ?>>Jersey</option>
                <option value="Johnston Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Johnston Island'); ?>>Johnston Island</option>
                <option value="Jordan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Jordan'); ?>>Jordan</option>
                <option value="Kazakhstan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kazakhstan'); ?>>Kazakhstan</option>
                <option value="Kenya" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kenya'); ?>>Kenya</option>
                <option value="Kiribati" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kiribati'); ?>>Kiribati</option>
                <option value="Korea, Democratic People's Republic of" <?php selected(get_user_meta($user->ID, 'address-country', true), "Korea, Democratic People's Republic of"); ?>>Korea, Democratic People's Republic of</option>
                <option value="Korea, Republic of" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Korea, Republic of'); ?>>Korea, Republic of</option>
                <option value="Kosovo" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kosovo'); ?>>Kosovo</option>
                <option value="Kuwait" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kuwait'); ?>>Kuwait</option>
                <option value="Kyrgyzstan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Kyrgyzstan'); ?>>Kyrgyzstan</option>
                <option value="Lao People's Democratic Republic" <?php selected(get_user_meta($user->ID, 'address-country', true), "Lao People's Democratic Republic"); ?>>Lao People's Democratic Republic</option>
                <option value="Latvia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Latvia'); ?>>Latvia</option>
                <option value="Lebanon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Lebanon'); ?>>Lebanon</option>
                <option value="Lesotho" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Lesotho'); ?>>Lesotho</option>
                <option value="Liberia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Liberia'); ?>>Liberia</option>
                <option value="Libya" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Libya'); ?>>Libya</option>
                <option value="Liechtenstein" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Liechtenstein'); ?>>Liechtenstein</option>
                <option value="Lithuania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Lithuania'); ?>>Lithuania</option>
                <option value="Luxembourg" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Luxembourg'); ?>>Luxembourg</option>
                <option value="Macau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Macau'); ?>>Macau</option>
                <option value="Madagascar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Madagascar'); ?>>Madagascar</option>
                <option value="Malawi" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Malawi'); ?>>Malawi</option>
                <option value="Malaysia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Malaysia'); ?>>Malaysia</option>
                <option value="Maldives" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Maldives'); ?>>Maldives</option>
                <option value="Mali" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mali'); ?>>Mali</option>
                <option value="Malta" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Malta'); ?>>Malta</option>
                <option value="Marshall Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Marshall Islands'); ?>>Marshall Islands</option>
                <option value="Martinique" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Martinique'); ?>>Martinique</option>
                <option value="Mauritania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mauritania'); ?>>Mauritania</option>
                <option value="Mauritius" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mauritius'); ?>>Mauritius</option>
                <option value="Mayotte" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mayotte'); ?>>Mayotte</option>
                <option value="Mexico" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mexico'); ?>>Mexico</option>
                <option value="Micronesia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Micronesia'); ?>>Micronesia</option>
                <option value="Moldova" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Moldova'); ?>>Moldova</option>
                <option value="Monaco" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Monaco'); ?>>Monaco</option>
                <option value="Mongolia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mongolia'); ?>>Mongolia</option>
                <option value="Montenegro" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Montenegro'); ?>>Montenegro</option>
                <option value="Montserrat" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Montserrat'); ?>>Montserrat</option>
                <option value="Morocco" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Morocco'); ?>>Morocco</option>
                <option value="Mozambique" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Mozambique'); ?>>Mozambique</option>
                <option value="Myanmar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Myanmar'); ?>>Myanmar</option>
                <option value="Namibia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Namibia'); ?>>Namibia</option>
                <option value="Nauru" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nauru'); ?>>Nauru</option>
                <option value="Nepal" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nepal'); ?>>Nepal</option>
                <option value="Netherlands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Netherlands'); ?>>Netherlands</option>
                <option value="Netherlands Antilles" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Netherlands Antilles'); ?>>Netherlands Antilles</option>
                <option value="New Caledonia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'New Caledonia'); ?>>New Caledonia</option>
                <option value="New Zealand" <?php selected(get_user_meta($user->ID, 'address-country', true), 'New Zealand'); ?>>New Zealand</option>
                <option value="Nicaragua" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nicaragua'); ?>>Nicaragua</option>
                <option value="Niger" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Niger'); ?>>Niger</option>
                <option value="Nigeria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Nigeria'); ?>>Nigeria</option>
                <option value="Niue" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Niue'); ?>>Niue</option>
                <option value="Norfolk Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Norfolk Island'); ?>>Norfolk Island</option>
                <option value="North Macedonia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'North Macedonia'); ?>>North Macedonia</option>
                <option value="Northern Mariana Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Northern Mariana Islands'); ?>>Northern Mariana Islands</option>
                <option value="Norway" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Norway'); ?>>Norway</option>
                <option value="Oman" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Oman'); ?>>Oman</option>
                <option value="Pakistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Pakistan'); ?>>Pakistan</option>
                <option value="Palau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Palau'); ?>>Palau</option>
                <option value="Palestine, State of" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Palestine, State of'); ?>>Palestine, State of</option>
                <option value="Panama" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Panama'); ?>>Panama</option>
                <option value="Papua New Guinea" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Papua New Guinea'); ?>>Papua New Guinea</option>
                <option value="Paraguay" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Paraguay'); ?>>Paraguay</option>
                <option value="Peru" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Peru'); ?>>Peru</option>
                <option value="Philippines" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Philippines'); ?>>Philippines</option>
                <option value="Pitcairn Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Pitcairn Islands'); ?>>Pitcairn Islands</option>
                <option value="Poland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Poland'); ?>>Poland</option>
                <option value="Portugal" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Portugal'); ?>>Portugal</option>
                <option value="Puerto Rico" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Puerto Rico'); ?>>Puerto Rico</option>
                <option value="Qatar" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Qatar'); ?>>Qatar</option>
                <option value="Reunion Island" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Reunion Island'); ?>>Reunion Island</option>
                <option value="Romania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Romania'); ?>>Romania</option>
                <option value="Russia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Russia'); ?>>Russia</option>
                <option value="Rwanda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Rwanda'); ?>>Rwanda</option>
                <option value="Saint Helena" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Helena'); ?>>Saint Helena</option>
                <option value="Saint Kitts and Nevis" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Kitts and Nevis'); ?>>Saint Kitts and Nevis</option>
                <option value="Saint Lucia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Lucia'); ?>>Saint Lucia</option>
                <option value="Saint Pierre & Miquelon" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Pierre & Miquelon'); ?>>Saint Pierre & Miquelon</option>
                <option value="Saint Vincent and the Grenadines" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saint Vincent and the Grenadines'); ?>>Saint Vincent and the Grenadines</option>
                <option value="Samoa" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Samoa'); ?>>Samoa</option>
                <option value="San Marino" <?php selected(get_user_meta($user->ID, 'address-country', true), 'San Marino'); ?>>San Marino</option>
                <option value="Sao Tome and Principe" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sao Tome and Principe'); ?>>Sao Tome and Principe</option>
                <option value="Saudi Arabia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Saudi Arabia'); ?>>Saudi Arabia</option>
                <option value="Senegal" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Senegal'); ?>>Senegal</option>
                <option value="Serbia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Serbia'); ?>>Serbia</option>
                <option value="Seychelles" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Seychelles'); ?>>Seychelles</option>
                <option value="Sierra Leone" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sierra Leone'); ?>>Sierra Leone</option>
                <option value="Singapore" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Singapore'); ?>>Singapore</option>
                <option value="Sint Maarten" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sint Maarten'); ?>>Sint Maarten</option>
                <option value="Slovakia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Slovakia'); ?>>Slovakia</option>
                <option value="Slovenia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Slovenia'); ?>>Slovenia</option>
                <option value="Solomon Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Solomon Islands'); ?>>Solomon Islands</option>
                <option value="Somalia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Somalia'); ?>>Somalia</option>
                <option value="South Africa" <?php selected(get_user_meta($user->ID, 'address-country', true), 'South Africa'); ?>>South Africa</option>
                <option value="South Georgia and South Sandwich" <?php selected(get_user_meta($user->ID, 'address-country', true), 'South Georgia and South Sandwich'); ?>>South Georgia and South Sandwich</option>
                <option value="Spain" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Spain'); ?>>Spain</option>
                <option value="Sri Lanka" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sri Lanka'); ?>>Sri Lanka</option>
                <option value="Stateless Persons" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Stateless Persons'); ?>>Stateless Persons</option>
                <option value="Sudan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sudan'); ?>>Sudan</option>
                <option value="Sudan, South" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sudan, South'); ?>>Sudan, South</option>
                <option value="Suriname" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Suriname'); ?>>Suriname</option>
                <option value="Svalbard and Jan Mayen" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Svalbard and Jan Mayen'); ?>>Svalbard and Jan Mayen</option>
                <option value="Swaziland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Swaziland'); ?>>Swaziland</option>
                <option value="Sweden" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Sweden'); ?>>Sweden</option>
                <option value="Switzerland" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Switzerland'); ?>>Switzerland</option>
                <option value="Syria" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Syria'); ?>>Syria</option>
                <option value="Taiwan, Republic of China" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Taiwan, Republic of China'); ?>>Taiwan, Republic of China</option>
                <option value="Tajikistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tajikistan'); ?>>Tajikistan</option>
                <option value="Tanzania" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tanzania'); ?>>Tanzania</option>
                <option value="Thailand" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Thailand'); ?>>Thailand</option>
                <option value="Togo" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Togo'); ?>>Togo</option>
                <option value="Tokelau" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tokelau'); ?>>Tokelau</option>
                <option value="Tonga" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tonga'); ?>>Tonga</option>
                <option value="Trinidad and Tobago" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Trinidad and Tobago'); ?>>Trinidad and Tobago</option>
                <option value="Tunisia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tunisia'); ?>>Tunisia</option>
                <option value="Turkey" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Turkey'); ?>>Turkey</option>
                <option value="Turkmenistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Turkmenistan'); ?>>Turkmenistan</option>
                <option value="Turks And Caicos Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Turks And Caicos Islands'); ?>>Turks And Caicos Islands</option>
                <option value="Tuvalu" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Tuvalu'); ?>>Tuvalu</option>
                <option value="US Minor Outlying Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'US Minor Outlying Islands'); ?>>US Minor Outlying Islands</option>
                <option value="Uganda" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Uganda'); ?>>Uganda</option>
                <option value="Ukraine" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Ukraine'); ?>>Ukraine</option>
                <option value="United Arab Emirates" <?php selected(get_user_meta($user->ID, 'address-country', true), 'United Arab Emirates'); ?>>United Arab Emirates</option>
                <option value="United Kingdom" <?php selected(get_user_meta($user->ID, 'address-country', true), 'United Kingdom'); ?>>United Kingdom</option>
                <option value="United States of America (USA)" <?php selected(get_user_meta($user->ID, 'address-country', true), 'United States of America (USA)'); ?>>United States of America (USA)</option>
                <option value="Uruguay" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Uruguay'); ?>>Uruguay</option>
                <option value="Uzbekistan" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Uzbekistan'); ?>>Uzbekistan</option>
                <option value="Vanuatu" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Vanuatu'); ?>>Vanuatu</option>
                <option value="Vatican City" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Vatican City'); ?>>Vatican City</option>
                <option value="Venezuela" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Venezuela'); ?>>Venezuela</option>
                <option value="Vietnam" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Vietnam'); ?>>Vietnam</option>
                <option value="Virgin Islands, British" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Virgin Islands, British'); ?>>Virgin Islands, British</option>
                <option value="Virgin Islands, U.S." <?php selected(get_user_meta($user->ID, 'address-country', true), 'Virgin Islands, U.S.'); ?>>Virgin Islands, U.S.</option>
                <option value="Wallis And Futuna Islands" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Wallis And Futuna Islands'); ?>>Wallis And Futuna Islands</option>
                <option value="Western Sahara" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Western Sahara'); ?>>Western Sahara</option>
                <option value="Yemen" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Yemen'); ?>>Yemen</option>
                <option value="Zambia" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Zambia'); ?>>Zambia</option>
                <option value="Zimbabwe" <?php selected(get_user_meta($user->ID, 'address-country', true), 'Zimbabwe'); ?>>Zimbabwe</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="address-city">City <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="address-city" id="address-city" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'address-city', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="how-long-lived-in-your-country">How long have you lived in your country of residence? <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="how-long-lived-in-your-country" id="how-long-lived-in-your-country" 
                value="<?php echo esc_attr(get_user_meta($user->ID, 'how-long-lived-in-your-country', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="height">Height (in feet and inches) <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="height" id="height" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'height', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="body-type">Body Type <span class="required">*</span></label>
			<select class="woocommerce-Input input-text" name="body-type" id="body-type" required>
				<option value="">Select Body Type</option>
				<option value="Slender" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Slender'); ?>>Slender</option>
				<option value="Slim" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Slim'); ?>>Slim</option>
				<option value="Average" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Average'); ?>>Average</option>
				<option value="Athletic" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Athletic'); ?>>Athletic</option>
				<option value="Well-Built" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Well-Built'); ?>>Well-Built</option>
				<option value="Overweight" <?php selected(get_user_meta($user->ID, 'body-type', true), 'Overweight'); ?>>Overweight</option>
			</select>
		</p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="smoker">Smoker <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="smoker" id="smoker" required>
                <option value="">Select</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'smoker', true), 'No'); ?>>No</option>
                <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'smoker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'smoker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'smoker', true), 'Yes - Social'); ?>>Yes - Social</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="drinker">Drinker <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="drinker" id="drinker" required>
                <option value="">Select</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'drinker', true), 'No'); ?>>No</option>
                <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'drinker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'drinker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'drinker', true), 'Yes - Social'); ?>>Yes - Social</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="health">Health <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="health" id="health" required>
                <option value="">Select</option>
                <option value="Very Good" <?php selected(get_user_meta($user->ID, 'health', true), 'Very Good'); ?>>Very Good</option>
                <option value="Good" <?php selected(get_user_meta($user->ID, 'health', true), 'Good'); ?>>Good</option>
                <option value="Average" <?php selected(get_user_meta($user->ID, 'health', true), 'Average'); ?>>Average</option>
                <option value="Poor" <?php selected(get_user_meta($user->ID, 'health', true), 'Poor'); ?>>Poor</option>
            </select>
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_basic_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Family & Relationships
function validate_family_relationships() {
    $required_fields = array(
        'marital-status' => 'Marital Status',
        'number-of-children' => 'Number of Children',
        'children-ages' => 'Children Ages',
        'do-you-want-children' => 'Do you want children?'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    // Special validation: "How many children do you want?" is required only if "Do you want children?" is "Yes"
    if (isset($_POST['do-you-want-children']) && $_POST['do-you-want-children'] === 'Yes') {
        if (empty($_POST['how-many-children-do-you-want']) || trim($_POST['how-many-children-do-you-want']) === '') {
            $errors[] = 'How many children do you want? (required when Do you want children is Yes)';
        }
    }

    return $errors;
}

// 2. Family & Relationships Tab
add_action('woocommerce_account_family-relationships_endpoint', 'family_relationships_content');
function family_relationships_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_family_info'])) {
        $errors = validate_family_relationships();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Family & Relationships</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="marital-status">Marital Status <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="marital-status" id="marital-status" required>
                <option value="">Select Marital Status</option>
                <option value="Single/Never Married" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Single/Never Married'); ?>>Single/Never Married</option>
                <option value="Single Parent/Never Married" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Single Parent/Never Married'); ?>>Single Parent/Never Married</option>
                <option value="Married/Separated - Processing Divorce" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Married/Separated - Processing Divorce'); ?>>Married/Separated - Processing Divorce</option>
                <option value="Divorced" <?php selected(get_user_meta($user->ID, 'marital-status', true), 'Divorced'); ?>>Divorced</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="number-of-children">Number of Children <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="number-of-children" id="number-of-children" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'number-of-children', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="children-ages">Children Ages <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="children-ages" id="children-ages" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'children-ages', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="do-you-want-children">Do you want children? <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="do-you-want-children" id="do-you-want-children" required>
                <option value="">Select</option>
                <option value="Yes" <?php selected(get_user_meta($user->ID, 'do-you-want-children', true), 'Yes'); ?>>Yes</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'do-you-want-children', true), 'No'); ?>>No</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="how-many-children-do-you-want">How many children do you want?</label>
            <input type="text" class="woocommerce-Input input-text" name="how-many-children-do-you-want" id="how-many-children-do-you-want" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'how-many-children-do-you-want', true)); ?>" />
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_family_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Faith & Religion
function validate_faith_religion() {
    $required_fields = array(
        'faith-or-religion' => 'Faith or Religion',
        'practising-level' => 'Practising Level'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    return $errors;
}

// 3. Faith & Religion Tab
add_action('woocommerce_account_faith-religion_endpoint', 'faith_religion_content');
function faith_religion_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_faith_info'])) {
        $errors = validate_faith_religion();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Faith & Religion</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="faith-or-religion">Faith or Religion <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="faith-or-religion" id="faith-or-religion" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'faith-or-religion', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="practising-level">Practising Level <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="practising-level" id="practising-level" required>
                <option value="">Select Practising Level</option>
                <option value="Believer" <?php selected(get_user_meta($user->ID, 'practising-level', true), 'Believer'); ?>>Believer</option>
                <option value="Attend Church" <?php selected(get_user_meta($user->ID, 'practising-level', true), 'Attend Church'); ?>>Attend Church</option>
                <option value="Devout" <?php selected(get_user_meta($user->ID, 'practising-level', true), 'Devout'); ?>>Devout</option>
            </select>
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_faith_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Education & Career
function validate_education_career() {
    $required_fields = array(
        'highest-level-of-education' => 'Highest Level of Education',
        'field-of-study' => 'Field of Study',
        'current-occupation-or-business' => 'Current Occupation or Business',
        'duration-in-current-occupation-business' => 'Duration in Current Occupation/Business'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    return $errors;
}

// 4. Education & Career Tab
add_action('woocommerce_account_education-career_endpoint', 'education_career_content');
function education_career_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_education_info'])) {
        $errors = validate_education_career();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Education & Career</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="highest-level-of-education">Highest Level of Education <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="highest-level-of-education" id="highest-level-of-education" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'highest-level-of-education', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="field-of-study">Field of Study <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="field-of-study" id="field-of-study" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'field-of-study', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="current-occupation-or-business">Current Occupation or Business <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="current-occupation-or-business" id="current-occupation-or-business" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'current-occupation-or-business', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="duration-in-current-occupation-business">Duration in Current Occupation/Business <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="duration-in-current-occupation-business" id="duration-in-current-occupation-business" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'duration-in-current-occupation-business', true)); ?>" required />
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_education_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Financial Information
function validate_financial_information() {
    $required_fields = array(
        'approximate-income-range-or-business-details' => 'Approximate Income Range or Business Details',
        'property-ownership' => 'Property Ownership',
        'car-ownership' => 'Car Ownership',
        'savings' => 'Savings'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    return $errors;
}

// 5. Financial Information Tab
add_action('woocommerce_account_financial-information_endpoint', 'financial_information_content');
function financial_information_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_financial_info'])) {
        $errors = validate_financial_information();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Financial Information</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="approximate-income-range-or-business-details">Approximate Income Range or Business Details <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="approximate-income-range-or-business-details" id="approximate-income-range-or-business-details" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'approximate-income-range-or-business-details', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="property-ownership">Property Ownership <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="property-ownership" id="property-ownership" required>
                <option value="">Select</option>
                <option value="Yes" <?php selected(get_user_meta($user->ID, 'property-ownership', true), 'Yes'); ?>>Yes</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'property-ownership', true), 'No'); ?>>No</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="car-ownership">Car Ownership <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="car-ownership" id="car-ownership" required>
                <option value="">Select</option>
                <option value="Yes" <?php selected(get_user_meta($user->ID, 'car-ownership', true), 'Yes'); ?>>Yes</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'car-ownership', true), 'No'); ?>>No</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="savings">Savings <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="savings" id="savings" required>
                <option value="">Select</option>
                <option value="Yes" <?php selected(get_user_meta($user->ID, 'savings', true), 'Yes'); ?>>Yes</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'savings', true), 'No'); ?>>No</option>
            </select>
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_financial_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Personal Information
function validate_personal_information() {
    $required_fields = array(
        'languages-spoken-fluently' => 'Languages Spoken Fluently',
        'personality-description' => 'Personality Description',
        'lifestyle-description' => 'Lifestyle Description',
        'hobbies-interest-passions' => 'Hobbies, Interests & Passions'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    return $errors;
}

// 6. Personal Information Tab
add_action('woocommerce_account_personal-information_endpoint', 'personal_information_content');
function personal_information_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_personal_info'])) {
        $errors = validate_personal_information();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Personal Information</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="languages-spoken-fluently">Languages Spoken Fluently <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="languages-spoken-fluently" id="languages-spoken-fluently" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'languages-spoken-fluently', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="personality-description">Personality Description <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="personality-description" id="personality-description" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'personality-description', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="lifestyle-description">Lifestyle Description <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="lifestyle-description" id="lifestyle-description" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'lifestyle-description', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="hobbies-interest-passions">Hobbies, Interests & Passions <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="hobbies-interest-passions" id="hobbies-interest-passions" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'hobbies-interest-passions', true)); ?>" required />
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_personal_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Relationship Experience
function validate_relationship_experience() {
    $required_fields = array(
        'relationship-or-marriage-experience' => 'How many years was your longest relationship or years in Marriage',
        'lessons-learned-from-past-relationships' => 'Lessons Learned from Past Relationships'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    return $errors;
}

// 7. Relationship Experience Tab
add_action('woocommerce_account_relationship-experience_endpoint', 'relationship_experience_content');
function relationship_experience_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_relationship_info'])) {
        $errors = validate_relationship_experience();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Relationship Experience</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="relationship-or-marriage-experience">How many years was your longest relationship or years in Marriage <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="relationship-or-marriage-experience" id="relationship-or-marriage-experience" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'relationship-or-marriage-experience', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="lessons-learned-from-past-relationships">Lessons Learned from Past Relationships <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="lessons-learned-from-past-relationships" id="lessons-learned-from-past-relationships" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'lessons-learned-from-past-relationships', true)); ?>" required />
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_relationship_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <?php
}

// Validation function for Partner Preferences
function validate_partner_preferences() {
    $required_fields = array(
        'happy-to-relocate' => 'Happy to Relocate',
        'age-range-preferred-from' => 'Age Range Preferred From',
        'age-range-preferred-to' => 'Age Range Preferred To',
        'height-range-preferred-from' => 'Height Range Preferred From (in feet and inches)',
        'height-range-preferred-to' => 'Height Range Preferred To (in feet and inches)',
        'accept-smoker' => 'Will you accept a Smoker',
        'accept-drinker' => 'Will you accept a Drinker',
        'health-preferred' => 'Health Preferred',
        'important-values-in-future-spouse' => 'Important Values in Future Spouse',
        'description-of-future-spouse' => 'Description of Future Spouse',
        'willing-to-entertain-people' => 'Willing to Entertain People with Different Cultures and Faiths',
        'conflict-handling-approach' => 'Conflict Handling Approach',
        'successful-relationship-definition' => 'Successful Relationship Definition'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }

    // Special validation: "Write the Countries..." is required only if "Happy to Relocate" is "Yes"
    if (isset($_POST['happy-to-relocate']) && $_POST['happy-to-relocate'] === 'Yes') {
        if (empty($_POST['write-the-countries-you-are-happy-to-relocate-to']) || trim($_POST['write-the-countries-you-are-happy-to-relocate-to']) === '') {
            $errors[] = 'Countries You Are Happy to Relocate To (required when Happy to Relocate is Yes)';
        }
    }

    return $errors;
}

// 8. Partner Preferences Tab
add_action('woocommerce_account_partner-preferences_endpoint', 'partner_preferences_content');
function partner_preferences_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_partner_info'])) {
        $errors = validate_partner_preferences();
        
        if (empty($errors)) {
            save_member_profile_data($user->ID);
            echo '<div class="woocommerce-message">Profile updated successfully!</div>';
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <h3>Partner Preferences</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account" id="partner-preferences-form">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="happy-to-relocate">Happy to Relocate <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="happy-to-relocate" id="happy-to-relocate" required>
                <option value="">Select</option>
                <option value="Yes" <?php selected(get_user_meta($user->ID, 'happy-to-relocate', true), 'Yes'); ?>>Yes</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'happy-to-relocate', true), 'No'); ?>>No</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" id="countries-field-wrapper">
            <label for="write-the-countries-you-are-happy-to-relocate-to">Write the Countries You Are Happy to Relocate To <span class="required" id="countries-required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="write-the-countries-you-are-happy-to-relocate-to" id="write-the-countries-you-are-happy-to-relocate-to" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'write-the-countries-you-are-happy-to-relocate-to', true)); ?>" />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="age-range-preferred-from">Age Range Preferred From <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="age-range-preferred-from" id="age-range-preferred-from" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'age-range-preferred-from', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="age-range-preferred-to">Age Range Preferred To <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="age-range-preferred-to" id="age-range-preferred-to" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'age-range-preferred-to', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="height-range-preferred-from">Height Range Preferred From (in feet and inches) <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="height-range-preferred-from" id="height-range-preferred-from" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'height-range-preferred-from', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="height-range-preferred-to">Height Range Preferred To (in feet and inches) <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="height-range-preferred-to" id="height-range-preferred-to" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'height-range-preferred-to', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="accept-smoker">Will you accept a Smoker <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="accept-smoker" id="accept-smoker" required>
                <option value="">Select</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'No'); ?>>No</option>
                <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'accept-smoker', true), 'Yes - Social'); ?>>Yes - Social</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="accept-drinker">Will you accept a Drinker <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="accept-drinker" id="accept-drinker" required>
                <option value="">Select</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'No'); ?>>No</option>
                <option value="Yes - Daily" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'Yes - Daily'); ?>>Yes - Daily</option>
                <option value="Yes - Occasional" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'Yes - Occasional'); ?>>Yes - Occasional</option>
                <option value="Yes - Social" <?php selected(get_user_meta($user->ID, 'accept-drinker', true), 'Yes - Social'); ?>>Yes - Social</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="health-preferred">Health Preferred <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="health-preferred" id="health-preferred" required>
                <option value="">Select</option>
                <option value="Very Good" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Very Good'); ?>>Very Good</option>
                <option value="Good" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Good'); ?>>Good</option>
                <option value="Average" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Average'); ?>>Average</option>
                <option value="Poor" <?php selected(get_user_meta($user->ID, 'health-preferred', true), 'Poor'); ?>>Poor</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="important-values-in-future-spouse">Important Values in Future Spouse <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input input-text" name="important-values-in-future-spouse" id="important-values-in-future-spouse" 
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'important-values-in-future-spouse', true)); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="description-of-future-spouse">Description of Future Spouse <span class="required">*</span></label>
            <textarea class="woocommerce-Input input-text" name="description-of-future-spouse" id="description-of-future-spouse" 
                      rows="5" required><?php echo esc_textarea(get_user_meta($user->ID, 'description-of-future-spouse', true)); ?></textarea>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="willing-to-entertain-people">Willing to Entertain People with Different Cultures and Faiths <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="willing-to-entertain-people" id="willing-to-entertain-people" required>
                <option value="">Select</option>
                <option value="Yes" <?php selected(get_user_meta($user->ID, 'willing-to-entertain-people', true), 'Yes'); ?>>Yes</option>
                <option value="No" <?php selected(get_user_meta($user->ID, 'willing-to-entertain-people', true), 'No'); ?>>No</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="conflict-handling-approach">Conflict Handling Approach <span class="required">*</span></label>
            <textarea class="woocommerce-Input input-text" name="conflict-handling-approach" id="conflict-handling-approach" 
                      rows="5" required><?php echo esc_textarea(get_user_meta($user->ID, 'conflict-handling-approach', true)); ?></textarea>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="successful-relationship-definition">Successful Relationship Definition <span class="required">*</span></label>
            <textarea class="woocommerce-Input input-text" name="successful-relationship-definition" id="successful-relationship-definition" 
                      rows="5" required><?php echo esc_textarea(get_user_meta($user->ID, 'successful-relationship-definition', true)); ?></textarea>
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_partner_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Function to toggle countries field requirement
        function toggleCountriesField() {
            var relocateValue = $('#happy-to-relocate').val();
            var countriesField = $('#write-the-countries-you-are-happy-to-relocate-to');
            var countriesWrapper = $('#countries-field-wrapper');
            var requiredSpan = $('#countries-required');
            
            if (relocateValue === 'Yes') {
                countriesField.prop('required', true);
                countriesWrapper.show();
                requiredSpan.show();
            } else if (relocateValue === 'No') {
                countriesField.prop('required', false);
                countriesField.val(''); // Clear the field
                countriesWrapper.hide();
                requiredSpan.hide();
            } else {
                // If nothing selected, keep field visible but not required
                countriesField.prop('required', false);
                countriesWrapper.show();
                requiredSpan.hide();
            }
        }
        
        // Run on page load
        toggleCountriesField();
        
        // Run when happy-to-relocate changes
        $('#happy-to-relocate').on('change', function() {
            toggleCountriesField();
        });
    });
    </script>
    
    <?php
}

// Validation function for Additional Information
function validate_additional_information() {
    $required_fields = array(
        'social-media-and-professional-platform' => 'Social Media & Professional Platform',
        'how-did-you-learn-about-the-supporting-marriage-platform' => 'How Did You Learn About Dr London and the Supporting Marriage Platform?'
    );

    $errors = array();
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label;
        }
    }
    
    // Check if at least one photo exists
    $existing_photos = get_user_meta(get_current_user_id(), 'upload-photos', true);
    $existing_photo_urls = array();
    
    if (!empty($existing_photos)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_photos, $matches);
        $existing_photo_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    // Check if user is uploading new photos
    $has_new_uploads = !empty($_FILES['new-photos']['name'][0]);
    
    if (empty($existing_photo_urls) && !$has_new_uploads) {
        $errors[] = 'At least one photo is required';
    }
    
    // Check if trying to upload too many photos
    if ($has_new_uploads) {
        $files = $_FILES['new-photos'];
        $new_file_count = 0;
        foreach ($files['name'] as $name) {
            if (!empty($name)) {
                $new_file_count++;
            }
        }
        
        $current_photo_count = count($existing_photo_urls);
        $total_photos = $current_photo_count + $new_file_count;
        
        if ($total_photos > 6) {
            $allowed = 6 - $current_photo_count;
            $errors[] = "Cannot upload {$new_file_count} photo(s). You currently have {$current_photo_count} photo(s) and can only upload {$allowed} more. Maximum is 6 photos total.";
        }
    }
    
    return $errors;
}

// Custom upload directory for member photos
function custom_member_photos_upload_dir($upload) {
    $user_id = get_current_user_id();
    
    // Create custom subdirectory: /wp-content/uploads/member-photos/USER_ID/
    $upload['subdir'] = '/member-photos/' . $user_id;
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    
    // Create directory if it doesn't exist
    if (!file_exists($upload['path'])) {
        wp_mkdir_p($upload['path']);
    }
    
    return $upload;
}

/*
// Handle photo uploads and deletions
function handle_photo_uploads($user_id) {
    // Get existing photos
    $existing_photos = get_user_meta($user_id, 'upload-photos', true);
    $existing_photo_urls = array();
    
    if (!empty($existing_photos)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_photos, $matches);
        $existing_photo_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    // Handle photo deletions
    if (isset($_POST['delete-photos']) && is_array($_POST['delete-photos'])) {
        foreach ($_POST['delete-photos'] as $photo_to_delete) {
            $key = array_search($photo_to_delete, $existing_photo_urls);
            if ($key !== false) {
                unset($existing_photo_urls[$key]);
                
                // Delete physical file from custom directory
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $photo_to_delete);
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
            }
        }
        $existing_photo_urls = array_values($existing_photo_urls); // Re-index array
    }
    
    // Handle new photo uploads
    if (!empty($_FILES['new-photos']['name'][0])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        // Add custom upload directory filter
        add_filter('upload_dir', 'custom_member_photos_upload_dir');
        
        $files = $_FILES['new-photos'];
        $upload_success = 0;
        $upload_errors = array();
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0 && !empty($files['name'][$i])) {
                // Validate file type
                $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
                $file_type = $files['type'][$i];
                
                // Also check file extension as a backup
                $file_ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                
                if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_extensions)) {
                    $upload_errors[] = 'File "' . $files['name'][$i] . '" is not a valid image type. Only JPG, JPEG, PNG, and GIF are allowed.';
                    continue;
                }
                
                // Validate file size (max 3MB)
                if ($files['size'][$i] > 3145728) {
                    $upload_errors[] = 'File "' . $files['name'][$i] . '" is too large (' . round($files['size'][$i] / 1048576, 2) . 'MB). Maximum file size is 3MB.';
                    continue;
                }
                
                // Prepare file for upload
                $file = array(
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                );
                
                // Use wp_handle_upload to move file to custom directory
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);
                
                if ($movefile && !isset($movefile['error'])) {
                    $existing_photo_urls[] = $movefile['url'];
                    $upload_success++;
                } else {
                    $upload_errors[] = 'Error uploading "' . $files['name'][$i] . '": ' . (isset($movefile['error']) ? $movefile['error'] : 'Unknown error');
                }
            }
        }
        
        // Remove custom upload directory filter
        remove_filter('upload_dir', 'custom_member_photos_upload_dir');
        
        // Display upload feedback using WooCommerce notices
        if ($upload_success > 0) {
            wc_add_notice($upload_success . ' photo(s) uploaded successfully!', 'success');
        }
        
        if (!empty($upload_errors)) {
            foreach ($upload_errors as $error) {
                wc_add_notice($error, 'error');
            }
        }
    }
    
    // Save updated photo URLs
    if (!empty($existing_photo_urls)) {
        $photo_links_html = '';
        foreach ($existing_photo_urls as $url) {
            $photo_links_html .= '<a href="' . esc_url($url) . '" target="_blank">' . basename($url) . '</a><br>';
        }
        update_user_meta($user_id, 'upload-photos', $photo_links_html);
    } else {
        update_user_meta($user_id, 'upload-photos', '');
    }
    
    return true;
}

// 9. Additional Information Tab
add_action('woocommerce_account_additional-information_endpoint', 'additional_information_content');
function additional_information_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_additional_info'])) {
        // Validate first (including photo count check)
        $errors = validate_additional_information();
        
        if (empty($errors)) {
            // Handle photo uploads
            handle_photo_uploads($user->ID);
            
            // Save other profile data
            save_member_profile_data($user->ID);
            
            // Check if there were any upload errors (shown via wc_add_notice)
            // Only show success if no WC errors were added
            $wc_notices = wc_get_notices('error');
            if (empty($wc_notices)) {
                echo '<div class="woocommerce-message">Profile updated successfully!</div>';
            }
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <style>
    .photo-notice-box .emoji {
        width: 16px !important;
        height: 16px !important;
        display: inline-block !important;
        vertical-align: middle !important;
        margin-right: 4px !important;
    }
    </style>
    
    <h3>Additional Information</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account" enctype="multipart/form-data" id="additional-info-form">
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="upload-photos">Photos <span class="required">*</span> (Maximum 6 photos)</label>
            
            <?php
            $photos = get_user_meta($user->ID, 'upload-photos', true);
            $photo_urls = array();
            
            if (!empty($photos)) {
                preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $photos, $matches);
                $photo_urls = isset($matches[1]) ? $matches[1] : array();
            }
            
            if (!empty($photo_urls)) {
                echo '<div style="margin-bottom: 15px;">';
                echo '<strong style="color: #2c3e50;">Current Photos (' . count($photo_urls) . '/6):</strong>';
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px;">';
                
                foreach ($photo_urls as $url) {
                    $filename = basename($url);
                    echo '<div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; background: #f9f9f9; position: relative;">';
                    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noreferrer">';
                    echo '<img src="' . esc_url($url) . '" style="width: 100%; height: 120px; object-fit: cover; border-radius: 3px; display: block; margin-bottom: 5px;" />';
                    echo '</a>';
                    echo '<div style="font-size: 11px; color: #666; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' . esc_attr($filename) . '">' . esc_html($filename) . '</div>';
                    echo '<label style="display: flex; align-items: center; font-size: 12px; cursor: pointer;">';
                    echo '<input type="checkbox" name="delete-photos[]" value="' . esc_attr($url) . '" style="margin-right: 5px;" />';
                    echo 'Delete this photo';
                    echo '</label>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="photo-notice-box" style="color: #d63638; font-weight: 500; background: #ffebe9; padding: 10px; border-left: 4px solid #d63638; margin: 10px 0; font-size: 14px;">';
                echo '⚠️ No photos uploaded yet. Please upload at least one photo.';
                echo '</div>';
            }
            
            // Show upload field only if less than 6 photos
            if (count($photo_urls) < 6) {
                $remaining = 6 - count($photo_urls);
                echo '<div class="photo-notice-box" style="margin-top: 15px; background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
                echo '<label for="new-photos" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0073aa; font-size: 14px;">📤 Upload New Photos</label>';
                echo '<p style="margin: 0 0 10px 0; font-size: 13px; color: #2c3e50;">You can upload up to <strong>' . $remaining . '</strong> more photo(s)</p>';
                echo '<input type="file" name="new-photos[]" id="new-photos" accept="image/jpeg,image/jpg,image/png,image/gif" multiple style="padding: 8px; width: 100%; box-sizing: border-box;" />';
                echo '<div style="margin-top: 8px; font-size: 12px; color: #666; line-height: 1.6;">';
                echo '• Accepted formats: JPG, JPEG, PNG, GIF<br>';
                echo '• Max size: 3MB per photo<br>';
                echo '• Select multiple photos at once (maximum ' . $remaining . ' total)';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="photo-notice-box" style="color: #856404; margin-top: 10px; background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; font-size: 14px;">';
                echo '⚠️ Maximum of 6 photos reached. Please delete existing photos if you want to upload new ones.';
                echo '</div>';
            }
            ?>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="social-media-and-professional-platform">Social Media & Professional Platform</label>
            <textarea class="woocommerce-Input input-text" name="social-media-and-professional-platform" id="social-media-and-professional-platform" 
                      rows="3"><?php echo esc_textarea(get_user_meta($user->ID, 'social-media-and-professional-platform', true)); ?></textarea>
            <span class="description">Enter your social media handles or professional profile links (LinkedIn, etc.)</span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="how-did-you-learn-about-the-supporting-marriage-platform">How Did You Learn About Dr London and the Supporting Marriage Platform?</label>
            <select class="woocommerce-Input input-text" name="how-did-you-learn-about-the-supporting-marriage-platform" id="how-did-you-learn-about-the-supporting-marriage-platform">
                <option value="">Select</option>
                <option value="Social Media" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Social Media'); ?>>Social Media</option>
                <option value="Referral" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Referral'); ?>>Referral</option>
                <option value="Advertisment" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Advertisment'); ?>>Advertisment</option>
                <option value="Already following Dr London" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Already following Dr London'); ?>>Already following Dr London</option>
            </select>
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_additional_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Add client-side validation for photo count
        $('#additional-info-form').on('submit', function(e) {
            var fileInput = $('#new-photos')[0];
            if (fileInput && fileInput.files.length > 0) {
                var currentPhotoCount = <?php echo count($photo_urls); ?>;
                var newFileCount = fileInput.files.length;
                var totalPhotos = currentPhotoCount + newFileCount;
                var maxPhotos = 6;
                
                if (totalPhotos > maxPhotos) {
                    var allowed = maxPhotos - currentPhotoCount;
                    e.preventDefault();
                    alert('ERROR: You cannot upload ' + newFileCount + ' photo(s).\n\n' +
                          'You currently have ' + currentPhotoCount + ' photo(s).\n' +
                          'You can only upload ' + allowed + ' more photo(s).\n' +
                          'Maximum is ' + maxPhotos + ' photos total.\n\n' +
                          'Please select fewer photos and try again.');
                    return false;
                }
            }
        });
    });
    </script>
    
    <?php
}
*/

// 9. Additional Information Tab
add_action('woocommerce_account_additional-information_endpoint', 'additional_information_content');
function additional_information_content() {
    $user = wp_get_current_user();
    
    if ($_POST && isset($_POST['update_additional_info'])) {
        // Validate first (including photo and document count check)
        $errors = validate_additional_information();
        
        if (empty($errors)) {
            // Handle photo uploads
            handle_member_photo_uploads($user->ID);
            
            // Handle document uploads
            handle_member_document_uploads($user->ID);
            
            // Save other profile data
            save_additional_info_profile_data($user->ID);
            
            // Check if there were any upload errors (shown via wc_add_notice)
            // Only show success if no WC errors were added
            $wc_notices = wc_get_notices('error');
            if (empty($wc_notices)) {
                echo '<div class="woocommerce-message">Profile updated successfully!</div>';
            }
        } else {
            display_validation_errors($errors);
        }
    }
    ?>
    
    <style>
    .photo-notice-box .emoji {
        width: 16px !important;
        height: 16px !important;
        display: inline-block !important;
        vertical-align: middle !important;
        margin-right: 4px !important;
    }
    .document-item {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        background: #f9f9f9;
        position: relative;
    }
    .document-icon {
        width: 100%;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e8f4f8;
        border-radius: 3px;
        margin-bottom: 5px;
        font-size: 48px;
    }
    </style>
    
    <h3>Additional Information / Photos</h3>
    <form method="post" class="woocommerce-EditAccountForm edit-account" enctype="multipart/form-data" id="additional-info-form">
        
        <!-- PHOTOS SECTION -->
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="upload-photos">Photos <span class="required">*</span> (Maximum 6 photos)</label>
            
            <?php
            $photos = get_user_meta($user->ID, 'upload-photos', true);
            $photo_urls = array();
            
            if (!empty($photos)) {
                preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $photos, $matches);
                $photo_urls = isset($matches[1]) ? $matches[1] : array();
            }
            
            if (!empty($photo_urls)) {
                echo '<div style="margin-bottom: 15px;">';
                echo '<strong style="color: #2c3e50;">Current Photos (' . count($photo_urls) . '/6):</strong>';
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px;">';
                
                foreach ($photo_urls as $url) {
                    $filename = basename($url);
                    $secure_url = get_secure_file_url($url); // Convert to secure URL
                    
                    echo '<div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; background: #f9f9f9; position: relative;">';
                    echo '<a href="' . esc_url($secure_url) . '" target="_blank" rel="noreferrer">';
                    echo '<img src="' . esc_url($secure_url) . '" style="width: 100%; height: 120px; object-fit: cover; border-radius: 3px; display: block; margin-bottom: 5px;" />';
                    echo '</a>';
                    echo '<div style="font-size: 11px; color: #666; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' . esc_attr($filename) . '">' . esc_html($filename) . '</div>';
                    echo '<label style="display: flex; align-items: center; font-size: 12px; cursor: pointer;">';
                    echo '<input type="checkbox" name="delete-photos[]" value="' . esc_attr($url) . '" style="margin-right: 5px;" />';
                    echo 'Delete this photo';
                    echo '</label>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                //echo '<div class="photo-notice-box" style="color: #d63638; font-weight: 500; background: #ffebe9; padding: 10px; border-left: 4px solid #d63638; margin: 10px 0; font-size: 14px;">';
                echo '<div class="photo-notice-box" style="color: #666; background: #f5f5f5; padding: 10px; border-left: 4px solid #999; margin: 10px 0; font-size: 14px;">';
                echo '⚠️ No photos uploaded yet.';
                echo '</div>';
            }
            
            // Show upload field only if less than 6 photos
            if (count($photo_urls) < 6) {
                $remaining = 6 - count($photo_urls);
                echo '<div class="photo-notice-box" style="margin-top: 15px; background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
                echo '<label for="new-photos" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0073aa; font-size: 14px;">📤 Upload New Photos</label>';
                echo '<p style="margin: 0 0 10px 0; font-size: 13px; color: #2c3e50;">You can upload up to <strong>' . $remaining . '</strong> more photo(s)</p>';
                echo '<input type="file" name="new-photos[]" id="new-photos" accept="image/jpeg,image/jpg,image/png,image/gif" multiple style="padding: 8px; width: 100%; box-sizing: border-box;" />';
                echo '<div style="margin-top: 8px; font-size: 12px; color: #666; line-height: 1.6;">';
                echo '• Accepted formats: JPG, JPEG, PNG, GIF<br>';
                echo '• Max size: 3MB per photo<br>';
                echo '• Select multiple photos at once (maximum ' . $remaining . ' total)';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="photo-notice-box" style="color: #856404; margin-top: 10px; background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; font-size: 14px;">';
                echo '⚠️ Maximum of 6 photos reached. Please delete existing photos if you want to upload new ones.';
                echo '</div>';
            }
            ?>
        </p>

        <?php /* Hide until Stage 2 for Upload Documents
        <!-- DOCUMENTS SECTION -->
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="upload-documents">Supporting Documents (Maximum 6 documents)</label>
            <?php
            $documents = get_user_meta($user->ID, 'upload-documents', true);
            $document_urls = array();
            
            if (!empty($documents)) {
                preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $documents, $matches);
                $document_urls = isset($matches[1]) ? $matches[1] : array();
            }
            
            if (!empty($document_urls)) {
                echo '<div style="margin-bottom: 15px;">';
                echo '<strong style="color: #2c3e50;">Current Documents (' . count($document_urls) . '/6):</strong>';
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px;">';
                
                foreach ($document_urls as $url) {
                    $filename = basename($url);
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $secure_url = get_secure_file_url($url); // Convert to secure URL
                    
                    // Determine icon based on file type
                    $icon = '📄';
                    $bg_color = '#e8f4f8';
                    if (in_array($extension, array('pdf'))) {
                        $icon = '📕';
                        $bg_color = '#ffe8e8';
                    } elseif (in_array($extension, array('doc', 'docx'))) {
                        $icon = '📘';
                        $bg_color = '#e8f0ff';
                    } elseif (in_array($extension, array('ppt', 'pptx'))) {
                        $icon = '📙';
                        $bg_color = '#fff4e8';
                    } elseif (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                        $icon = '🖼️';
                        $bg_color = '#f0ffe8';
                    }
                    
                    echo '<div class="document-item">';
                    echo '<a href="' . esc_url($secure_url) . '" target="_blank" rel="noreferrer">';
                    
                    // Show image preview for image files, icon for others
                    if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                        echo '<img src="' . esc_url($secure_url) . '" style="width: 100%; height: 120px; object-fit: cover; border-radius: 3px; display: block; margin-bottom: 5px;" />';
                    } else {
                        echo '<div class="document-icon" style="background: ' . $bg_color . ';">' . $icon . '</div>';
                    }
                    
                    echo '</a>';
                    echo '<div style="font-size: 11px; color: #666; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' . esc_attr($filename) . '">' . esc_html($filename) . '</div>';
                    echo '<label style="display: flex; align-items: center; font-size: 12px; cursor: pointer;">';
                    echo '<input type="checkbox" name="delete-documents[]" value="' . esc_attr($url) . '" style="margin-right: 5px;" />';
                    echo 'Delete this document';
                    echo '</label>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="photo-notice-box" style="color: #666; background: #f5f5f5; padding: 10px; border-left: 4px solid #999; margin: 10px 0; font-size: 14px;">';
                //echo '<div class="photo-notice-box" style="color: #d63638; font-weight: 500; background: #ffebe9; padding: 10px; border-left: 4px solid #d63638; margin: 10px 0; font-size: 14px;">';
                echo '⚠️ No documents uploaded yet.';
                echo '</div>';
            }
            
            // Show upload field only if less than 6 documents
            if (count($document_urls) < 6) {
                $remaining_docs = 6 - count($document_urls);
                echo '<div class="photo-notice-box" style="margin-top: 15px; background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
                echo '<label for="new-documents" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0073aa; font-size: 14px;">📎 Upload New Documents</label>';
                echo '<p style="margin: 0 0 10px 0; font-size: 13px; color: #2c3e50;">You can upload up to <strong>' . $remaining_docs . '</strong> more document(s)</p>';
                echo '<input type="file" name="new-documents[]" id="new-documents" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.ppt,.pptx" multiple style="padding: 8px; width: 100%; box-sizing: border-box;" />';
                echo '<div style="margin-top: 8px; font-size: 12px; color: #666; line-height: 1.6;">';
                echo '• Accepted formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, PPT, PPTX<br>';
                echo '• Max size: 5MB per document<br>';
                echo '• Select multiple documents at once (maximum ' . $remaining_docs . ' total)';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="photo-notice-box" style="color: #856404; margin-top: 10px; background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; font-size: 14px;">';
                echo '⚠️ Maximum of 6 documents reached. Please delete existing documents if you want to upload new ones.';
                echo '</div>';
            }
            ?>
        </p>
        */ ?>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="social-media-and-professional-platform">Social Media & Professional Platform <span class="required">*</span></label>
            <textarea class="woocommerce-Input input-text" name="social-media-and-professional-platform" id="social-media-and-professional-platform" 
                    rows="3" required><?php echo esc_textarea(get_user_meta($user->ID, 'social-media-and-professional-platform', true)); ?></textarea>
            <span class="description">Enter your social media handles or professional profile links (LinkedIn, etc.)</span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="how-did-you-learn-about-the-supporting-marriage-platform">How Did You Learn About Dr London and the Supporting Marriage Platform? <span class="required">*</span></label>
            <select class="woocommerce-Input input-text" name="how-did-you-learn-about-the-supporting-marriage-platform" id="how-did-you-learn-about-the-supporting-marriage-platform" required>
                <option value="">Select</option>
                <option value="Social Media" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Social Media'); ?>>Social Media</option>
                <option value="Referral" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Referral'); ?>>Referral</option>
                <option value="Advertisment" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Advertisment'); ?>>Advertisment</option>
                <option value="Already following Dr London" <?php selected(get_user_meta($user->ID, 'how-did-you-learn-about-the-supporting-marriage-platform', true), 'Already following Dr London'); ?>>Already following Dr London</option>
            </select>
        </p>

        <p>
            <button type="submit" class="woocommerce-Button button" name="update_additional_info" value="Update">Update Profile</button>
        </p>
        
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Add client-side validation for photo count
        $('#additional-info-form').on('submit', function(e) {
            // Validate photos
            var fileInput = $('#new-photos')[0];
            if (fileInput && fileInput.files.length > 0) {
                var currentPhotoCount = <?php echo count($photo_urls); ?>;
                var newFileCount = fileInput.files.length;
                var totalPhotos = currentPhotoCount + newFileCount;
                var maxPhotos = 6;
                
                if (totalPhotos > maxPhotos) {
                    var allowed = maxPhotos - currentPhotoCount;
                    e.preventDefault();
                    alert('ERROR: You cannot upload ' + newFileCount + ' photo(s).\n\n' +
                          'You currently have ' + currentPhotoCount + ' photo(s).\n' +
                          'You can only upload ' + allowed + ' more photo(s).\n' +
                          'Maximum is ' + maxPhotos + ' photos total.\n\n' +
                          'Please select fewer photos and try again.');
                    return false;
                }
            }
            
            // Validate documents
            <?php /* Hide until Stage 2 for Upload Documents
            var docInput = $('#new-documents')[0];
            if (docInput && docInput.files.length > 0) {
                var currentDocCount = <?php echo count($document_urls); ?>;
                var newDocCount = docInput.files.length;
                var totalDocs = currentDocCount + newDocCount;
                var maxDocs = 6;
                
                if (totalDocs > maxDocs) {
                    var allowedDocs = maxDocs - currentDocCount;
                    e.preventDefault();
                    alert('ERROR: You cannot upload ' + newDocCount + ' document(s).\n\n' +
                          'You currently have ' + currentDocCount + ' document(s).\n' +
                          'You can only upload ' + allowedDocs + ' more document(s).\n' +
                          'Maximum is ' + maxDocs + ' documents total.\n\n' +
                          'Please select fewer documents and try again.');
                    return false;
                }
            }
            */ ?>
        });
    });
    </script>
    
    <?php
}

// Validation function with unique name
function validate_additional_info_uploads() {
    $errors = array();
    
    $user = wp_get_current_user();
    
    // Validate photos
    $photos = get_user_meta($user->ID, 'upload-photos', true);
    $photo_urls = array();
    
    if (!empty($photos)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $photos, $matches);
        $photo_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    if (isset($_FILES['new-photos']) && !empty($_FILES['new-photos']['name'][0])) {
        $new_photos_count = count(array_filter($_FILES['new-photos']['name']));
        if (count($photo_urls) + $new_photos_count > 6) {
            $errors[] = 'You cannot upload more than 6 photos total.';
        }
    }
    
    // Validate documents
    $documents = get_user_meta($user->ID, 'upload-documents', true);
    $document_urls = array();
    
    if (!empty($documents)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $documents, $matches);
        $document_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    if (isset($_FILES['new-documents']) && !empty($_FILES['new-documents']['name'][0])) {
        $new_docs_count = count(array_filter($_FILES['new-documents']['name']));
        if (count($document_urls) + $new_docs_count > 6) {
            $errors[] = 'You cannot upload more than 6 documents total.';
        }
    }
    
    return $errors;
}

// Display errors function with unique name
function display_additional_info_errors($errors) {
    if (!empty($errors)) {
        echo '<div class="woocommerce-error">';
        foreach ($errors as $error) {
            echo '<p>' . esc_html($error) . '</p>';
        }
        echo '</div>';
    }
}

// Save member profile data with unique name
function save_additional_info_profile_data($user_id) {
    if (isset($_POST['social-media-and-professional-platform'])) {
        update_user_meta($user_id, 'social-media-and-professional-platform', sanitize_textarea_field($_POST['social-media-and-professional-platform']));
    }
    
    if (isset($_POST['how-did-you-learn-about-the-supporting-marriage-platform'])) {
        update_user_meta($user_id, 'how-did-you-learn-about-the-supporting-marriage-platform', sanitize_text_field($_POST['how-did-you-learn-about-the-supporting-marriage-platform']));
    }
}

// Handle photo uploads with unique name
function handle_member_photo_uploads($user_id) {
    return handle_member_file_uploads($user_id, 'photos');
}

// Handle document uploads with unique name
function handle_member_document_uploads($user_id) {
    return handle_member_file_uploads($user_id, 'documents');
}

// Unified file upload handler with unique name
// Unified file upload handler for both photos and documents
function handle_member_file_uploads($user_id, $type = 'photos') {
    // Configuration based on type
    $config = array(
        'photos' => array(
            'meta_key' => 'upload-photos',
            'post_field' => 'new-photos',
            'delete_field' => 'delete-photos',
            'allowed_types' => array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'),
            'allowed_extensions' => array('jpg', 'jpeg', 'png', 'gif'),
            'max_size' => 3145728, // 3MB
            'max_size_label' => '3MB',
            'upload_dir_filter' => 'member_photos_upload_directory',
            'type_label' => 'photo'
        ),
        'documents' => array(
            'meta_key' => 'upload-documents',
            'post_field' => 'new-documents',
            'delete_field' => 'delete-documents',
            'allowed_types' => array(
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ),
            'allowed_extensions' => array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx'),
            'max_size' => 5242880, // 5MB
            'max_size_label' => '5MB',
            'upload_dir_filter' => 'member_documents_upload_directory',
            'type_label' => 'document'
        )
    );
    
    $settings = $config[$type];
    
    // Get existing files
    $existing_files = get_user_meta($user_id, $settings['meta_key'], true);
    $existing_file_urls = array();
    
    if (!empty($existing_files)) {
        preg_match_all('/href=[\'"]([^\'"]+)[\'"]/', $existing_files, $matches);
        $existing_file_urls = isset($matches[1]) ? $matches[1] : array();
    }
    
    // Handle file deletions
    if (isset($_POST[$settings['delete_field']]) && is_array($_POST[$settings['delete_field']])) {
        foreach ($_POST[$settings['delete_field']] as $file_to_delete) {
            $key = array_search($file_to_delete, $existing_file_urls);
            if ($key !== false) {
                unset($existing_file_urls[$key]);
                
                // Delete physical file
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_to_delete);
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
                
                // *** NEW: Delete ownership record ***
                delete_file_ownership_record($file_to_delete);
            }
        }
        $existing_file_urls = array_values($existing_file_urls);
    }
    
    // Handle new file uploads
    if (isset($_FILES[$settings['post_field']]) && !empty($_FILES[$settings['post_field']]['name'][0])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        add_filter('upload_dir', $settings['upload_dir_filter']);
        
        $files = $_FILES[$settings['post_field']];
        $upload_success = 0;
        $upload_errors = array();
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0 && !empty($files['name'][$i])) {
                $file_type = $files['type'][$i];
                $file_ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                
                if (!in_array($file_type, $settings['allowed_types']) || !in_array($file_ext, $settings['allowed_extensions'])) {
                    $upload_errors[] = 'File "' . $files['name'][$i] . '" is not a valid file type. Allowed: ' . strtoupper(implode(', ', $settings['allowed_extensions']));
                    continue;
                }
                
                if ($files['size'][$i] > $settings['max_size']) {
                    $upload_errors[] = 'File "' . $files['name'][$i] . '" is too large (' . round($files['size'][$i] / 1048576, 2) . 'MB). Maximum file size is ' . $settings['max_size_label'] . '.';
                    continue;
                }
                
                $file = array(
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                );
                
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);
                
                if ($movefile && !isset($movefile['error'])) {
                    $existing_file_urls[] = $movefile['url'];
                    $upload_success++;
                    
                    // *** NEW: Track file ownership ***
                    track_file_ownership($user_id, $movefile['url'], $type);
                } else {
                    $upload_errors[] = 'Error uploading "' . $files['name'][$i] . '": ' . (isset($movefile['error']) ? $movefile['error'] : 'Unknown error');
                }
            }
        }
        
        remove_filter('upload_dir', $settings['upload_dir_filter']);
        
        if ($upload_success > 0) {
            wc_add_notice($upload_success . ' ' . $settings['type_label'] . '(s) uploaded successfully!', 'success');
        }
        
        if (!empty($upload_errors)) {
            foreach ($upload_errors as $error) {
                wc_add_notice($error, 'error');
            }
        }
    }
    
    // Save updated file URLs
    if (!empty($existing_file_urls)) {
        $file_links_html = '';
        foreach ($existing_file_urls as $url) {
            $file_links_html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noreferrer">' . basename($url) . '</a><br>';
        }
        update_user_meta($user_id, $settings['meta_key'], $file_links_html);
    } else {
        update_user_meta($user_id, $settings['meta_key'], '');
    }
    
    return true;
}

// Custom upload directories with unique names
function member_photos_upload_directory($upload) {
    $upload['subdir'] = '/member-photos';
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}

function member_documents_upload_directory($upload) {
    $upload['subdir'] = '/member-documents';
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}

// Reorder My Account menu items
add_filter('woocommerce_account_menu_items', 'reorder_my_account_menu', 999);
function reorder_my_account_menu($items) {
    // Create new order
    $new_order = array(
        'dashboard' => $items['dashboard'],
        'edit-account' => $items['edit-account'], // Account Details moved here
        'basic-information' => $items['basic-information'],
        'family-relationships' => $items['family-relationships'],
        'faith-religion' => $items['faith-religion'],
        'education-career' => $items['education-career'],
        'financial-information' => $items['financial-information'],
        'personal-information' => $items['personal-information'],
        'relationship-experience' => $items['relationship-experience'],
        'partner-preferences' => $items['partner-preferences'],
        'additional-information' => $items['additional-information'],
        'orders' => $items['orders'],
        'downloads' => isset($items['downloads']) ? $items['downloads'] : '',
        'edit-address' => $items['edit-address'],
        'payment-methods' => isset($items['payment-methods']) ? $items['payment-methods'] : '',
        'customer-logout' => $items['customer-logout'],
    );
    
    // Remove empty items
    return array_filter($new_order);
}

// Redirect non-admins away from wp-admin
//add_action('admin_init', 'block_wp_admin_for_subscribers');
function block_wp_admin_for_subscribers() {
    if (!current_user_can('administrator') && !wp_doing_ajax()) {
        wp_redirect(home_url('/my-account/')); // Redirect to your custom profile page
        exit;
    }
}

// Hide admin bar for non-admins
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}


// Universal password toggle - works on all pages
add_action('wp_footer', 'universal_password_toggle', 999);
function universal_password_toggle() {
	if (is_page('registration')) {
    ?>
    <script>
    (function() {
        function addPasswordToggles() {
            var passwordInputs = document.querySelectorAll('input[type="password"]');
            
            passwordInputs.forEach(function(input) {
                // Skip if already has toggle
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('password-toggle-btn')) {
                    return;
                }
                
                // Create button
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'password-toggle-btn';
                button.textContent = 'Show Password';
                button.style.cssText = 'margin-top: 8px; padding: 8px 16px; background: #6b1fb8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; display: block;';
                
                // Insert after input
                input.parentNode.insertBefore(button, input.nextSibling);
                
                // Add click handler
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        button.textContent = 'Hide Password';
                        button.style.background = '#4a1580';
                    } else {
                        input.type = 'password';
                        button.textContent = 'Show Password';
                        button.style.background = '#6b1fb8';
                    }
                });
            });
        }
        
        // Run when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', addPasswordToggles);
        } else {
            addPasswordToggles();
        }
        
        // Run again after a delay (for dynamic forms)
        setTimeout(addPasswordToggles, 1000);
        setTimeout(addPasswordToggles, 2000);
    })();
    </script>
    <?php
	}
}

// Fix CP Appointment Booking inline scripts
add_action('wp_footer', 'fix_cpapp_inline_scripts', 5);
function fix_cpapp_inline_scripts() {
    if (is_page('consultation')) {
        ?>
        <script>
        // Move variables here if needed
        </script>
        <?php
    }
}

add_action('show_user_profile', 'display_forminator_submissions');
add_action('edit_user_profile', 'display_forminator_submissions');
function display_forminator_submissions($user) {
    $form_id = 14036; // Replace with your form ID
    
    ?>
    <h3>Psychometric Test Submission</h3>
    <table class="form-table">
        <tr>
            <td>
                <?php
                global $wpdb;
                $entries = array();
                
                // Check if Forminator tables exist
                $table_name = $wpdb->prefix . 'frmt_form_entry';
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                
                if (!$table_exists) {
                    echo '<div style="padding: 15px; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;">';
                    echo '<strong>⚠️ Error:</strong> Forminator database table not found.';
                    echo '</div>';
                    return;
                }
                
                // Method 1: Find entries by _user_id in meta table (checking form_id)
                $entries = $wpdb->get_results($wpdb->prepare(
                    "SELECT DISTINCT e.* 
                    FROM {$wpdb->prefix}frmt_form_entry e
                    INNER JOIN {$wpdb->prefix}frmt_form_entry_meta m ON e.entry_id = m.entry_id
                    WHERE e.form_id = %d 
                    AND m.meta_key = '_user_id'
                    AND m.meta_value = %d
                    ORDER BY e.date_created DESC",
                    $form_id,
                    $user->ID
                ));
                
                // Method 2: Fallback - find by email in hidden-1 field
                if (empty($entries)) {
                    $entries = $wpdb->get_results($wpdb->prepare(
                        "SELECT DISTINCT e.* 
                        FROM {$wpdb->prefix}frmt_form_entry e
                        INNER JOIN {$wpdb->prefix}frmt_form_entry_meta m ON e.entry_id = m.entry_id
                        WHERE e.form_id = %d 
                        AND m.meta_key = 'hidden-1'
                        AND m.meta_value = %s
                        ORDER BY e.date_created DESC",
                        $form_id,
                        $user->user_email
                    ));
                }
                
                // Method 3: Fallback - find by any email field
                if (empty($entries)) {
                    $entries = $wpdb->get_results($wpdb->prepare(
                        "SELECT DISTINCT e.* 
                        FROM {$wpdb->prefix}frmt_form_entry e
                        INNER JOIN {$wpdb->prefix}frmt_form_entry_meta m ON e.entry_id = m.entry_id
                        WHERE e.form_id = %d 
                        AND m.meta_value = %s
                        ORDER BY e.date_created DESC",
                        $form_id,
                        $user->user_email
                    ));
                }
                
                // Display entries
                if (!empty($entries)): ?>
                    <?php foreach ($entries as $entry): ?>
                        <div style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-left: 4px solid #2271b1; border-radius: 4px;">
                            <div style="margin-bottom: 10px;">
                                <strong>📅 Submitted:</strong> 
                                <?php echo date('F j, Y g:i A', strtotime($entry->date_created)); ?>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <strong>🆔 Entry ID:</strong> <?php echo esc_html($entry->entry_id); ?>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <?php if (current_user_can('manage_options')): ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=forminator-entries&form_id=' . $form_id . '&entry_id=' . $entry->entry_id)); ?>" 
                                       class="button button-primary">
                                        👁️ View This Entry
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                        <strong>ℹ️ No submissions found</strong>
                        <p style="margin: 10px 0;">This user hasn't submitted the psychometric test yet.</p>
                        
                        <a href="<?php echo esc_url(admin_url('admin.php?page=forminator-entries&form_id=' . $form_id)); ?>" 
                           class="button">
                            View All Form Entries
                        </a>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}



// 1. Display login message for non-logged-in users on specific page
//add_action('wp_footer', 'forminator_login_message');
function forminator_login_message() {
    // Only run on the psychometric-test page
    if (!is_page('psychometric-test')) {
        return;
    }
    
    if (!is_user_logged_in()) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Try multiple selectors to find the form
            var targetForm = $('.forminator-custom-form-14036, #forminator-module-14036, [data-form-id="14036"]').first();
            
            // Fallback: find any forminator form on the page
            if (targetForm.length === 0) {
                targetForm = $('.forminator-ui, .forminator-custom-form').first();
            }
            
            if (targetForm.length > 0) {
                
                // Get current page URL for redirect after login
                var currentUrl = window.location.href;
                var loginUrl = 'https://supportingmarriage.com/my-account/?redirect_to=' + encodeURIComponent(currentUrl);
                
                // Hide the form
                targetForm.hide();
                
                // Add login message before the form
                targetForm.before(
                    '<div class="forminator-login-required" style="padding: 30px; background: #f8f9fa; border: 2px solid #331181; border-radius: 5px; text-align: center; margin: 20px 0;">' +
                        '<h3 style="margin-top: 0; color: #331181;">Login Required</h3>' +
                        '<p style="font-size: 16px; margin-bottom: 20px;">You must be logged in to access this psychometric test.</p>' +
                        '<a href="' + loginUrl + '" class="forminator-login-button" style="display: inline-block; padding: 12px 30px; background: #331181; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Login to Continue</a>' +
                    '</div>'
                );
            }
        });
        </script>
        <?php
    }
}

// 2. Securely capture logged-in user email on form submission
add_filter('forminator_custom_form_submit_field_data', 'secure_capture_user_email', 10, 2);
function secure_capture_user_email($field_data_array, $form_id) {
    //error_log('=== Email Capture Debug ===');
    //error_log('Form ID: ' . $form_id);
    
    // Only for form ID 14036
    if (intval($form_id) !== 14036) {
        return $field_data_array;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        //error_log('User is NOT logged in');
        return $field_data_array;
    }
    
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    
    //error_log('User IS logged in - Email: ' . $user_email);
    
    // Find and update the hidden email field (change 'hidden-1' to your actual field name)
    $found = false;
    foreach ($field_data_array as $key => $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-1') {
            $field_data_array[$key]['value'] = $user_email;
            $found = true;
            //error_log('✓ Updated existing hidden-1 field with email');
            break;
        }
    }
    
    // If hidden field wasn't found, add it
    if (!$found) {
        $field_data_array[] = array(
            'name'  => 'hidden-1',
            'value' => $user_email,
        );
        //error_log('✓ Added new hidden-1 field with email');
    }
    
    //error_log('=== Email Capture Complete ===');
    return $field_data_array;
}

// 3. Save verified email as separate meta
add_action('forminator_custom_form_after_save_entry', 'save_verified_user_email', 10, 2);
function save_verified_user_email($entry_id, $form_id) {
    //error_log('=== Save Meta Debug ===');
    //error_log('Entry ID: ' . $entry_id);
    //error_log('Form ID: ' . $form_id);
    
    // Only for form ID 14036
    if (intval($form_id) !== 14036) {
        return;
    }
    
    if (!is_user_logged_in()) {
        //error_log('User is NOT logged in');
        return;
    }
    
    $current_user = wp_get_current_user();
    
    //error_log('Saving user meta data...');
    
    // Save additional user data as meta
    update_post_meta($entry_id, 'verified_user_email', $current_user->user_email);
    update_post_meta($entry_id, 'verified_user_id', $current_user->ID);
    update_post_meta($entry_id, 'verified_user_name', $current_user->display_name);
    
    //error_log('✓ Meta data saved - Email: ' . $current_user->user_email);
    //error_log('=== Save Meta Complete ===');
}

add_shortcode('psychometric_form', 'display_psychometric_form');
function display_psychometric_form() {
    // Not logged in
    if (!is_user_logged_in()) {
        $current_url = esc_url($_SERVER['REQUEST_URI']);
        $login_url = 'https://supportingmarriage.com/my-account/?redirect_to=' . urlencode(home_url($current_url));
        
        return '<div style="padding: 30px; background: #f8f9fa; border: 2px solid #6B1FB8; border-radius: 5px; text-align: center; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #6B1FB8;">Login Required</h3>
            <p style="font-size: 16px; margin-bottom: 20px;">You must be logged in to access this psychometric test.</p>
            <a href="' . $login_url . '" style="display: inline-block; padding: 12px 30px; background: #6B1FB8; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Login to Continue</a>
        </div>';
    }
    
    // Logged in - check if already submitted using email
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    
    global $wpdb;
    
    // Search for submissions with this email address in form 14036
    $has_submitted = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
        FROM {$wpdb->prefix}frmt_form_entry 
        WHERE form_id = 14036
        AND entry_id IN (
            SELECT entry_id 
            FROM {$wpdb->prefix}frmt_form_entry_meta 
            WHERE meta_value LIKE %s
        )",
        '%' . $wpdb->esc_like($user_email) . '%'
    ));
    
    // Already submitted
    if ($has_submitted > 0) {
        return '<div style="padding: 30px; background: #f8f9fa; border: 2px solid #6B1FB8; border-radius: 5px; text-align: center; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #6B1FB8;">✓ Test Already Completed</h3>
            <p style="font-size: 16px; margin-bottom: 0;">You have already completed this psychometric test. Thank you for your submission!</p>
        </div>';
    }
    
    // Show the form
    return do_shortcode('[forminator_form id="14036"]');
}


// Add YouTube video above shop products
add_action( 'woocommerce_before_shop_loop', 'add_video_to_shop_page', 5 );
function add_video_to_shop_page() {
    if ( is_shop() ) {
        echo '<div class="shop-intro-video" style="margin-bottom: 40px; text-align: center;">';
        echo '<h2>Book Trailer - "It didn\'t have to be this way"</h2>';
        echo '<iframe width="800" height="450" src="https://www.youtube.com/embed/gJ19FeirDPA?si=l0szHCb_VGt_pewB" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        echo '</div>';
    }
}


// Add member ID and name to Psychometric Test form submission
add_filter('forminator_custom_form_submit_field_data', 'add_member_id_to_psychometric_test', 10, 2);
function add_member_id_to_psychometric_test($field_data_array, $form_id) {
    //error_log('=== Psychometric Test Member ID Addition Started ===');
    //error_log('Form ID: ' . $form_id);
    
    // Only for Psychometric Test form (ID: 14036)
    if (intval($form_id) !== 14036) {
        return $field_data_array;
    }
    
    // Get email from hidden-1 field
    $user_email = '';
    
    foreach ($field_data_array as $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-1' && isset($field['value'])) {
            $user_email = $field['value'];
            break;
        }
    }
    
    // If no email found, try to get current logged-in user
    if (empty($user_email) && is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
    }
    
    if (empty($user_email)) {
        //error_log('No email found for member ID lookup');
        return $field_data_array;
    }
    
    // Get user by email
    $user = get_user_by('email', $user_email);
    
    if (!$user) {
        //error_log('User not found for email: ' . $user_email);
        return $field_data_array;
    }
    
    // Get member ID from user meta
    $member_id = get_user_meta($user->ID, 'member-id', true);
    
    if (empty($member_id)) {
        //error_log('No member ID found for user: ' . $user->ID);
        return $field_data_array;
    }
    
    //error_log('Member ID found: ' . $member_id);
    
    // Get first name and last name
    $first_name = get_user_meta($user->ID, 'first_name', true);
    $last_name = get_user_meta($user->ID, 'last_name', true);
    $full_name = trim($first_name . ' ' . $last_name);
    
    // If no name in user meta, try display name
    if (empty($full_name)) {
        $full_name = $user->display_name;
    }
    
    //error_log('User name found: ' . $full_name);
    
    // Add/update the hidden-3 field (Member ID) in the field data array
    $found = false;
    foreach ($field_data_array as $key => $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-3') {
            $field_data_array[$key]['value'] = $member_id;
            $found = true;
            //error_log('Updated existing hidden-3 field with member ID');
            break;
        }
    }
    
    // If hidden-3 wasn't found, add it
    if (!$found) {
        $field_data_array[] = array(
            'name'  => 'hidden-3',
            'value' => $member_id,
        );
        //error_log('Added new hidden-3 field with member ID');
    }
    
    // Add/update the hidden-4 field (Name) in the field data array
    $found_name = false;
    foreach ($field_data_array as $key => $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-4') {
            $field_data_array[$key]['value'] = $full_name;
            $found_name = true;
            //error_log('Updated existing hidden-4 field with name');
            break;
        }
    }
    
    // If hidden-4 wasn't found, add it
    if (!$found_name) {
        $field_data_array[] = array(
            'name'  => 'hidden-4',
            'value' => $full_name,
        );
        //error_log('Added new hidden-4 field with name');
    }
    
    // **NEW: Store in global variable for PDF renaming**
    global $psychometric_test_member_id;
    $psychometric_test_member_id = $member_id;
    //error_log('Stored member ID in global: ' . $member_id);
    
    //error_log('=== Psychometric Test Member ID Addition Completed ===');
    return $field_data_array;
}

// Add member ID directly to email message
add_filter('forminator_custom_form_mail_data', 'inject_member_id_to_psychometric_email', 10, 3);
function inject_member_id_to_psychometric_email($mail_data, $custom_form, $data) {
    // Only for form 14036
    if ($custom_form->id != 14036) {
        return $mail_data;
    }
    
    // Get member ID from submission data
    $member_id = '';
    foreach ($data as $field) {
        if (isset($field['name']) && $field['name'] === 'hidden-3' && isset($field['value'])) {
            $member_id = $field['value'];
            break;
        }
    }
    
    if (empty($member_id)) {
        return $mail_data;
    }
    
    // Inject member ID at the top of the email message
    if (isset($mail_data['message'])) {
        $member_id_line = "Member ID: " . $member_id . "\n" . str_repeat("-", 40) . "\n\n";
        $mail_data['message'] = $member_id_line . $mail_data['message'];
    }
    
    return $mail_data;
}


// Enqueue intl-tel-input library
add_action('wp_enqueue_scripts', 'enqueue_intl_tel_input');
function enqueue_intl_tel_input() {
    if (is_account_page()) {
        // Enqueue CSS
        wp_enqueue_style(
            'intl-tel-input-css',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css',
            array(),
            '18.2.1'
        );
        
        // Enqueue JS
        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js',
            array('jquery'),
            '18.2.1',
            true
        );
    }
}

// Add Mobile Number field after email in WooCommerce My Account
add_action('woocommerce_edit_account_form', 'add_mobile_after_email');
function add_mobile_after_email() {
    $user = wp_get_current_user();
    $mobile = get_user_meta($user->ID, 'billing_phone', true);
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" id="mobile-no-wrapper" style="display:none;">
        <label for="billing_phone">Mobile No. <span class="required">*</span></label>
        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr($mobile); ?>" />
        <input type="hidden" name="billing_phone_full" id="billing_phone_full" value="<?php echo esc_attr($mobile); ?>" />
        <span class="error-message" id="mobile-error" style="display:none; color: #e2401c; font-size: 0.875em;"></span>
    </p>
    
    <script>
    jQuery(document).ready(function($) {
        // Move the field after email
        $('#mobile-no-wrapper').insertAfter($('p:has(#account_email)')).show();
        
        var input = document.querySelector("#billing_phone");
        var errorMsg = document.querySelector("#mobile-error");
        
        // Error messages
        var errorMap = [
            "Invalid number",
            "Invalid country code",
            "Too short",
            "Too long",
            "Invalid number"
        ];
        
        // Initialize intl-tel-input
        var iti = window.intlTelInput(input, {
            initialCountry: "gb",
            preferredCountries: ["gb"],
            separateDialCode: true,
            nationalMode: false,
            formatOnDisplay: true,
            autoPlaceholder: "polite",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                return selectedCountryPlaceholder;
            }
        });
        
        // Function to adjust input padding based on dial code width
        function adjustPadding() {
            var dialCodeElement = $('.iti__selected-dial-code');
            if (dialCodeElement.length) {
                var dialCodeWidth = dialCodeElement.outerWidth();
                var flagWidth = 52; // Flag + dropdown arrow width
                var totalPadding = flagWidth + dialCodeWidth + 15; // Extra spacing
                $('#billing_phone').css('padding-left', totalPadding + 'px');
            }
        }
        
        // Adjust padding on load
        setTimeout(adjustPadding, 150);
        
        // Adjust padding when country changes
        input.addEventListener('countrychange', function() {
            adjustPadding();
        });
        
        // Load saved number if exists
        <?php if ($mobile): ?>
        setTimeout(function() {
            iti.setNumber('<?php echo esc_js($mobile); ?>');
            adjustPadding();
        }, 100);
        <?php endif; ?>
        
        // Reset validation on input
        input.addEventListener('blur', function() {
            reset();
            if (input.value.trim()) {
                if (iti.isValidNumber()) {
                    errorMsg.style.display = "none";
                    input.classList.remove("error");
                } else {
                    input.classList.add("error");
                    var errorCode = iti.getValidationError();
                    errorMsg.innerHTML = errorMap[errorCode] || "Invalid number";
                    errorMsg.style.display = "block";
                }
            }
        });
        
        // Reset on keyup
        input.addEventListener('change', reset);
        input.addEventListener('keyup', reset);
        
        function reset() {
            errorMsg.style.display = "none";
            input.classList.remove("error");
        }
        
        // Store full international number when form is submitted
        var form = $('form.woocommerce-EditAccountForm');
        
        form.on('submit', function(e) {
            var phoneValue = input.value.trim();
            
            // If field is empty
            if (!phoneValue) {
                e.preventDefault();
                input.classList.add("error");
                errorMsg.innerHTML = "Mobile No. is required";
                errorMsg.style.display = "block";
                return false;
            }
            
            // Check if valid
            if (iti.isValidNumber()) {
                $('#billing_phone_full').val(iti.getNumber());
                errorMsg.style.display = "none";
                input.classList.remove("error");
                return true;
            } else {
                e.preventDefault();
                input.classList.add("error");
                var errorCode = iti.getValidationError();
                errorMsg.innerHTML = errorMap[errorCode] || "Please enter a valid mobile number";
                errorMsg.style.display = "block";
                
                // Scroll to error
                $('html, body').animate({
                    scrollTop: $('#billing_phone').offset().top - 100
                }, 500);
                
                return false;
            }
        });
    });
    </script>
    
    <style>
        .iti {
            width: 100%;
            display: block;
        }
        .iti__selected-country {
            padding: 0 8px 0 8px;
        }
        .iti__selected-dial-code {
            margin-left: 6px;
        }
        #billing_phone {
            padding-left: 90px !important; /* Default, will be adjusted by JS */
            width: 100%;
        }
        #billing_phone.error {
            border-color: #e2401c !important;
        }
        .iti__flag {
            background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/img/flags.png");
        }
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {
                background-image: url("https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/img/flags@2x.png");
            }
        }
    </style>
    <?php
}

// Save Mobile Number field
add_action('woocommerce_save_account_details', 'save_mobile_field_my_account');
function save_mobile_field_my_account($user_id) {
    if (isset($_POST['billing_phone_full']) && !empty($_POST['billing_phone_full'])) {
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone_full']));
    }
}

// Validate Mobile Number field
add_action('woocommerce_save_account_details_errors', 'validate_mobile_field_my_account', 10, 1);
function validate_mobile_field_my_account($args) {
    if (empty($_POST['billing_phone_full'])) {
        $args->add('error', __('Mobile No. is required!', 'woocommerce'));
    }
}

// Change "Phone" to "Mobile No." in user profile billing fields
add_filter('woocommerce_customer_meta_fields', 'change_phone_label_in_user_profile');
function change_phone_label_in_user_profile($fields) {
    if (isset($fields['billing']['fields']['billing_phone'])) {
        $fields['billing']['fields']['billing_phone']['label'] = __('Mobile No.', 'woocommerce');
    }
    return $fields;
}


// 1. Secure file access - Block direct access to member files
add_action('init', 'secure_member_files_htaccess');
function secure_member_files_htaccess() {
    $upload_dir = wp_upload_dir();
    
    // Secure photos directory
    $photos_dir = $upload_dir['basedir'] . '/member-photos';
    $photos_htaccess = $photos_dir . '/.htaccess';
    
    if (!file_exists($photos_dir)) {
        wp_mkdir_p($photos_dir);
    }
    
    if (!file_exists($photos_htaccess)) {
        $htaccess_content = "# Deny direct access\n";
        $htaccess_content .= "<Files ~ \"\\.(jpg|jpeg|png|gif)$\">\n";
        $htaccess_content .= "    Order Allow,Deny\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</Files>\n";
        
        file_put_contents($photos_htaccess, $htaccess_content);
    }
    
    // Secure documents directory
    $docs_dir = $upload_dir['basedir'] . '/member-documents';
    $docs_htaccess = $docs_dir . '/.htaccess';
    
    if (!file_exists($docs_dir)) {
        wp_mkdir_p($docs_dir);
    }
    
    if (!file_exists($docs_htaccess)) {
        $htaccess_content = "# Deny direct access\n";
        $htaccess_content .= "<Files ~ \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx)$\">\n";
        $htaccess_content .= "    Order Allow,Deny\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</Files>\n";
        
        file_put_contents($docs_htaccess, $htaccess_content);
    }
}

// 2. Create secure file download endpoint
add_action('init', 'register_secure_file_endpoint');
function register_secure_file_endpoint() {
    add_rewrite_rule('^secure-file/([^/]+)/(.+)/?$', 'index.php?secure_file_type=$matches[1]&secure_file_name=$matches[2]', 'top');
}

add_filter('query_vars', 'add_secure_file_query_vars');
function add_secure_file_query_vars($vars) {
    $vars[] = 'secure_file_type';
    $vars[] = 'secure_file_name';
    return $vars;
}

// 3. Handle secure file requests
add_action('template_redirect', 'handle_secure_file_request');
function handle_secure_file_request() {
    $file_type = get_query_var('secure_file_type');
    $file_name = get_query_var('secure_file_name');
    
    if (!$file_type || !$file_name) {
        return;
    }
    
    // Validate file type
    if (!in_array($file_type, array('photos', 'documents'))) {
        wp_die('Invalid file type');
    }
    
    // Sanitize filename
    $file_name = sanitize_file_name($file_name);
    
    // Get file path
    $upload_dir = wp_upload_dir();
    if ($file_type === 'photos') {
        $file_path = $upload_dir['basedir'] . '/member-photos/' . $file_name;
    } else {
        $file_path = $upload_dir['basedir'] . '/member-documents/' . $file_name;
    }
    
    // Check if file exists
    if (!file_exists($file_path)) {
        wp_die('File not found');
    }
    
    // Get file owner
    $file_owner_id = get_file_owner_id($file_path);
    
    // Check permissions
    if (!can_user_access_file($file_owner_id)) {
        wp_die('You do not have permission to access this file');
    }
    
    // Serve the file
    serve_secure_file($file_path);
    exit;
}

// 4. Check if current user can access the file
function can_user_access_file($file_owner_id) {
    // Not logged in - deny
    if (!is_user_logged_in()) {
        return false;
    }
    
    $current_user_id = get_current_user_id();
    
    // Admin can access all files
    if (current_user_can('administrator')) {
        return true;
    }
    
    // Owner can access their own files
    if ($current_user_id === $file_owner_id) {
        return true;
    }
    
    // Everyone else - deny
    return false;
}

// 5. Get file owner ID from database
function get_file_owner_id($file_path) {
    global $wpdb;
    
    $upload_dir = wp_upload_dir();
    $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
    
    // Search in upload-photos meta
    $photo_owner = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} 
        WHERE meta_key = 'upload-photos' 
        AND meta_value LIKE %s 
        LIMIT 1",
        '%' . $wpdb->esc_like($file_url) . '%'
    ));
    
    if ($photo_owner) {
        return (int) $photo_owner;
    }
    
    // Search in upload-documents meta
    $doc_owner = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} 
        WHERE meta_key = 'upload-documents' 
        AND meta_value LIKE %s 
        LIMIT 1",
        '%' . $wpdb->esc_like($file_url) . '%'
    ));
    
    if ($doc_owner) {
        return (int) $doc_owner;
    }
    
    return 0; // No owner found
}

// 6. Serve the file securely
function serve_secure_file($file_path) {
    // Get file info
    $file_name = basename($file_path);
    $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    // Set content type
    $mime_types = array(
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    );
    
    $content_type = isset($mime_types[$file_ext]) ? $mime_types[$file_ext] : 'application/octet-stream';
    
    // Clear output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    header('Content-Type: ' . $content_type);
    header('Content-Length: ' . filesize($file_path));
    header('Content-Disposition: inline; filename="' . $file_name . '"');
    header('Cache-Control: private, max-age=3600');
    header('Pragma: private');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    
    // Output file
    readfile($file_path);
}

// 7. Replace file URLs with secure URLs when displaying
function get_secure_file_url($file_url) {
    $upload_dir = wp_upload_dir();
    
    // Check if it's a member photo
    if (strpos($file_url, '/member-photos/') !== false) {
        $file_name = basename($file_url);
        return home_url('/secure-file/photos/' . $file_name);
    }
    
    // Check if it's a member document
    if (strpos($file_url, '/member-documents/') !== false) {
        $file_name = basename($file_url);
        return home_url('/secure-file/documents/' . $file_name);
    }
    
    return $file_url;
}

// 8. Filter URLs when displaying in My Account
add_filter('member_file_url', 'convert_to_secure_url');
function convert_to_secure_url($url) {
    return get_secure_file_url($url);
}

// 9. Update file upload functions to store user ID association
// Modify your handle_member_file_uploads function to track ownership
function track_file_ownership($user_id, $file_url, $type) {
    global $wpdb;
    
    // Store file ownership in custom table (optional, for better performance)
    $table_name = $wpdb->prefix . 'member_file_ownership';
    
    // Create table if it doesn't exist
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        file_url varchar(500) NOT NULL,
        file_type varchar(20) NOT NULL,
        uploaded_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY file_url (file_url)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Insert ownership record
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'file_url' => $file_url,
            'file_type' => $type
        ),
        array('%d', '%s', '%s')
    );
}

// 10. Flush rewrite rules once
add_action('after_switch_theme', 'flush_rewrite_rules');

// Rename generated Forminator PDF to include submission ID
add_filter('forminator_addon_file_upload_file', 'rename_forminator_generated_pdf', 10, 3);
function rename_forminator_generated_pdf($file_path, $form_id, $entry_id) {
    // For both registration form 13233 and PDF regeneration form 14497
    if (!in_array(intval($form_id), [13233, 14497])) {
        return $file_path;
    }
    
    $filename = basename($file_path);
    
    // Check if this is a registration PDF (either original or updated)
    if (strpos($filename, 'registration-submission') === false && 
        strpos($filename, 'updated-registration-submission') === false) {
        return $file_path;
    }
    
    // Get file info
    $file_info = pathinfo($file_path);
    $extension = isset($file_info['extension']) ? $file_info['extension'] : 'pdf';
    
    // Determine prefix based on form
    $prefix = (intval($form_id) === 14497) ? 'updated-registration-submission-' : 'registration-submission-';
    
    // Create new filename with submission ID
    $new_filename = $prefix . $entry_id . '.' . $extension;
    $new_path = $file_info['dirname'] . '/' . $new_filename;
    
    // Rename the file
    if (file_exists($file_path) && rename($file_path, $new_path)) {
        //error_log('✓ Renamed generated PDF: ' . $filename . ' -> ' . $new_filename);
        return $new_path;
    }
    
    return $file_path;
}

// Alternative: Hook into the PDF generation settings
add_filter('forminator_custom_form_pdf_file_name', 'customize_generated_pdf_filename', 10, 3);
function customize_generated_pdf_filename($filename, $form_id, $entry_id) {
    // For both registration form 13233 and PDF regeneration form 14497
    if (!in_array(intval($form_id), [13233, 14497])) {
        return $filename;
    }
    
    // Check if this is a registration PDF
    if (strpos($filename, 'registration-submission') !== false || 
        strpos($filename, 'updated-registration-submission') !== false) {
        
        $file_info = pathinfo($filename);
        $extension = isset($file_info['extension']) ? $file_info['extension'] : 'pdf';
        
        // Determine prefix based on form
        $prefix = (intval($form_id) === 14497) ? 'updated-registration-submission-' : 'registration-submission-';
        
        // Return new filename with submission ID
        $new_filename = $prefix . $entry_id . '.' . $extension;
        
        //error_log('✓ Customized PDF filename: ' . $filename . ' -> ' . $new_filename);
        
        return $new_filename;
    }
    
    return $filename;
}

// Custom instant logout handler (works for both header and footer)
add_action('init', 'custom_instant_logout_handler');
function custom_instant_logout_handler() {
    if (isset($_GET['custom-logout']) && $_GET['custom-logout'] === 'true') {
        if (is_user_logged_in()) {
            // Verify nonce for security
            if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'custom-logout-nonce')) {
                wp_logout();
                wp_safe_redirect(home_url('/my-account/'));
                exit;
            }
        }
    }
}

// Shortcode for logout URL (for footer)
add_shortcode('instant_logout_url', 'get_instant_logout_url');
function get_instant_logout_url() {
    $nonce = wp_create_nonce('custom-logout-nonce');
    return home_url('/?custom-logout=true&_wpnonce=' . $nonce);
}

// Add dynamic nonce to header menu logout link
add_filter('wp_nav_menu_items', 'add_nonce_to_logout_link', 10, 2);
function add_nonce_to_logout_link($items, $args) {
    if (is_user_logged_in()) {
        $nonce = wp_create_nonce('custom-logout-nonce');
        $logout_url = home_url('/?custom-logout=true&_wpnonce=' . $nonce);
        
        // Replace the static URL with dynamic one
        $items = str_replace(
            'href="https://supportingmarriage.com/?custom-logout=true"',
            'href="' . $logout_url . '"',
            $items
        );
        
        // Also support relative URLs
        $items = str_replace(
            'href="/?custom-logout=true"',
            'href="' . $logout_url . '"',
            $items
        );
    }
    return $items;
}

// Block "Account Activated" emails completely
add_filter('wp_mail', 'block_account_activated_email', 999);
function block_account_activated_email($args) {
    // Check if subject contains "Account Activated"
    if (isset($args['subject']) && strpos($args['subject'], 'Account Activated') !== false) {
        // Don't send the email
        $args['to'] = '';
        return $args;
    }
    return $args;
}

// Hide Personal Options and Elementor AI sections without flash
add_action('admin_head-profile.php', 'hide_complete_profile_sections');
add_action('admin_head-user-edit.php', 'hide_complete_profile_sections');
function hide_complete_profile_sections() {
    ?>
    <style>
        /* Hide sections immediately to prevent flash */
        #your-profile > h2:first-of-type,
        #your-profile > table.form-table:first-of-type {
            display: none !important;
        }
        
        /* Hide any Elementor sections */
        h2:has(+ table .elementor-ai-user-profile),
        table:has(.elementor-ai-user-profile) {
            display: none !important;
        }
    </style>
    
    <script>
        jQuery(document).ready(function($) {
            // Clean up Personal Options section
            $('#your-profile h2').filter(function() {
                return $(this).text().trim() === 'Personal Options';
            }).each(function() {
                $(this).next('table').remove();
                $(this).remove();
            });
            
            // Clean up Elementor sections
            $('#your-profile h2, #your-profile h3').filter(function() {
                return $(this).text().includes('Elementor');
            }).each(function() {
                $(this).next('table').remove();
                $(this).remove();
            });
        });
    </script>
    <?php
}

add_filter( 'woocommerce_account_menu_items', 'remove_downloads_my_account', 999 );
function remove_downloads_my_account( $items ) {
    unset($items['downloads']);
    return $items;
}

// Add Member ID column to users list
add_filter('manage_users_columns', 'add_member_id_column');
function add_member_id_column($columns) {
    $columns['member_id'] = 'Member ID';
    return $columns;
}

// Populate the Member ID column with data
add_filter('manage_users_custom_column', 'show_member_id_column_content', 10, 3);
function show_member_id_column_content($value, $column_name, $user_id) {
    if ($column_name === 'member_id') {
        $member_id = get_user_meta($user_id, 'member-id', true);
        return $member_id ? esc_html($member_id) : '—';
    }
    return $value;
}

// Make the Member ID column sortable
add_filter('manage_users_sortable_columns', 'make_member_id_sortable');
function make_member_id_sortable($columns) {
    $columns['member_id'] = 'member_id';
    return $columns;
}

// Handle the sorting logic
add_action('pre_get_users', 'sort_users_by_member_id');
function sort_users_by_member_id($query) {
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');
    
    if ($orderby === 'member_id') {
        $query->set('meta_key', 'member-id');
        $query->set('orderby', 'meta_value');
    }
}


// For new user registration - set initial profile-last-updated date
add_action('user_register', 'set_initial_profile_updated_date');
function set_initial_profile_updated_date($user_id) {
    $current_date = date('Y-m-d H:i:s'); // or use 'd-m-Y' for day-month-year format
    update_user_meta($user_id, 'profile-last-updated', $current_date);
}

// For existing users - update profile-last-updated on profile updates
add_action('profile_update', 'update_profile_last_updated_date', 10, 2);
function update_profile_last_updated_date($user_id, $old_user_data) {
    $current_date = date('Y-m-d H:i:s');
    update_user_meta($user_id, 'profile-last-updated', $current_date);
}

add_action('show_user_profile', 'make_email_readonly');
add_action('edit_user_profile', 'make_email_readonly');

function make_email_readonly() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#email').attr('readonly', true);
    });
    </script>
    <?php
}


/**
 * Forminator User Search & Auto-Fill - FINAL VERSION
 * Paste this at the end of functions.php
 */

// Shortcode for search box
add_shortcode('user_search_box', function() {
    
    if (!current_user_can('manage_options')) {
        return '<div style="background: #ffdddd; padding: 20px; margin: 20px 0; border: 2px solid red;">
            <strong>Access Restricted:</strong> Only administrators can use this feature.
        </div>';
    }
    
    ob_start();
    ?>
    
    <div style="background: #0073aa; color: white; padding: 30px; margin: 20px 0; border-radius: 10px;">
        <h2 style="margin: 0 0 20px 0; color: white;">Search User by Email</h2>
        <p style="margin: 0 0 20px 0;">Enter a user's email to auto-fill the form below with their profile data.</p>
        
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <input type="email" 
                   id="search-email-input" 
                   placeholder="user@example.com"
                   style="flex: 1; padding: 12px; font-size: 14px; border: none; border-radius: 5px;">
            <button id="search-email-btn" 
                    style="padding: 12px 20px; font-size: 14px; background: white; color: #0073aa; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                Search
            </button>
        </div>
        
        <div id="search-result" style="background: white; color: #333; padding: 15px; border-radius: 5px; display: none;"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        console.log('✓ Search & Fill System Loaded');
        
        // Fix phone field dropdown width
        $('<style>.iti__dropdown-content { max-width: 300px !important; }</style>').appendTo('head');
        
        // Field mapping: user_meta_key => forminator_field_id
        var fieldMap = {
            'user_login': 'text-1',
            'user_email': 'email-1',
            'email': 'email-1',
            'first_name': 'name-1',
            'last_name': 'name-2',
            'billing_phone': 'phone-1',
            'member-id': 'text-35',
            'registration-date': 'hidden-2',
            'gender': 'select-1',
            'date-of-birth': 'date-1',
            'nationality': 'text-2',
            'ethnic-or-cultural-background': 'text-7',
            'valid-passports': 'text-31',
            'address-country': 'address-1-country',
            'address-city': 'text-34',
            'how-long-lived-in-your-country': 'text-33',
            'height': 'text-5',
            'body-type': 'select-9',
            'smoker': 'select-10',
            'drinker': 'select-11',
            'health': 'select-12',
            'marital-status': 'select-2',
            'number-of-children': 'text-8',
            'children-ages': 'text-9',
            'do-you-want-children': 'select-16',
            'how-many-children-do-you-want': 'text-32',
            'faith-or-religion': 'text-10',
            'practising-level': 'select-6',
            'highest-level-of-education': 'text-12',
            'field-of-study': 'text-13',
            'current-occupation-or-business': 'text-14',
            'duration-in-current-occupation-business': 'text-15',
            'approximate-income-range-or-business-details': 'text-16',
            'property-ownership': 'select-4',
            'car-ownership': 'select-15',
            'savings': 'select-5',
            'languages-spoken-fluently': 'text-18',
            'personality-description': 'text-19',
            'lifestyle-description': 'text-20',
            'hobbies-interest-passions': 'text-21',
            'relationship-or-marriage-experience': 'text-22',
            'lessons-learned-from-past-relationships': 'text-23',
            'happy-to-relocate': 'select-14',
            'write-the-countries-you-are-happy-to-relocate-to': 'text-30',
            'age-range-preferred-from': 'text-26',
            'age-range-preferred-to': 'text-27',
            'height-range-preferred-from': 'text-28',
            'height-range-preferred-to': 'text-29',
            'accept-smoker': 'select-17',
            'accept-drinker': 'select-18',
            'health-preferred': 'select-19',
            'important-values-in-future-spouse': 'text-24',
            'description-of-future-spouse': 'textarea-1',
            'willing-to-entertain-people': 'select-13',
            'conflict-handling-approach': 'textarea-2',
            'successful-relationship-definition': 'textarea-3',
            'social-media-and-professional-platform': 'textarea-4',
            'how-did-you-learn-about-the-supporting-marriage-platform': 'select-8',
            'payment-method': 'radio-2'
        };
        
        $('#search-email-btn').click(function() {
            var email = $('#search-email-input').val().trim();
            var result = $('#search-result');
            
            if (!email) {
                result.show().html('<strong style="color:red;">Please enter an email address</strong>');
                return;
            }
            
            result.show().html('Searching for: ' + email + '...');
            
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'search_user_data',
                    email: email
                },
                success: function(response) {
                    console.log('Search response:', response);
                    
                    if (response.success) {
                        result.html('<strong style="color:green;">USER FOUND: ' + response.data.name + '</strong><br>' +
                                  'User ID: ' + response.data.user_id + '<br>' +
                                  'Total fields: ' + response.data.field_count + '<br>' +
                                  '<button id="fill-form-btn" style="margin-top:10px; padding:10px 20px; background:#0073aa; color:white; border:none; cursor:pointer; font-weight:bold;">Fill Form Now</button>');
                        
                        $('#fill-form-btn').click(function() {
                            $(this).prop('disabled', true).text('Filling...');
                            fillFormWithMapping(response.data.fields, fieldMap);
                        });
                    } else {
                        result.html('<strong style="color:red;">ERROR:</strong> ' + response.data.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error:', xhr.responseText);
                    result.html('<strong style="color:red;">REQUEST FAILED</strong><br>Check console for details.');
                }
            });
        });
        
        function fillFormWithMapping(userData, mapping) {
            console.log('=== STARTING FORM FILL ===');
            console.log('User data:', userData);
            console.log('Looking for member-id:', userData['member-id']);
            
            var filled = 0;
            var notMapped = [];
            var notFound = [];
            
            $.each(userData, function(metaKey, value) {
                if (!value) return;
                
                // Check if we have a mapping for this field
                if (!mapping[metaKey]) {
                    notMapped.push(metaKey);
                    return;
                }
                
                var fieldId = mapping[metaKey];
                console.log('Filling:', metaKey, '→', fieldId, '=', value);
                
                // Find the field using Forminator's naming pattern
                var $field = $('[name="' + fieldId + '"]');
                
                // If not found, try with form ID prefix (Forminator sometimes adds this)
                if ($field.length === 0) {
                    $field = $('[name*="[' + fieldId + ']"]');
                }
                
                if ($field.length > 0) {
                    console.log('  ✓ Found field:', fieldId);
                    
                    // Special handling for phone field with international prefix
                    if (fieldId === 'phone-1') {
                        // For international phone field, fill the visible input
                        var $phoneInput = $('[name="phone-1"]');
                        if ($phoneInput.length > 0) {
                            // Remove any country code if it's in the value
                            var cleanPhone = value.replace(/^\+\d+\s*/, ''); // Remove +44 etc
                            $phoneInput.val(cleanPhone).trigger('input').trigger('change');
                            filled++;
                        }
                    } 
                    // Special handling for hidden fields
                    else if (fieldId === 'hidden-1' || fieldId === 'hidden-2' || $field.attr('type') === 'hidden') {
                        $field.val(value);
                        filled++;
                        console.log('  ✓ Filled hidden field:', fieldId, '=', value);
                    }
                    else if ($field.is(':radio')) {
                        // Radio button
                        $('[name="' + $field.attr('name') + '"][value="' + value + '"]').prop('checked', true).trigger('change');
                        filled++;
                    } else if ($field.is(':checkbox')) {
                        // Checkbox
                        if (value == '1' || value == 'yes' || value == 'Yes' || value == true) {
                            $field.prop('checked', true).trigger('change');
                            filled++;
                        }
                    } else if ($field.is('select')) {
                        // Select dropdown
                        $field.val(value).trigger('change');
                        filled++;
                    } else {
                        // Text, email, textarea, etc.
                        $field.val(value).trigger('change').trigger('input');
                        filled++;
                    }
                } else {
                    console.log('  ✗ Field not found in form:', fieldId);
                    notFound.push(fieldId);
                }
            });
            
            console.log('=== FILL COMPLETE ===');
            console.log('✓ Filled:', filled);
            console.log('⚠ Not mapped:', notMapped.length);
            console.log('✗ Not found:', notFound.length);
            
            var html = '<strong style="color:green; font-size:18px;">SUCCESS!</strong><br><br>';
            html += '<strong>' + filled + ' fields filled</strong><br>';
            
            if (notMapped.length > 0) {
                html += '<br><details><summary style="cursor:pointer;">' + notMapped.length + ' fields not mapped (click to view)</summary>';
                html += '<div style="font-size:12px; margin-top:5px;">' + notMapped.join(', ') + '</div></details>';
            }
            
            if (notFound.length > 0) {
                html += '<br><details><summary style="cursor:pointer;">' + notFound.length + ' mapped fields not found in form</summary>';
                html += '<div style="font-size:12px; margin-top:5px;">' + notFound.join(', ') + '</div></details>';
            }
            
            html += '<br><br><strong>Scroll down to review the form, then click Submit to generate PDF.</strong>';
            
            $('#search-result').html(html);
            
            // Make all form fields read-only
            makeFormReadOnly();
            
            // Scroll to show the form
            $('html, body').animate({
                scrollTop: $('#search-result').offset().top + 150
            }, 500);
        }
        
        // Function to make entire form read-only
        function makeFormReadOnly() {
            console.log('Making form read-only...');
            
            // Make all text inputs, textareas read-only
            $('#forminator-module-14497 input[type="text"], #forminator-module-14497 input[type="email"], #forminator-module-14497 input[type="number"], #forminator-module-14497 input[type="tel"], #forminator-module-14497 textarea').prop('readonly', true).css({
                'background-color': '#f5f5f5',
                'cursor': 'not-allowed'
            });
            
            // For selects - disable them but re-enable on submit
            $('#forminator-module-14497 select').prop('disabled', true).css({
                'background-color': '#f5f5f5',
                'cursor': 'not-allowed'
            });
            
            // Disable radio buttons and checkboxes
            $('#forminator-module-14497 input[type="radio"], #forminator-module-14497 input[type="checkbox"]').prop('disabled', true).css({
                'cursor': 'not-allowed'
            });
            
            // Disable date pickers
            $('#forminator-module-14497 input[type="date"]').prop('readonly', true).css({
                'background-color': '#f5f5f5'
            });
            
            // Disable Forminator datepickers (jQuery UI datepicker)
            $('#forminator-module-14497 .forminator-datepicker').each(function() {
                // Disable the datepicker widget
                if ($(this).hasClass('hasDatepicker')) {
                    $(this).datepicker('destroy');
                }
                // Make the input readonly
                $(this).prop('readonly', true).css({
                    'background-color': '#f5f5f5',
                    'cursor': 'not-allowed'
                });
            });
            
            // Prevent calendar icon clicks
            $('#forminator-module-14497 .forminator-icon-calendar').css({
                'pointer-events': 'none',
                'opacity': '0.5'
            });
            
            // Disable country selector in phone field
            $('.iti__selected-country').css('pointer-events', 'none');
            
            // Add visual indicator
            $('#forminator-module-14497').prepend('<div id="readonly-banner" style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center;"><strong>READ-ONLY MODE</strong><br>Fields are locked and cannot be edited. Review the information and click Submit to generate PDF.</div>');
            
            // Intercept form submission - re-enable fields JUST before submit
            var form = document.querySelector('#forminator-module-14497');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Re-enabling fields for submission...');
                    
                    // Re-enable all disabled fields so their values submit
                    $('#forminator-module-14497 select').prop('disabled', false);
                    $('#forminator-module-14497 input[type="radio"], #forminator-module-14497 input[type="checkbox"]').prop('disabled', false);
                    
                    console.log('Fields re-enabled');
                    // Form will now submit with all values
                }, true); // Use capture phase to ensure this runs first
            }
            
            console.log('Form is now read-only');
        }
        
        // Allow Enter key
        $('#search-email-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#search-email-btn').click();
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
});

// AJAX handler
add_action('wp_ajax_search_user_data', function() {
    $email = sanitize_email($_POST['email']);
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied']);
        return;
    }
    
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email address']);
        return;
    }
    
    $user = get_user_by('email', $email);
    
    if (!$user) {
        wp_send_json_error(['message' => 'No user found with email: ' . $email]);
        return;
    }
    
    // Get all user meta
    $meta = get_user_meta($user->ID);
    $fields = [];
    
    foreach ($meta as $key => $val) {
        if (strpos($key, 'wp_') !== 0 && 
            $key !== 'session_tokens' && 
            $key !== 'capabilities' && 
            $key !== 'user_level') {
            $fields[$key] = is_array($val) ? $val[0] : $val;
        }
    }
    
    // Make sure critical fields are included
    $fields['user_login'] = $user->user_login;
    $fields['user_email'] = $user->user_email;
    $fields['email'] = $user->user_email;
    $fields['first_name'] = $user->first_name;
    $fields['last_name'] = $user->last_name;
    
    // Debug: Log member-id specifically
    //error_log('Member ID for user ' . $user->ID . ': ' . (isset($fields['member-id']) ? $fields['member-id'] : 'NOT FOUND'));
    //error_log('All fields count: ' . count($fields));
    
    wp_send_json_success([
        'name' => $user->display_name,
        'user_id' => $user->ID,
        'field_count' => count($fields),
        'fields' => $fields
    ]);
});


/**
 * STRONG Method to Hide Form 14497 for Non-Admins
 * Add this to functions.php
 */

// Method 1: Block form shortcode output completely
add_filter('forminator_render_shortcode', 'block_form_for_non_admins', 10, 2);

function block_form_for_non_admins($markup, $id) {
    // Only affect form 14497
    if ($id != 14497) {
        return $markup;
    }
    
    // If not admin, replace entire form with message
    if (!current_user_can('manage_options')) {
        return '<div style="background: #f8d7da; border: 2px solid #dc3545; padding: 30px; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 600px;">
            <h2 style="color: #721c24; margin-top: 0;">Access Restricted</h2>
            <p style="color: #721c24; font-size: 16px; margin-bottom: 0;">This form is only available to administrators.</p>
        </div>';
    }
    
    return $markup;
}

// Method 2: Hide with CSS (backup method)
add_action('wp_head', 'hide_form_css_for_non_admins');

function hide_form_css_for_non_admins() {
    if (!current_user_can('manage_options')) {
        echo '<style>
            #forminator-module-14497,
            .forminator-custom-form[data-form-id="14497"],
            form[id*="forminator-module-14497"] {
                display: none !important;
            }
        </style>';
    }
}

// Method 3: Show message before form loads
add_action('wp_footer', 'show_admin_only_message');

function show_admin_only_message() {
    if (!current_user_can('manage_options') && is_page()) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Find and hide form 14497
            $('#forminator-module-14497, [data-form-id="14497"]').hide();
            
            // Check if search box exists on page (means form should be there)
            if ($('#user-search-section').length > 0) {
                $('#user-search-section').after('<div style="background: #f8d7da; border: 2px solid #dc3545; padding: 30px; border-radius: 8px; text-align: center; margin: 20px 0;"><h2 style="color: #721c24; margin-top: 0;">Access Restricted</h2><p style="color: #721c24; font-size: 16px;">This form is only available to administrators.</p></div>');
            }
        });
        </script>
        <?php
    }
}


// Step 1: Capture entry ID
add_action('forminator_custom_form_submit_before_set_fields', 'capture_entry_id_for_link', 20, 3);
function capture_entry_id_for_link($entry, $form_id, $field_data_array) {
    if (intval($form_id) === 14497) {
        $_POST['_forminator_entry_id_for_link'] = $entry->entry_id;
    }
}

// Step 2: Modify the AJAX response - Simple text link only
add_filter('forminator_form_ajax_submit_response', 'modify_ajax_response', 99, 2);
function modify_ajax_response($response, $form_id) {
    if (intval($form_id) === 14497 && isset($_POST['_forminator_entry_id_for_link'])) {
        $entry_id = $_POST['_forminator_entry_id_for_link'];
        $entry_url = admin_url('admin.php?page=forminator-entries&form_id=' . $form_id . '&entry_id=' . $entry_id);
        
        // Simple text link without any styling that could break layout
        if (isset($response['message'])) {
            $response['message'] = $response['message'] . '<br><a href="' . esc_url($entry_url) . '" target="_blank">View your submission</a>';
        }
    }
    
    return $response;
}

// Auto-clear SG Optimizer cache weekly
add_filter( 'cron_schedules', function( $schedules ) {
    if ( ! isset( $schedules['weekly'] ) ) {
        $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display'  => __( 'Once Weekly' ),
        ];
    }
    return $schedules;
} );

add_action( 'init', function() {
    if ( ! wp_next_scheduled( 'clear_sg_cache_weekly' ) ) {
        wp_schedule_event( time(), 'weekly', 'clear_sg_cache_weekly' );
    }
} );

add_action( 'clear_sg_cache_weekly', function() {
    if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
        sg_cachepress_purge_cache();
    } else {$cache_dir = WP_CONTENT_DIR . '/uploads/siteground-optimizer-assets/';
        if ( is_dir( $cache_dir ) ) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator( $cache_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ( $files as $file ) {
                if ( $file->isFile() ) {
                    unlink( $file->getRealPath() );
                }
            }
        }
    }
});

add_filter('authenticate', function($user, $username, $password) {
    if (!empty($username) && !empty($password)) {
        $attempting_user = get_user_by('login', $username);
        if (!$attempting_user) {
            $attempting_user = get_user_by('email', $username);
        }
        if ($attempting_user && in_array('administrator', $attempting_user->roles)) {
            $referer = wp_get_referer() ?? '';
            $current_url = $_SERVER['REQUEST_URI'] ?? '';
            $http_referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (strpos($current_url, 'my-account') !== false || 
                strpos($referer, 'my-account') !== false ||
                strpos($http_referer, 'my-account') !== false) {
                return new WP_Error('admin_login_blocked', 
                    'Administrators cannot login from this page.');
            }
        }
    }
    return $user;
}, 30, 3);

add_shortcode('current_year', function() {
    return date('Y');
});

add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );