<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_020
{
    public static function up(): void
    {
        global $wpdb;

        $table = Fingerprint_DB::get_table_name();

        if (!self::column_exists($table, 'product_name')) {

            $wpdb->query(
                "ALTER TABLE {$table}
                 ADD COLUMN product_name VARCHAR(255) NOT NULL
                 AFTER product_id"
            );

            Logger::log(
                'Migration 0.2.0 executed: product_name column added.'
            );
        }
    }

    private static function column_exists(
        string $table,
        string $column
    ): bool {

        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = %s
                  AND COLUMN_NAME = %s
                ",
                $table,
                $column
            )
        );

        return (int) $result > 0;
    }
}