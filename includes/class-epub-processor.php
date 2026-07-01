<?php

namespace DWW_Fingerprinting;

use DOMDocument;
use DOMXPath;
use ZipArchive;

if (!defined('ABSPATH')) {
    exit;
}

class Epub_Processor
{
    public static function open(string $epub_file): ?array
    {
        if (!is_file($epub_file)) {
            return null;
        }

        $zip = new ZipArchive();

        if ($zip->open($epub_file) !== true) {
            return null;
        }

        $opf_path = self::get_opf_path($zip);

        if ($opf_path === null) {
            self::close($zip);
            return null;
        }

        return [
            'zip' => $zip,
            'opf' => $opf_path,
        ];
    }

    public static function read_metadata(
        ZipArchive $zip,
        string $opf_path
    ): ?array {
        $dom = self::load_opf_dom($zip, $opf_path);

        if (!$dom) {
            return null;
        }

        $xpath = new DOMXPath($dom);

        return [
            'title'      => self::get_first_node_value($xpath, 'title'),
            'creator'    => self::get_first_node_value($xpath, 'creator'),
            'identifier' => self::get_first_node_value($xpath, 'identifier'),
            'language'   => self::get_first_node_value($xpath, 'language'),
        ];
    }

    public static function get_metadata_property(
        ZipArchive $zip,
        string $opf_path,
        string $property
    ): ?string {
        $dom = self::load_opf_dom($zip, $opf_path);

        if (!$dom) {
            return null;
        }

        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query(
            '//*[local-name()="meta"][@property="' .
            htmlspecialchars($property, ENT_QUOTES) .
            '"]'
        );

        if (!$nodes || $nodes->length === 0) {
            return null;
        }

        return trim((string) $nodes->item(0)->textContent);
    }

    public static function set_metadata(
        ZipArchive $zip,
        string $opf_path,
        string $property,
        string $value
    ): bool {
        $dom = self::load_opf_dom($zip, $opf_path, true);

        if (!$dom) {
            return false;
        }

        $xpath = new DOMXPath($dom);

        $metadata_nodes = $xpath->query(
            '//*[local-name()="metadata"]'
        );

        if (!$metadata_nodes || $metadata_nodes->length === 0) {
            return false;
        }

        $metadata = $metadata_nodes->item(0);

        $meta = $dom->createElement('meta');
        $meta->setAttribute('property', sanitize_text_field($property));
        $meta->appendChild(
            $dom->createTextNode(sanitize_text_field($value))
        );

        $metadata->appendChild($meta);

        return $zip->addFromString(
            $opf_path,
            (string) $dom->saveXML()
        );
    }

    public static function close(ZipArchive $zip): void
    {
        try {
            $zip->close();
        } catch (\ValueError $exception) {
            // ZipArchive ya estaba cerrado o no inicializado.
        }
    }

    private static function get_opf_path(ZipArchive $zip): ?string
    {
        $container = $zip->getFromName('META-INF/container.xml');

        if ($container === false) {
            return null;
        }

        $dom = new DOMDocument();

        if (!@$dom->loadXML($container)) {
            return null;
        }

        $xpath = new DOMXPath($dom);

        $rootfiles = $xpath->query(
            '//*[local-name()="rootfile"]'
        );

        if (!$rootfiles || $rootfiles->length === 0) {
            return null;
        }

        $opf_path = $rootfiles
            ->item(0)
            ->getAttribute('full-path');

        return $opf_path !== '' ? $opf_path : null;
    }

    private static function load_opf_dom(
        ZipArchive $zip,
        string $opf_path,
        bool $format_output = false
    ): ?DOMDocument {
        $opf_content = $zip->getFromName($opf_path);

        if ($opf_content === false) {
            return null;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = !$format_output;
        $dom->formatOutput = $format_output;

        if (!@$dom->loadXML($opf_content)) {
            return null;
        }

        return $dom;
    }

    private static function get_first_node_value(
        DOMXPath $xpath,
        string $node_name
    ): string {
        $nodes = $xpath->query(
            '//*[local-name()="' . $node_name . '"]'
        );

        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        return trim((string) $nodes->item(0)->nodeValue);
    }
}