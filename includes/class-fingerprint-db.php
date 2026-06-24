<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_DB
{

    public static function exists(string $fingerprint_id): bool
    {
        return self::get_by_fingerprint($fingerprint_id) !== null;
    }
    
    public static function insert(array $data): int
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dww_fingerprints';

        $inserted = $wpdb->insert(
            $table_name,
            [
                'fingerprint_id' => sanitize_text_field($data['fingerprint_id'] ?? ''),
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'order_id'       => sanitize_text_field($data['order_id'] ?? ''),
                'product_id'     => sanitize_text_field($data['product_id'] ?? ''),
                'source_file'    => sanitize_text_field($data['source_file'] ?? ''),
                'generated_file' => sanitize_text_field($data['generated_file'] ?? ''),
                'created_at'     => current_time('mysql'),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public static function get_by_fingerprint(string $fingerprint_id): ?object
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dww_fingerprints';

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE fingerprint_id = %s LIMIT 1",
                $fingerprint_id
            )
        );

        return $result ?: null;
    }
}
