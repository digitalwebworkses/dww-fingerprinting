<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Health_Check_Abstract implements Health_Check_Interface
{
    protected function result(
        string $id,
        string $name,
        string $category,
        bool $passed,
        string $successMessage,
        string $failureMessage,
        string $description,
        string $fix = '',
        string $successSeverity = 'success',
        string $failureSeverity = 'critical'
    ): array {

        return [
            'id' => $id,
            'name' => $name,
            'category' => $category,
            'status' => $passed ? 'ok' : 'error',
            'severity' => $passed
                ? $successSeverity
                : $failureSeverity,
            'message' => $passed
                ? $successMessage
                : $failureMessage,
            'description' => $description,
            'fix' => $passed ? '' : $fix,
        ];
    }
}
