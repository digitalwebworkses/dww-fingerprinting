<?php

namespace DWW_Fingerprinting\Handlers;

use DWW_Fingerprinting\Epub_Processor;

if (!defined('ABSPATH')) {
    exit;
}

class Epub_Fingerprint_Handler extends Abstract_Fingerprint_Handler
{
    public function get_name(): string
    {
        return 'EPUB';
    }

    public function get_supported_extensions(): array
    {
        return ['epub'];
    }

    public function get_supported_mime_types(): array
    {
        return ['application/epub+zip'];
    }

    public function supports(string $file_path): bool
    {
        return strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'epub';
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

        if (!$this->copy_file($source_file, $destination_file)) {
            return false;
        }

        $epub = Epub_Processor::open($destination_file);

        if ($epub === null) {
            return false;
        }

        $zip = $epub['zip'];
        $opf = $epub['opf'];

        $fingerprint_id = (string) $this->get_context('fingerprint_id', '');
        $customer_email = (string) $this->get_context('customer_email', '');
        $order_id = (string) $this->get_context('order_id', '');
        $product_name = (string) $this->get_context('product_name', '');

        $success = true;

        $success = $success && Epub_Processor::set_metadata(
            $zip,
            $opf,
            'dww:fingerprint',
            $fingerprint_id
        );

        $success = $success && Epub_Processor::set_metadata(
            $zip,
            $opf,
            'dww:customer',
            $customer_email
        );

        $success = $success && Epub_Processor::set_metadata(
            $zip,
            $opf,
            'dww:order',
            $order_id
        );

        $success = $success && Epub_Processor::set_metadata(
            $zip,
            $opf,
            'dww:product',
            $product_name
        );

        Epub_Processor::close($zip);

        return $success;
    }
}