<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Dashboard_Widget_Abstract implements Dashboard_Widget_Interface
{
    protected array $snapshot = [];

    public function set_snapshot(array $snapshot): void
    {
        $this->snapshot = $snapshot;
    }

    protected function health(): array
    {
        return $this->snapshot['health'] ?? [];
    }

    protected function health_summary(): array
    {
        return $this->snapshot['health_summary'] ?? [];
    }

    protected function stats(): array
    {
        return $this->snapshot['stats'] ?? [];
    }

    protected function meta(): array
    {
        return $this->snapshot['meta'] ?? [];
    }

    protected function system(): array
    {
        return $this->snapshot['system'] ?? [];
    }

    protected function recent_fingerprints(): array
    {
        return $this->snapshot['recent_fingerprints'] ?? [];
    }

    protected function card_start(string $extra_class = ''): void
    {
        $classes = trim('dww-dashboard-card ' . $extra_class);

        echo '<div class="' . esc_attr($classes) . '">';
    }

    protected function card_end(): void
    {
        echo '</div>';
    }

    protected function card_title(string $title): void
    {
        echo '<h2 class="dww-dashboard-card-title">' .
            esc_html($title) .
            '</h2>';
    }

    protected function metric(
        string $value,
        string $label,
        string $icon = ''
    ): void {
        echo '<div class="dww-dashboard-metric">';

        if ($icon !== '') {
            echo '<div class="dww-dashboard-metric-icon">' .
                esc_html($icon) .
                '</div>';
        }

        echo '<div class="dww-dashboard-metric-value">' .
            esc_html($value) .
            '</div>';

        echo '<div class="dww-dashboard-metric-label">' .
            esc_html($label) .
            '</div>';

        echo '</div>';
    }

    protected function button(
        string $label,
        string $url,
        string $class = 'button-secondary'
    ): void {
        echo '<a class="button ' .
            esc_attr($class) .
            '" href="' .
            esc_url($url) .
            '">' .
            esc_html($label) .
            '</a>';
    }

    protected function button_group_start(): void
    {
        echo '<div class="dww-dashboard-button-group">';
    }

    protected function button_group_end(): void
    {
        echo '</div>';
    }

    protected function badge(string $label): void
    {
        echo '<span class="dww-dashboard-badge">' .
            esc_html($label) .
            '</span>';
    }

    protected function empty_state(string $message): void
    {
        echo '<p class="dww-dashboard-empty">' .
            esc_html($message) .
            '</p>';
    }

    protected function table_start(): void
    {
        echo '<table class="widefat striped dww-dashboard-table">';
    }

    protected function table_end(): void
    {
        echo '</table>';
    }

    protected function table_body_start(): void
    {
        echo '<tbody>';
    }

    protected function table_body_end(): void
    {
        echo '</tbody>';
    }

    protected function table_row(
        string $label,
        string $value
    ): void {

        echo '<tr>';

        echo '<td><strong>' .
            esc_html($label) .
            '</strong></td>';

        echo '<td>' .
            esc_html($value) .
            '</td>';

        echo '</tr>';
    }

    protected function list_start(): void
    {
        echo '<ul class="dww-dashboard-list">';
    }

    protected function list_end(): void
    {
        echo '</ul>';
    }

    protected function list_item(string $html): void
    {
        echo '<li>' . $html . '</li>';
    }

    protected function grid_start(): void
    {
        echo '<div class="dww-dashboard-grid">';
    }

    protected function grid_end(): void
    {
        echo '</div>';
    }

    protected function separator(): void
    {
        echo '<hr class="dww-dashboard-separator">';
    }

    protected function chip(string $label): void
    {
        echo '<span class="dww-dashboard-chip">' .
            esc_html($label) .
            '</span>';
    }

    protected function hero_score(
        int $score,
        string $color
    ): void {
        echo '<p class="dww-dashboard-hero-score" style="color:' .
            esc_attr($color) .
            ';">' .
            esc_html((string) $score) .
            '</p>';
    }
}
