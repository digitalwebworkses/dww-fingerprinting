<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Quick_Actions_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'quick_actions';
    }

    public function title(): string
    {
        return 'Acciones rápidas';
    }

    public function render(): void
    {
        $this->card_start();

        $this->card_title('⚡ Acciones rápidas');

        $this->button_group_start();

        $this->button(
            'Verificar documento',
            admin_url('admin.php?page=dww-fingerprinting-verify'),
            'button-primary'
        );

        $this->button(
            'Abrir Doctor',
            admin_url('admin.php?page=dww-fingerprinting-doctor')
        );

        $this->button(
            'REST API',
            admin_url('admin.php?page=dww-fingerprinting-rest-api')
        );

        $this->button(
            'Fingerprints',
            admin_url('admin.php?page=dww-fingerprinting-fingerprints')
        );

        $this->button_group_end();

        $this->card_end();
    }
}