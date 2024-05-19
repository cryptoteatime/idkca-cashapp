<?php
if (!defined('ABSPATH')) {
    exit;
}

class DComp_CashApp_Utils {

    public static function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', NONCE_KEY, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function decrypt($data) {
        $data = base64_decode($data);
        list($encrypted_data, $iv) = explode('::', $data, 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', NONCE_KEY, 0, $iv);
    }

    /**
     * Helper function to check if the add-on plugin is active.
     *
     * @return bool
     */
    public static function is_addon_active($plugin_path) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        return is_plugin_active( $plugin_path );
    }

    // Add other shared utility functions here
}