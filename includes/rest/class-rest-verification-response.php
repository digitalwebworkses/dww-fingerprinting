<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class REST_Verification_Response
{
    public static function build(array $result): array
    {
        $trust = $result['trust'] ?? [];

        return [
            'success' => true,

            'verified' => (bool) ($result['valid'] ?? false),

            'status' => (string) ($result['status'] ?? 'unknown'),

            'trust' => [
                'score' => (int) ($trust['score'] ?? 0),
                'label' => (string) ($trust['label'] ?? ''),
                'color' => (string) ($trust['color'] ?? ''),
            ],

            'document' => [
                'format' => (string) ($result['evidence']['format'] ?? ''),
                'fingerprint' => (string) ($result['evidence']['fingerprint_id'] ?? ''),
                'payload_hash' => (string) ($result['evidence']['payload_hash'] ?? ''),
            ],

            'record' => $result['record'] ?? null,

            'warnings' => array_values(
                $result['warnings'] ?? []
            ),

            'errors' => array_values(
                $result['errors'] ?? []
            ),
        ];
    }

    public static function error(
        string $message,
        int $status = 400
    ): \WP_REST_Response {

        return new \WP_REST_Response(
            [
                'success' => false,
                'message' => $message,
            ],
            $status
        );
    }
}