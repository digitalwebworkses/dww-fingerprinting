<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Integrity_Key_Health_Check extends Health_Check_Abstract
{
    public function run(): array
    {
        $key = (string) get_option(Installer::INTEGRITY_KEY_OPTION, '');
        $passed = strlen($key) >= 64;

        return $this->result(
            id: 'integrity_key',
            name: 'Clave de integridad',
            category: 'Security',
            passed: $passed,
            successMessage: 'La clave persistente de integridad está configurada.',
            failureMessage: 'La clave persistente de integridad no existe o no es válida.',
            description: 'Comprueba que los documentos nuevos utilizan una clave propia independiente de los salts de WordPress.',
            fix: 'Desactiva y vuelve a activar el plugin o revisa la escritura de opciones de WordPress.'
        );
    }
}
