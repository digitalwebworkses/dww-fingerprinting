<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Generator
{
    public static function generate(
        string $customer_email,
        string $order_id,
        string $product_id = '',
        string $format = '',
        string $asset_id = ''
    ): string {
        $parts = [
            strtolower(trim($customer_email)),
            trim($order_id),
            trim($product_id),
            strtolower(trim($format)),
            trim($asset_id),
        ];

        $raw_value = implode('|', $parts);

        return 'DWW-' . strtoupper(substr(hash('sha256', $raw_value), 0, 16));
    }
}