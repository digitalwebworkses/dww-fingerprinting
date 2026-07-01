<?php

namespace DWW_Fingerprinting\Handlers;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Abstract_Fingerprint_Handler implements Fingerprint_Handler_Interface
{
    protected array $context = [];

    protected function set_context(array $context): void
    {
        $this->context = $context;
    }

    protected function get_context(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->context[$key] ?? $default;
    }

    protected function ensure_file_exists(
        string $file_path
    ): bool {
        return is_file($file_path);
    }

    protected function copy_file(
        string $source_file,
        string $destination_file
    ): bool {
        return copy($source_file, $destination_file);
    }
}