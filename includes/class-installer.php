<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Installer
{
    public static function install(): void
    {
        Fingerprint_DB::create_table();
        Download_Token_DB::create_table();
        Fingerprint_Log_DB::create_table();

        Migration_Manager::run();

        self::create_upload_directories();
    }

    private static function create_upload_directories(): void
    {
        $upload_dir = wp_upload_dir();

        $base_dir = trailingslashit($upload_dir['basedir']) . 'dww-fingerprinting';

        $directories = [
            $base_dir,
            $base_dir . '/generated',
            $base_dir . '/temp',
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
            }
        }
    }
}
