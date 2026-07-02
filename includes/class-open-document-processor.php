<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Open_Document_Processor
{
    private const META_PATH = 'meta.xml';

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

        $meta_xml = $zip->getFromName(self::META_PATH);

        if ($meta_xml === false) {
            $zip->close();
            return false;
        }

        $updated_meta_xml = self::add_fingerprint_metadata(
            $meta_xml,
            self::build_fingerprint_properties($context)
        );

        if ($updated_meta_xml === '') {
            $zip->close();
            return false;
        }

        $zip->deleteName(self::META_PATH);
        $zip->addFromString(self::META_PATH, $updated_meta_xml);

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

    private static function add_fingerprint_metadata(
        string $meta_xml,
        array $properties
    ): string {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        if (!$dom->loadXML($meta_xml)) {
            return '';
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace(
            'office',
            'urn:oasis:names:tc:opendocument:xmlns:office:1.0'
        );
        $xpath->registerNamespace(
            'meta',
            'urn:oasis:names:tc:opendocument:xmlns:meta:1.0'
        );

        $office_meta = $xpath->query('/office:document-meta/office:meta')->item(0);

        if (!$office_meta instanceof \DOMElement) {
            return '';
        }

        self::remove_existing_dww_properties($xpath, $office_meta);

        foreach ($properties as $name => $value) {
            $property = $dom->createElementNS(
                'urn:oasis:names:tc:opendocument:xmlns:meta:1.0',
                'meta:user-defined'
            );

            $property->setAttributeNS(
                'urn:oasis:names:tc:opendocument:xmlns:meta:1.0',
                'meta:name',
                $name
            );

            $property->appendChild(
                $dom->createTextNode((string) $value)
            );

            $office_meta->appendChild($property);
        }

        return (string) $dom->saveXML();
    }

    private static function remove_existing_dww_properties(
        \DOMXPath $xpath,
        \DOMElement $office_meta
    ): void {
        $nodes = $xpath->query(
            'meta:user-defined[starts-with(@meta:name, "DWW ")]',
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
}