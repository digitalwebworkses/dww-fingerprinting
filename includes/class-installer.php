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

    public static function ensure_runtime_environment(): void
    {
        self::create_upload_directories();
    }

    private static function create_upload_directories(): void
    {
        $upload_dir = wp_upload_dir();

        $base_dir = trailingslashit($upload_dir['basedir']) . 'dww-fingerprinting';

        $directories = [
            $base_dir,
            $base_dir . '/storage',
            $base_dir . '/storage/generated',
            $base_dir . '/storage/temp',
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
            }

            self::write_index_file($directory);
        }

        self::write_htaccess_file($base_dir);
        self::write_htaccess_file($base_dir . '/storage');
    }

    private static function write_index_file(string $directory): void
    {
        $index_file = trailingslashit($directory) . 'index.php';

        if (file_exists($index_file)) {
            return;
        }

        file_put_contents(
            $index_file,
            "<?php\n// Silence is golden.\n"
        );
    }

    private static function write_htaccess_file(string $base_dir): void
    {
        $htaccess_file = trailingslashit($base_dir) . '.htaccess';

        file_put_contents(
            $htaccess_file,
            "Options -Indexes\n" .
                "<IfModule mod_authz_core.c>\n" .
                "    Require all denied\n" .
                "</IfModule>\n" .
                "<IfModule !mod_authz_core.c>\n" .
                "    Deny from all\n" .
                "</IfModule>\n"
        );
    }
}
