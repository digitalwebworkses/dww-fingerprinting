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

        self::verify_storage_security();

        self::register_fingerprint_handlers();

        self::register_rest_api();
    }

    private static function register_fingerprint_handlers(): void
    {
        Fingerprint_Manager::register_default_handlers();
    }

    private static function verify_storage_security(): void
    {
        if (!class_exists(Storage_Security::class)) {
            return;
        }

        $upload_dir = wp_upload_dir();

        $base = trailingslashit($upload_dir['basedir']) . 'dww-fingerprinting';

        Storage_Security::protect_directory($base);
        Storage_Security::protect_directory(trailingslashit($base) . 'storage');
        Storage_Security::protect_directory(trailingslashit($base) . 'storage/generated');

        $status = Storage_Security::verify_upload_storage();

        foreach ($status as $name => $directory_status) {
            if (
                empty($directory_status['exists']) ||
                empty($directory_status['writable']) ||
                empty($directory_status['protected'])
            ) {
                Logger::log(
                    sprintf(
                        'Storage security warning [%s]: exists=%s writable=%s protected=%s',
                        $name,
                        !empty($directory_status['exists']) ? 'yes' : 'no',
                        !empty($directory_status['writable']) ? 'yes' : 'no',
                        !empty($directory_status['protected']) ? 'yes' : 'no'
                    )
                );
            }
        }
    }

    private static function register_rest_api(): void
    {
        add_action(
            'rest_api_init',
            static function (): void {
                REST_API_Registry::register();
                REST_API_Manager::boot();
            }
        );
    }
}
