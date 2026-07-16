<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Formats_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'formats';
    }

    public function title(): string
    {
        return 'Formatos soportados';
    }

    public function render(): void
    {
        $stats = $this->stats();

        $formats = Fingerprint_Manager::get_supported_extensions();

        $this->card_start();

        $this->card_title('🧩 Formatos soportados');

        echo '<p><strong>' .
            esc_html((string) ($stats['formats'] ?? count($formats))) .
            '</strong> formatos disponibles</p>';

        $this->button_group_start();

        foreach ($formats as $format) {
            $this->chip(
                strtoupper((string) $format)
            );
        }

        $this->button_group_end();

        $this->card_end();
    }
}
