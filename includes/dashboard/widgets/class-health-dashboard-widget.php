<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Health_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'health';
    }

    public function title(): string
    {
        return 'Estado del sistema';
    }

    public function render(): void
    {
        $summary = $this->snapshot['health_summary'] ?? [];

        $score = (int) ($summary['score'] ?? 0);
        $checks = (int) ($summary['checks'] ?? 0);
        $ok = (int) ($summary['ok'] ?? 0);
        $warnings = (int) ($summary['warnings'] ?? 0);
        $errors = (int) ($summary['errors'] ?? 0);

        $this->card_start();

        $this->card_title('🩺 Estado del sistema');

        $this->metric(
            (string) $score . '/100',
            $this->status_label($score),
            '🩺'
        );

        $this->list_start();

        $this->list_item(
            '<strong>' .
                esc_html((string) $checks) .
                '</strong> comprobaciones'
        );

        $this->list_item(
            '<strong>' .
                esc_html((string) $ok) .
                '</strong> correctas'
        );

        $this->list_item(
            '<strong>' .
                esc_html((string) $warnings) .
                '</strong> advertencias'
        );

        $this->list_item(
            '<strong>' .
                esc_html((string) $errors) .
                '</strong> errores'
        );

        $this->list_end();

        $this->button_group_start();
        $this->button(
            'Abrir Doctor',
            admin_url('admin.php?page=dww-fingerprinting-doctor')
        );
        $this->button_group_end();

        $this->card_end();
    }

    private function status_label(int $score): string
    {
        if ($score >= 90) {
            return 'Sistema saludable';
        }

        if ($score >= 60) {
            return 'Sistema con advertencias';
        }

        return 'Sistema con errores críticos';
    }
}
