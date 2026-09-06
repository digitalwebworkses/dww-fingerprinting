<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Installer
{
    public const INTEGRITY_KEY_OPTION = 'dww_fingerprinting_integrity_key';

    public static function install(): void
    {
        Fingerprint_DB::create_table();
        Download_Token_DB::create_table();
        Fingerprint_Log_DB::create_table();

        Migration_Manager::run();

        self::ensure_integrity_key();
        self::create_upload_directories();
    }

    public static function ensure_runtime_environment(): void
    {
        self::ensure_integrity_key();
        self::create_upload_directories();
    }

    public static function ensure_integrity_key(): string
    {
        $key = (string) get_option(self::INTEGRITY_KEY_OPTION, '');

        if ($key !== '') {
            return $key;
        }

        $key = bin2hex(random_bytes(32));
        update_option(self::INTEGRITY_KEY_OPTION, $key, false);

        return $key;
    }

    private static function create_upload_directories(): void
    {
        $base_dir = Storage_Security::get_storage_root();

        $directories = [
            $base_dir,
            $base_dir . '/generated',
            $base_dir . '/temp',
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
            }

            self::write_index_file($directory);
        }

        self::write_htaccess_file($base_dir);
        self::write_htaccess_file($base_dir . '/generated');
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
