<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class File_Validator
{
    private const MAX_FILE_SIZE = 2147483648; // 2 GB
    private const MAX_VERIFICATION_FILE_SIZE = 104857600; // 100 MB
    private const MAX_ZIP_ENTRIES = 5000;
    private const MAX_UNCOMPRESSED_SIZE = 268435456; // 256 MB
    private const MAX_COMPRESSION_RATIO = 100;

    public static function validate(string $file_path): array
    {
        $errors = [];

        if ($file_path === '') {
            $errors[] = 'Ruta de archivo vacía.';

            return self::result(false, $errors);
        }

        if (!file_exists($file_path)) {
            $errors[] = 'El archivo no existe.';

            return self::result(false, $errors);
        }

        if (!is_file($file_path)) {
            $errors[] = 'La ruta no corresponde a un archivo válido.';
        }

        if (!is_readable($file_path)) {
            $errors[] = 'El archivo no se puede leer.';
        }

        $size = filesize($file_path);

        if ($size === false) {
            $errors[] = 'No se pudo determinar el tamaño del archivo.';
        } elseif ($size <= 0) {
            $errors[] = 'El archivo está vacío.';
        } elseif ($size > self::MAX_FILE_SIZE) {
            $errors[] = 'El archivo supera el tamaño máximo permitido.';
        }

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if ($extension === '') {
            $errors[] = 'El archivo no tiene extensión.';
        } elseif (!self::is_supported_extension($extension)) {
            $errors[] = 'Extensión no soportada: ' . $extension;
        }

        if (!self::is_zip_container($file_path)) {
            $errors[] = 'El contenedor ZIP está dañado.';
        }

        $errors = array_merge(
            $errors,
            self::validate_container_structure($file_path, $extension)
        );

        return self::result(
            empty($errors),
            $errors
        );
    }

    public static function is_valid(string $file_path): bool
    {
        return self::validate($file_path)['valid'];
    }

    public static function validate_for_verification(string $file_path): array
    {
        if (is_file($file_path)) {
            $size = filesize($file_path);

            if ($size !== false && $size > self::verification_file_size_limit()) {
                return self::result(false, [
                    'El archivo supera el tamaño máximo permitido para verificación.',
                ]);
            }
        }

        $result = self::validate($file_path);

        if (!$result['valid']) {
            return $result;
        }

        $errors = array_merge(
            self::validate_file_signature($file_path),
            self::validate_zip_safety($file_path)
        );

        return self::result($errors === [], $errors);
    }

    private static function verification_file_size_limit(): int
    {
        return (int) apply_filters(
            'dww_fingerprinting_max_verification_file_size',
            self::MAX_VERIFICATION_FILE_SIZE
        );
    }

    public static function get_supported_extensions(): array
    {
        if (class_exists(Fingerprint_Manager::class)) {
            $extensions = Fingerprint_Manager::get_supported_extensions();

            if (!empty($extensions)) {
                return array_map(
                    'strtolower',
                    $extensions
                );
            }
        }

        return [
            'pdf',
            'epub',
            'docx',
            'xlsx',
            'pptx',
            'odt',
            'ods',
            'odp',
        ];
    }

    private static function is_supported_extension(string $extension): bool
    {
        return in_array(
            strtolower($extension),
            self::get_supported_extensions(),
            true
        );
    }

    private static function result(
        bool $valid,
        array $errors
    ): array {
        return [
            'valid' => $valid,
            'errors' => $errors,
        ];
    }

    public static function is_zip_container(string $file_path): bool
    {
        $extension = strtolower(
            pathinfo($file_path, PATHINFO_EXTENSION)
        );

        if (!in_array(
            $extension,
            [
                'docx',
                'xlsx',
                'pptx',
                'odt',
                'ods',
                'odp',
                'epub',
            ],
            true
        )) {
            return true;
        }

        $zip = new \ZipArchive();

        $opened = $zip->open($file_path);

        if ($opened !== true) {
            return false;
        }

        $zip->close();

        return true;
    }

    private static function validate_container_structure(
        string $file_path,
        string $extension
    ): array {
        if (!in_array(
            $extension,
            ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp', 'epub'],
            true
        )) {
            return [];
        }

        $zip = new \ZipArchive();

        if ($zip->open($file_path) !== true) {
            return ['No se pudo abrir el contenedor ZIP.'];
        }

        $required = self::required_zip_entries($extension);
        $errors = [];

        foreach ($required as $entry) {
            if ($zip->locateName($entry) === false) {
                $errors[] = 'Falta entrada requerida en el documento: ' . $entry;
            }
        }

        $zip->close();

        return $errors;
    }

    private static function required_zip_entries(string $extension): array
    {
        return match ($extension) {
            'docx' => [
                '[Content_Types].xml',
                '_rels/.rels',
                'word/document.xml',
            ],
            'xlsx' => [
                '[Content_Types].xml',
                '_rels/.rels',
                'xl/workbook.xml',
            ],
            'pptx' => [
                '[Content_Types].xml',
                '_rels/.rels',
                'ppt/presentation.xml',
            ],
            'odt', 'ods', 'odp' => [
                'content.xml',
                'meta.xml',
                'META-INF/manifest.xml',
            ],
            'epub' => [
                'mimetype',
                'META-INF/container.xml',
            ],
            default => [],
        };
    }

    private static function validate_zip_safety(string $file_path): array
    {
        if (!self::is_zip_container($file_path)) {
            return [];
        }

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if (!in_array($extension, ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp', 'epub'], true)) {
            return [];
        }

        $zip = new \ZipArchive();

        if ($zip->open($file_path) !== true) {
            return ['No se pudo abrir el contenedor ZIP.'];
        }

        $errors = [];
        $total_uncompressed = 0;

        if ($zip->numFiles > self::MAX_ZIP_ENTRIES) {
            $errors[] = 'El documento contiene demasiadas entradas ZIP.';
        }

        for ($index = 0; $index < $zip->numFiles && $errors === []; $index++) {
            $stat = $zip->statIndex($index);

            if (!is_array($stat)) {
                $errors[] = 'No se pudo inspeccionar una entrada ZIP.';
                break;
            }

            $size = max(0, (int) ($stat['size'] ?? 0));
            $compressed = max(0, (int) ($stat['comp_size'] ?? 0));
            $total_uncompressed += $size;

            if ($total_uncompressed > self::MAX_UNCOMPRESSED_SIZE) {
                $errors[] = 'El contenido descomprimido del documento es demasiado grande.';
                break;
            }

            if ($compressed > 0 && $size / $compressed > self::MAX_COMPRESSION_RATIO) {
                $errors[] = 'El documento contiene una entrada ZIP con compresión sospechosa.';
                break;
            }
        }

        $zip->close();

        return $errors;
    }

    private static function validate_file_signature(string $file_path): array
    {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if ($extension !== 'pdf') {
            return [];
        }

        $handle = fopen($file_path, 'rb');

        if ($handle === false) {
            return ['No se pudo inspeccionar la firma del documento.'];
        }

        $signature = fread($handle, 5);
        fclose($handle);

        return $signature === '%PDF-'
            ? []
            : ['La firma interna del archivo no corresponde a un PDF.'];
    }

    public static function load_xml(
        string $xml
    ): ?\DOMDocument {
        if (trim($xml) === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        $loaded = $dom->loadXML(
            $xml,
            LIBXML_NONET
                | LIBXML_COMPACT
                | LIBXML_NOBLANKS
                | LIBXML_NOERROR
                | LIBXML_NOWARNING
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return null;
        }

        return $dom;
    }
}
