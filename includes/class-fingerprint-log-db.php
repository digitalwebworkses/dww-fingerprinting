<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Log_DB
{
    public static function get_table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'dww_fingerprint_logs';
    }

    public static function create_table(): void
    {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            fingerprint_id VARCHAR(64) NOT NULL,
            action VARCHAR(64) NOT NULL,
            message TEXT NOT NULL,
            context LONGTEXT NULL,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            ip_address VARCHAR(45) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY fingerprint_id (fingerprint_id),
            KEY action (action),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }

    public static function insert(
        string $fingerprint_id,
        string $action,
        string $message,
        array $context = []
    ): int {
        global $wpdb;

        $inserted = $wpdb->insert(
            self::get_table_name(),
            [
                'fingerprint_id' => sanitize_text_field($fingerprint_id),
                'action'         => sanitize_key($action),
                'message'        => sanitize_text_field($message),
                'context'        => !empty($context)
                    ? wp_json_encode($context)
                    : null,
                'user_id'        => get_current_user_id(),
                'ip_address'     => self::get_ip_address(),
                'created_at'     => current_time('mysql'),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public static function get_by_fingerprint(string $fingerprint_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
                 FROM " . self::get_table_name() . "
                 WHERE fingerprint_id = %s
                 ORDER BY created_at DESC, id DESC",
                sanitize_text_field($fingerprint_id)
            )
        );

        return is_array($results)
            ? $results
            : [];
    }

    private static function get_ip_address(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        return sanitize_text_field(
            wp_unslash($ip)
        );
    }
}