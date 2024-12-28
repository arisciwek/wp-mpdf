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
define('WP_MPDF_QRCODE_PATH', WP_MPDF_DIR . 'libs/QrCode/src/');

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
    // Load required files first
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-loader.php';
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf.php';
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-activator.php';
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-settings.php';

    // After loading required files
    require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-qrcode.php';

    // Register activation/deactivation hooks
    register_activation_hook(__FILE__, array('WP_MPDF_Activator', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_MPDF_Activator', 'deactivate'));

    /**
     * Initialize WP mPDF plugin.
     * This function ensures the plugin is only initialized once by using a static flag.
     * Prevents multiple initializations that could occur due to repeated 'plugins_loaded' hooks.
     * 
     * Problem:
     * The original code would reinitialize the plugin every time 'plugins_loaded' was called,
     * causing unnecessary object creation and resource usage.
     * Debug logs showed initialization occurring every minute.
     * 
     * Solution:
     * - Use static flag to track initialization state
     * - Only initialize if not already done
     * - Maintain debug logging for transparency
     * 
     * @since 1.0.1
     * @see WP_MPDF::get_instance()
     * @see WP_MPDF::run()
     */
    function run_wp_mpdf() {
        // Static flag persists between function calls but is local to this function
        static $initialized = false;
        
        // Skip if already initialized
        if ($initialized) {
            return;
        }

        error_log('WP mPDF: Initializing plugin');
        
        // Get singleton instance - will create if doesn't exist
        $plugin = WP_MPDF::get_instance();
        
        // Initialize plugin systems
        $plugin->run();
        
        error_log('WP mPDF: Plugin initialized');
        
        // Mark as initialized to prevent future runs
        $initialized = true;
    }

    if (!function_exists('wp_mpdf_get_library_path')) {
        /**
         * Get mPDF library base path
         * 
         * @return string|null Path to mPDF library or null if not available
         */
        function wp_mpdf_get_library_path() {
            // Check if plugin exists and is activated
            if (!defined('WP_MPDF_DIR')) {
                return null;
            }
            
            try {
                return WP_MPDF_Activator::get_mpdf_base_path();
            } catch (Exception $e) {
                return null;
            }
        }
    }

    if (!function_exists('wp_mpdf_get_pdf_settings')) {
        /**
         * Get complete mPDF path settings for PDF generation
         * 
         * @return array|null Array of mPDF settings or null if not available
         */
        function wp_mpdf_get_pdf_settings() {
            try {
                return [
                    'library_path' => WP_MPDF_DIR . 'libs/mpdf',
                    'src_path' => WP_MPDF_DIR . 'libs/mpdf/src',
                    'temp_path' => trailingslashit(wp_upload_dir()['basedir']) . 'mpdf-temp',
                    'font_path' => trailingslashit(wp_upload_dir()['basedir']) . 'mpdf-fonts/dejavu',
                    'cache_path' => trailingslashit(wp_upload_dir()['basedir']) . 'mpdf-fonts'
                ];
            } catch (Exception $e) {
                return null;
            }
        }
    }

    if (!function_exists('wp_mpdf_verify_library')) {
        /**
         * Verify mPDF library is complete and usable
         * 
         * @return bool True if library is available and complete
         */
        function wp_mpdf_verify_library() {
            $path = wp_mpdf_get_library_path();
            if (!$path) {
                return false;
            }

            $required_files = [
                '/src/Mpdf.php',
                '/src/Config/ConfigVariables.php',
                '/src/Config/FontVariables.php'
            ];

            foreach ($required_files as $file) {
                if (!file_exists($path . $file)) {
                    return false;
                }
            }

            return true;
        }
    }

    // Hook to WordPress plugin initialization
    // NOTE: While 'plugins_loaded' may fire multiple times,
    // our static flag ensures single initialization
    add_action('plugins_loaded', 'run_wp_mpdf');

    // Initialize QR setup
    WP_Mpdf_QrCode::init();

    /**
     * Helper function untuk akses global instance
     */
    function wp_mpdf() {
        return WP_MPDF::get_instance();
    }
}
