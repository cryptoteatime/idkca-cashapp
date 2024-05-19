<?php
/**
 * Plugin Name: CashApp Gateway for WooCommerce
 * Plugin URI: https://idkcode.com
 * Description: A custom WooCommerce Payment Gateway for CashApp.
 * Version: 1.1.21
 * Author: JD Farrell
 * Author URI: https://idkcode.com
 * Text Domain: dcomp
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright: 2023 Digital Compass. All rights reserved.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DCOMP_IDKCA_LICENSE_KEY_OPTION', 'dcomp_idkca_license_key');
define('DCOMP_IDKCA_PLUGIN_VERSION', '1.1.21');
define('DCOMP_IDKCA_DIR_PATH', plugin_dir_path(__FILE__));
define('DCOMP_IDKCA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include utility functions
require_once DCOMP_IDKCA_DIR_PATH . 'includes/class-cashapp-utils.php';


/**
 * Handles plugin uninstallation tasks.
 */
function dcomp_cashapp_uninstall() {
    if (get_option('woocommerce_dcomp_cashapp_retain_data') !== 'yes') {
        // Clean up options
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

// Load the main plugin class
require_once DCOMP_IDKCA_DIR_PATH . 'includes/class-cashapp-main.php';