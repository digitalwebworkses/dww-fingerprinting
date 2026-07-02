<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Verification_Report
{
    public static function render(array $result): void
    {
        echo self::render_notice($result);
        echo self::render_summary($result);
        echo self::render_warnings($result);
        echo self::render_errors($result);
        echo self::render_record($result);
    }

    private static function render_notice(array $result): string
    {
        if (!empty($result['valid'])) {
            return '<div class="notice notice-success"><p><strong>🟢 Documento verificado correctamente.</strong></p></div>';
        }

        $status = (string) ($result['status'] ?? '');

        if (in_array($status, ['payload_tampered', 'hash_mismatch', 'fingerprint_mismatch', 'integrity_failed'], true)) {
            return '<div class="notice notice-warning"><p><strong>🟠 Documento sospechoso. Revisión recomendada.</strong></p></div>';
        }

        return '<div class="notice notice-error"><p><strong>🔴 Documento no verificable.</strong></p></div>';
    }

    private static function render_summary(array $result): string
    {
        $evidence = $result['evidence'] ?? [];
        $record = $result['record'] ?? null;
        $trust = $result['trust'] ?? [];

        $score = (int) ($trust['score'] ?? 0);
        $label = (string) ($trust['label'] ?? 'Desconocido');

        return sprintf(
            '<div style="background:#fff;border:1px solid #ccd0d4;padding:16px;margin:16px 0;">
                <h2 style="margin-top:0;">Resumen de verificación</h2>
                <p><strong>Estado:</strong> %s</p>
                <p><strong>Trust Score:</strong> %d/100 (%s)</p>
                <p><strong>Pedido:</strong> %s</p>
                <p><strong>Cliente:</strong> %s</p>
                <p><strong>Producto:</strong> %s</p>
                <p><strong>Formato:</strong> %s</p>
                <p><strong>Fingerprint:</strong> %s</p>
                <p><strong>Acción recomendada:</strong> %s</p>
            </div>',
            esc_html(self::status_label((string) ($result['status'] ?? ''))),
            $score,
            esc_html($label),
            esc_html((string) ($record->order_id ?? ($evidence['properties']['DWW Order'] ?? '—'))),
            esc_html((string) ($record->customer_email ?? ($evidence['properties']['DWW Customer'] ?? '—'))),
            esc_html((string) ($record->product_name ?? ($evidence['properties']['DWW Product'] ?? '—'))),
            esc_html((string) ($evidence['format'] ?? '—')),
            esc_html((string) ($evidence['fingerprint_id'] ?? '—')),
            esc_html(self::recommendation((string) ($result['status'] ?? '')))
        );
    }

    private static function render_warnings(array $result): string
    {
        if (empty($result['warnings'])) {
            return '';
        }

        return self::render_list('Advertencias', $result['warnings']);
    }

    private static function render_errors(array $result): string
    {
        if (empty($result['errors'])) {
            return '';
        }

        return self::render_list('Errores', $result['errors']);
    }

    private static function render_record(array $result): string
    {
        if (empty($result['record'])) {
            return '';
        }

        $html = '<h2>Registro encontrado</h2>';
        $html .= '<table class="widefat striped"><tbody>';

        foreach ((array) $result['record'] as $key => $value) {
            $html .= self::row((string) $key, (string) $value);
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private static function render_list(string $title, array $items): string
    {
        $html = '<h2>' . esc_html($title) . '</h2><ul>';

        foreach ($items as $item) {
            $html .= '<li>' . esc_html((string) $item) . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    private static function row(string $label, string $value): string
    {
        return '<tr><th style="width:220px;">' .
            esc_html($label) .
            '</th><td>' .
            esc_html($value) .
            '</td></tr>';
    }

    private static function status_label(string $status): string
    {
        return match ($status) {
            'verified' => 'Documento verificado correctamente',
            'payload_tampered' => 'Payload manipulado',
            'hash_mismatch' => 'Hash no coincidente',
            'fingerprint_mismatch' => 'Fingerprint no coincidente',
            'missing_evidence' => 'Sin evidencias DWW',
            'not_found' => 'No encontrado en base de datos',
            'extract_error' => 'Error al extraer evidencias',
            'upload_error' => 'Error de subida',
            'integrity_failed' => 'Fallo de integridad',
            default => $status,
        };
    }

    private static function recommendation(string $status): string
    {
        return match ($status) {
            'verified' => 'Sin acción necesaria. Documento auténtico.',
            'payload_tampered' => 'Revisar manualmente. El documento parece manipulado.',
            'hash_mismatch' => 'Revisar integridad. El hash no coincide.',
            'fingerprint_mismatch' => 'Revisar origen del documento. Fingerprint alterado.',
            'missing_evidence' => 'No se puede atribuir el documento. Solicitar otro archivo.',
            'not_found' => 'No hay registro interno. Revisar si fue generado por otro entorno.',
            'extract_error' => 'Comprobar que el archivo no esté corrupto.',
            'upload_error' => 'Repetir la subida del documento.',
            default => 'Revisión manual recomendada.',
        };
    }
}