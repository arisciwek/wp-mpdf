<?php
/**
 * WP mPDF Loader Class
 *
 * @package     CustomerManagement
 * @subpackage  Includes
 * @author      arisciwek <arisciwek@gmail.com>
 * @copyright   2024 arisciwek
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * 
 * Path: includes/class-wp-mpdf-loader.php
 * 
 * Description: Class untuk menghandle autoloading mPDF library.
 *              Menangani registrasi autoloader untuk mPDF dan dependencies-nya.
 *              Memastikan library mPDF bisa diload tanpa Composer.
 *              Includes PSR-4 autoloading untuk folder libs/mpdf.
 *              Menyediakan fallback loading jika autoload gagal.
 * 
 * Required Methods:
 * - register()        : Register autoloader
 * - autoload()        : PSR-4 autoloader implementation
 * - load_mpdf()       : Load mPDF core files
 * 
 * Dependencies:
 * - mPDF library files in libs/mpdf/
 * 
 * Usage:
 * $loader = new WP_MPDF_Loader();
 * $loader->register();
 * 
 * Changelog:
 * 1.0.0 - 2024-12-21
 * - Initial release
 * - Added PSR-4 autoloader
 * - Added mPDF library loading
 * - Added class mapping
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class WP_MPDF_Loader {
    /**
     * Namespace prefix untuk mPDF
     */
    private $mpdf_prefix = 'Mpdf\\';

    /**
     * Base directory untuk mPDF files
     */
    private $mpdf_base_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->mpdf_base_dir = WP_MPDF_DIR . 'libs/mpdf/src/';
    }

    /**
     * Register autoloader
     */
    public function register() {
        spl_autoload_register([$this, 'autoload']);

        // Load mPDF core files yang dibutuhkan
        $this->load_mpdf();
    }

    /**
     * PSR-4 Autoloader implementation
     * 
     * @param string $class The fully-qualified class name.
     * @return void
     */
    public function autoload($class) {
        // Check if class menggunakan mPDF namespace
        $len = strlen($this->mpdf_prefix);
        if (strncmp($this->mpdf_prefix, $class, $len) !== 0) {
            return;
        }

        // Get relative class name
        $relative_class = substr($class, $len);

        // Convert ke file path
        $file = $this->mpdf_base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Load file jika exists
        if (file_exists($file)) {
            require $file;
        }
    }

    /**
     * Load mPDF core files
     */
    private function load_mpdf() {
        // Core files yang harus di-load
        $core_files = [
            'functions.php',
            'constants.php'
        ];

        foreach ($core_files as $file) {
            $file_path = WP_MPDF_DIR . 'libs/mpdf/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
}
