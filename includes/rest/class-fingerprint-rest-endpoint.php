<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_REST_Endpoint extends REST_Endpoint_Abstract
{
    public function register(): void
    {
        register_rest_route(
            $this->namespace(),
            '/fingerprint/(?P<fingerprint_id>[A-Z0-9\-]+)',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'handle'],
                'permission_callback' => $this->permissions(),
            ]
        );
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $fingerprint_id = sanitize_text_field(
            (string) $request->get_param('fingerprint_id')
        );

        if ($fingerprint_id === '') {
            return new \WP_REST_Response(
                [
                    'success' => false,
                    'message' => 'Fingerprint no especificado.',
                ],
                400
            );
        }

        $record = Fingerprint_DB::get_by_fingerprint($fingerprint_id);

        if (!$record) {
            return new \WP_REST_Response(
                [
                    'success' => false,
                    'message' => 'Fingerprint no encontrado.',
                ],
                404
            );
        }

        return new \WP_REST_Response(
            [
                'success' => true,
                'record' => $record,
            ],
            200
        );
    }
}