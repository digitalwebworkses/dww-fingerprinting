<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Payload
{
    public const HASH_ALGORITHM = 'sha256';
    public const DEFAULT_CHUNK_LENGTH = 8;
    public const PAYLOAD_VERSION = '1.0';

    public static function build(array $context): array
    {
        return [
            'fingerprint_id' => self::string_value($context, 'fingerprint_id'),
            'customer_email' => self::string_value($context, 'customer_email'),
            'customer_name'  => self::string_value($context, 'customer_name'),
            'order_id'       => self::string_value($context, 'order_id'),
            'product_id'     => self::string_value($context, 'product_id'),
            'product_name'   => self::string_value($context, 'product_name'),
            'asset_format'   => self::string_value($context, 'asset_format'),
            'asset_id'       => self::string_value($context, 'asset_id'),
            'generated_at'   => self::string_value($context, 'generated_at') !== ''
                ? self::string_value($context, 'generated_at')
                : current_time('mysql'),

            'plugin_version' => self::string_value($context, 'plugin_version') !== ''
                ? self::string_value($context, 'plugin_version')
                : DWW_FP_VERSION,
        ];
    }

    public static function metadata(array $context): array
    {
        $payload = self::build($context);
        $hash = self::hash($context);
        $chunks = self::chunks($context);

        return [
            'fingerprint_id' => $payload['fingerprint_id'],
            'payload'        => self::compact($context),
            'hash'           => $hash,
            'hash_short'     => self::short_hash($context),
            'chunks'         => $chunks,
            'chunk_count'    => count($chunks),
            'algorithm'      => self::HASH_ALGORITHM . '-hmac',
            'version'        => self::PAYLOAD_VERSION,
        ];
    }

    public static function compact(array $context): string
    {
        $payload = self::build($context);

        $payload = array_filter(
            $payload,
            static fn($value): bool => trim((string) $value) !== ''
        );

        ksort($payload);

        return wp_json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: '';
    }

    public static function fingerprint(array $context): string
    {
        return self::string_value($context, 'fingerprint_id');
    }

    public static function hash(array $context): string
    {
        return hash_hmac(
            self::HASH_ALGORITHM,
            self::compact($context),
            self::secret_key()
        );
    }

    private static function secret_key(): string
    {
        $secret = defined('DWW_FP_SECRET_KEY')
            ? (string) DWW_FP_SECRET_KEY
            : '';

        if ($secret !== '') {
            return $secret;
        }

        return defined('AUTH_KEY')
            ? (string) AUTH_KEY
            : 'dww-fingerprinting-fallback-secret';
    }

    public static function short_hash(array $context, int $length = 16): string
    {
        return substr(self::hash($context), 0, max(1, $length));
    }

    public static function chunks(
        array $context,
        int $length = self::DEFAULT_CHUNK_LENGTH
    ): array {
        $length = max(1, $length);

        return str_split(self::hash($context), $length);
    }

    public static function chunk(
        array $context,
        int $index,
        int $length = self::DEFAULT_CHUNK_LENGTH
    ): string {
        $chunks = self::chunks($context, $length);

        return $chunks[$index] ?? '';
    }

    public static function property_map(array $context): array
    {
        $payload = self::build($context);
        $metadata = self::metadata($context);

        $properties = [
            'DWW Fingerprint'     => $payload['fingerprint_id'],
            'DWW Customer'        => $payload['customer_email'],
            'DWW Customer Name'   => $payload['customer_name'],
            'DWW Order'           => $payload['order_id'],
            'DWW Product ID'      => $payload['product_id'],
            'DWW Product'         => $payload['product_name'],
            'DWW Format'          => $payload['asset_format'],
            'DWW Asset ID'        => $payload['asset_id'],
            'DWW Generated At'    => $payload['generated_at'],
            'DWW Plugin Version'  => $payload['plugin_version'],
            'DWW Hash'            => $metadata['hash'],
            'DWW Hash Short'      => $metadata['hash_short'],
            'DWW Hash Algorithm'  => $metadata['algorithm'],
            'DWW Payload Version' => $metadata['version'],
        ];

        foreach ($metadata['chunks'] as $index => $chunk) {
            $properties[sprintf('DWW Chunk %02d', $index + 1)] = $chunk;
        }

        return array_filter(
            $properties,
            static fn($value): bool => trim((string) $value) !== ''
        );
    }

    private static function string_value(array $context, string $key): string
    {
        return trim((string) ($context[$key] ?? ''));
    }

    public static function context_from_properties(array $properties): array
    {
        return [
            'fingerprint_id'  => (string) ($properties['DWW Fingerprint'] ?? ''),
            'customer_email'  => (string) ($properties['DWW Customer'] ?? ''),
            'customer_name'   => (string) ($properties['DWW Customer Name'] ?? ''),
            'order_id'        => (string) ($properties['DWW Order'] ?? ''),
            'product_id'      => (string) ($properties['DWW Product ID'] ?? ''),
            'product_name'    => (string) ($properties['DWW Product'] ?? ''),
            'asset_format'    => (string) ($properties['DWW Format'] ?? ''),
            'asset_id'        => (string) ($properties['DWW Asset ID'] ?? ''),
            'generated_at'    => (string) ($properties['DWW Generated At'] ?? ''),
            'plugin_version'  => (string) ($properties['DWW Plugin Version'] ?? ''),
            'hash_algorithm'  => (string) ($properties['DWW Hash Algorithm'] ?? ''),
            'payload_version' => (string) ($properties['DWW Payload Version'] ?? ''),
        ];
    }

    public static function hash_from_properties(array $properties): string
    {
        $context = self::context_from_properties($properties);
        $compact = self::compact($context);

        $algorithm = strtolower(
            trim((string) ($properties['DWW Hash Algorithm'] ?? ''))
        );

        if ($algorithm === 'sha256') {
            return hash(
                self::HASH_ALGORITHM,
                $compact
            );
        }

        return hash_hmac(
            self::HASH_ALGORITHM,
            $compact,
            self::secret_key()
        );
    }
}
