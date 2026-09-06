<?php

namespace {
    define('ABSPATH', __DIR__ . '/');
    define('DWW_FP_VERSION', 'test-version');
    define('DWW_FP_SECRET_KEY', 'test-secret');

    $dww_test_clock = 0;

    function current_time(string $type): string
    {
        global $dww_test_clock;
        $dww_test_clock++;

        return sprintf('2026-01-01 00:00:%02d', $dww_test_clock);
    }

    function wp_json_encode($value, int $flags = 0): string|false
    {
        return json_encode($value, $flags);
    }

    function sanitize_text_field($value): string
    {
        return trim((string) $value);
    }

    function assert_same($expected, $actual, string $message): void
    {
        if ($expected !== $actual) {
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

    class PDF_Processor
    {
        public static array $received_context = [];

        public static function personalize_pdf(
            string $source,
            string $destination,
            string $name,
            string $email,
            string $order,
            array $context = []
        ): bool {
            self::$received_context = $context;

            return true;
        }
    }
}

namespace DWW_Fingerprinting\Handlers {
    interface Fingerprint_Handler_Interface
    {
    }

    abstract class Abstract_Fingerprint_Handler implements Fingerprint_Handler_Interface
    {
        protected function ensure_file_exists(string $file): bool
        {
            return true;
        }
    }
}

namespace {
    require_once dirname(__DIR__) . '/includes/class-fingerprint-payload.php';
    require_once dirname(__DIR__) . '/includes/class-download-token-db.php';
    require_once dirname(__DIR__) . '/includes/handlers/class-pdf-fingerprint-handler.php';

    $context = [
        'fingerprint_id' => 'DWW-0123456789ABCDEF',
        'customer_email' => 'customer@example.com',
        'customer_name' => 'Test Customer',
        'order_id' => '123',
        'product_id' => '456',
        'product_name' => 'Test Product',
        'asset_format' => 'pdf',
        'asset_id' => '789',
    ];

    $properties = \DWW_Fingerprinting\Fingerprint_Payload::property_map($context);
    $recalculated = \DWW_Fingerprinting\Fingerprint_Payload::hash_from_properties($properties);

    assert_same(
        $properties['DWW Hash'],
        $recalculated,
        'The embedded payload and its HMAC must use the same timestamp.'
    );

    $metadata = \DWW_Fingerprinting\Fingerprint_Payload::metadata($context);
    assert_same($metadata['hash'], implode('', $metadata['chunks']), 'Chunks must rebuild the HMAC.');
    assert_same(substr($metadata['hash'], 0, 16), $metadata['hash_short'], 'Short hash must derive from the HMAC.');

    $handler = new \DWW_Fingerprinting\Handlers\Pdf_Fingerprint_Handler();
    $handler->process('source.pdf', 'destination.pdf', $context);
    assert_same(
        $context,
        \DWW_Fingerprinting\PDF_Processor::$received_context,
        'The PDF handler must forward the complete fingerprint context.'
    );

    $wpdb = new class {
        public string $prefix = 'wp_';
        public int $query_result = 0;

        public function prepare(string $query, ...$args): string
        {
            return $query;
        }

        public function query(string $query): int
        {
            return $this->query_result;
        }
    };

    assert_same(
        false,
        \DWW_Fingerprinting\Download_Token_DB::mark_downloaded('token'),
        'Zero affected rows must reject the download.'
    );

    $wpdb->query_result = 1;
    assert_same(
        true,
        \DWW_Fingerprinting\Download_Token_DB::mark_downloaded('token'),
        'Exactly one affected row must authorize the download.'
    );

    echo "Phase 1 regression tests passed.\n";
}
