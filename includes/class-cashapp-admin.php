<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Admin class for the CashApp Gateway plugin.
 * 
 * This class handles the display of CashApp-related information in the WooCommerce admin area.
 */
class DComp_CashApp_Admin {

    /**
     * Constructor to initialize the class.
     * 
     * Hooks into WooCommerce admin actions to display custom order data.
     */
    public function __construct() {
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_cashapp_tag_on_order_details'));
    }

    /**
     * Displays the CashApp tag on the order details page in the WooCommerce admin.
     * 
     * @param WC_Order $order The order object.
     */
    public function display_cashapp_tag_on_order_details($order) {
        $cashapp_tag = $order->get_meta('_dcomp_cashapp_tag', true); // Retrieve the CashApp tag meta data from the order.
        if (!empty($cashapp_tag)) {
            echo '<p><strong>CashApp Tag: </strong>' . esc_html($cashapp_tag) . '</p>'; // Display the CashApp tag if it exists.
        }
    }
}

// Initialize the admin class.
new DComp_CashApp_Admin();