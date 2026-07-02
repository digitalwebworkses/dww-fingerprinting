<?php

namespace DWW_Fingerprinting\Handlers;

use DWW_Fingerprinting\Open_Document_Processor;

if (!defined('ABSPATH')) {
    exit;
}

class Ods_Fingerprint_Handler extends Abstract_Fingerprint_Handler
{
    public function get_name(): string
    {
        return 'ODS';
    }

    public function get_supported_extensions(): array
    {
        return ['ods'];
    }

    public function get_supported_mime_types(): array
    {
        return [
            'application/vnd.oasis.opendocument.spreadsheet',
        ];
    }

    public function supports(string $file_path): bool
    {
        return strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'ods';
    }

    public function validate(string $file_path): bool
    {
        return $this->supports($file_path)
            && $this->ensure_file_exists($file_path);
    }

    public function process(
        string $source_path,
        string $destination_path,
        array $context = []
    ): bool {
        return Open_Document_Processor::personalize(
            $source_path,
            $destination_path,
            $context
        );
    }
}