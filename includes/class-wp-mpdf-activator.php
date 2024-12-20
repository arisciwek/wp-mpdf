<?php
/**
 * WP mPDF Activator Class
 *
 * @package     CustomerManagement
 * @subpackage  Includes
 * @author      arisciwek <arisciwek@gmail.com>
 * @copyright   2024 arisciwek
 * @license     GPL-2.0-or-later
 * @since       1.0.0 
 * 
 * Path: includes/class-wp-mpdf-activator.php
 * 
 * Description: Class untuk menangani aktivasi dan deaktivasi plugin.
 *              Memastikan semua requirements terpenuhi saat aktivasi.
 *              Setup direktori yang dibutuhkan oleh mPDF.
 *              Verifikasi file-file library mPDF.
 *              Cek permissions pada direktori temporary.
 *              Handle upgrade dan downgrade plugin.
 * 
 * Required Methods:
 * - activate()       : Handle plugin activation
 * - deactivate()     : Handle plugin deactivation
 * - verify_mpdf()    : Verify mPDF library files
 * - setup_directories(): Setup required directories
 * 
 * Dependencies:
 * - mPDF library files in libs/mpdf/
 * - WordPress upload directory
 * - Write permissions on temp directory
 * 
 * Usage:
 * register_activation_hook(__FILE__, array('WP_MPDF_Activator', 'activate'));
 * register_deactivation_hook(__FILE__, array('WP_MPDF_Activator', 'deactivate'));
 * 
 * Changelog:
 * 1.0.0 - 2024-12-21
 * - Initial release
 * - Added activation checks
 * - Added directory setup
 * - Added mPDF verification
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class WP_MPDF_Activator {
    /**
     * Activate the plugin
     * 
     * @return void
     */
    public static function activate() {
        self::verify_mpdf();
        self::setup_directories();
        self::check_permissions();
    }

    /**
     * Deactivate the plugin
     * 
     * @return void
     */
    public static function deactivate() {
        // Cleanup temporary files
        self::cleanup_temp();
    }

    /**
     * Verify mPDF library files exist
     * 
     * @throws Exception If required files are missing
     * @return void
     */
    private static function verify_mpdf() {
	    $required_files = [
	        'libs/mpdf/src/Mpdf.php'  // Jika tanpa Composer
	    ];

	    foreach ($required_files as $file) {
	        $file_path = WP_MPDF_DIR . $file;
	        if (!file_exists($file_path)) {
	            throw new Exception(
	                sprintf(
	                    /* translators: %s: File path */
	                    __('Required mPDF file missing: %s', 'wp-mpdf'),
	                    $file
	                )
	            );
	        }
	    }
	}

    /**
     * Setup required directories
     * 
     * @throws Exception If directory creation fails
     * @return void
     */
    private static function setup_directories() {
        $upload_dir = wp_upload_dir();
        
        // Directories that need to be created
        $required_dirs = [
            'mpdf-cache' => trailingslashit($upload_dir['basedir']) . 'mpdf-cache',
            'mpdf-temp' => trailingslashit($upload_dir['basedir']) . 'mpdf-temp',
            'mpdf-fonts' => trailingslashit($upload_dir['basedir']) . 'mpdf-fonts'
        ];

        foreach ($required_dirs as $name => $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }

            // Verify directory was created
            if (!file_exists($dir)) {
                throw new Exception(
                    sprintf(
                        /* translators: %s: Directory name */
                        __('Failed to create required directory: %s', 'wp-mpdf'),
                        $name
                    )
                );
            }

            // Add .htaccess for security
            $htaccess = trailingslashit($dir) . '.htaccess';
            if (!file_exists($htaccess)) {
                $content = "deny from all\n";
                @file_put_contents($htaccess, $content);
            }
        }
    }

    /**
     * Check directory permissions
     * 
     * @throws Exception If permissions are incorrect
     * @return void
     */
    private static function check_permissions() {
        $upload_dir = wp_upload_dir();
        $dirs_to_check = [
            'mpdf-cache',
            'mpdf-temp',
            'mpdf-fonts'
        ];

        foreach ($dirs_to_check as $dir) {
            $full_path = trailingslashit($upload_dir['basedir']) . $dir;
            if (!is_writable($full_path)) {
                throw new Exception(
                    sprintf(
                        /* translators: %s: Directory path */
                        __('Directory not writable: %s', 'wp-mpdf'),
                        $full_path
                    )
                );
            }
        }
    }

    /**
     * Cleanup temporary files on deactivation
     * 
     * @return void
     */
    private static function cleanup_temp() {
        $upload_dir = wp_upload_dir();
        $temp_dir = trailingslashit($upload_dir['basedir']) . 'mpdf-temp';
        
        if (file_exists($temp_dir)) {
            array_map('unlink', glob("$temp_dir/*.*"));
        }
    }
}
