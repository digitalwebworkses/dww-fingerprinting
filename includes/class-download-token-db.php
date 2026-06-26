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
            revoked_at DATETIME NULL,
            downloads_count INT UNSIGNED NOT NULL DEFAULT 0,
            max_downloads INT UNSIGNED NOT NULL DEFAULT 3,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY fingerprint_id (fingerprint_id),
            KEY expires_at (expires_at),
            KEY revoked_at (revoked_at)
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

        $inserted = $wpdb->insert(
            self::get_table_name(),
            [
                'fingerprint_id'  => sanitize_text_field($fingerprint_id),
                'token'           => $token,
                'expires_at'      => gmdate('Y-m-d H:i:s', time() + ($expires_in_hours * HOUR_IN_SECONDS)),
                'revoked_at'      => null,
                'downloads_count' => 0,
                'max_downloads'   => absint($max_downloads),
                'created_at'      => current_time('mysql'),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
            ]
        );

        if ($inserted === false) {
            return '';
        }

        return $token;
    }

    public static function regenerate_token(
        string $fingerprint_id,
        int $expires_in_hours = 72,
        int $max_downloads = 3
    ): string {
        $fingerprint_id = sanitize_text_field($fingerprint_id);

        if ($fingerprint_id === '') {
            return '';
        }

        self::revoke_by_fingerprint($fingerprint_id);

        return self::create_token(
            $fingerprint_id,
            $expires_in_hours,
            $max_downloads
        );
    }

    public static function revoke_by_fingerprint(string $fingerprint_id): bool
    {
        global $wpdb;

        $fingerprint_id = sanitize_text_field($fingerprint_id);

        if ($fingerprint_id === '') {
            return false;
        }

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . self::get_table_name() . "
                 SET revoked_at = %s
                 WHERE fingerprint_id = %s
                 AND revoked_at IS NULL",
                current_time('mysql', true),
                $fingerprint_id
            )
        );

        return $updated !== false;
    }

    public static function expire_by_fingerprint(string $fingerprint_id): bool
    {
        return self::revoke_by_fingerprint($fingerprint_id);
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

    public static function get_by_fingerprint(string $fingerprint_id): ?object
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_table_name() . "
                 WHERE fingerprint_id = %s
                 ORDER BY created_at DESC, id DESC
                 LIMIT 1",
                sanitize_text_field($fingerprint_id)
            )
        );

        return $result ?: null;
    }

    public static function is_valid(object $token_row): bool
    {
        if (
            property_exists($token_row, 'revoked_at') &&
            !empty($token_row->revoked_at)
        ) {
            return false;
        }

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
                 WHERE token = %s
                 AND revoked_at IS NULL
                 AND downloads_count < max_downloads
                 AND expires_at >= UTC_TIMESTAMP()",
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
