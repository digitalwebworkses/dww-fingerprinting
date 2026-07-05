<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Menu
{
    private const MENU_SLUG = 'dww-fingerprinting';

    private const FINGERPRINTS_SLUG = 'dww-fingerprinting-fingerprints';

    private const VERIFY_SLUG = 'dww-fingerprinting-verify';

    private const DOCTOR_SLUG = 'dww-fingerprinting-doctor';

    private const REST_API_SLUG = 'dww-fingerprinting-rest-api';

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

        add_submenu_page(
            self::MENU_SLUG,
            'Verificar documento',
            'Verificar documento',
            'manage_options',
            self::VERIFY_SLUG,
            [Verify_Page::class, 'render']
        );

        add_submenu_page(
            self::MENU_SLUG,
            'Doctor',
            'Doctor',
            'manage_options',
            self::DOCTOR_SLUG,
            [Doctor_Page::class, 'render']
        );

        add_submenu_page(
            self::MENU_SLUG,
            'REST API',
            'REST API',
            'manage_options',
            self::REST_API_SLUG,
            [REST_API_Page::class, 'render']
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

    public static function get_verify_slug(): string
    {
        return self::VERIFY_SLUG;
    }

    public static function get_doctor_slug(): string
    {
        return self::DOCTOR_SLUG;
    }

    public static function get_rest_api_slug(): string
    {
        return self::REST_API_SLUG;
    }
}
