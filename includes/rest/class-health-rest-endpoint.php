<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Health_REST_Endpoint extends REST_Endpoint_Abstract
{
    public function register(): void
    {
        register_rest_route(
            $this->namespace(),
            '/health',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'handle'],
                'permission_callback' => $this->permissions(),
            ]
        );
    }

    public function handle(): \WP_REST_Response
    {
        $response = Health_Check::run();

        $response = apply_filters(
            'dww_rest_health_response',
            $response
        );

        return new \WP_REST_Response(
            $response,
            200
        );
    }
}
