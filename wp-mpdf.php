<?php
/**
 * WP mPDF - A Complete mPDF Integration for WordPress
 *
 * @package     CustomerManagement
 * @subpackage  Includes
 * @author      arisciwek <arisciwek@gmail.com>
 * @copyright   2024 arisciwek
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Path: wp-mpdf.php
 * Created:     2024-12-21
 * Modified:    2024-12-21
 * 
 * Plugin Name: WP mPDF
 * Plugin URI: http://example.com/wp-mpdf
 * Description: Plugin untuk mengintegrasikan mPDF library ke WordPress
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: arisciwek
 * Author URI: http://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-mpdf
 * Domain Path: /languages
 * 
 * 
 * Description: Plugin utama untuk integrasi mPDF ke WordPress.
 *              Menyediakan class wrapper dan interface untuk
 *              menggunakan mPDF dalam plugin WordPress lain.
 *              Support fitur utama mPDF seperti konversi HTML ke PDF,
 *              watermark, header/footer kustom, dan multi-format.
 *              Support Unicode untuk multiple language.
 * 
 * Required Methods:
 * - init()            : Initialize plugin components
 * - register_hooks()  : Register WordPress hooks
 * - check_requirements(): Check system requirements
 * 
 * Dependencies:
 * - mPDF Library
 * - WordPress 5.8+
 * - PHP 7.4+
 * 
 * Changelog:
 * 1.0.0 - 2024-12-21
 * - Initial release
 * - Added mPDF library integration
 * - Added basic autoloader
 * - Added system requirement checks
 * - Added language support
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Plugin version
define('WP_MPDF_VERSION', '1.0.0');

// Plugin paths
define('WP_MPDF_FILE', __FILE__);
define('WP_MPDF_DIR', plugin_dir_path(__FILE__));
define('WP_MPDF_URL', plugin_dir_url(__FILE__));
define('WP_MPDF_BASENAME', plugin_basename(__FILE__));

/**
 * Check system requirements before loading plugin
 */
function wp_mpdf_check_system_requirements() {
    $errors = array();

    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = sprintf(
            /* translators: 1: Current PHP version 2: Required PHP version */
            __('WP mPDF requires PHP version %2$s or higher. Your current version is %1$s', 'wp-mpdf'),
            PHP_VERSION,
            '7.4'
        );
    }

    if (version_compare(get_bloginfo('version'), '5.8', '<')) {
        $errors[] = sprintf(
            /* translators: 1: Current WordPress version 2: Required WordPress version */
            __('WP mPDF requires WordPress version %2$s or higher. Your current version is %1$s', 'wp-mpdf'),
            get_bloginfo('version'),
            '5.8'
        );
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        // Deactivate plugin
        deactivate_plugins(plugin_basename(__FILE__));
        return false;
    }

    return true;
}
add_action('admin_notices', 'wp_mpdf_check_system_requirements');

// Only load the plugin if requirements are met
if (wp_mpdf_check_system_requirements()) {
    // Required files
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-loader.php';
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf.php';
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-activator.php';

    // Activation/Deactivation hooks
    register_activation_hook(__FILE__, array('WP_MPDF_Activator', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_MPDF_Activator', 'deactivate'));

    /**
     * Load plugin text domain for translations
     */
    function wp_mpdf_load_textdomain() {
        load_plugin_textdomain(
            'wp-mpdf',
            false,
            dirname(WP_MPDF_BASENAME) . '/languages/'
        );
    }
    add_action('plugins_loaded', 'wp_mpdf_load_textdomain');

    /**
     * Initialize plugin
     */
	function run_wp_mpdf() {
	    $plugin = WP_MPDF::get_instance(); 
	    $plugin->run();
	}


    run_wp_mpdf();
}

/**
 * Helper function untuk akses global instance
 * @return WP_MPDF Main plugin instance
 */
function wp_mpdf() {
    return WP_MPDF::get_instance();
}
