<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Service
{
    public static function snapshot(): array
    {
        $health = Health_Check::run();

        return [
            'health' => $health,

            'health_summary' => [
                'score'    => (int) ($health['score'] ?? 0),
                'checks'   => count($health['checks'] ?? []),
                'ok'       => self::count_status($health, 'ok'),
                'warnings' => self::count_severity($health, 'warning'),
                'errors'   => self::count_errors($health),
            ],

            'api' => [
                'enabled' => REST_API_Auth::has_key(),
                'key'      => REST_API_Auth::get_api_key(),
            ],


            'stats' => [

                'fingerprints' => Fingerprint_DB::count(),
                'downloads'    => Download_Token_DB::count_downloads(),
                'tokens'       => Download_Token_DB::count(),
                'handlers'     => Fingerprint_Manager::count(),
                'formats'      => count(Fingerprint_Manager::get_supported_extensions()),
                'api_enabled'  => REST_API_Auth::has_key(),
                'active_tokens'  => Download_Token_DB::count_active(),
                'expired_tokens' => Download_Token_DB::count_expired(),
            ],

            'recent_fingerprints' => self::recent_fingerprints(),

            'meta' => [
                'generated_at' => current_time('mysql'),
                'version'      => DWW_FP_VERSION,
            ],

            'system' => [
                'plugin_version' => DWW_FP_VERSION,
                'db_version'     => Migration_Manager::get_installed_version(),
                'php_version'    => PHP_VERSION,
                'wp_version'     => get_bloginfo('version'),
                'wc_version'     => defined('WC_VERSION') ? WC_VERSION : '',
            ],
        ];
    }

    private static function count_status(
        array $health,
        string $status
    ): int {

        $count = 0;

        foreach ($health['checks'] ?? [] as $check) {

            if (($check['status'] ?? '') === $status) {
                $count++;
            }
        }

        return $count;
    }

    private static function count_severity(
        array $health,
        string $severity
    ): int {

        $count = 0;

        foreach ($health['checks'] ?? [] as $check) {

            if (($check['status'] ?? '') === 'ok') {
                continue;
            }

            if (($check['severity'] ?? '') === $severity) {
                $count++;
            }
        }

        return $count;
    }

    private static function count_errors(
        array $health
    ): int {

        $count = 0;

        foreach ($health['checks'] ?? [] as $check) {

            if (($check['status'] ?? '') === 'ok') {
                continue;
            }

            if (($check['severity'] ?? '') !== 'warning') {
                $count++;
            }
        }

        return $count;
    }

    private static function recent_fingerprints(int $limit = 5): array
    {
        global $wpdb;

        $table = Fingerprint_DB::get_table_name();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
             FROM {$table}
             ORDER BY created_at DESC
             LIMIT %d",
                $limit
            )
        ) ?: [];
    }
}
