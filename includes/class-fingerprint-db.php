<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_DB
{
    public static function get_table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'dww_fingerprints';
    }

    public static function create_table(): void
    {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            fingerprint_id VARCHAR(64) NOT NULL,
            payload_hash VARCHAR(64) NOT NULL DEFAULT '',
            customer_email VARCHAR(255) NOT NULL,
            order_id VARCHAR(50) NOT NULL,
            product_id VARCHAR(50) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            asset_format VARCHAR(50) NOT NULL DEFAULT '',
            asset_id VARCHAR(50) NOT NULL DEFAULT '',
            source_file TEXT NOT NULL,
            generated_file TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY fingerprint_id (fingerprint_id),
            KEY payload_hash (payload_hash),
            KEY order_id (order_id),
            KEY customer_email (customer_email),
            KEY asset_format (asset_format)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }

    public static function exists(string $fingerprint_id): bool
    {
        return self::get_by_fingerprint($fingerprint_id) !== null;
    }

    public static function insert(array $data): int
    {
        global $wpdb;

        $inserted = $wpdb->insert(
            self::get_table_name(),
            [
                'fingerprint_id' => sanitize_text_field($data['fingerprint_id'] ?? ''),
                'payload_hash'   => sanitize_text_field($data['payload_hash'] ?? ''),
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'order_id'       => sanitize_text_field($data['order_id'] ?? ''),
                'product_id'     => sanitize_text_field($data['product_id'] ?? ''),
                'product_name'   => sanitize_text_field($data['product_name'] ?? ''),
                'asset_format'   => sanitize_text_field($data['asset_format'] ?? ''),
                'asset_id'       => sanitize_text_field($data['asset_id'] ?? ''),
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
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            Logger::log('Fingerprint DB insert failed: ' . (string) $wpdb->last_error);
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public static function get_by_fingerprint(string $fingerprint_id): ?object
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_table_name() . " WHERE fingerprint_id = %s LIMIT 1",
                sanitize_text_field($fingerprint_id)
            )
        );

        return $result ?: null;
    }

    public static function get_by_payload_hash(string $payload_hash): ?object
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_table_name() . " WHERE payload_hash = %s LIMIT 1",
                sanitize_text_field($payload_hash)
            )
        );

        return $result ?: null;
    }

    public static function get_by_order(string $order_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_table_name() . " WHERE order_id = %s ORDER BY created_at DESC",
                sanitize_text_field($order_id)
            )
        );

        return is_array($results) ? $results : [];
    }

    public static function search(string $search = ''): array
    {
        global $wpdb;

        $fingerprints_table = self::get_table_name();
        $tokens_table = Download_Token_DB::get_table_name();

        $latest_token_join = "
            LEFT JOIN {$tokens_table} dt
                ON dt.id = (
                    SELECT dt2.id
                    FROM {$tokens_table} dt2
                    WHERE dt2.fingerprint_id = fp.fingerprint_id
                    ORDER BY dt2.created_at DESC, dt2.id DESC
                    LIMIT 1
                )
        ";

        $select = "
            SELECT
                fp.*,
                dt.token,
                dt.downloads_count,
                dt.max_downloads,
                dt.expires_at,
                dt.revoked_at
            FROM {$fingerprints_table} fp
            {$latest_token_join}
        ";

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';

            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    {$select}
                    WHERE
                        fp.fingerprint_id LIKE %s
                        OR fp.payload_hash LIKE %s
                        OR fp.customer_email LIKE %s
                        OR fp.order_id LIKE %s
                        OR fp.product_id LIKE %s
                        OR fp.product_name LIKE %s
                        OR fp.asset_format LIKE %s
                        OR fp.asset_id LIKE %s
                    ORDER BY fp.created_at DESC
                    ",
                    $like,
                    $like,
                    $like,
                    $like,
                    $like,
                    $like,
                    $like,
                    $like
                )
            );
        } else {
            $results = $wpdb->get_results(
                "
                {$select}
                ORDER BY fp.created_at DESC
                "
            );
        }

        return is_array($results)
            ? $results
            : [];
    }
}