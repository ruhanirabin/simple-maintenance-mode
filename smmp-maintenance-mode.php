<?php
/*
 * Plugin Name:       Simple Maintenance Mode Plugin
 * Plugin URI:        https://www.ruhanirabin.com/simple-maintenance-mode/
 * Description:       A maintenance mode plugin that allows you to set a pre-existing page as a maintenance mode landing page and restrict access to other areas of the WordPress site.
 * Version:           0.2.3
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ruhani Rabin
 * Author URI:        https://www.ruhanirabin.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://www.ruhanirabin.com/simple-maintenance-mode/
 * Text Domain:       smmp_maintenance_mode
 * Domain Path:       /languages
 */

// Check if the current user has admin permissions
function smmp_is_admin() {
    return current_user_can('manage_options');
}

// load plugin text-domain
function smmp_load_textdomain() {
    load_plugin_textdomain('smmp_maintenance_mode', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'smmp_load_textdomain');


// Get the list of pre-existing pages for the drop-down
function smmp_get_pages() {
    $pages = get_pages();
    $options = array();
    foreach ($pages as $page) {
        $options[$page->ID] = $page->post_title;
    }
    return $options;
}

// Check if the maintenance mode is enabled
function smmp_is_maintenance_mode_enabled() {
    return get_option('smmp_maintenance_mode_enabled') == '1';
}

// Check if the current URL is an exception
function smmp_is_exception($url) {
    $exceptions = explode("\n", get_option('smmp_exceptions'));
    foreach ($exceptions as $exception) {
        if (trim($exception) == $url) {
            return true;
        }
    }
    return false;
}

// Display the maintenance mode page if necessary
function smmp_display_maintenance_page() {
    if (!smmp_is_admin() && smmp_is_maintenance_mode_enabled() && !smmp_is_exception($_SERVER['REQUEST_URI'])) {
        $maintenance_page_id = get_option('smmp_maintenance_page_id');
        if ($maintenance_page_id) {
            $maintenance_page = get_post($maintenance_page_id);
            if ($maintenance_page) {
                // Load the necessary styles and scripts for the maintenance page
                wp_head();
                echo '<!DOCTYPE html>';
                echo '<html ' . get_language_attributes() . '>';
                echo '<head>';
                echo '<meta charset="' . get_bloginfo('charset') . '">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
                echo '<title>' . get_bloginfo('name') . ' - ' . get_bloginfo('description') . '</title>';
                wp_print_styles();
                wp_print_scripts();
                echo '</head>';
                echo '<body>';
                echo apply_filters('the_content', $maintenance_page->post_content);
                echo '</body>';
                echo '</html>';
                wp_footer();
                die();
            }
        }
    }
}
add_action('template_redirect', 'smmp_display_maintenance_page');

// Add plugin settings page
function smmp_add_settings_page() {
    add_options_page(__('Simple Maintenance Mode Settings', 'smmp_maintenance_mode'), __('Simple Maintenance Mode', 'smmp_maintenance_mode'), 'manage_options', 'maintenance-mode-plugin', 'smmp_settings_page');
}
add_action('admin_menu', 'smmp_add_settings_page');

// Display plugin settings page
function smmp_settings_page() {
    // Save settings
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && smmp_is_admin()) {
        update_option('smmp_maintenance_mode_enabled', isset($_POST['smmp_maintenance_mode_enabled']) ? '1' : '0');
        update_option('smmp_maintenance_page_id', $_POST['smmp_maintenance_page_id']);
        update_option('smmp_exceptions', $_POST['smmp_exceptions']);
        update_option('smmp_show_banner', isset($_POST['smmp_show_banner']) ? '1' : '0');
        update_option('smmp_show_top_bar_menu', isset($_POST['smmp_show_top_bar_menu']) ? '1' : '0');
    }

    // Load settings
    $maintenance_mode_enabled = smmp_is_maintenance_mode_enabled();
    $maintenance_page_id = get_option('smmp_maintenance_page_id');
    $exceptions = get_option('smmp_exceptions');
    $show_banner = get_option('smmp_show_banner') == '1';
    $show_top_bar_menu = get_option('smmp_show_top_bar_menu') == '1';

    // Display settings form
    include 'settings_page.php';
}

// Add top bar menu
function smmp_add_top_bar_menu() {
    global $wp_admin_bar;

    if (smmp_is_admin() && get_option('smmp_show_top_bar_menu') == '1') {
        $menu_maintenance_on = __('Maintenance: ON', 'smmp_maintenance_mode');
        $menu_maintenance_off = __('Maintenance: OFF', 'smmp_maintenance_mode');
        $menu_title = smmp_is_maintenance_mode_enabled() ? '<span style="color: green;">●</span> ' . $menu_maintenance_on : '<span style="color: red;">●</span> ' . $menu_maintenance_off;

        $wp_admin_bar->add_menu(array(
            'id' => 'smmp_top_bar_menu',
            'title' => $menu_title,
            'href' => admin_url('options-general.php?page=maintenance-mode-plugin')
        ));
    }
}


// Show admin notice when maintenance mode is active
function smmp_show_admin_notice() {
    $show_banner_notice = get_option('smmp_show_banner_notice') == '1';

    if (smmp_is_admin() && smmp_is_maintenance_mode_enabled() && $show_banner_notice) {
        $class = 'notice notice-error';
        $message = __('Maintenance Mode is currently active.', 'smmp_maintenance_mode');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
}
/**
 * smmp_display_admin_notice
 * @return void
 */
function smmp_display_admin_notice() {
    $admin_banner_string = __('Maintenance Mode is currently active.', 'smmp_maintenance_mode');
    if (smmp_is_admin() && smmp_is_maintenance_mode_enabled() && get_option('smmp_show_banner') == '1') {
        echo '<div class="notice notice-error"><p><strong>' . $admin_banner_string . '</strong></p></div>';
    }
}
add_action('admin_notices', 'smmp_display_admin_notice');
add_action('admin_bar_menu', 'smmp_add_top_bar_menu', 999);
