<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    public static function init(): void
    {
        Migration_Manager::run();

        Installer::ensure_runtime_environment();

        self::register_fingerprint_handlers();
    }

    private static function register_fingerprint_handlers(): void
    {
        Fingerprint_Manager::register_default_handlers();
    }
}