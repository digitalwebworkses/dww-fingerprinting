<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Stats_REST_Endpoint extends REST_Endpoint_Abstract
{
    public function register(): void
    {
        register_rest_route(
            $this->namespace(),
            '/stats',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'handle'],
                'permission_callback' => $this->permissions(),
            ]
        );
    }

    public function handle(): \WP_REST_Response
    {
        $response = [
            'fingerprints' => Fingerprint_DB::count(),
            'tokens'       => Download_Token_DB::count(),
            'handlers'     => Fingerprint_Manager::count(),
            'formats'      => Fingerprint_Manager::get_supported_extensions(),
            'health'       => Health_Check::run(),
        ];

        $response = apply_filters(
            'dww_rest_stats_response',
            $response
        );

        $response = [
            'success' => true,
            'record'  => $record,
        ];

        $response = apply_filters(
            'dww_rest_fingerprint_response',
            $response,
            $record,
            $request
        );

        return new \WP_REST_Response(
            $response,
            200
        );
    }
}
