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
        try {

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

            $success = Epub_Processor::set_fingerprint_metadata(
                $zip,
                $opf,
                $context
            );

            Epub_Processor::close($zip);

            return $success;
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
