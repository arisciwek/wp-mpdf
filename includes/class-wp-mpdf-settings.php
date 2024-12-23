<?php
/**
 * WP mPDF Settings Class
 *
 * @package     CustomerManagement
 * @subpackage  Includes
 * @author      arisciwek <arisciwek@gmail.com>
 * @copyright   2024 arisciwek
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * 
 * Path: includes/class-wp-mpdf-settings.php
 * 
 * Description: Class untuk mengelola settings WP mPDF.
 *              Menyediakan halaman admin untuk konfigurasi path dan opsi.
 *              Mengelola penyimpanan settings di WordPress options.
 *              Menyediakan helper methods untuk akses settings.
 *              Support untuk konfigurasi path font dan temporary files.
 * 
 * Required Methods:
 * - register_settings()  : Register WordPress settings
 * - add_settings_page() : Add settings page to menu
 * - render_settings()   : Render settings form
 * - get_path()         : Get path setting
 * 
 * Dependencies:
 * - WordPress Settings API
 * - WordPress Options API
 * - WordPress Admin Menu API
 * 
 * Usage:
 * $settings = new WP_MPDF_Settings();
 * $temp_dir = WP_MPDF_Settings::get_path('temp_dir');
 * 
 * Changelog:
 * 1.0.0 - 2024-12-21
 * - Initial release
 * - Added settings page
 * - Added path configuration
 * - Added helper methods
 */


if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class WP_MPDF_Settings {
    /**
     * Option group name
     */
    const OPTION_GROUP = 'wp_mpdf_options';
    
    /**
     * Page/Menu slug
     */
    const PAGE_SLUG = 'wp-mpdf';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook lebih awal untuk admin_menu
        add_action('admin_menu', [$this, 'add_settings_page'], 99);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'options-general.php',                  // Parent slug
            __('WP mPDF Settings', 'wp-mpdf'),     // Page title
            __('WP mPDF', 'wp-mpdf'),             // Menu title
            'manage_options',                      // Capability
            'wp-mpdf',                            // Menu slug
            [$this, 'render_settings_page']        // Callback function
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            self::OPTION_GROUP, 
            'wp_mpdf_paths',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_paths'],
                'default' => [
                    'temp_dir' => wp_upload_dir()['basedir'] . '/mpdf-temp',
                    'font_dir' => wp_upload_dir()['basedir'] . '/mpdf-fonts',
                    'font_data' => wp_upload_dir()['basedir'] . '/mpdf-fontdata'
                ]
            ]
        );

        add_settings_section(
            'wp_mpdf_paths_section',
            __('Path Settings', 'wp-mpdf'),
            [$this, 'render_paths_section'],
            self::PAGE_SLUG
        );

        add_settings_field(
            'wp_mpdf_temp_dir',
            __('Temporary Directory', 'wp-mpdf'),
            [$this, 'render_dir_field'],
            self::PAGE_SLUG,
            'wp_mpdf_paths_section',
            ['key' => 'temp_dir']
        );

        add_settings_field(
            'wp_mpdf_font_dir',
            __('Font Directory', 'wp-mpdf'),
            [$this, 'render_dir_field'],
            self::PAGE_SLUG,
            'wp_mpdf_paths_section',
            ['key' => 'font_dir']
        );

        add_settings_field(
            'wp_mpdf_font_data',
            __('Font Data Directory', 'wp-mpdf'),
            [$this, 'render_dir_field'],
            self::PAGE_SLUG,
            'wp_mpdf_paths_section',
            ['key' => 'font_data']
        );
    }

    /**
     * Sanitize paths settings
     */
    public function sanitize_paths($paths) {
        $sanitized = [];
        foreach ($paths as $key => $path) {
            $sanitized[$key] = untrailingslashit(wp_normalize_path($path));
        }
        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return wp_die(
                __('Sorry, you are not allowed to access this page.', 'wp-mpdf'),
                403
            );
        }

        // Check nonce and other security measures
        if (!empty($_POST)) {
            check_admin_referer(self::OPTION_GROUP . '-options');
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render paths section description
     */
    public function render_paths_section($args) {
        ?>
        <p>
            <?php _e('Configure directories used by mPDF for temporary files and fonts.', 'wp-mpdf'); ?>
        </p>
        <?php
    }

    /**
     * Render directory field
     */
    public function render_dir_field($args) {
        $paths = get_option('wp_mpdf_paths');
        $key = $args['key'];
        $value = isset($paths[$key]) ? $paths[$key] : '';
        ?>
        <input type="text" 
               id="wp_mpdf_<?php echo esc_attr($key); ?>"
               name="wp_mpdf_paths[<?php echo esc_attr($key); ?>]"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text">
        <p class="description">
            <?php 
            switch ($key) {
                case 'temp_dir':
                    _e('Directory for temporary files. Must be writable.', 'wp-mpdf');
                    break;
                case 'font_dir':
                    _e('Directory for custom fonts. Must be writable.', 'wp-mpdf');
                    break;
                case 'font_data':
                    _e('Directory for font data cache. Must be writable.', 'wp-mpdf');
                    break;
            }
            ?>
        </p>
        <?php
    }

    /**
     * Get path setting
     * 
     * @param string $key Path key
     * @return string|null Path value or null if not found
     */
    public static function get_path($key) {
        $paths = get_option('wp_mpdf_paths');
        return isset($paths[$key]) ? $paths[$key] : null;
    }
}
