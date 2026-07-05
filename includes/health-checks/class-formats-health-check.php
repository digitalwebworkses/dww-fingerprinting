<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Formats_Health_Check extends Health_Check_Abstract
{
    private const EXPECTED_FORMATS = [
        'pdf',
        'epub',
        'docx',
        'xlsx',
        'pptx',
        'odt',
        'ods',
        'odp',
    ];

    public function run(): array
    {
        $supported = Fingerprint_Manager::get_supported_extensions();

        $supported = array_map(
            'strtolower',
            $supported
        );

        $missing = array_diff(
            self::EXPECTED_FORMATS,
            $supported
        );

        $passed = empty($missing);

        return $this->result(
            id: 'formats',
            name: 'Formatos soportados',
            category: 'Engine',
            passed: $passed,
            successMessage: 'Formatos disponibles: ' . implode(', ', $supported),
            failureMessage: 'Faltan formatos: ' . implode(', ', $missing),
            description: 'Comprueba que el motor declara todos los formatos soportados.',
            fix: 'Revisa los handlers registrados y sus extensiones.'
        );
    }
}
