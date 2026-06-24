<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Generator
{
    public static function generate(string $customer_email, string $order_id): string
    {
        $raw_value = strtolower(trim($customer_email)) . '|' . trim($order_id);

        return 'DWW-' . strtoupper(substr(hash('sha256', $raw_value), 0, 16));
    }
}