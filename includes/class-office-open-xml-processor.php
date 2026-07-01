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

        $zip = new \ZipArchive();

        if ($zip->open($destination_path) !== true) {
            return false;
        }

        self::write_custom_properties(
            $zip,
            self::build_fingerprint_properties($context)
        );

        self::ensure_root_relationship($zip);
        self::ensure_content_type($zip);

        $zip->close();

        return true;
    }

    private static function build_fingerprint_properties(array $context): array
    {
        $allowed = [
            'fingerprint_id' => 'DWW Fingerprint',
            'customer_email' => 'DWW Customer',
            'customer_name'  => 'DWW Customer Name',
            'order_id'       => 'DWW Order',
            'product_id'     => 'DWW Product ID',
            'product_name'   => 'DWW Product',
            'asset_format'   => 'DWW Format',
            'asset_id'       => 'DWW Asset ID',
        ];

        $properties = [];

        foreach ($allowed as $context_key => $property_name) {
            $value = trim((string) ($context[$context_key] ?? ''));

            if ($value === '') {
                continue;
            }

            $properties[$property_name] = $value;
        }

        $properties['DWW Generated At'] = current_time('mysql');
        $properties['DWW Plugin Version'] = DWW_FP_VERSION;

        return $properties;
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
            'http://schemas.openxmlformats.org/officeDocument/2006/custom-properties',
            'Properties'
        );

        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:vt',
            'http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes'
        );

        $dom->appendChild($root);

        $pid = 2;

        foreach ($properties as $name => $value) {
            $property = $dom->createElement('property');
            $property->setAttribute('fmtid', '{D5CDD505-2E9C-101B-9397-08002B2CF9AE}');
            $property->setAttribute('pid', (string) $pid);
            $property->setAttribute('name', $name);

            $vt = $dom->createElement('vt:lpwstr');
            $vt->appendChild($dom->createTextNode((string) $value));

            $property->appendChild($vt);
            $root->appendChild($property);

            $pid++;
        }

        return (string) $dom->saveXML();
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

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        if (!$dom->loadXML($xml)) {
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

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        if (!$dom->loadXML($xml)) {
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

        $zip->deleteName($path);
        $zip->addFromString($path, (string) $dom->saveXML());
    }
}