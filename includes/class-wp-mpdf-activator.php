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
     */
    private static function verify_mpdf() {
        $mpdf_path = self::get_mpdf_base_path();
        $fpdi_path = $mpdf_path . '/fpdi';

        // Array dari file yang dibutuhkan dengan pesan errornya
        $required_files = [
            // Core mPDF files
            'src/Mpdf.php' => 'File core mPDF tidak ditemukan',
            'src/Config/ConfigVariables.php' => 'File konfigurasi mPDF tidak ditemukan',
            'src/Config/FontVariables.php' => 'File konfigurasi font tidak ditemukan',
            
            // FPDI files
            'fpdi/src/FpdiTrait.php' => 'File FPDI trait tidak ditemukan',
            'fpdi/src/PdfParser/PdfParser.php' => 'File PDF parser FPDI tidak ditemukan',
            'fpdi/src/Fpdi.php' => 'File core FPDI tidak ditemukan'
        ];

        foreach ($required_files as $file => $error_message) {
            $file_path = $mpdf_path . '/' . $file;
            if (!file_exists($file_path)) {
                throw new Exception(
                    sprintf(
                        /* translators: 1: Error message 2: File path */
                        __('%1$s: %2$s', 'wp-mpdf'),
                        $error_message,
                        $file_path
                    )
                );
            }
        }

        // Verifikasi struktur dan permission direktori FPDI
        if (!is_dir($fpdi_path)) {
            throw new Exception(
                __('Direktori FPDI tidak ditemukan. Silakan install FPDI 2.6.2', 'wp-mpdf')
            );
        }

        if (!is_readable($fpdi_path . '/src')) {
            throw new Exception(
                __('Direktori FPDI src tidak dapat dibaca', 'wp-mpdf')
            );
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
        
        // Direktori yang perlu dibuat
        $required_dirs = [
            'mpdf-cache' => [
                'path' => trailingslashit($upload_dir['basedir']) . 'mpdf-cache',
                'error' => 'Cache mPDF'
            ],
            'mpdf-temp' => [
                'path' => trailingslashit($upload_dir['basedir']) . 'mpdf-temp',
                'error' => 'Temporary mPDF'
            ],
            'mpdf-fonts' => [
                'path' => trailingslashit($upload_dir['basedir']) . 'mpdf-fonts',
                'error' => 'Font mPDF'
            ]
        ];

        foreach ($required_dirs as $name => $config) {
            if (!file_exists($config['path'])) {
                wp_mkdir_p($config['path']);
            }

            // Verifikasi direktori berhasil dibuat
            if (!file_exists($config['path'])) {
                throw new Exception(
                    sprintf(
                        /* translators: %s: Directory type */
                        __('Gagal membuat direktori %s', 'wp-mpdf'),
                        $config['error']
                    )
                );
            }

            // Tambah .htaccess untuk keamanan
            $htaccess = trailingslashit($config['path']) . '.htaccess';
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

    private static function get_mpdf_base_path() {
        return WP_MPDF_DIR . 'libs/mpdf';
    }
    /**
     * Get mPDF library source path
     * 
     * @return string Absolute path to mPDF source files
     */
    public static function get_mpdf_paths() {
        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']) . 'mpdf';
        
        // Create base mpdf directory if not exists
        if (!file_exists($base_dir)) {
            wp_mkdir_p($base_dir);
        }
        
        // Define writable directories
        $writable_dirs = [
            'temp' => $base_dir . '/temp',
            'fonts' => $base_dir . '/fonts',
            'cache' => $base_dir . '/cache'
        ];
        
        // Create writable directories and set permissions
        foreach ($writable_dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
            
            // Add .htaccess to prevent direct access
            $htaccess = trailingslashit($dir) . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, 'deny from all');
            }
        }
        
        // Return complete configuration
        return [
            // Read-only paths (for mPDF library files)
            'library_path' => WP_MPDF_DIR . 'libs/mpdf',
            'src_path' => WP_MPDF_DIR . 'libs/mpdf/src',
            
            // Writable paths (for runtime files)
            'temp_path' => $writable_dirs['temp'],
            'font_path' => $writable_dirs['fonts'],
            'cache_path' => $writable_dirs['cache'],
            
            // Full mPDF configuration
            'config' => [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 0,
                'default_font' => 'dejavusans',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
                'margin_header' => 9,
                'margin_footer' => 9,
                'orientation' => 'P',
                'tempDir' => $writable_dirs['temp'],
                'fontDir' => [$writable_dirs['fonts']],
                'fontCache' => $writable_dirs['cache']
            ]
        ];
    }

}
