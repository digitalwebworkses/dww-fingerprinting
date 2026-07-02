<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Evidence
{
    public static function empty(string $format = ''): array
    {
        return [
            'format'         => $format,
            'fingerprint_id' => '',
            'payload_hash'   => '',
            'hash_short'     => '',
            'chunks'         => [],
            'properties'     => [],
            'warnings'       => [],
            'errors'         => [],
        ];
    }

    public static function from_properties(string $format, array $properties): array
    {
        $evidence = self::empty($format);

        $evidence['properties'] = $properties;

        $evidence['fingerprint_id'] = (string) ($properties['DWW Fingerprint'] ?? '');
        $evidence['payload_hash'] = (string) ($properties['DWW Hash'] ?? '');
        $evidence['hash_short'] = (string) ($properties['DWW Hash Short'] ?? '');

        foreach ($properties as $key => $value) {
            if (preg_match('/^DWW Chunk \d+$/', (string) $key)) {
                $evidence['chunks'][$key] = (string) $value;
            }
        }

        ksort($evidence['chunks']);

        return $evidence;
    }
}