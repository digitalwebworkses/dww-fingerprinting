<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Manager
{
    /**
     * @var Dashboard_Widget_Interface[]
     */
    private static array $widgets = [];

    private static ?array $snapshot = null;

    public static function register(
        Dashboard_Widget_Interface $widget
    ): void {
        self::$widgets[] = $widget;
    }

    /**
     * @return Dashboard_Widget_Interface[]
     */
    public static function widgets(): array
    {
        return self::$widgets;
    }

    public static function clear(): void
    {
        self::$widgets = [];
        self::$snapshot = null;
    }

    public static function snapshot(): array
    {
        if (self::$snapshot === null) {
            self::$snapshot = Dashboard_Service::snapshot();
        }

        return self::$snapshot;
    }

    public static function render(): void
    {
        $snapshot = self::snapshot();

        echo '<div class="dww-dashboard-widgets">';

        foreach (self::$widgets as $widget) {
            $widget->set_snapshot($snapshot);
            $widget->render();
        }

        echo '</div>';
    }
}