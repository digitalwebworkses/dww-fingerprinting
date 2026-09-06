<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Extractor
{
    public static function extract(string $file_path): array
    {
        if (!is_file($file_path)) {
            $evidence = Fingerprint_Evidence::empty();
            $evidence['errors'][] = 'El archivo no existe.';

            return $evidence;
        }

        $validation = File_Validator::validate_for_verification($file_path);

        if (!$validation['valid']) {
            $evidence = Fingerprint_Evidence::empty(
                strtoupper(pathinfo($file_path, PATHINFO_EXTENSION))
            );

            $evidence['errors'] = $validation['errors'];

            return $evidence;
        }

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'pdf' => PDF_Processor::extract($file_path),

                'docx', 'xlsx', 'pptx' => Office_Open_XML_Processor::extract(
                    $file_path,
                    strtoupper($extension)
                ),

                'odt', 'ods', 'odp' => Open_Document_Processor::extract(
                    $file_path,
                    strtoupper($extension)
                ),

                'epub' => Epub_Processor::extract($file_path),

                default => self::unsupported($extension),
            };
        } catch (\Throwable $exception) {
            Logger::log(
                sprintf(
                    'Fingerprint extraction exception [%s]: %s',
                    $extension,
                    $exception->getMessage()
                )
            );

            $evidence = Fingerprint_Evidence::empty(strtoupper($extension));
            $evidence['errors'][] = 'No se pudo analizar el documento.';

            return $evidence;
        }
    }

    private static function unsupported(string $extension): array
    {
        $evidence = Fingerprint_Evidence::empty(strtoupper($extension));

        $evidence['errors'][] = 'Formato no soportado por el extractor.';

        return $evidence;
    }
}
