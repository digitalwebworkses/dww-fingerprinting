<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Product_Settings
{
    public static function init(): void
    {
        add_action(
            'woocommerce_product_options_general_product_data',
            [self::class, 'render_fields']
        );

        add_action(
            'woocommerce_admin_process_product_object',
            [self::class, 'save_fields']
        );
    }

    public static function render_fields(): void
    {
        echo '<div class="options_group">';

        woocommerce_wp_checkbox([
            'id'          => '_dww_fingerprinting_enabled',
            'label'       => 'Activar DWW Fingerprinting',
            'description' => 'Generar fingerprint documental para las compras de este producto.',
            'desc_tip'    => true,
        ]);

        woocommerce_wp_text_input([
            'id'          => '_dww_fingerprinting_source_pdf',
            'label'       => 'PDF origen',
            'description' => 'Ruta absoluta del PDF origen que se usará para generar la copia personalizada.',
            'desc_tip'    => true,
            'placeholder' => '/var/www/html/wp-content/uploads/documento.pdf',
        ]);

        echo '</div>';
    }

    public static function save_fields($product): void
    {
        $enabled = isset($_POST['_dww_fingerprinting_enabled']) ? 'yes' : 'no';

        $product->update_meta_data(
            '_dww_fingerprinting_enabled',
            $enabled
        );

        $source_pdf = isset($_POST['_dww_fingerprinting_source_pdf'])
            ? sanitize_text_field(wp_unslash($_POST['_dww_fingerprinting_source_pdf']))
            : '';

        $product->update_meta_data(
            '_dww_fingerprinting_source_pdf',
            $source_pdf
        );
    }

    public static function is_enabled($product): bool
    {
        if (!$product) {
            return false;
        }

        return $product->get_meta('_dww_fingerprinting_enabled') === 'yes';
    }

    public static function get_source_pdf($product): string
    {
        if (!$product) {
            return '';
        }

        return (string) $product->get_meta('_dww_fingerprinting_source_pdf');
    }
}