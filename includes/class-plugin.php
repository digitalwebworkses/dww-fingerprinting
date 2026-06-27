<?php

namespace DWW_Fingerprinting;

use DWW_Fingerprinting\Handlers\Pdf_Fingerprint_Handler;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    public static function init(): void
    {
        Migration_Manager::run();

        self::register_fingerprint_handlers();
    }

    private static function register_fingerprint_handlers(): void
    {
        Fingerprint_Manager::clear_handlers();

        Fingerprint_Manager::register_handler(
            new Pdf_Fingerprint_Handler()
        );

        do_action('dww_fingerprinting_register_handlers');
    }
}