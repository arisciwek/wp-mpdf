<?php
/**
 * WP mPDF Main Class
 *
 * @package     CustomerManagement
 * @subpackage  Includes
 * @author      arisciwek <arisciwek@gmail.com>
 * @copyright   2024 arisciwek
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * 
 * Path: includes/class-wp-mpdf.php
 * 
 * Description: Class utama untuk mengelola plugin WP mPDF.
 *              Menginisialisasi komponen-komponen plugin.
 *              Mengatur hooks dan filters WordPress.
 *              Menyediakan API untuk generate PDF.
 *              Mengelola konfigurasi mPDF.
 *              Interface utama untuk plugin lain.
 * 
 * Required Methods:
 * - get_instance()   : Get singleton instance
 * - run()           : Initialize plugin
 * - setup_hooks()   : Register WordPress hooks
 * - generate_pdf()  : Generate PDF with mPDF
 * 
 * Dependencies:
 * - WP_MPDF_Loader class
 * - mPDF library
 * - WordPress Hooks API
 * 
 * Usage:
 * $mpdf = WP_MPDF::get_instance();
 * $mpdf->generate_pdf($html, $options);
 * 
 * Changelog:
 * 1.0.0 - 2024-12-21
 * - Initial release
 * - Added core functionality
 * - Added PDF generation
 * - Added configuration management
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class WP_MPDF {

    /**
     * Singleton instance
     */
    private static $instance = null;

    private $mpdf = null;  // Tambahkan property untuk menyimpan instance mPDF

    /**
     * mPDF loader instance
     */
    private $loader;

    private $settings;

    private $version;

    /**
     * Default mPDF options
     */
    private $default_options = [
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9,
        'orientation' => 'P',
        'default_font' => 'dejavusans'
    ];

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */

    private function __construct() {
        $this->version = WP_MPDF_VERSION;
        $this->loader = new WP_MPDF_Loader();
        $this->settings = new WP_MPDF_Settings();
    }


    /**
     * Generate PDF using mPDF
     * 
     * @param string $html    HTML content
     * @param array  $options mPDF options
     * @return string|WP_Error Path to generated PDF or error
     */
    public function generate_pdf($html, $options = []) {
        try {
            // Get paths from Activator
            $paths = WP_MPDF_Activator::get_mpdf_paths();
                
            // Merge configurations with defaults
            $config = array_merge($this->default_options, [
                'tempDir' => $paths['temp_path'],
                'fontDir' => [
                    WP_MPDF_DIR . 'libs/mpdf/ttfonts',  // Plugin font directory
                    $paths['font_path']
                ],
                'fontCache' => $paths['cache_path']
            ], $options);

            error_log('PDF Config: ' . print_r($config, true));

            // Initialize mPDF
            $this->mpdf = new \Mpdf\Mpdf($config);

            // Set document info
            $this->mpdf->SetTitle(wp_get_document_title());
            $this->mpdf->SetAuthor(get_bloginfo('name')); 
            $this->mpdf->SetCreator('WP mPDF ' . WP_MPDF_VERSION);

            // Write content if provided
            if (!empty($html)) {
                $this->mpdf->WriteHTML($html);
            }

            return $this->mpdf;

        } catch (Exception $e) {
            error_log('mPDF Error: ' . $e->getMessage());
            return new WP_Error('mpdf_error', $e->getMessage());
        }
    }

        /*
        public function generate_pdf($html, $options = []) {
            // Get complete paths configuration from Activator
            $paths = WP_MPDF_Activator::get_mpdf_paths();
            try {
                
                // Merge configuration
                $config = array_merge($this->default_options, [
                    'tempDir' => $paths['temp_path'],
                    'fontDir' => [
                        WP_MPDF_DIR . 'libs/mpdf/ttfonts',  // Plugin font directory
                        $paths['font_path']
                    ],
                    'fontdata' => [
                        'dejavusans' => [
                            'R' => 'DejaVuSans.ttf',
                            'B' => 'DejaVuSans-Bold.ttf',
                            'I' => 'DejaVuSans-Oblique.ttf',
                            'BI' => 'DejaVuSans-BoldOblique.ttf'
                        ]
                    ],
                    'default_font' => 'dejavusans'
                ], $options);


                // Verify and ensure temp directory exists and is writable
                if (!file_exists($paths['temp_path'])) {
                    wp_mkdir_p($paths['temp_path']);
                }

                if (!is_writable($paths['temp_path'])) {
                    throw new Exception('Temporary directory is not writable: ' . $paths['temp_path']);
                }

                // Get complete mPDF configuration from Activator
                // $config = $paths['config'];

                // Merge configuration
                $config = array_merge($paths['config'], [
                    'fontDir' => [
                        WP_MPDF_DIR . 'libs/mpdf/ttfonts',  // Plugin font directory
                        $paths['font_path']
                    ]
                ], $options);

                // Initialize mPDF dengan config lengkap

                error_log('PDF Config: ' . print_r($config, true));

                $mpdf = new \Mpdf\Mpdf($config);

                // Set document info
                $mpdf->SetTitle(wp_get_document_title());
                $mpdf->SetAuthor(get_bloginfo('name')); 
                $mpdf->SetCreator('WP mPDF ' . WP_MPDF_VERSION);

                if (!empty($html)) {
                    $mpdf->WriteHTML($html);
                }

                return $mpdf;

            } catch (Exception $e) {
                error_log('mPDF Error: ' . $e->getMessage());
                return new WP_Error('mpdf_error', $e->getMessage());
            }
        }
        */

    /**
     * Get mPDF library base path
     * 
     * @return string Absolute path to mPDF library
     */
    public static function get_mpdf_path() {
        return WP_MPDF_DIR . 'libs/mpdf';
    }

    /**
     * Get mPDF source path
     * 
     * @return string Absolute path to mPDF source files
     */
    public static function get_mpdf_src_path() {
        return self::get_mpdf_path() . '/src';
    }

    /**
     * Verify mPDF library exists
     * 
     * @return bool True if mPDF library exists and is accessible
     */
    public static function verify_mpdf_library() {
        $base_path = self::get_mpdf_path();
        $required_files = [
            '/src/Mpdf.php',
            '/src/Config/ConfigVariables.php',
            '/src/Config/FontVariables.php'
        ];

        foreach ($required_files as $file) {
            if (!file_exists($base_path . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * For plugin integrations - get mPDF settings for PDF renderer
     * 
     * @return array Settings array for PDF renderer configuration
     */
    public static function get_pdf_renderer_settings() {
        return [
            'pdf_renderer_path' => self::get_mpdf_path(),
            'pdf_renderer_src' => self::get_mpdf_src_path(),
            'font_path' => self::get_mpdf_path() . '/ttfonts',
            'temp_dir' => WP_MPDF_Settings::get_path('temp_dir')
        ];
    }

    /**
     * Initialize plugin
     */
    public function run() {

        error_log('WP mPDF: run() method called');
        
        // Initialize settings
        // require_once WP_MPDF_DIR . 'includes/class-wp-mpdf-settings.php';
        
        error_log('WP mPDF: Settings class loaded');

        // Register autoloader
        $this->loader->register();

        // Setup hooks
        $this->setup_hooks();

        // Maybe setup admin
        if (is_admin()) {
            $this->setup_admin();
        }
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Add action untuk cleanup temporary files
        add_action('wp_scheduled_delete', [$this, 'cleanup_temp_files']);
    }

    /**
     * Setup admin functionality
     */
    private function setup_admin() {
        // Add menu pages if needed
        add_action('admin_menu', [$this, 'add_menu_pages']);

        // Add settings link to plugins page
        add_filter(
            'plugin_action_links_' . WP_MPDF_BASENAME,
            [$this, 'add_settings_link']
        );
    }



    // Method untuk mendapatkan options dari mPDF instance
    public function getOptions($mpdf) {
        if (!($mpdf instanceof \Mpdf\Mpdf)) {
            return new WP_Error('invalid_mpdf', 'Invalid mPDF instance');
        }
        return $mpdf->__get('options');
    }


    /**
     * Get temporary directory path
     */
    private function get_temp_dir() {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . 'mpdf-temp';
    }

    /**
     * Cleanup old temporary files
     */
    public function cleanup_temp_files() {
        $temp_dir = $this->get_temp_dir();
        if (!is_dir($temp_dir)) {
            return;
        }

        // Delete files older than 1 hour
        $files = glob($temp_dir . '/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 3600) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        // Implement if needed
    }

    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=wp-mpdf'),
            __('Settings', 'wp-mpdf')
        );
        array_unshift($links, $settings_link);
        return $links;
    }
}
