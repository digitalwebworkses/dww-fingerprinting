<?php

namespace DWW_Fingerprinting\Handlers;

use DWW_Fingerprinting\PDF_Processor;

if (!defined('ABSPATH')) {
    exit;
}

class Pdf_Fingerprint_Handler extends Abstract_Fingerprint_Handler
{
    public function get_name(): string
    {
        return 'PDF';
    }

    public function get_supported_extensions(): array
    {
        return ['pdf'];
    }

    public function get_supported_mime_types(): array
    {
        return ['application/pdf'];
    }

    public function supports(string $file_path): bool
    {
        return strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function validate(string $file_path): bool
    {
        return $this->ensure_file_exists($file_path);
    }

    public function process(
        string $source_file,
        string $destination_file,
        array $context = []
    ): bool {
        $this->set_context($context);

        return PDF_Processor::personalize_pdf(
            $source_file,
            $destination_file,
            (string) $this->get_context('customer_name', ''),
            (string) $this->get_context('customer_email', ''),
            (string) $this->get_context('order_id', '')
        );
    }
}