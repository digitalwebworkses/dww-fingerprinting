<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Menu
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'register_menu']);
    }

    public static function register_menu(): void
    {
        add_menu_page(
            'DWW Fingerprinting',
            'DWW Fingerprinting',
            'manage_options',
            'dww-fingerprinting',
            [Dashboard_Page::class, 'render'],
            'dashicons-shield',
            56
        );

        add_submenu_page(
            'dww-fingerprinting',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'dww-fingerprinting',
            [Dashboard_Page::class, 'render']
        );

        add_submenu_page(
            'dww-fingerprinting',
            'Fingerprints',
            'Fingerprints',
            'manage_options',
            'dww-fingerprinting-fingerprints',
            [Fingerprints_Page::class, 'render']
        );
    }
}