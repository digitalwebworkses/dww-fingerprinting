<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class PHP_Health_Check extends Health_Check_Abstract
{
    public function run(): array
    {
        $passed = version_compare(PHP_VERSION, '8.1', '>=');

        return $this->result(
            id: 'php',
            name: 'PHP',
            category: 'Environment',
            passed: $passed,
            successMessage: 'PHP ' . PHP_VERSION,
            failureMessage: 'PHP ' . PHP_VERSION . ' no cumple el mínimo requerido.',
            description: 'Comprueba que la versión de PHP cumple el mínimo requerido por el plugin.',
            fix: 'Actualiza PHP a la versión 8.1 o superior.'
        );
    }
}
