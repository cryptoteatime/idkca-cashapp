<?php
if (!defined('ABSPATH')) {
    exit;
}

class DComp_CashApp_Admin {

    public function __construct() {
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_cashapp_tag_on_order_details'));
    }

    public function display_cashapp_tag_on_order_details($order) {
        $cashapp_tag = $order->get_meta('_dcomp_cashapp_tag', true);
        if (!empty($cashapp_tag)) {
            echo '<p><strong>CashApp Tag: </strong>' . esc_html($cashapp_tag) . '</p>';
        }
    }
}

new DComp_CashApp_Admin();