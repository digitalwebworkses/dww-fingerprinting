<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Installer
{
    public static function install(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dww_fingerprints';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            fingerprint_id VARCHAR(64) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            order_id VARCHAR(50) NOT NULL,
            product_id VARCHAR(50) NOT NULL,
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
}