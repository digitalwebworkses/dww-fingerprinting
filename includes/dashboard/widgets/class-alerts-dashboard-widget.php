<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Alerts_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'alerts';
    }

    public function title(): string
    {
        return 'Alertas';
    }

    public function render(): void
    {
        $summary = $this->health_summary();

        $warnings = (int) ($summary['warnings'] ?? 0);
        $errors = (int) ($summary['errors'] ?? 0);

        if ($warnings === 0 && $errors === 0) {
            return;
        }

        $this->card_start();

        $this->card_title('🚨 Alertas');

        if ($errors > 0) {
            echo '<p><strong>' .
                esc_html((string) $errors) .
                ' errores críticos detectados.</strong></p>';
        }

        if ($warnings > 0) {
            echo '<p><strong>' .
                esc_html((string) $warnings) .
                ' advertencias detectadas.</strong></p>';
        }

        $this->button_group_start();
        $this->button(
            'Abrir Doctor',
            admin_url('admin.php?page=dww-fingerprinting-doctor'),
            'button-primary'
        );
        $this->button_group_end();

        $this->card_end();
    }
}
