<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Utility class for the CashApp Gateway plugin.
 * 
 * This class provides shared utility functions such as encryption, decryption,
 * and checking the activation status of the add-on plugin.
 */
class DComp_CashApp_Utils {

    /**
     * Encrypts data using AES-256-CBC encryption.
     * 
     * @param string $data The data to be encrypted.
     * @return string The encrypted data, base64 encoded.
     */
    public static function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(16); // Generate a random initialization vector (IV).
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', NONCE_KEY, 0, $iv); // Encrypt the data.
        return base64_encode($encrypted . '::' . $iv); // Return the encrypted data and IV, base64 encoded.
    }

    /**
     * Decrypts data using AES-256-CBC decryption.
     * 
     * @param string $data The data to be decrypted, base64 encoded.
     * @return string The decrypted data.
     */
    public static function decrypt($data) {
        $data = base64_decode($data); // Base64 decode the input data.
        list($encrypted_data, $iv) = explode('::', $data, 2); // Split the encrypted data and IV.
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', NONCE_KEY, 0, $iv); // Decrypt the data and return it.
    }

    /**
     * Checks if the specified add-on plugin is active.
     *
     * @param string $plugin_path The relative path to the plugin file.
     * @return bool True if the add-on plugin is active, false otherwise.
     */
    public static function is_addon_active($plugin_path) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php'); // Include the plugin.php file to use its functions.
        return is_plugin_active($plugin_path); // Check if the specified plugin is active.
    }

    // Add other shared utility functions here.
}
