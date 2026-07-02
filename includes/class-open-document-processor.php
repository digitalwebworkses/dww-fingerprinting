<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Open_Document_Processor
{
    private const META_PATH = 'meta.xml';

    private const META_NS =
    'urn:oasis:names:tc:opendocument:xmlns:meta:1.0';

    private const OFFICE_NS =
    'urn:oasis:names:tc:opendocument:xmlns:office:1.0';

    public static function personalize(
        string $source_path,
        string $destination_path,
        array $context = []
    ): bool {
        if (!file_exists($source_path)) {
            return false;
        }

        if (!copy($source_path, $destination_path)) {
            return false;
        }

        $zip = self::open_zip($destination_path);

        if (!$zip) {
            return false;
        }

        $meta_xml = $zip->getFromName(self::META_PATH);

        if ($meta_xml === false) {
            $zip->close();
            return false;
        }

        $updated = self::write_properties(
            (string) $meta_xml,
            Fingerprint_Payload::property_map($context)
        );

        if ($updated === '') {
            $zip->close();
            return false;
        }

        self::replace_meta($zip, $updated);

        $zip->close();

        return true;
    }

    public static function extract(
        string $file_path,
        string $format = ''
    ): array {

        $format = $format !== ''
            ? strtoupper($format)
            : strtoupper(pathinfo($file_path, PATHINFO_EXTENSION));

        $zip = self::open_zip($file_path);

        if (!$zip) {

            $evidence = Fingerprint_Evidence::empty($format);

            $evidence['errors'][] =
                'No se pudo abrir el documento OpenDocument.';

            return $evidence;
        }

        $meta_xml = $zip->getFromName(self::META_PATH);

        $zip->close();

        if ($meta_xml === false) {

            $evidence = Fingerprint_Evidence::empty($format);

            $evidence['warnings'][] =
                'No existe meta.xml.';

            return $evidence;
        }

        return Fingerprint_Evidence::from_properties(
            $format,
            self::read_properties((string) $meta_xml)
        );
    }

    private static function open_zip(
        string $file
    ): ?\ZipArchive {

        $zip = new \ZipArchive();

        if ($zip->open($file) !== true) {
            return null;
        }

        return $zip;
    }

    private static function replace_meta(
        \ZipArchive $zip,
        string $xml
    ): void {

        $zip->deleteName(self::META_PATH);

        $zip->addFromString(
            self::META_PATH,
            $xml
        );
    }

    private static function write_properties(
        string $meta_xml,
        array $properties
    ): string {

        $dom = self::load_dom($meta_xml);

        if (!$dom) {
            return '';
        }

        $xpath = self::create_xpath($dom);

        $office_meta = $xpath
            ->query('/office:document-meta/office:meta')
            ->item(0);

        if (!$office_meta instanceof \DOMElement) {
            return '';
        }

        self::remove_existing_properties(
            $xpath,
            $office_meta
        );

        foreach ($properties as $name => $value) {

            self::append_property(
                $dom,
                $office_meta,
                (string) $name,
                (string) $value
            );
        }

        return (string) $dom->saveXML();
    }

    private static function read_properties(
        string $meta_xml
    ): array {

        $dom = self::load_dom($meta_xml);

        if (!$dom) {
            return [];
        }

        $xpath = self::create_xpath($dom);

        $nodes = $xpath->query(
            '//meta:user-defined'
        );

        if (!$nodes) {
            return [];
        }

        $properties = [];

        foreach ($nodes as $node) {

            if (!$node instanceof \DOMElement) {
                continue;
            }

            $name = $node->getAttributeNS(
                self::META_NS,
                'name'
            );

            if (!str_starts_with($name, 'DWW ')) {
                continue;
            }

            $properties[$name] = trim(
                (string) $node->textContent
            );
        }

        return $properties;
    }

    private static function append_property(
        \DOMDocument $dom,
        \DOMElement $office_meta,
        string $name,
        string $value
    ): void {

        $property = $dom->createElementNS(
            self::META_NS,
            'meta:user-defined'
        );

        $property->setAttributeNS(
            self::META_NS,
            'meta:name',
            $name
        );

        $property->appendChild(
            $dom->createTextNode($value)
        );

        $office_meta->appendChild($property);
    }

    private static function remove_existing_properties(
        \DOMXPath $xpath,
        \DOMElement $office_meta
    ): void {

        $nodes = $xpath->query(
            'meta:user-defined[starts-with(@meta:name,"DWW ")]',
            $office_meta
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

    private static function load_dom(
        string $xml
    ): ?\DOMDocument {

        $dom = File_Validator::load_xml($xml);

        if (!$dom) {
            return null;
        }

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        return $dom;
    }

    private static function create_xpath(
        \DOMDocument $dom
    ): \DOMXPath {

        $xpath = new \DOMXPath($dom);

        $xpath->registerNamespace(
            'office',
            self::OFFICE_NS
        );

        $xpath->registerNamespace(
            'meta',
            self::META_NS
        );

        return $xpath;
    }
}
