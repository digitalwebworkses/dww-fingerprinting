<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class System_Info_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'system_info';
    }

    public function title(): string
    {
        return 'Información del sistema';
    }

    public function render(): void
    {
        $system = $this->system();

        $this->card_start();

        $this->title('⚙️ Información del sistema');

        $this->table_start();
        $this->table_body_start();

        $this->table_row(
            'Plugin',
            (string) ($system['plugin_version'] ?? '')
        );

        $this->table_row(
            'Base de datos',
            (string) ($system['db_version'] ?? '')
        );

       $this->table_row(
            'PHP',
            (string) ($system['php_version'] ?? '')
        );

        $this->table_row(
            'WordPress',
            (string) ($system['wp_version'] ?? '')
        );

        $this->table_row(
            'WooCommerce',
            (string) ($system['wc_version'] ?? 'No instalado')
        );

        $this->table_body_end();
        $this->table_end();

        $this->card_end();
    }

}
