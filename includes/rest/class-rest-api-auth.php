<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class REST_API_Auth
{
    private const OPTION_NAME = 'dww_fingerprinting_api_key';

    public static function permission_callback(): callable
    {
        return static function (): bool {
            if (current_user_can('manage_options')) {
                return true;
            }

            return self::validate_request_key();
        };
    }

    public static function get_key(): string
    {
        return (string) get_option(self::OPTION_NAME, '');
    }

    public static function set_key(string $key): void
    {
        update_option(
            self::OPTION_NAME,
            trim($key),
            false
        );
    }

    public static function generate_key(): string
    {
        return 'dww_' . bin2hex(random_bytes(32));
    }

    private static function validate_request_key(): bool
    {
        $stored = self::get_key();

        if ($stored === '') {
            return false;
        }

        $provided = self::request_key();

        if ($provided === '') {
            return false;
        }

        return hash_equals($stored, $provided);
    }

    private static function request_key(): string
    {
        $header = self::header('HTTP_X_DWW_API_KEY');

        if ($header !== '') {
            return $header;
        }

        $authorization = self::header('HTTP_AUTHORIZATION');

        if (
            stripos($authorization, 'Bearer ') === 0
        ) {
            return trim(substr($authorization, 7));
        }

        return '';
    }

    private static function header(string $key): string
    {
        return isset($_SERVER[$key])
            ? trim((string) $_SERVER[$key])
            : '';
    }

    public static function has_key(): bool
    {
        return self::get_key() !== '';
    }

    public static function masked_api_key(): string
    {
        $key = self::get_api_key();

        if ($key === '') {
            return 'No configurada';
        }

        return substr($key, 0, 12) . '••••••••';
    }
}
