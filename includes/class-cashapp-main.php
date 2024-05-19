<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main class for the CashApp Gateway plugin.
 * 
 * This class handles the initialization, inclusion of necessary files, 
 * and setup of custom hooks and filters required for the plugin functionality.
 */
class DComp_CashApp_Main {

    /**
     * Constructor to initialize the class.
     * 
     * Hooks various actions and filters to set up the plugin.
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
        add_action('init', array($this, 'register_custom_order_status'));
        add_filter('wc_order_statuses', array($this, 'add_custom_order_statuses'));
    }

    /**
     * Initializes the plugin.
     * 
     * Checks if WooCommerce is active, includes necessary files, and sets up 
     * filters and actions for the payment gateway and add-on functionalities.
     */
    public function init() {
        if (!class_exists('WC_Payment_Gateway')) {
            return; // Exit if WooCommerce is not active.
        }

        $this->includes();

        add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        // Check if the add-on is active and schedule cron jobs if necessary.
        if (DComp_CashApp_Utils::is_addon_active('idkca-cashapp-autoconfirm-addon/cashapp-autoconfirm-plugin.php')) {
            add_filter('cron_schedules', array('DComp_CashApp_Email_Handler', 'add_cron_interval'));
            add_action('dcomp_check_email_for_payment', array('DComp_CashApp_Email_Handler', 'check_email_for_payment'));

            // Schedule a cron job for checking emails if not already scheduled.
            if (!wp_next_scheduled('dcomp_check_email_for_payment')) {
                wp_schedule_event(time(), 'dcomp_cashapp_custom_interval', 'dcomp_check_email_for_payment');
            }
        }
    }

    /**
     * Includes necessary files for the plugin.
     * 
     * Loads the main gateway class, admin class, and optionally the add-on email handler class.
     */
    public function includes() {
        require_once plugin_dir_path(__FILE__) . 'class-cashapp-gateway.php';
        
        // Check if the premium add-on is active and include its email handler.
        if (DComp_CashApp_Utils::is_addon_active('idkca-cashapp-autoconfirm-addon/cashapp-autoconfirm-plugin.php')) {
            require_once WP_PLUGIN_DIR . '/idkca-cashapp-autoconfirm-addon/includes/class-cashapp-email-handler.php';
        }
        
        require_once plugin_dir_path(__FILE__) . 'class-cashapp-admin.php';
    }

    /**
     * Adds the CashApp payment gateway to WooCommerce.
     * 
     * @param array $gateways Existing payment gateways.
     * @return array Modified payment gateways with the CashApp gateway added.
     */
    public function add_gateway($gateways) {
        $gateways[] = 'DComp_CashApp_Payment_Gateway';
        return $gateways;
    }

    /**
     * Adds a settings link to the plugin action links.
     * 
     * @param array $links Existing plugin action links.
     * @return array Modified plugin action links with the settings link added.
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=dcomp_cashapp') . '">' . __('Settings', 'idkca-cashapp') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Displays an admin notice if WooCommerce is not active.
     */
    public function woocommerce_missing_notice() {
        if (!class_exists('WC_Payment_Gateway')) {
            /* translators: %s: WooCommerce download link */
            echo '<div class="error"><p><strong>' . sprintf(esc_html__('CashApp Gateway requires WooCommerce to be installed and active. You can download %s here.', 'idkca-cashapp'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
        }
    }

    /**
     * Registers a custom order status for confirming payments.
     */
    public function register_custom_order_status() {
        register_post_status('wc-confirm-payment', array(
            'label'                     => _x('Confirming', 'Order status', 'idkca-cashapp'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            /* translators: %s: number of orders */
            'label_count'               => _n_noop('Confirm Payment <span class="count">(%s)</span>', 'Confirm Payment <span class="count">(%s)</span>', 'idkca-cashapp'),
        ));
    }

    /**
     * Adds custom order statuses to WooCommerce.
     * 
     * @param array $order_statuses Existing order statuses.
     * @return array Modified order statuses with the custom status added.
     */
    public function add_custom_order_statuses($order_statuses) {
        $new_order_statuses = array();

        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-processing' === $key) {
                $new_order_statuses['wc-confirm-payment'] = _x('Confirming', 'Order status', 'idkca-cashapp');
            }
        }

        return $new_order_statuses;
    }
}

// Initialize the main plugin class.
new DComp_CashApp_Main();