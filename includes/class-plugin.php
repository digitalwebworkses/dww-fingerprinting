<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    public static function init(): void
    {
        add_action('admin_notices', [self::class, 'admin_notice']);
    }

    public static function admin_notice(): void
    {
        echo '<div class="notice notice-success"><p>DWW Fingerprinting activo.</p></div>';
    }
}