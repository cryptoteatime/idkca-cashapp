<?php
if (!defined('ABSPATH')) {
    exit;
}

class DComp_CashApp_Main {

    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
        add_action('init', array($this, 'register_custom_order_status'));
        add_filter('wc_order_statuses', array($this, 'add_custom_order_statuses'));
    }

    public function init() {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        $this->includes();

        add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        
        if (DComp_CashApp_Utils::is_addon_active('idkca-cashapp-autoconfirm-addon/cashapp-autoconfirm-plugin.php')) {
            add_filter('cron_schedules', array('DComp_CashApp_Email_Handler', 'add_cron_interval'));
            add_action('dcomp_check_email_for_payment', array('DComp_CashApp_Email_Handler', 'check_email_for_payment'));
		
    		// Schedule a cron job for checking emails if not already scheduled
    		if (!wp_next_scheduled('dcomp_check_email_for_payment')) {
    			wp_schedule_event(time(), 'dcomp_cashapp_custom_interval', 'dcomp_check_email_for_payment');
    		}
        } 
        

    }

    public function includes() {
        require_once plugin_dir_path(__FILE__) . 'class-cashapp-gateway.php';
        // Check if premium add-on is included.
        if (DComp_CashApp_Utils::is_addon_active('idkca-cashapp-autoconfirm-addon/cashapp-autoconfirm-plugin.php')) {
            // Dynamically build the path to the add-on plugin's file.
            require_once WP_PLUGIN_DIR . '/idkca-cashapp-autoconfirm-addon/includes/class-cashapp-email-handler.php';
        }
        require_once plugin_dir_path(__FILE__) . 'class-cashapp-admin.php';
    }

    public function add_gateway($gateways) {
        $gateways[] = 'DComp_CashApp_Payment_Gateway';
        return $gateways;
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=dcomp_cashapp') . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function woocommerce_missing_notice() {
        if (!class_exists('WC_Payment_Gateway')) {
            echo '<div class="error"><p><strong>' . sprintf(esc_html__('CashApp Gateway requires WooCommerce to be installed and active. You can download %s here.'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
        }
    }

    public function register_custom_order_status() {
        register_post_status('wc-confirm-payment', array(
            'label'                     => _x('Confirming', 'Order status', 'woocommerce'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Confirm Payment <span class="count">(%s)</span>', 'Confirm Payment <span class="count">(%s)</span>', 'woocommerce'),
        ));
    }

    public function add_custom_order_statuses($order_statuses) {
        $new_order_statuses = array();

        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-processing' === $key) {
                $new_order_statuses['wc-confirm-payment'] = _x('Confirming', 'Order status', 'woocommerce');
            }
        }

        return $new_order_statuses;
    }
}

new DComp_CashApp_Main();