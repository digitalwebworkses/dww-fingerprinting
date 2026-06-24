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
        woocommerce_wp_checkbox([
            'id'          => '_dww_fingerprinting_enabled',
            'label'       => 'Activar DWW Fingerprinting',
            'description' => 'Generar fingerprint documental para las compras de este producto.',
            'desc_tip'    => true,
        ]);
    }

    public static function save_fields($product): void
    {
        $enabled = isset($_POST['_dww_fingerprinting_enabled']) ? 'yes' : 'no';

        $product->update_meta_data(
            '_dww_fingerprinting_enabled',
            $enabled
        );
    }

    public static function is_enabled($product): bool
    {
        if (!$product) {
            return false;
        }

        return $product->get_meta('_dww_fingerprinting_enabled') === 'yes';
    }
}