<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Woocommerce_Health_Check extends Health_Check_Abstract
{
    public function run(): array
    {
        $passed = function_exists('wc_get_order');

        return $this->result(
            id: 'woocommerce',
            name: 'WooCommerce',
            category: 'Environment',
            passed: $passed,
            successMessage: 'WooCommerce disponible.',
            failureMessage: 'WooCommerce no está instalado o no está activo.',
            description: 'Comprueba que WooCommerce está disponible para gestionar pedidos y productos.',
            fix: 'Instala y activa WooCommerce.'
        );
    }
}
