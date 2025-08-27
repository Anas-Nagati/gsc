<?php
if (!defined('ABSPATH')) {
    exit;
}

class GSC_Woo {

    public function __construct() {
        add_action('woocommerce_checkout_order_processed', [$this, 'send_to_api'], 10, 1);
    }

    public function send_to_api($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $data = [
            'order_id' => $order->get_id(),
            'customer' => [
                'first_name' => $order->get_billing_first_name(),
                'last_name'  => $order->get_billing_last_name(),
                'email'      => $order->get_billing_email(),
            ],
            'items' => [],
            'total' => $order->get_total(),
        ];

        foreach ($order->get_items() as $item) {
            $data['items'][] = [
                'product_id' => $item->get_product_id(),
                'name'       => $item->get_name(),
                'quantity'   => $item->get_quantity(),
                'subtotal'   => $item->get_subtotal(),
            ];
        }

        $sheet_id = get_option('sheet_for_orders', null);

        $payload = [
            'form_id'  => 'woocommerce_checkout',
            'sheet_id' => $sheet_id,
            'data'     => $data,
        ];

        GSC_API::send($payload);
    }
}
