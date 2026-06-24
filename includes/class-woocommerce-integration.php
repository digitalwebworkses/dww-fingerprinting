<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Integration
{
    public static function init(): void
    {
        add_action(
            'woocommerce_order_status_processing',
            [self::class, 'handle_order'],
            10,
            1
        );

        add_action(
            'woocommerce_order_status_completed',
            [self::class, 'handle_order'],
            10,
            1
        );

        add_action(
            'woocommerce_order_status_changed',
            [self::class, 'handle_order_status_changed'],
            10,
            4
        );
    }

    public static function handle_order_status_changed(
        int $order_id,
        string $old_status,
        string $new_status,
        $order
    ): void {
        if (!in_array($new_status, ['processing', 'completed'], true)) {
            return;
        }

        self::handle_order($order_id);
    }

    public static function handle_order(int $order_id): void
    {
        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $customer_email = $order->get_billing_email();

        if (empty($customer_email)) {
            $customer_email = 'test@example.com';
        }

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            if (!$product) {
                continue;
            }

            if (!Product_Settings::is_enabled($product)) {
                continue;
            }

            $product_id = (string) $product->get_id();

            $fingerprint_id = Fingerprint_Generator::generate(
                $customer_email,
                (string) $order_id
            );

            if (Fingerprint_DB::exists($fingerprint_id)) {
                continue;
            }

            Fingerprint_DB::insert([
                'fingerprint_id' => $fingerprint_id,
                'customer_email' => $customer_email,
                'order_id'       => (string) $order_id,
                'product_id'     => $product_id,
                'source_file'    => 'woocommerce-order-detection-test',
                'generated_file' => 'pending-pdf-generation',
            ]);
        }
    }
}