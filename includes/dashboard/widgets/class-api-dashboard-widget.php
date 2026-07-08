<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class API_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'api';
    }

    public function title(): string
    {
        return 'REST API';
    }

    public function render(): void
    {
        $api = $this->snapshot['api'] ?? [];

        $enabled = (bool) ($api['enabled'] ?? false);
        $key = (string) ($api['key'] ?? '');

        $this->card_start();

        $this->card_title('🌐 REST API');

        echo '<p><strong>Estado:</strong> ';

        Admin_UI::badge(
            $enabled ? 'Activa' : 'Deshabilitada',
            $enabled ? 'success' : 'danger'
        );

        echo '</p>';

        echo '<p><strong>API Key:</strong></p>';

        echo '<code>' . esc_html($key) . '</code>';

        $this->button_group_start();

        $this->button(
            'Administrar API',
            admin_url('admin.php?page=dww-fingerprinting-rest-api')
        );

        $this->button_group_end();

        $this->card_end();
    }
}
