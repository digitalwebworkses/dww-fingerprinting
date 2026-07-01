<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Menu
{
    private const MENU_SLUG = 'dww-fingerprinting';

    private const FINGERPRINTS_SLUG = 'dww-fingerprinting-fingerprints';

    public static function init(): void
    {
        add_action(
            'admin_menu',
            [self::class, 'register_menu']
        );
    }

    public static function register_menu(): void
    {
        add_menu_page(
            'DWW Fingerprinting',
            'DWW Fingerprinting',
            'manage_options',
            self::MENU_SLUG,
            [Dashboard_Page::class, 'render'],
            'dashicons-shield',
            56
        );

        add_submenu_page(
            self::MENU_SLUG,
            'Resumen',
            'Resumen',
            'manage_options',
            self::MENU_SLUG,
            [Dashboard_Page::class, 'render']
        );

        add_submenu_page(
            self::MENU_SLUG,
            'Fingerprints',
            'Fingerprints',
            'manage_options',
            self::FINGERPRINTS_SLUG,
            [Fingerprints_Page::class, 'render']
        );
    }

    public static function get_dashboard_slug(): string
    {
        return self::MENU_SLUG;
    }

    public static function get_fingerprints_slug(): string
    {
        return self::FINGERPRINTS_SLUG;
    }
}