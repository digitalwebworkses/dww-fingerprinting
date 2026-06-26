<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_UI
{
    public static function section(
        string $title,
        callable $content
    ): void {

        echo '<div class="dww-admin-section">';

        echo '<h2 class="dww-admin-section-title">';
        echo esc_html($title);
        echo '</h2>';

        call_user_func($content);

        echo '</div>';
    }

    public static function stat_card(
        string $title,
        string $value,
        string $color = 'blue'
    ): void {

        echo '<div class="dww-stat-card dww-stat-card-' . esc_attr($color) . '">';

        echo '<div class="dww-stat-card-value">';
        echo esc_html($value);
        echo '</div>';

        echo '<div class="dww-stat-card-title">';
        echo esc_html($title);
        echo '</div>';

        echo '</div>';
    }

    public static function badge(
        string $text,
        string $type = 'default'
    ): void {

        $class = 'dww-badge';

        switch ($type) {

            case 'success':
                $class .= ' dww-badge-success';
                break;

            case 'warning':
                $class .= ' dww-badge-warning';
                break;

            case 'danger':
                $class .= ' dww-badge-danger';
                break;

            case 'info':
                $class .= ' dww-badge-info';
                break;

            default:
                $class .= ' dww-badge-default';
                break;
        }

        echo '<span class="' . esc_attr($class) . '">';
        echo esc_html($text);
        echo '</span>';
    }

    public static function empty_state(
        string $title,
        string $message
    ): void {

        echo '<div class="dww-empty-state">';

        echo '<h3 class="dww-empty-state-title">';
        echo esc_html($title);
        echo '</h3>';

        echo '<p class="dww-empty-state-message">';
        echo esc_html($message);
        echo '</p>';

        echo '</div>';
    }
}