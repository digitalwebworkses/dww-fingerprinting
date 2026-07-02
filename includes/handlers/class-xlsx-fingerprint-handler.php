<?php

namespace DWW_Fingerprinting\Handlers;

use DWW_Fingerprinting\Office_Open_XML_Processor;

if (!defined('ABSPATH')) {
    exit;
}

class Xlsx_Fingerprint_Handler extends Abstract_Fingerprint_Handler
{
    public function get_name(): string
    {
        return 'XLSX';
    }

    public function get_supported_extensions(): array
    {
        return ['xlsx'];
    }

    public function get_supported_mime_types(): array
    {
        return [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    public function supports(string $file_path): bool
    {
        return strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'xlsx';
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
        try {

            return Office_Open_XML_Processor::personalize(
                $source_path,
                $destination_path,
                $context
            );
        } catch (\Throwable $exception) {

            \DWW_Fingerprinting\Logger::log(
                sprintf(
                    '%s handler exception: %s',
                    $this->get_name(),
                    $exception->getMessage()
                )
            );

            return false;
        }
    }
}
