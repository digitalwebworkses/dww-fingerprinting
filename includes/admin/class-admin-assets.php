<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Assets
{
    public static function init(): void
    {
        add_action(
            'admin_enqueue_scripts',
            [self::class, 'enqueue']
        );
    }

    public static function enqueue(string $hook): void
    {
        if (!self::is_plugin_page()) {
            return;
        }

        wp_enqueue_style(
            'dww-fingerprinting-admin',
            DWW_FP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            DWW_FP_VERSION
        );

        wp_enqueue_script(
            'dww-fingerprinting-admin',
            DWW_FP_PLUGIN_URL . 'assets/js/admin.js',
            [],
            DWW_FP_VERSION,
            true
        );
    }

    private static function is_plugin_page(): bool
    {
        if (!isset($_GET['page'])) {
            return false;
        }

        $page = sanitize_text_field(
            wp_unslash($_GET['page'])
        );

        return str_starts_with($page, 'dww-fingerprinting');
    }
}