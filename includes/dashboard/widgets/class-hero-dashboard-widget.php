<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Hero_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'hero';
    }

    public function title(): string
    {
        return 'Estado general';
    }

    public function render(): void
    {
        $score = (int) ($this->health()['score'] ?? 0);
        $label = $this->score_label($score);
        $color = $this->score_color($score);
        $version = (string) ($this->meta()['version'] ?? '');

        $this->card_start('dww-dashboard-hero');

        $this->card_title('DWW Fingerprinting');

        $this->hero_score(
            $score,
            $color
        );

        echo '<p><strong>' . esc_html($label) . '</strong></p>';

        echo '<div class="dww-dashboard-progress">';
        echo '<div class="dww-dashboard-progress-bar" style="width:' .
            esc_attr((string) $score) .
            '%;background:' .
            esc_attr($color) .
            ';"></div>';
        echo '</div>';

        if ($version !== '') {
            echo '<p class="dww-dashboard-muted">Versión ' .
                esc_html($version) .
                '</p>';
        }

        $this->card_end();
    }

    private function score_label(int $score): string
    {
        if ($score >= 90) {
            return 'Sistema saludable';
        }

        if ($score >= 60) {
            return 'Sistema con advertencias';
        }

        return 'Sistema con errores críticos';
    }

    private function score_color(int $score): string
    {
        if ($score >= 90) {
            return '#2e7d32';
        }

        if ($score >= 60) {
            return '#f9a825';
        }

        return '#c62828';
    }
}
