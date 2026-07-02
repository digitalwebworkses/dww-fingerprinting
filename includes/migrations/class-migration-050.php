<?php

namespace DWW_Fingerprinting;

use DWW_Fingerprinting\Fingerprint_DB;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_050
{
    public static function up(): void
    {
        global $wpdb;

        $table_name = Fingerprint_DB::get_table_name();

        $column = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM {$table_name} LIKE %s",
                'payload_hash'
            )
        );

        if (!empty($column)) {
            return;
        }

        $wpdb->query(
            "ALTER TABLE {$table_name}
             ADD payload_hash VARCHAR(64) NOT NULL DEFAULT ''
             AFTER fingerprint_id"
        );

        $wpdb->query(
            "ALTER TABLE {$table_name}
             ADD KEY payload_hash (payload_hash)"
        );
    }
}