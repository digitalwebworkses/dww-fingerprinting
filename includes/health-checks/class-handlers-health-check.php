<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Handlers_Health_Check extends Health_Check_Abstract
{
    private const EXPECTED_MIN_HANDLERS = 8;

    public function run(): array
    {
        $count = Fingerprint_Manager::count();
        $names = Fingerprint_Manager::get_handler_names();

        $passed = $count >= self::EXPECTED_MIN_HANDLERS;

        return $this->result(
            id: 'handlers',
            name: 'Handlers',
            category: 'Engine',
            passed: $passed,
            successMessage: 'Handlers registrados: ' . $count . ' (' . implode(', ', $names) . ')',
            failureMessage: 'Handlers insuficientes registrados: ' . $count,
            description: 'Comprueba que el motor tiene registrados los handlers documentales esperados.',
            fix: 'Revisa el registro automático de handlers.'
        );
    }
}
