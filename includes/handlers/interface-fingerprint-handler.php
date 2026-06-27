<?php

namespace DWW_Fingerprinting\Handlers;

if (!defined('ABSPATH')) {
    exit;
}

interface Fingerprint_Handler_Interface
{
    public function get_name(): string;

    public function get_supported_extensions(): array;

    public function get_supported_mime_types(): array;

    public function supports(
        string $file_path
    ): bool;

    public function validate(
        string $file_path
    ): bool;

    public function process(
        string $source_file,
        string $destination_file,
        array $context = []
    ): bool;
}