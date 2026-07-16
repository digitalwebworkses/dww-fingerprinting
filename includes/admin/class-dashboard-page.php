<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes.'));
        }

        Dashboard_Registry::register();

        ?>

        <div class="wrap">

            <h1>DWW Fingerprinting</h1>

            <p>
                Sistema de trazabilidad documental para WooCommerce.
            </p>

            <?php Dashboard_Manager::render(); ?>

        </div>

        <?php
    }
}