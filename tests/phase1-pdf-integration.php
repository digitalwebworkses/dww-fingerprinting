<?php

define('ABSPATH', __DIR__ . '/');
define('DWW_FP_VERSION', 'test-version');
define('DWW_FP_SECRET_KEY', 'test-secret');

function current_time(string $type): string
{
    return '2026-01-01 12:00:00';
}

function wp_json_encode($value, int $flags = 0): string|false
{
    return json_encode($value, $flags);
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-generator.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-payload.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-evidence.php';
require_once dirname(__DIR__) . '/includes/class-pdf-processor.php';

function fail_test(string $message): void
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

$context = [
    'fingerprint_id' => 'DWW-0123456789ABCDEF',
    'customer_email' => 'customer@example.com',
    'customer_name' => 'Test Customer',
    'order_id' => '123',
    'product_id' => '456',
    'product_name' => 'Test Product',
    'asset_format' => 'pdf',
    'asset_id' => '789',
    'generated_at' => '2026-01-01 12:00:00',
    'plugin_version' => DWW_FP_VERSION,
];

$destination = sys_get_temp_dir() . '/dww-phase1-' . bin2hex(random_bytes(8)) . '.pdf';

try {
    $generated = \DWW_Fingerprinting\PDF_Processor::personalize_pdf(
        __DIR__ . '/sample.pdf',
        $destination,
        $context['customer_name'],
        $context['customer_email'],
        $context['order_id'],
        $context
    );

    if (!$generated) {
        fail_test('The PDF could not be generated.');
    }

    $evidence = \DWW_Fingerprinting\PDF_Processor::extract($destination);
    $properties = $evidence['properties'] ?? [];

    foreach ([
        'DWW Fingerprint' => $context['fingerprint_id'],
        'DWW Product ID' => $context['product_id'],
        'DWW Asset ID' => $context['asset_id'],
        'DWW Generated At' => $context['generated_at'],
    ] as $property => $expected) {
        if (($properties[$property] ?? null) !== $expected) {
            fail_test("Unexpected value for {$property}.");
        }
    }

    $recalculated = \DWW_Fingerprinting\Fingerprint_Payload::hash_from_properties($properties);

    if (!hash_equals((string) ($properties['DWW Hash'] ?? ''), $recalculated)) {
        fail_test('The extracted PDF payload does not match its HMAC.');
    }

    echo "Phase 1 PDF integration test passed.\n";
} finally {
    if (file_exists($destination)) {
        unlink($destination);
    }
}
