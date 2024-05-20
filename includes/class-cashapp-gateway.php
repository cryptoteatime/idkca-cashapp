<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Digital Compass CashApp Gateway Class
 * 
 * Provides the CashApp Payment Gateway functionality for WooCommerce.
 * 
 * @package DComp_CashApp_Payment_Gateway
 * @since 1.0.0
 * @license GPL-2.0+
 */
class DComp_CashApp_Payment_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     * 
     * Initializes the gateway settings, hooks, and actions.
     */
    public function __construct() {
        $this->id = 'dcomp_cashapp';
        $this->method_title = __('CashApp', 'idkca-cashapp');
        $this->method_description = __('CashApp Payment Gateway for WooCommerce.', 'idkca-cashapp');
        $this->log = wc_get_logger();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->debug = 'yes' === $this->get_option('debug', 'no');
        $this->ispremium = DComp_CashApp_Utils::is_addon_active('idkca-cashapp-autoconfirm-addon/cashapp-autoconfirm-plugin.php');

        $this->init_form_fields();
        $this->init_settings();

        add_action('init', array($this, 'register_custom_order_status'));
        add_filter('wc_order_statuses', array($this, 'add_custom_order_statuses'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_css'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_css'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_checkout_process', array($this, 'checkout_process'));
    }

    /**
     * Initialize Gateway Settings Form Fields
     * 
     * Sets up the form fields for the gateway settings page.
     */
    public function init_form_fields() {
        if ($this->ispremium) {
            $this->form_fields = array(
                'step_by_step_guide' => array(
                    'title'       => __('Step-by-Step Guide', 'idkca-cashapp'),
                    'type'        => 'title',
                    'description' => __(
                        'Step by step guide to create billing email and cashapp account.',
                        'idkca-cashapp'
                    ),
                ),
                'enabled' => array(
                    'title'   => __('Enable/Disable', 'idkca-cashapp'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable CashApp Payment', 'idkca-cashapp'),
                    'default' => 'no'
                ),
            );
        } else {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Enable/Disable', 'idkca-cashapp'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable CashApp Payment', 'idkca-cashapp'),
                    'default' => 'no'
                ),
            );
        }
        if ($this->ispremium) {
            $this->form_fields = array_merge($this->form_fields, array(
                'license_key' => array(
                    'title'       => __('License Key', 'idkca-cashapp'),
                    'type'        => 'text',
                    'description' => __('Enter your license key for updates and support.', 'idkca-cashapp'),
                    'default'     => '',
                ),
            ));
        }

        $this->form_fields = array_merge($this->form_fields, array(
            'title' => array(
                'title'       => __('Title', 'idkca-cashapp'),
                'type'        => 'text',
                'description' => __('Title shown to the customer during checkout', 'idkca-cashapp'),
                'default'     => __('CashApp', 'idkca-cashapp')
            ),
            'description' => array(
                'title'       => __('Description', 'idkca-cashapp'),
                'type'        => 'textarea',
                'description' => __('Description shown to the customer during checkout', 'idkca-cashapp'),
                'default'     => __('Pay via CashApp', 'idkca-cashapp')
            ),
            'cashapp_tag' => array(
                'title'       => __('CashApp Tag', 'idkca-cashapp'),
                'type'        => 'text',
                'description' => __('Your CashApp tag that customers will send payments to.', 'idkca-cashapp'),
                'default'     => ''
            ),
            'use_qr_code' => array(
                'title'       => __('Use QR Code Url?', 'idkca-cashapp'),
                'type'        => 'checkbox',
                'description' => __('Enable this to use a QR code URL.', 'idkca-cashapp'),
                'default'     => 'no'
            ),
        ));
        if ($this->ispremium) {
            $this->form_fields = array_merge($this->form_fields, array(
                'email_server' => array(
                    'title'       => __('Email Server', 'idkca-cashapp'),
                    'type'        => 'text',
                    'description' => __('The incoming mail server for the email account to check for CashApp payments.', 'idkca-cashapp'),
                    'default'     => ''
                ),
                'email_port' => array(
                    'title'       => __('Email Port', 'idkca-cashapp'),
                    'type'        => 'number',
                    'description' => __('The port used for the incoming mail server.', 'idkca-cashapp'),
                    'default'     => 993
                ),
                'email_username' => array(
                    'title'       => __('Email Username', 'idkca-cashapp'),
                    'type'        => 'text',
                    'description' => __('The username for the email account to check for CashApp payments.', 'idkca-cashapp'),
                    'default'     => ''
                ),
                'email_password' => array(
                    'title'       => __('Email Password', 'idkca-cashapp'),
                    'type'        => 'password',
                    'description' => __('The password for the email account to check for CashApp payments. (This is encrypted for your safety)', 'idkca-cashapp'),
                    'default'     => ''
                ),
                'cron_interval' => array(
                    'title'       => __('Cron Interval (Seconds)', 'idkca-cashapp'),
                    'type'        => 'number',
                    'description' => __('Set the interval for the cron job in seconds.', 'idkca-cashapp'),
                    'default'     => 300,
                ),
            ));
        }
        $this->form_fields = array_merge($this->form_fields, array(
            'retain_data' => array(
                'title'       => __('Retain Data on Uninstall', 'idkca-cashapp'),
                'type'        => 'checkbox',
                'label'       => __('Retain plugin data upon uninstallation', 'idkca-cashapp'),
                'description' => __('Enable this option to keep all plugin data when the plugin is deleted. Useful for testing.', 'idkca-cashapp'),
                'default'     => 'no'
            ),
            'debug' => array(
                'title'       => __('Debug Log', 'idkca-cashapp'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'idkca-cashapp'),
                'default'     => 'no',
                // Translators: %s is the path to the debug log file
                'description' => sprintf(__('Log CashApp events, such as IPN requests, inside %s.', 'idkca-cashapp'), '<code>' . esc_html(WC_Log_Handler_File::get_log_file_path('dcomp_cashapp')) . '</code>'),
            ),
        ));
    }

    /**
     * Process Payment
     *
     * Processes the payment and returns the result.
     * 
     * @param int $order_id The order ID.
     * @return array The result of the payment processing.
     */
    public function process_payment($order_id) {
        // Check nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-process_checkout')) {
            wc_add_notice(__('Nonce verification failed', 'idkca-cashapp'), 'error');
            return array(
                'result' => 'failure',
                'reload' => true,
            );
        }

        $order = wc_get_order($order_id);

        // Retrieve the CashApp tag from the POST data
        $cashapp_tag = isset($_POST['dcomp_cashapp_tag_input']) ? sanitize_text_field($_POST['dcomp_cashapp_tag_input']) : '';

        // Validate the CashApp tag
        if (empty($cashapp_tag)) {
            wc_add_notice(__('CashApp tag is required for payment.', 'idkca-cashapp'), 'error');
            return array(
                'result' => 'failure',
                'reload' => true,
            );
        }

        // Mark as pending (we're awaiting the CashApp payment)
        $order->update_status('confirm-payment', __('Awaiting CashApp payment', 'idkca-cashapp'));

        // Save the CashApp tag as order metadata
        $order->update_meta_data('_dcomp_cashapp_tag', $cashapp_tag);
        $order->save_meta_data();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    /**
     * Display CashApp details on the checkout page
     * 
     * Outputs the payment fields on the checkout page.
     */
    public function payment_fields() {
        $amount = WC()->cart->total;

        if ($this->debug) {
            $this->log->debug('Cart total amount: ' . esc_html($amount), array('source' => 'dcomp_cashapp'));
        }

        $cashapp_tag = $this->get_option('cashapp_tag');

        if ($cashapp_tag) {
            $cashapp_url = esc_url("https://cash.me/\{$cashapp_tag}/{$amount}/");

            echo '<p class="form-row address-field validate-required form-row-wide" id="dcomp_cashapp_tag_field" data-o_class="form-row form-row-wide address-field validate-required">';
            echo wp_kses_post($this->description . "<br/>" . __("Please include cashtag for payment confirmation.", 'idkca-cashapp'));
            echo '<label for="dcomp_cashapp_tag_input">' . esc_html__('Your CashApp Tag', 'idkca-cashapp') . '&nbsp;<abbr class="required" title="required">*</abbr></label>';
            echo '<span class="woocommerce-input-wrapper">';
            echo '<input type="text" class="input-text" name="dcomp_cashapp_tag_input" id="dcomp_cashapp_tag_input" placeholder="$yourtag" value="" required autocomplete="off" style="max-width: 250px;"></span></p>';

            if ($this->get_option('use_qr_code') === 'yes') {
                echo '<div style="text-align: center; margin-top: 20px;">';
                echo '<a href="' . esc_url($cashapp_url) . '" target="_blank">';
                echo '<img style="width: 200px; height: auto; margin-bottom: 10px;" src="' . esc_url('https://cash.app/qr/'.$cashapp_tag) . '" alt="' . esc_attr__('CashApp QR Code', 'idkca-cashapp') . '">';
                echo '</a>';
                echo '<p>' . esc_html__('Click or Scan to Pay', 'idkca-cashapp') . '</p>';
                echo '</div>';
            } else {
                echo '<p>' . esc_html__('Pay CashApp Tag: ', 'idkca-cashapp') . '<a href="' . esc_url($cashapp_url) . '" target="_blank">' . esc_html($cashapp_tag) . '</a></p>';
            }
        }
    }

    /**
     * Override process_admin_options to encrypt email_password
     */
    public function process_admin_options() {
        // Check nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-settings')) {
            wp_die(esc_html__('Nonce verification failed', 'idkca-cashapp'), esc_html__('Error', 'idkca-cashapp'), array('response' => 403));
        }

        parent::process_admin_options();

        if ($this->ispremium) {
            $new_license_key = isset($_POST['woocommerce_dcomp_cashapp_license_key']) ? sanitize_text_field($_POST['woocommerce_dcomp_cashapp_license_key']) : '';
            update_option(DCOMP_IDKCA_LICENSE_KEY_OPTION, $new_license_key);

            $new_interval = isset($_POST['woocommerce_dcomp_cashapp_cron_interval']) ? intval($_POST['woocommerce_dcomp_cashapp_cron_interval']) : 300;
            update_option('woocommerce_dcomp_cashapp_cron_interval', $new_interval);

            add_filter('cron_schedules', array('DComp_CashApp_Email_Handler', 'add_cron_interval'));
            wp_clear_scheduled_hook('dcomp_check_email_for_payment');
        }
    }

    /**
     * Register the new order status
     */
    public function register_custom_order_status() {
        // Translators: Order status
        register_post_status('wc-confirm-payment', array(
            'label'                     => _x('Confirming', 'Order status', 'idkca-cashapp'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            // Translators: %s represents the count of orders with this status
            'label_count'               => _n_noop('Confirm Payment <span class="count">(%s)</span>', 'Confirm Payment <span class="count">(%s)</span>', 'idkca-cashapp'),
        ));
    }

    /**
     * Add the 'Confirm Payment' status to the list of order statuses in WooCommerce.
     * 
     * @param array $order_statuses Existing order statuses.
     * @return array Modified order statuses with the custom status added.
     */
    public function add_custom_order_statuses($order_statuses) {
        $new_order_statuses = array();

        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-processing' === $key) {
                $new_order_statuses['wc-confirm-payment'] = __('Confirming', 'idkca-cashapp');
            }
        }

        return $new_order_statuses;
    }

    /**
     * Enqueue custom admin CSS.
     * 
     * Enqueues the admin CSS file for the plugin.
     */
    public function enqueue_admin_css() {
        global $pagenow;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not applicable here as this function does not process form data.
        if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'wc-orders') {
            $css_file_url = plugins_url('assets/css/admin-order-status.css', dirname(__FILE__));
            wp_enqueue_style('admin-order-status', $css_file_url, array(), DCOMP_IDKCA_PLUGIN_VERSION);
        }
    }

    /**
     * Enqueue custom frontend CSS on the WooCommerce checkout page.
     * 
     * Enqueues the frontend CSS file for the plugin.
     */
    public function enqueue_frontend_css() {
        if (is_checkout()) {
            $css_file_url = plugins_url('assets/css/frontend-inline.css', dirname(__FILE__));
            wp_enqueue_style('frontend-inline', $css_file_url, array(), DCOMP_IDKCA_PLUGIN_VERSION);
        }
    }

    /**
     * Custom checkout process.
     * 
     * Adds validation for the CashApp tag during the checkout process.
     */
    public function checkout_process() {
        if (WC()->session->get('chosen_payment_method') === 'dcomp_cashapp') {
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-process_checkout')) {
                wc_add_notice(__('Nonce verification failed', 'idkca-cashapp'), 'error');
            } elseif (empty($_POST['dcomp_cashapp_tag_input'])) {
                wc_add_notice(__('CashApp tag is required for payment.', 'idkca-cashapp'), 'error');
            }
        }
    }
}