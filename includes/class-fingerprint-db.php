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
            customer_email VARCHAR(255) NOT NULL,
            order_id VARCHAR(50) NOT NULL,
            product_id VARCHAR(50) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            source_file TEXT NOT NULL,
            generated_file TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY fingerprint_id (fingerprint_id),
            KEY order_id (order_id),
            KEY customer_email (customer_email)
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
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'order_id'       => sanitize_text_field($data['order_id'] ?? ''),
                'product_id'     => sanitize_text_field($data['product_id'] ?? ''),
                'product_name'   => sanitize_text_field($data['product_name'] ?? ''),
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

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_table_name() . " WHERE fingerprint_id = %s LIMIT 1",
                sanitize_text_field($fingerprint_id)
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
                        OR fp.customer_email LIKE %s
                        OR fp.order_id LIKE %s
                        OR fp.product_id LIKE %s
                        OR fp.product_name LIKE %s
                    ORDER BY fp.created_at DESC
                    ",
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
