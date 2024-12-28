<?php
/**
 * QR Code Integration Setup
 * Path: wp-mpdf/includes/class-wp-mpdf-qrcode.php
 */

class WP_Mpdf_QrCode {
    
    /**
     * Initialize QR setup
     */
    public static function init() {
        if (!self::verify_installation()) {
            add_action('admin_notices', function() {
                $message = __('QR Code library not found. Please check mPDF installation.', 'wp-mpdf');
                echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
            });
            return false;
        }
        return true;
    }

    /**
     * Verify QR installation
     */
    public static function verify_installation() {
        $required_files = [
            'QrCode.php',
            'QrCodeException.php',
            'Output/Html.php',
            'Output/Mpdf.php',
            'Output/Png.php',
            'Output/Svg.php'
        ];

        $base_dir = WP_MPDF_QRCODE_PATH;
        
        foreach ($required_files as $file) {
            if (!file_exists($base_dir . $file)) {
                error_log('QR Code file not found: ' . $base_dir . $file);
                return false;
            }
        }

        return true;
    }
}