<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Database_Health_Check extends Health_Check_Abstract
{
    public function run(): array
    {
        global $wpdb;

        $tables = [
            Fingerprint_DB::get_table_name(),
            Download_Token_DB::get_table_name(),
            Fingerprint_Log_DB::get_table_name(),
        ];

        $missing = [];

        foreach ($tables as $table) {
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    'SHOW TABLES LIKE %s',
                    $table
                )
            );

            if ($exists !== $table) {
                $missing[] = $table;
            }
        }

        $passed = empty($missing);

        return $this->result(
            id: 'database',
            name: 'Base de datos',
            category: 'Storage',
            passed: $passed,
            successMessage: 'Todas las tablas requeridas existen.',
            failureMessage: 'Faltan tablas: ' . implode(', ', $missing),
            description: 'Comprueba que las tablas internas del plugin existen en la base de datos.',
            fix: 'Ejecuta las migraciones del plugin o reactiva el plugin.'
        );
    }
}
