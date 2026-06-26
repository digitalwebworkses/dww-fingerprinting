<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_030
{
    public static function up(): void
    {
        global $wpdb;

        $table = Download_Token_DB::get_table_name();

        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = %s
                AND COLUMN_NAME = 'revoked_at'
                ",
                $table
            )
        );

        if ((int) $column_exists === 0) {

            $wpdb->query(
                "
                ALTER TABLE {$table}
                ADD COLUMN revoked_at DATETIME NULL
                AFTER expires_at
                "
            );

            Logger::log(
                'Migration 0.4.0: column revoked_at added to download tokens table.'
            );
        }
    }
}