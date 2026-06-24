<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Download_Token_DB
{
    public static function get_table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'dww_download_tokens';
    }

    public static function create_table(): void
    {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            fingerprint_id VARCHAR(64) NOT NULL,
            token VARCHAR(128) NOT NULL,
            expires_at DATETIME NOT NULL,
            downloads_count INT UNSIGNED NOT NULL DEFAULT 0,
            max_downloads INT UNSIGNED NOT NULL DEFAULT 3,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY fingerprint_id (fingerprint_id),
            KEY expires_at (expires_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }

    public static function create_token(
        string $fingerprint_id,
        int $expires_in_hours = 72,
        int $max_downloads = 3
    ): string {
        global $wpdb;

        $token = self::generate_token();

        $wpdb->insert(
            self::get_table_name(),
            [
                'fingerprint_id'   => sanitize_text_field($fingerprint_id),
                'token'            => $token,
                'expires_at'       => gmdate('Y-m-d H:i:s', time() + ($expires_in_hours * HOUR_IN_SECONDS)),
                'downloads_count'  => 0,
                'max_downloads'    => absint($max_downloads),
                'created_at'       => current_time('mysql'),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
            ]
        );

        return $token;
    }

    public static function get_by_token(string $token): ?object
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_table_name() . " WHERE token = %s LIMIT 1",
                sanitize_text_field($token)
            )
        );

        return $result ?: null;
    }

    public static function is_valid(object $token_row): bool
    {
        if ((int) $token_row->downloads_count >= (int) $token_row->max_downloads) {
            return false;
        }

        $expires_at = strtotime($token_row->expires_at);

        if (!$expires_at || $expires_at < time()) {
            return false;
        }

        return true;
    }

    public static function mark_downloaded(string $token): bool
    {
        global $wpdb;

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . self::get_table_name() . "
                 SET downloads_count = downloads_count + 1
                 WHERE token = %s",
                sanitize_text_field($token)
            )
        );

        return $updated !== false;
    }

    private static function generate_token(): string
    {
        return bin2hex(random_bytes(32));
    }
}