<?php

namespace DWW_Fingerprinting;

use DOMDocument;
use DOMElement;
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

    public static function extract(string $file_path): array
    {
        $epub = self::open($file_path);

        if ($epub === null) {
            $evidence = Fingerprint_Evidence::empty('EPUB');
            $evidence['errors'][] = 'No se pudo abrir el EPUB.';

            return $evidence;
        }

        $properties = self::read_fingerprint_properties(
            $epub['zip'],
            $epub['opf']
        );

        self::close($epub['zip']);

        return Fingerprint_Evidence::from_properties(
            'EPUB',
            $properties
        );
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

        $metadata = self::get_metadata_node($dom);

        if (!$metadata) {
            return false;
        }

        self::remove_metadata_property(
            $dom,
            $metadata,
            $property
        );

        self::append_metadata_property(
            $dom,
            $metadata,
            $property,
            $value
        );

        return $zip->addFromString(
            $opf_path,
            (string) $dom->saveXML()
        );
    }

    public static function set_fingerprint_metadata(
        ZipArchive $zip,
        string $opf_path,
        array $context
    ): bool {
        $dom = self::load_opf_dom($zip, $opf_path, true);

        if (!$dom) {
            return false;
        }

        $metadata = self::get_metadata_node($dom);

        if (!$metadata) {
            return false;
        }

        self::remove_existing_dww_properties($dom, $metadata);

        foreach (Fingerprint_Payload::property_map($context) as $name => $value) {
            self::append_metadata_property(
                $dom,
                $metadata,
                self::property_name_to_epub_key((string) $name),
                (string) $value
            );
        }

        return $zip->addFromString(
            $opf_path,
            (string) $dom->saveXML()
        );
    }

    public static function read_fingerprint_properties(
        ZipArchive $zip,
        string $opf_path
    ): array {
        $dom = self::load_opf_dom($zip, $opf_path);

        if (!$dom) {
            return [];
        }

        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query(
            '//*[local-name()="meta"][starts-with(@property, "dww:")]'
        );

        if (!$nodes) {
            return [];
        }

        $properties = [];

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $property = $node->getAttribute('property');
            $name = self::epub_key_to_property_name($property);

            if ($name === '') {
                continue;
            }

            $properties[$name] = trim((string) $node->textContent);
        }

        return $properties;
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

        $dom = File_Validator::load_xml($container);

        if (!$dom) {
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

        $dom = File_Validator::load_xml($opf_content);

        if (!$dom) {
            return null;
        }

        $dom->preserveWhiteSpace = !$format_output;
        $dom->formatOutput = $format_output;

        return $dom;
    }

    private static function get_metadata_node(DOMDocument $dom): ?DOMElement
    {
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query(
            '//*[local-name()="metadata"]'
        );

        if (!$nodes || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private static function append_metadata_property(
        DOMDocument $dom,
        DOMElement $metadata,
        string $property,
        string $value
    ): void {
        $meta = $dom->createElement('meta');
        $meta->setAttribute('property', sanitize_text_field($property));
        $meta->appendChild(
            $dom->createTextNode(sanitize_text_field($value))
        );

        $metadata->appendChild($meta);
    }

    private static function remove_metadata_property(
        DOMDocument $dom,
        DOMElement $metadata,
        string $property
    ): void {
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query(
            '*[local-name()="meta"][@property="' .
                htmlspecialchars($property, ENT_QUOTES) .
                '"]',
            $metadata
        );

        if (!$nodes) {
            return;
        }

        foreach ($nodes as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    private static function remove_existing_dww_properties(
        DOMDocument $dom,
        DOMElement $metadata
    ): void {
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query(
            '*[local-name()="meta"][starts-with(@property, "dww:")]',
            $metadata
        );

        if (!$nodes) {
            return;
        }

        foreach ($nodes as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    private static function property_name_to_epub_key(string $name): string
    {
        $key = strtolower(str_replace('DWW ', '', $name));
        $key = preg_replace('/[^a-z0-9]+/', '-', $key) ?: '';

        return 'dww:' . trim($key, '-');
    }

    private static function epub_key_to_property_name(string $property): string
    {
        if (!str_starts_with($property, 'dww:')) {
            return '';
        }

        $key = substr($property, 4);

        $map = [
            'fingerprint' => 'DWW Fingerprint',
            'customer' => 'DWW Customer',
            'customer-name' => 'DWW Customer Name',
            'order' => 'DWW Order',
            'product-id' => 'DWW Product ID',
            'product' => 'DWW Product',
            'format' => 'DWW Format',
            'asset-id' => 'DWW Asset ID',
            'generated-at' => 'DWW Generated At',
            'plugin-version' => 'DWW Plugin Version',
            'hash' => 'DWW Hash',
            'hash-short' => 'DWW Hash Short',
            'hash-algorithm' => 'DWW Hash Algorithm',
            'key-id' => 'DWW Key ID',
            'payload-version' => 'DWW Payload Version',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        if (preg_match('/^chunk-(\d+)$/', $key, $matches)) {
            return sprintf('DWW Chunk %02d', (int) $matches[1]);
        }

        return '';
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
