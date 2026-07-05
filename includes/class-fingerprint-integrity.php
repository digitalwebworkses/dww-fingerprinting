<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Integrity
{
    public static function verify(
        array $properties,
        ?object $record
    ): array {

        $result = [
            'valid' => true,
            'warnings' => [],
            'errors' => [],
        ];

        if ($record === null) {

            $result['valid'] = false;
            $result['errors'][] =
                'No existe ningún fingerprint registrado.';

            return $result;
        }

        $document_hash = (string) ($properties['DWW Hash'] ?? '');

        if ($document_hash !== '') {
            $recalculated_hash = Fingerprint_Payload::hash_from_properties($properties);

            if (!hash_equals($document_hash, $recalculated_hash)) {
                $result['valid'] = false;
                $result['errors'][] =
                    'El hash del documento no coincide con el payload reconstruido.';
            }
        }

        self::compare(
            $result,
            'Fingerprint',
            $properties['DWW Fingerprint'] ?? '',
            (string) $record->fingerprint_id
        );

        self::compare(
            $result,
            'Payload Hash',
            $properties['DWW Hash'] ?? '',
            (string) ($record->payload_hash ?? '')
        );

        self::compare(
            $result,
            'Customer',
            $properties['DWW Customer'] ?? '',
            (string) $record->customer_email
        );

        self::compare(
            $result,
            'Order',
            $properties['DWW Order'] ?? '',
            (string) $record->order_id
        );

        self::compare(
            $result,
            'Product',
            $properties['DWW Product'] ?? '',
            (string) $record->product_name
        );

        self::compare(
            $result,
            'Asset Format',
            $properties['DWW Format'] ?? '',
            (string) $record->asset_format
        );

        self::compare(
            $result,
            'Asset ID',
            $properties['DWW Asset ID'] ?? '',
            (string) $record->asset_id
        );

        if (!empty($result['errors'])) {
            $result['valid'] = false;
        }

        return $result;
    }

    private static function compare(
        array &$result,
        string $field,
        string $document,
        string $database
    ): void {

        $document = trim($document);
        $database = trim($database);

        if ($document === '') {

            $result['warnings'][] =
                sprintf(
                    '%s no encontrado en el documento.',
                    $field
                );

            return;
        }

        if ($database === '') {

            $result['warnings'][] =
                sprintf(
                    '%s no existe en la base de datos.',
                    $field
                );

            return;
        }

        if (!hash_equals($database, $document)) {

            $result['errors'][] =
                sprintf(
                    '%s no coincide.',
                    $field
                );
        }
    }

    public static function context_from_properties(array $properties): array
    {
        return [
            'fingerprint_id' => (string) ($properties['DWW Fingerprint'] ?? ''),
            'customer_email' => (string) ($properties['DWW Customer'] ?? ''),
            'customer_name'  => (string) ($properties['DWW Customer Name'] ?? ''),
            'order_id'       => (string) ($properties['DWW Order'] ?? ''),
            'product_id'     => (string) ($properties['DWW Product ID'] ?? ''),
            'product_name'   => (string) ($properties['DWW Product'] ?? ''),
            'asset_format'   => (string) ($properties['DWW Format'] ?? ''),
            'asset_id'       => (string) ($properties['DWW Asset ID'] ?? ''),
        ];
    }
}
