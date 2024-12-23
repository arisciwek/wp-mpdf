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
    private $fpdi_prefix = 'setasign\\Fpdi\\';
    private $psr_prefix = 'Psr\\Log\\';
    private $mpdf_trait_prefix = 'Mpdf\\PsrLogAwareTrait\\';
    

    /**
     * Base directory untuk mPDF files
     */
    private $mpdf_base_dir;
    private $fpdi_base_dir;
    private $psr_base_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->mpdf_base_dir = WP_MPDF_DIR . 'libs/mpdf/src/';
        $this->fpdi_base_dir = WP_MPDF_DIR . 'libs/mpdf/fpdi/src/';
        $this->psr_base_dir = WP_MPDF_DIR . 'libs/psr/log/src/';
    }

    /**
     * Register autoloader
     */
    public function register() {
        spl_autoload_register([$this, 'autoload']);

        // Load mPDF core files yang dibutuhkan
        $this->load_mpdf();
        
        // Add class alias for PSR LoggerAwareTrait
        if (!trait_exists('Mpdf\PsrLogAwareTrait\MpdfPsrLogAwareTrait', false)) {
            class_alias('Psr\Log\LoggerAwareTrait', 'Mpdf\PsrLogAwareTrait\MpdfPsrLogAwareTrait');
        }

        if (!trait_exists('Mpdf\PsrLogAwareTrait\PsrLogAwareTrait', false)) {
            class_alias('Psr\Log\LoggerAwareTrait', 'Mpdf\PsrLogAwareTrait\PsrLogAwareTrait');
        }
    }

    /**
     * PSR-4 Autoloader implementation
     * 
     * @param string $class The fully-qualified class name.
     * @return void
     */
    public function autoload($class) {

        // Special case untuk LoggerAwareTrait
        if ($class === 'Mpdf\PsrLogAwareTrait\MpdfPsrLogAwareTrait') {
            $trait_file = $this->psr_base_dir . 'LoggerAwareTrait.php';
            if (file_exists($trait_file)) {
                require $trait_file;
                return;
            }
        }

        // Check PSR namespace
        $psr_len = strlen($this->psr_prefix);
        if (strncmp($this->psr_prefix, $class, $psr_len) === 0) {
            $relative_class = substr($class, $psr_len);
            $file = $this->psr_base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }

        // Check mPDF namespace
        $mpdf_len = strlen($this->mpdf_prefix);
        if (strncmp($this->mpdf_prefix, $class, $mpdf_len) === 0) {
            $relative_class = substr($class, $mpdf_len);
            $file = $this->mpdf_base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }

        // Check FPDI namespace
        $fpdi_len = strlen($this->fpdi_prefix);
        if (strncmp($this->fpdi_prefix, $class, $fpdi_len) === 0) {
            $relative_class = substr($class, $fpdi_len);
            $file = $this->fpdi_base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
            }
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
