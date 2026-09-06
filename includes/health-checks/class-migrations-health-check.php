<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Migrations_Health_Check extends Health_Check_Abstract implements Repairable_Health_Check_Interface
{
    private const EXPECTED_VERSION = '0.6.0';

    public function run(): array
    {
        $version = Migration_Manager::get_installed_version();

        $passed = version_compare(
            (string) $version,
            self::EXPECTED_VERSION,
            '>='
        );

        return $this->result(
            id: 'migrations',
            name: 'Migraciones',
            category: 'Database',
            passed: $passed,
            successMessage: 'Base de datos actualizada (' . $version . ').',
            failureMessage: 'Versión actual: ' . $version . '. Esperada: ' . self::EXPECTED_VERSION . '.',
            description: 'Comprueba que todas las migraciones del plugin han sido aplicadas.',
            fix: 'Ejecuta nuevamente las migraciones del plugin.'
        );
    }

    public function repair(): array
    {
        try {
            Migration_Manager::run();

            $version = Migration_Manager::get_installed_version();

            $passed = version_compare(
                (string) $version,
                self::EXPECTED_VERSION,
                '>='
            );

            return [
                'success' => $passed,
                'message' => $passed
                    ? 'Migraciones ejecutadas correctamente. Versión actual: ' . $version . '.'
                    : 'Migraciones ejecutadas, pero la versión sigue siendo ' . $version . '.',
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'message' => 'Error ejecutando migraciones: ' . $exception->getMessage(),
            ];
        }
    }
}
