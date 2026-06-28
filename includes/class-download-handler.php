<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Download_Handler
{
    public static function init(): void
    {
        add_action('init', [self::class, 'maybe_handle_download']);
    }

    public static function maybe_handle_download(): void
    {
        if (empty($_GET['dww-download'])) {
            return;
        }

        $token = sanitize_text_field(
            wp_unslash($_GET['dww-download'])
        );

        if (empty($token)) {
            self::log_download_event(
                '',
                'download_invalid_token',
                'Intento de descarga con token vacío.',
                [
                    'reason' => 'empty_token',
                ]
            );

            wp_die('Token de descarga inválido.');
        }

        Logger::log('Download requested. Token: ' . $token);

        $token_row = Download_Token_DB::get_by_token($token);

        if (!$token_row) {
            Logger::log('Download failed. Token not found.');

            self::log_download_event(
                '',
                'download_invalid_token',
                'Intento de descarga con token inexistente.',
                [
                    'reason' => 'token_not_found',
                    'token'  => self::mask_token($token),
                ]
            );

            wp_die('Token de descarga no encontrado.');
        }

        if (!Download_Token_DB::is_valid($token_row)) {
            $reason = self::get_invalid_token_reason($token_row);

            Logger::log(
                'Download failed. Invalid token reason: ' . $reason
            );

            self::log_download_event(
                (string) $token_row->fingerprint_id,
                'download_' . $reason,
                self::get_invalid_token_message($reason),
                [
                    'reason'          => $reason,
                    'token'           => self::mask_token($token),
                    'downloads_count' => (int) $token_row->downloads_count,
                    'max_downloads'   => (int) $token_row->max_downloads,
                    'expires_at'      => (string) $token_row->expires_at,
                    'revoked_at'      => (string) ($token_row->revoked_at ?? ''),
                ]
            );

            wp_die('El enlace de descarga ha caducado, ha sido revocado o ha superado el límite de descargas.');
        }

        $fingerprint = Fingerprint_DB::get_by_fingerprint(
            $token_row->fingerprint_id
        );

        if (!$fingerprint) {
            Logger::log('Download failed. Fingerprint not found.');

            self::log_download_event(
                (string) $token_row->fingerprint_id,
                'download_fingerprint_not_found',
                'Intento de descarga con fingerprint inexistente.',
                [
                    'token' => self::mask_token($token),
                ]
            );

            wp_die('Fingerprint no encontrado.');
        }

        $file_path = $fingerprint->generated_file;

        if (empty($file_path) || !file_exists($file_path)) {
            Logger::log('Download failed. Generated file missing.');

            self::log_download_event(
                (string) $fingerprint->fingerprint_id,
                'download_file_missing',
                'Intento de descarga con archivo generado no disponible.',
                [
                    'token' => self::mask_token($token),
                    'file'  => basename((string) $file_path),
                ]
            );

            wp_die('El archivo no existe o ya no está disponible.');
        }

        $downloads_before = (int) $token_row->downloads_count;
        $marked_downloaded = Download_Token_DB::mark_downloaded($token);
        $downloads_after = $downloads_before + 1;

        if (!$marked_downloaded) {
            Logger::log('Download failed. Could not mark token as downloaded.');

            self::log_download_event(
                (string) $fingerprint->fingerprint_id,
                'download_mark_failed',
                'No se pudo registrar la descarga en el token.',
                [
                    'token'            => self::mask_token($token),
                    'downloads_before' => $downloads_before,
                ]
            );

            wp_die('No se pudo registrar la descarga.');
        }

        self::log_download_event(
            (string) $fingerprint->fingerprint_id,
            'download_success',
            'Documento descargado correctamente.',
            [
                'token'               => self::mask_token($token),
                'downloads_before'    => $downloads_before,
                'downloads_after'     => $downloads_after,
                'remaining_downloads' => max(
                    0,
                    (int) $token_row->max_downloads - $downloads_after
                ),
                'file'                => basename((string) $file_path),
            ]
        );

        Logger::log(
            'Serving file for fingerprint: ' .
                $fingerprint->fingerprint_id
        );

        self::serve_file(
            $file_path,
            (string) $fingerprint->product_name
        );
    }

    private static function get_invalid_token_reason(object $token_row): string
    {
        if (!empty($token_row->revoked_at)) {
            return 'revoked';
        }

        $expires_at = strtotime($token_row->expires_at);

        if (!$expires_at || $expires_at < time()) {
            return 'expired';
        }

        if ((int) $token_row->downloads_count >= (int) $token_row->max_downloads) {
            return 'limit_reached';
        }

        return 'invalid_token';
    }

    private static function get_invalid_token_message(string $reason): string
    {
        switch ($reason) {
            case 'revoked':
                return 'Intento de descarga con token revocado.';

            case 'expired':
                return 'Intento de descarga con token caducado.';

            case 'limit_reached':
                return 'Intento de descarga con límite de descargas alcanzado.';

            default:
                return 'Intento de descarga con token no válido.';
        }
    }

    private static function log_download_event(
        string $fingerprint_id,
        string $action,
        string $message,
        array $context = []
    ): void {
        if (!class_exists(Fingerprint_Log_DB::class)) {
            return;
        }

        Fingerprint_Log_DB::insert(
            $fingerprint_id,
            $action,
            $message,
            $context
        );
    }

    private static function mask_token(string $token): string
    {
        if (strlen($token) <= 16) {
            return $token;
        }

        return substr($token, 0, 8) . '…' . substr($token, -8);
    }

    private static function get_mime_type(

        string $extension

    ): string {

        switch ($extension) {

            case 'pdf':

                return 'application/pdf';

            case 'epub':

                return 'application/epub+zip';

            default:

                return 'application/octet-stream';
        }
    }

    private static function serve_file(
        string $file_path,
        string $product_name
    ): void {

        if (ob_get_length()) {
            ob_end_clean();
        }

        $extension = strtolower(
            pathinfo($file_path, PATHINFO_EXTENSION)
        );

        if (empty($extension)) {
            $extension = 'pdf';
        }

        $download_name = trim($product_name);

        if ($download_name === '') {
            $download_name = basename($file_path, '.' . $extension);
        }

        $download_name = sanitize_file_name(
            $download_name . '.' . $extension
        );

        header(
            'Content-Type: ' .
                self::get_mime_type($extension)
        );
        header('X-Content-Type-Options: nosniff');
        header(
            'Content-Disposition: attachment; filename="' .
                $download_name .
                '"'
        );
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($file_path);
        exit;
    }
}
