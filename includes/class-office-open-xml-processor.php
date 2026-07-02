<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Office_Open_XML_Processor
{
    private const CUSTOM_PROPERTIES_PATH = 'docProps/custom.xml';

    private const CUSTOM_PROPERTIES_REL_TYPE =
    'http://schemas.openxmlformats.org/officeDocument/2006/relationships/custom-properties';

    private const CUSTOM_PROPERTIES_CONTENT_TYPE =
    'application/vnd.openxmlformats-officedocument.custom-properties+xml';

    private const CUSTOM_PROPERTIES_NS =
    'http://schemas.openxmlformats.org/officeDocument/2006/custom-properties';

    private const CUSTOM_PROPERTY_TYPES_NS =
    'http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes';

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

        self::write_custom_properties(
            $zip,
            Fingerprint_Payload::property_map($context)
        );

        self::ensure_root_relationship($zip);
        self::ensure_content_type($zip);

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
            $evidence['errors'][] = 'No se pudo abrir el archivo Office Open XML.';

            return $evidence;
        }

        $xml = $zip->getFromName(self::CUSTOM_PROPERTIES_PATH);
        $zip->close();

        if ($xml === false) {
            $evidence = Fingerprint_Evidence::empty($format);
            $evidence['warnings'][] = 'No existe docProps/custom.xml.';

            return $evidence;
        }

        return Fingerprint_Evidence::from_properties(
            $format,
            self::read_custom_properties_xml((string) $xml)
        );
    }

    private static function open_zip(string $file_path): ?\ZipArchive
    {
        $zip = new \ZipArchive();

        if ($zip->open($file_path) !== true) {
            return null;
        }

        return $zip;
    }

    private static function write_custom_properties(
        \ZipArchive $zip,
        array $properties
    ): void {
        $xml = self::build_custom_properties_xml($properties);

        if ($zip->locateName(self::CUSTOM_PROPERTIES_PATH) !== false) {
            $zip->deleteName(self::CUSTOM_PROPERTIES_PATH);
        }

        $zip->addFromString(self::CUSTOM_PROPERTIES_PATH, $xml);
    }

    private static function build_custom_properties_xml(array $properties): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        $root = $dom->createElementNS(
            self::CUSTOM_PROPERTIES_NS,
            'Properties'
        );

        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:vt',
            self::CUSTOM_PROPERTY_TYPES_NS
        );

        $dom->appendChild($root);

        $pid = 2;

        foreach ($properties as $name => $value) {
            self::append_custom_property(
                $dom,
                $root,
                (string) $name,
                (string) $value,
                $pid
            );

            $pid++;
        }

        return (string) $dom->saveXML();
    }

    private static function append_custom_property(
        \DOMDocument $dom,
        \DOMElement $root,
        string $name,
        string $value,
        int $pid
    ): void {
        $property = $dom->createElement('property');
        $property->setAttribute('fmtid', '{D5CDD505-2E9C-101B-9397-08002B2CF9AE}');
        $property->setAttribute('pid', (string) $pid);
        $property->setAttribute('name', $name);

        $vt = $dom->createElement('vt:lpwstr');
        $vt->appendChild($dom->createTextNode($value));

        $property->appendChild($vt);
        $root->appendChild($property);
    }

    private static function read_custom_properties_xml(string $xml): array
    {
        $dom = File_Validator::load_xml($xml);

        if (!$dom) {
            return [];
        }

        $xpath = new \DOMXPath($dom);

        $xpath->registerNamespace(
            'cp',
            self::CUSTOM_PROPERTIES_NS
        );

        $properties = [];
        $nodes = $xpath->query('//cp:property');

        if (!$nodes) {
            return [];
        }

        foreach ($nodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $name = $node->getAttribute('name');

            if (!str_starts_with($name, 'DWW ')) {
                continue;
            }

            $properties[$name] = trim((string) $node->textContent);
        }

        return $properties;
    }

    private static function ensure_root_relationship(\ZipArchive $zip): void
    {
        $path = '_rels/.rels';

        $xml = $zip->getFromName($path);

        if ($xml === false) {
            return;
        }

        if (strpos($xml, self::CUSTOM_PROPERTIES_REL_TYPE) !== false) {
            return;
        }

        $dom = self::load_xml_document((string) $xml);

        if (!$dom) {
            return;
        }

        $relationships = $dom->documentElement;

        if (!$relationships) {
            return;
        }

        $relationship = $dom->createElement('Relationship');
        $relationship->setAttribute('Id', self::get_next_relationship_id($dom));
        $relationship->setAttribute('Type', self::CUSTOM_PROPERTIES_REL_TYPE);
        $relationship->setAttribute('Target', self::CUSTOM_PROPERTIES_PATH);

        $relationships->appendChild($relationship);

        self::replace_zip_xml($zip, $path, $dom);
    }

    private static function ensure_content_type(\ZipArchive $zip): void
    {
        $path = '[Content_Types].xml';

        $xml = $zip->getFromName($path);

        if ($xml === false) {
            return;
        }

        if (strpos($xml, self::CUSTOM_PROPERTIES_CONTENT_TYPE) !== false) {
            return;
        }

        $dom = self::load_xml_document((string) $xml);

        if (!$dom) {
            return;
        }

        $types = $dom->documentElement;

        if (!$types) {
            return;
        }

        $override = $dom->createElement('Override');
        $override->setAttribute('PartName', '/' . self::CUSTOM_PROPERTIES_PATH);
        $override->setAttribute('ContentType', self::CUSTOM_PROPERTIES_CONTENT_TYPE);

        $types->appendChild($override);

        self::replace_zip_xml($zip, $path, $dom);
    }

    private static function load_xml_document(string $xml): ?\DOMDocument
    {
        $dom = File_Validator::load_xml($xml);

        if (!$dom) {
            return null;
        }

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        return $dom;
    }

    private static function replace_zip_xml(
        \ZipArchive $zip,
        string $path,
        \DOMDocument $dom
    ): void {
        $zip->deleteName($path);
        $zip->addFromString($path, (string) $dom->saveXML());
    }

    private static function get_next_relationship_id(\DOMDocument $dom): string
    {
        $max = 0;

        foreach ($dom->getElementsByTagName('Relationship') as $relationship) {
            $id = $relationship->getAttribute('Id');

            if (preg_match('/^rId(\d+)$/', $id, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return 'rId' . ($max + 1);
    }
}
