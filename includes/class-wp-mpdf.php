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

    /**
     * mPDF loader instance
     */
    private $loader;

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
        'margin_footer' => 9
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
        $this->loader = new WP_MPDF_Loader();
    }

    /**
     * Initialize plugin
     */
    public function run() {
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

    /**
     * Generate PDF using mPDF
     * 
     * @param string $html    HTML content
     * @param array  $options mPDF options
     * @return string|WP_Error Path to generated PDF or error
     */
    public function generate_pdf($html, $options = []) {
        try {
            // Merge dengan default options
            $options = wp_parse_args($options, $this->default_options);

            // Initialize mPDF
            $mpdf = new \Mpdf\Mpdf($options);

            // Set document info
            $mpdf->SetTitle(wp_get_document_title());
            $mpdf->SetAuthor(get_bloginfo('name'));
            $mpdf->SetCreator('WP mPDF ' . WP_MPDF_VERSION);

            // Write content
            $mpdf->WriteHTML($html);

            // Generate temporary filename
            $filename = wp_unique_filename(
                $this->get_temp_dir(),
                'wpmpdf-' . time() . '.pdf'
            );
            $output_file = trailingslashit($this->get_temp_dir()) . $filename;

            // Output to file
            $mpdf->Output($output_file, 'F');

            return $output_file;

        } catch (Exception $e) {
            return new WP_Error(
                'mpdf_error',
                $e->getMessage()
            );
        }
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
