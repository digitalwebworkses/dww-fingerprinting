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
            wp_die('Token de descarga inválido.');
        }

        Logger::log('Download requested. Token: ' . $token);

        $token_row = Download_Token_DB::get_by_token($token);

        if (!$token_row) {
            Logger::log('Download failed. Token not found.');
            wp_die('Token de descarga no encontrado.');
        }

        if (!Download_Token_DB::is_valid($token_row)) {
            Logger::log('Download failed. Token expired.');
            wp_die('El enlace de descarga ha caducado o ha superado el límite de descargas.');
        }

        $fingerprint = Fingerprint_DB::get_by_fingerprint(
            $token_row->fingerprint_id
        );

        if (!$fingerprint) {
            Logger::log('Download failed. Fingerprint not found.');
            wp_die('Fingerprint no encontrado.');
        }

        $file_path = $fingerprint->generated_file;

        if (empty($file_path) || !file_exists($file_path)) {
            Logger::log('Download failed. Generated file missing.');
            wp_die('El archivo no existe o ya no está disponible.');
        }

        Download_Token_DB::mark_downloaded($token);

        Logger::log(
            'Serving file for fingerprint: ' .
            $fingerprint->fingerprint_id
        );

        self::serve_file(
            $file_path,
            (string) $fingerprint->product_name
        );
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

        header('Content-Type: application/pdf');
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