<?php
/**
 * Plugin Name: CashApp Gateway for WooCommerce
 * Plugin URI: https://idkcode.com
 * Description: A custom WooCommerce Payment Gateway for CashApp.
 * Version: 1.0.7
 * Author: JD Farrell
 * Author URI: https://idkcode.com
 * Text Domain: idkca-cashapp
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright: 2023 Digital Compass. All rights reserved.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Read the version from version.json
$version_info = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'version.json'), true);
if (!isset($version_info['version'])) {
    $version_info['version'] = '1.0.0'; // Fallback version
}

// Define constants using the configuration array
$plugin_config = array(
    'license_key_option' => 'dcomp_idkca_license_key',
    'plugin_version' => $version_info['version'],
    'plugin_dir_path' => plugin_dir_path(__FILE__),
    'plugin_url' => plugin_dir_url(__FILE__),
);
define('DCOMP_IDKCA_LICENSE_KEY_OPTION', $plugin_config['license_key_option']); // Option name for storing the license key.
define('DCOMP_IDKCA_PLUGIN_VERSION', $plugin_config['plugin_version']); // Plugin version.
define('DCOMP_IDKCA_DIR_PATH', $plugin_config['plugin_dir_path']); // Directory path of the plugin.
define('DCOMP_IDKCA_PLUGIN_URL', $plugin_config['plugin_url']); // URL of the plugin directory.

// Include utility functions.
require_once DCOMP_IDKCA_DIR_PATH . 'includes/class-cashapp-utils.php';

/**
 * Handles plugin uninstallation tasks.
 * 
 * This function is called when the plugin is uninstalled. It removes all plugin-related options
 * from the WordPress database if the 'retain data' option is not enabled.
 */
function dcomp_cashapp_uninstall() {
    if (get_option('woocommerce_dcomp_cashapp_retain_data') !== 'yes') {
        // Clean up options.
        delete_option('woocommerce_dcomp_cashapp_enabled');
        delete_option('woocommerce_dcomp_cashapp_title');
        delete_option('woocommerce_dcomp_cashapp_description');
        delete_option('woocommerce_dcomp_cashapp_cashapp_tag');
        delete_option('woocommerce_dcomp_cashapp_use_qr_code');
        delete_option('woocommerce_dcomp_cashapp_email_server');
        delete_option('woocommerce_dcomp_cashapp_email_port');
        delete_option('woocommerce_dcomp_cashapp_email_username');
        delete_option('woocommerce_dcomp_cashapp_email_password');
        delete_option('woocommerce_dcomp_cashapp_cron_interval');
        delete_option('woocommerce_dcomp_cashapp_retain_data');
        delete_option('woocommerce_dcomp_cashapp_debug');
    }
}
register_uninstall_hook(__FILE__, 'dcomp_cashapp_uninstall');

// Load plugin text domain for translations.
function dcomp_cashapp_load_textdomain() {
    load_plugin_textdomain( 'idkca-cashapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'dcomp_cashapp_load_textdomain' );

// Load the main plugin class.
require_once DCOMP_IDKCA_DIR_PATH . 'includes/class-cashapp-main.php';

