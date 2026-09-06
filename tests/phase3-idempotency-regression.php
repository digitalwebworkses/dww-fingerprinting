<?php

namespace {
    define('ABSPATH', __DIR__ . '/');
    define('DWW_FP_VERSION', 'test-version');

    function sanitize_file_name(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $value);
    }

    function trailingslashit(string $value): string
    {
        return rtrim($value, '/\\') . '/';
    }

    function assert_phase3(bool $condition, string $message): void
    {
        if (!$condition) {
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }
    }
}

namespace DWW_Fingerprinting {
    class Logger
    {
        public static function log(string $message): void
        {
        }
    }

    class Fingerprint_DB
    {
        public static ?object $record = null;
        public static array $saved = [];
        public static bool $updated = false;
        public static int $insert_result = 1;

        public static function get_table_name(): string
        {
            return 'wp_dww_fingerprints';
        }

        public static function get_by_fingerprint(string $id): ?object
        {
            return self::$record;
        }

        public static function insert(array $data): int
        {
            self::$saved = $data;
            return self::$insert_result;
        }

        public static function update_generated_asset(string $id, array $data): bool
        {
            self::$updated = true;
            self::$saved = $data;
            return true;
        }
    }

    class Download_Token_DB
    {
        public static array $rows = [];
        public static int $created = 0;

        public static function get_all_by_fingerprint(string $id): array
        {
            return self::$rows;
        }

        public static function create_token(string $id, int $hours, int $downloads): string
        {
            self::$created++;
            return 'token';
        }
    }

    class Fingerprint_Manager
    {
        public static int $processed = 0;

        public static function process(string $source, string $destination, array $context): bool
        {
            self::$processed++;
            return file_put_contents($destination, 'generated') !== false;
        }
    }

    class Storage_Security
    {
        public static function get_generated_directory(): string
        {
            return sys_get_temp_dir() . '/dww-phase3-tests';
        }

        public static function protect_directory(string $directory): bool
        {
            return is_dir($directory) || mkdir($directory, 0700, true);
        }
    }
}

namespace {
    require_once dirname(__DIR__) . '/includes/class-woocommerce-integration.php';
    require_once dirname(__DIR__) . '/includes/migrations/class-migration-060.php';

    $method = new ReflectionMethod(
        \DWW_Fingerprinting\WooCommerce_Integration::class,
        'process_locked_asset'
    );

    \DWW_Fingerprinting\Fingerprint_DB::$record = null;
    \DWW_Fingerprinting\Download_Token_DB::$rows = [];
    $method->invoke(null, 1, '2', 'Product', 'test@example.com', __FILE__, 'pdf', '3', 'DWW-ID', 'hash', []);

    assert_phase3(\DWW_Fingerprinting\Fingerprint_Manager::$processed === 1, 'A new asset must be generated once.');
    assert_phase3(\DWW_Fingerprinting\Download_Token_DB::$created === 1, 'A missing token must be recovered.');
    assert_phase3(isset(\DWW_Fingerprinting\Fingerprint_DB::$saved['generated_file']), 'The generated path must be registered.');

    $generated = \DWW_Fingerprinting\Fingerprint_DB::$saved['generated_file'];
    if (is_file($generated)) {
        unlink($generated);
    }

    \DWW_Fingerprinting\Fingerprint_DB::$insert_result = 0;
    \DWW_Fingerprinting\Download_Token_DB::$created = 0;
    $method->invoke(null, 1, '2', 'Product', 'test@example.com', __FILE__, 'pdf', '3', 'DWW-FAIL', 'hash', []);
    $failed_file = \DWW_Fingerprinting\Fingerprint_DB::$saved['generated_file'];
    assert_phase3(!is_file($failed_file), 'A failed database insert must not leave an orphan file.');
    assert_phase3(\DWW_Fingerprinting\Download_Token_DB::$created === 0, 'A failed registration must not create a token.');

    \DWW_Fingerprinting\Fingerprint_DB::$insert_result = 1;

    \DWW_Fingerprinting\Download_Token_DB::$rows = [(object) ['revoked_at' => '2026-01-01']];
    $ensure = new ReflectionMethod(
        \DWW_Fingerprinting\WooCommerce_Integration::class,
        'ensure_download_token'
    );
    $ensure->invoke(null, 'DWW-ID');
    assert_phase3(\DWW_Fingerprinting\Download_Token_DB::$created === 0, 'A revoked token must not be recreated automatically.');

    $wpdb = new class {
        public int $duplicates = 2;
        public int $queries = 0;

        public function get_var(string $query)
        {
            return str_contains($query, 'HAVING COUNT') ? $this->duplicates : null;
        }

        public function prepare(string $query, ...$args): string
        {
            return $query;
        }

        public function query(string $query): int
        {
            $this->queries++;
            return 1;
        }
    };

    try {
        \DWW_Fingerprinting\Migration_060::up();
        assert_phase3(false, 'The migration must stop when historical duplicates exist.');
    } catch (RuntimeException $exception) {
        assert_phase3($wpdb->queries === 0, 'Historical duplicates must be preserved without destructive queries.');
    }

    $wpdb->duplicates = 0;
    \DWW_Fingerprinting\Migration_060::up();
    assert_phase3($wpdb->queries === 1, 'The unique index must be created when data is clean.');

    echo "Phase 3 idempotency regression tests passed.\n";
}
