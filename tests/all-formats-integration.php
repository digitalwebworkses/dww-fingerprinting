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

function sanitize_text_field($value): string
{
    return trim(strip_tags((string) $value));
}

function sanitize_key($value): string
{
    return strtolower(preg_replace('/[^a-z0-9_-]/i', '', (string) $value));
}

function apply_filters(string $hook, $value)
{
    return $value;
}

function do_action(string $hook, ...$args): void
{
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/includes/class-logger.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-generator.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-payload.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-evidence.php';
require_once dirname(__DIR__) . '/includes/class-file-validator.php';
require_once dirname(__DIR__) . '/includes/class-pdf-processor.php';
require_once dirname(__DIR__) . '/includes/class-epub-processor.php';
require_once dirname(__DIR__) . '/includes/class-office-open-xml-processor.php';
require_once dirname(__DIR__) . '/includes/class-open-document-processor.php';
require_once dirname(__DIR__) . '/includes/handlers/interface-fingerprint-handler.php';
require_once dirname(__DIR__) . '/includes/handlers/abstract-fingerprint-handler.php';

foreach (['pdf', 'epub', 'docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp'] as $extension) {
    require_once dirname(__DIR__) . '/includes/handlers/class-' . $extension . '-fingerprint-handler.php';
}

require_once dirname(__DIR__) . '/includes/class-fingerprint-manager.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-extractor.php';

function fail_format(string $format, string $message): void
{
    fwrite(STDERR, "FAIL [{$format}]: {$message}\n");
    exit(1);
}

\DWW_Fingerprinting\Fingerprint_Manager::register_default_handlers();

$context = [
    'fingerprint_id' => 'DWW-0123456789ABCDEF',
    'customer_email' => 'customer@example.com',
    'customer_name' => 'Test Customer',
    'order_id' => '123',
    'product_id' => '456',
    'product_name' => 'Test Product',
    'asset_id' => '789',
    'generated_at' => '2026-01-01 12:00:00',
    'plugin_version' => DWW_FP_VERSION,
];

foreach (['pdf', 'epub', 'docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp'] as $format) {
    $source = __DIR__ . '/sample.' . $format;
    $destination = sys_get_temp_dir() . '/dww-format-' . bin2hex(random_bytes(8)) . '.' . $format;
    $format_context = array_merge($context, ['asset_format' => $format]);

    try {
        if (!\DWW_Fingerprinting\Fingerprint_Manager::process($source, $destination, $format_context)) {
            fail_format($format, 'generation failed');
        }

        $evidence = \DWW_Fingerprinting\Fingerprint_Extractor::extract($destination);

        if (!empty($evidence['errors'])) {
            fail_format($format, implode(' | ', $evidence['errors']));
        }

        $properties = $evidence['properties'] ?? [];

        if (($properties['DWW Fingerprint'] ?? '') !== $context['fingerprint_id']) {
            fail_format($format, 'fingerprint mismatch');
        }

        if (($properties['DWW Asset ID'] ?? '') !== $context['asset_id']) {
            fail_format($format, 'asset mismatch');
        }

        if (!\DWW_Fingerprinting\Fingerprint_Payload::verify_properties_hash(
            $properties,
            (string) ($properties['DWW Hash'] ?? '')
        )) {
            fail_format($format, 'payload HMAC mismatch');
        }
    } finally {
        if (is_file($destination)) {
            unlink($destination);
        }
    }
}

echo "All eight format integration tests passed.\n";
