<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Storage_Health_Check extends Health_Check_Abstract implements Repairable_Health_Check_Interface
{
    public function run(): array
    {
        $status = Storage_Security::verify_upload_storage();

        $errors = [];

        foreach ($status as $name => $directory) {
            if (empty($directory['exists'])) {
                $errors[] = $name . ': no existe';
            }

            if (empty($directory['writable'])) {
                $errors[] = $name . ': no escribible';
            }

            if (empty($directory['protected'])) {
                $errors[] = $name . ': no protegido';
            }

            if (!empty($directory['public'])) {
                $errors[] = $name . ': dentro del directorio público';
            }
        }

        $passed = empty($errors);

        return $this->result(
            id: 'storage',
            name: 'Storage',
            category: 'Storage',
            passed: $passed,
            successMessage: 'Storage creado, escribible y protegido.',
            failureMessage: implode(' | ', $errors),
            description: 'Comprueba que las carpetas internas del plugin existen, son escribibles y están protegidas.',
            fix: 'Revisa permisos o ejecuta la protección automática del storage.'
        );
    }

    public function repair(): array
    {
        $base = Storage_Security::get_storage_root();

        $paths = [
            $base,
            trailingslashit($base) . 'generated',
            trailingslashit($base) . 'temp',
        ];

        $success = true;
        $messages = [];

        foreach ($paths as $path) {
            $protected = Storage_Security::protect_directory($path);

            if ($protected) {
                $messages[] = 'Protegido: ' . $path;
            } else {
                $success = false;
                $messages[] = 'No se pudo proteger: ' . $path;
            }
        }

        return [
            'success' => $success,
            'message' => implode(' | ', $messages),
        ];
    }
}
