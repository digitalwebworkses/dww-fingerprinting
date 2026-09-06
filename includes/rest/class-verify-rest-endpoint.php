<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Verify_REST_Endpoint extends REST_Endpoint_Abstract
{
    public function register(): void
    {
        register_rest_route(
            $this->namespace(),
            '/verify',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'handle'],
                'permission_callback' => $this->permissions(),
            ]
        );
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $files = $request->get_file_params();

        if (
            empty($files['document']) ||
            empty($files['document']['tmp_name'])
        ) {
            return REST_Verification_Response::error(
                'No se ha recibido ningún documento.',
                400
            );
        }

        $file = $files['document'];

        if (!empty($file['error'])) {
            return REST_Verification_Response::error(
                'Error al recibir el documento.',
                400
            );
        }

        $tmp_file = (string) $file['tmp_name'];

        $original_name = sanitize_file_name(
            (string) ($file['name'] ?? '')
        );

        $extension = strtolower(
            pathinfo($original_name, PATHINFO_EXTENSION)
        );

        $verify_file = $tmp_file;

        if ($extension !== '') {
            $verify_file = $tmp_file . '.' . $extension;

            if (!copy($tmp_file, $verify_file)) {
                return REST_Verification_Response::error(
                    'No se pudo preparar el documento para verificación.',
                    500
                );
            }
        }

        try {
            $result = Fingerprint_Verifier::verify($verify_file);

            $response = REST_Verification_Response::build($result);

            $response = apply_filters(
                'dww_rest_verify_response',
                $response,
                $result,
                $request
            );

            return new \WP_REST_Response(
                $response,
                ($result['status'] ?? '') === 'extract_error' ? 400 : 200
            );
        } finally {
            if (
                $verify_file !== $tmp_file &&
                file_exists($verify_file)
            ) {
                unlink($verify_file);
            }
        }
    }
}
