<?php

define('ABSPATH', __DIR__ . '/');
define('DWW_FP_VERSION', 'test-version');
define('AUTH_KEY', 'legacy-auth');
define('SECURE_AUTH_KEY', 'legacy-secure');
define('LOGGED_IN_KEY', 'legacy-login');
define('NONCE_KEY', 'legacy-nonce');

$dww_test_option = 'persistent-integrity-key';
$dww_test_size_limit = 104857600;

function get_option(string $name, $default = false)
{
    global $dww_test_option;

    return $name === 'dww_fingerprinting_integrity_key' ? $dww_test_option : $default;
}

function current_time(string $type): string
{
    return '2026-01-01 12:00:00';
}

function wp_json_encode($value, int $flags = 0): string|false
{
    return json_encode($value, $flags);
}

function apply_filters(string $hook, $value)
{
    global $dww_test_size_limit;

    return $hook === 'dww_fingerprinting_max_verification_file_size'
        ? $dww_test_size_limit
        : $value;
}

require_once dirname(__DIR__) . '/includes/class-installer.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-payload.php';
require_once dirname(__DIR__) . '/includes/class-fingerprint-manager.php';
require_once dirname(__DIR__) . '/includes/class-file-validator.php';

function require_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$context = [
    'fingerprint_id' => 'DWW-0123456789ABCDEF',
    'customer_email' => 'customer@example.com',
    'order_id' => '123',
    'product_id' => '456',
    'asset_format' => 'pdf',
    'asset_id' => '789',
    'generated_at' => '2026-01-01 12:00:00',
    'plugin_version' => DWW_FP_VERSION,
];

$properties = \DWW_Fingerprinting\Fingerprint_Payload::property_map($context);
require_true(
    \DWW_Fingerprinting\Fingerprint_Payload::verify_properties_hash(
        $properties,
        $properties['DWW Hash']
    ),
    'A payload signed with the persistent key must verify.'
);

$tampered_properties = $properties;
$tampered_properties['DWW Customer'] = 'attacker@example.com';
require_true(
    !\DWW_Fingerprinting\Fingerprint_Payload::verify_properties_hash(
        $tampered_properties,
        $properties['DWW Hash']
    ),
    'A modified payload must fail HMAC verification.'
);

$legacy_properties = $properties;
unset($legacy_properties['DWW Key ID']);
$legacy_context = \DWW_Fingerprinting\Fingerprint_Payload::context_from_properties($legacy_properties);
$compact = \DWW_Fingerprinting\Fingerprint_Payload::compact($legacy_context);
$legacy_key = AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY . NONCE_KEY;
$legacy_properties['DWW Hash'] = hash_hmac('sha256', $compact, $legacy_key);

require_true(
    \DWW_Fingerprinting\Fingerprint_Payload::verify_properties_hash(
        $legacy_properties,
        $legacy_properties['DWW Hash']
    ),
    'A legacy payload without a key identifier must remain verifiable.'
);

$dww_test_size_limit = 1;
$validation = \DWW_Fingerprinting\File_Validator::validate_for_verification(__DIR__ . '/sample.pdf');
require_true(
    !$validation['valid'] && str_contains(implode(' ', $validation['errors']), 'tamaño máximo'),
    'The verification-specific file size limit must be enforced before extraction.'
);

$dww_test_size_limit = 104857600;

foreach (['pdf', 'epub', 'docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp'] as $extension) {
    $validation = \DWW_Fingerprinting\File_Validator::validate_for_verification(
        __DIR__ . '/sample.' . $extension
    );

    require_true(
        $validation['valid'],
        strtoupper($extension) . ' sample rejected: ' . implode(' | ', $validation['errors'])
    );
}

echo "Phase 2 security regression tests passed.\n";
