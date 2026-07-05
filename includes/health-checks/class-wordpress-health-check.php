<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class WordPress_Health_Check extends Health_Check_Abstract
{
    public function run(): array
    {
        $passed = defined('ABSPATH');

        return $this->result(
            id: 'wordpress',
            name: 'WordPress',
            category: 'Environment',
            passed: $passed,
            successMessage: 'WordPress cargado correctamente.',
            failureMessage: 'WordPress no está cargado.',
            description: 'Comprueba que el plugin se está ejecutando dentro de un entorno WordPress válido.',
            fix: 'Revisa la instalación de WordPress.'
        );
    }
}
