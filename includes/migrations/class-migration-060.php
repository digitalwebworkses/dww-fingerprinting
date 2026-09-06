<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_060
{
    public static function up(): void
    {
        global $wpdb;

        $table = Fingerprint_DB::get_table_name();
        $duplicates = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM (
                SELECT fingerprint_id
                FROM {$table}
                GROUP BY fingerprint_id
                HAVING COUNT(*) > 1
            ) duplicate_fingerprints"
        );

        if ($duplicates > 0) {
            throw new \RuntimeException(
                'Existen fingerprints duplicados. Deben revisarse antes de crear el índice único.'
            );
        }

        $index = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT INDEX_NAME
                 FROM INFORMATION_SCHEMA.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = %s
                   AND INDEX_NAME = 'fingerprint_id_unique'
                   AND NON_UNIQUE = 0",
                $table
            )
        );

        if ($index) {
            return;
        }

        $created = $wpdb->query(
            "ALTER TABLE {$table} ADD UNIQUE KEY fingerprint_id_unique (fingerprint_id)"
        );

        if ($created === false) {
            throw new \RuntimeException('No se pudo crear el índice único de fingerprints.');
        }
    }
}
