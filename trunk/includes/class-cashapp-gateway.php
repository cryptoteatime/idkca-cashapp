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
        $this->method_title = __('CashApp', 'dcomp');
        $this->method_description = __('CashApp Payment Gateway for WooCommerce.', 'dcomp');
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
                // Step-by-Step Guide
                //check if premium add-on is included.
                'step_by_step_guide' => array(
                    'title'       => __('Step-by-Step Guide', 'dcomp'),
                    'type'        => 'title',
                    'description' => __(
                        '<ol>
                            <li>Step by step guide to create billing email and cashapp account.</li>
                        </ol>', 'dcomp'),
                ),
                'enabled' => array(
                    'title'   => __('Enable/Disable', 'dcomp'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable CashApp Payment', 'dcomp'),
                    'default' => 'no'
                ),
            );
        } else {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Enable/Disable', 'dcomp'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable CashApp Payment', 'dcomp'),
                    'default' => 'no'
                ),
            );
        }
        // check if license key is required. 
        if ($this->ispremium) {
            $this->form_fields = array_merge($this->form_fields, array(
                'license_key' => array(
                    'title'       => __('License Key', 'dcomp'),
                    'type'        => 'text',
                    'description' => __('Enter your license key for updates and support.', 'dcomp'),
                    'default'     => '',
                ),
            ));
        }

        // add generic fields. 
        $this->form_fields = array_merge($this->form_fields, array(
            'title' => array(
                'title'       => __('Title', 'dcomp'),
                'type'        => 'text',
                'description' => __('Title shown to the customer during checkout', 'dcomp'),
                'default'     => __('CashApp', 'dcomp')
            ),
            'description' => array(
                'title'       => __('Description', 'dcomp'),
                'type'        => 'textarea',
                'description' => __('Description shown to the customer during checkout', 'dcomp'),
                'default'     => __('Pay via CashApp', 'dcomp')
            ),
            // Additional fields for CashApp tag and QR code
            'cashapp_tag' => array(
                'title'       => __('CashApp Tag', 'dcomp'),
                'type'        => 'text',
                'description' => __('Your CashApp tag that customers will send payments to.', 'dcomp'),
                'default'     => ''
            ),
            'use_qr_code' => array(
                'title'       => __('Use QR Code Url?', 'dcomp'),
                'type'        => 'checkbox',
                'description' => __('Enable this to use a QR code URL.', 'dcomp'),
                'default'     => 'no'
            ),
        ));
        //check if premium add-on is included.
        if ($this->ispremium) {
            $this->form_fields = array_merge($this->form_fields, array(
                'email_server' => array(
                    'title'       => __('Email Server', 'dcomp'),
                    'type'        => 'text',
                    'description' => __('The incoming mail server for the email account to check for CashApp payments.', 'dcomp'),
                    'default'     => ''
                ),
                'email_port' => array(
                    'title'       => __('Email Port', 'dcomp'),
                    'type'        => 'number',
                    'description' => __('The port used for the incoming mail server.', 'dcomp'),
                    'default'     => 993
                ),
                'email_username' => array(
                    'title'       => __('Email Username', 'dcomp'),
                    'type'        => 'text',
                    'description' => __('The username for the email account to check for CashApp payments.', 'dcomp'),
                    'default'     => ''
                ),
                'email_password' => array(
                    'title'       => __('Email Password', 'dcomp'),
                    'type'        => 'password',
                    'description' => __('The password for the email account to check for CashApp payments. (This is encrypted for your safety)', 'dcomp'),
                    'default'     => ''
                ),
                'cron_interval' => array(
                    'title'       => __('Cron Interval (Seconds)', 'dcomp'),
                    'type'        => 'number',
                    'description' => __('Set the interval for the cron job in seconds.', 'dcomp'),
                    'default'     => 300, // Default is 5 minutes
                ),
            ));
        }
        $this->form_fields = array_merge($this->form_fields, array(
            'retain_data' => array(
                'title'       => __('Retain Data on Uninstall', 'dcomp'),
                'type'        => 'checkbox',
                'label'       => __('Retain plugin data upon uninstallation', 'dcomp'),
                'description' => __('Enable this option to keep all plugin data when the plugin is deleted. Useful for testing.', 'dcomp'),
                'default'     => 'no'
            ),
            'debug' => array(
                'title'       => __('Debug Log', 'dcomp'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'dcomp'),
                'default'     => 'no',
                'description' => sprintf(__('Log CashApp events, such as IPN requests, inside %s.', 'dcomp'), '<code>' . WC_Log_Handler_File::get_log_file_path('dcomp_cashapp') . '</code>'),
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
        $order = wc_get_order($order_id);

        // Retrieve the CashApp tag from the POST data
        $cashapp_tag = isset($_POST['dcomp_cashapp_tag_input']) ? sanitize_text_field($_POST['dcomp_cashapp_tag_input']) : '';

        // Validate the CashApp tag
        if (empty($cashapp_tag)) {
            // Add a WooCommerce error notice
            wc_add_notice(__('CashApp tag is required for payment.', 'dcomp'), 'error');

            // Return an array to signify failure and to not proceed to payment
            return array(
                'result' => 'failure',
                'reload' => true,
            );
        }

        // Mark as pending (we're awaiting the CashApp payment)
        $order->update_status('confirm-payment', __('Awaiting CashApp payment', 'dcomp'));

        // Save the CashApp tag as order metadata
        $order->update_meta_data('_dcomp_cashapp_tag', $cashapp_tag);
        $order->save_meta_data();

        // Return thank you page redirect
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
        // Get the cart total amount
        $amount = WC()->cart->total;

        // Debug log for the cart amount
        if ($this->debug) {
            $this->log->debug('Cart total amount: ' . $amount, array('source' => 'dcomp_cashapp'));
        }

        // CashApp tag and QR code from settings
        $cashapp_tag = $this->get_option('cashapp_tag');

        if ($cashapp_tag) {
            $cashapp_url = "https://cash.me/\{$cashapp_tag}/{$amount}/";

            // Description with nonce for faster processing
            $description_with_nonce = $this->description . "<br/>" . __("Please include cashtag for payment confirmation.", 'dcomp');

            // Add a text input for the CashApp tag
            echo '<p class="form-row address-field validate-required form-row-wide" id="dcomp_cashapp_tag_field" data-o_class="form-row form-row-wide address-field validate-required">';
            echo wp_kses_post($description_with_nonce);
            echo '<label for="dcomp_cashapp_tag_input">Your CashApp Tag&nbsp;<abbr class="required" title="required">*</abbr></label>';
            echo '<span class="woocommerce-input-wrapper">';
            echo '<input type="text" class="input-text" name="dcomp_cashapp_tag_input" id="dcomp_cashapp_tag_input" placeholder="$yourtag" value="" required autocomplete="off" style="max-width: 250px;"></span></p>';

            if ($this->get_option('use_qr_code') === 'yes') {
                echo '<div style="text-align: center; margin-top: 20px;">';
                echo '<a href="' . esc_url($cashapp_url) . '" target="_blank">';
                echo '<img style="width: 200px; height: auto; margin-bottom: 10px;" src="' . esc_url('https://cash.app/qr/'.$cashapp_tag) . '" alt="' . __('CashApp QR Code', 'dcomp') . '">';
                echo '</a>';
                echo '<p>Click or Scan to Pay</p>';
                echo '</div>';
            } else {
                echo '<p>' . __('Pay CashApp Tag: ', 'dcomp') . '<a href="' . esc_url($cashapp_url) . '" target="_blank">' . esc_attr($cashapp_tag) . '</a></p>';
            }
        }
    }

    /**
     * Override process_admin_options to encrypt email_password
     */
    public function process_admin_options() {
        parent::process_admin_options();

        // Additional processing if the add-on is active
        if ($this->ispremium) {
            // Save the license key
            $new_license_key = isset($_POST['woocommerce_dcomp_cashapp_license_key']) ? sanitize_text_field($_POST['woocommerce_dcomp_cashapp_license_key']) : '';
            update_option(DCOMP_IDKCA_LICENSE_KEY_OPTION, $new_license_key);

            // Get the new interval from the POST data
            $new_interval = isset($_POST['woocommerce_dcomp_cashapp_cron_interval']) ? intval($_POST['woocommerce_dcomp_cashapp_cron_interval']) : 300;
            update_option('woocommerce_dcomp_cashapp_cron_interval', $new_interval);

            // Re-run the function to add the custom cron schedule
            add_filter('cron_schedules', array('DComp_CashApp_Email_Handler', 'add_cron_interval'));

            // Clear the existing cron event
            wp_clear_scheduled_hook('dcomp_check_email_for_payment');
        }
    }

    /**
     * Register the new order status
     */
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

    /**
     * Add the 'Confirm Payment' status to the list of order statuses in WooCommerce.
     * 
     * @param array $order_statuses Existing order statuses.
     * @return array Modified order statuses with the custom status added.
     */
    public function add_custom_order_statuses($order_statuses) {
        $new_order_statuses = array();

        // Add 'Confirm Payment' status after 'On Hold'
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-processing' === $key) {
                $new_order_statuses['wc-confirm-payment'] = __('Confirming', 'woocommerce');
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

        // Check if we are on the WooCommerce Orders page
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
        // Check if we are on the WooCommerce checkout page
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
        // Check if the chosen payment method is CashApp
        if (WC()->session->get('chosen_payment_method') === 'dcomp_cashapp') {
            if (empty($_POST['dcomp_cashapp_tag_input'])) {
                wc_add_notice(__('CashApp tag is required for payment.', 'dcomp'), 'error');
            }
        }
    }
}