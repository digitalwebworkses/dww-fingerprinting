<?php

namespace DWW_Fingerprinting;

use setasign\Fpdi\Fpdi;

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Processor
{
    public static function personalize_pdf(
        string $source_path,
        string $destination_path,
        string $customer_name,
        string $customer_email,
        string $order_id,
        array $context = []
    ): bool {
        if (!file_exists($source_path)) {
            return false;
        }

        $fingerprint_id = $context['fingerprint_id'] ?? Fingerprint_Generator::generate(
            $customer_email,
            $order_id
        );

        $context = array_merge(
            [
                'customer_name'  => $customer_name,
                'customer_email' => $customer_email,
                'order_id'       => $order_id,
                'fingerprint_id' => $fingerprint_id,
                'asset_format'   => 'pdf',
            ],
            $context
        );

        $properties = Fingerprint_Payload::property_map($context);

        $pdf = new Fpdi();

        $pdf->SetTitle('DWW Fingerprinted Document');
        $pdf->SetAuthor('DWW Fingerprinting');
        $pdf->SetSubject('Document fingerprint: ' . $fingerprint_id);
        $pdf->SetKeywords(self::build_keywords($properties));
        $pdf->SetCreator('DWW Fingerprinting');

        $page_count = $pdf->setSourceFile($source_path);

        for ($page_number = 1; $page_number <= $page_count; $page_number++) {
            $template_id = $pdf->importPage($page_number);
            $size = $pdf->getTemplateSize($template_id);

            $pdf->AddPage(
                $size['orientation'],
                [
                    $size['width'],
                    $size['height'],
                ]
            );

            $pdf->useTemplate($template_id);
        }

        $pdf->Output('F', $destination_path);

        return file_exists($destination_path);
    }

    public static function extract(string $file_path): array
    {
        if (!is_file($file_path)) {
            $evidence = Fingerprint_Evidence::empty('PDF');
            $evidence['errors'][] = 'El archivo PDF no existe.';

            return $evidence;
        }

        $content = file_get_contents($file_path);

        if ($content === false || $content === '') {
            $evidence = Fingerprint_Evidence::empty('PDF');
            $evidence['errors'][] = 'No se pudo leer el PDF.';

            return $evidence;
        }

        $properties = self::extract_properties_from_pdf_content($content);

        if (empty($properties)) {
            $evidence = Fingerprint_Evidence::empty('PDF');
            $evidence['warnings'][] = 'No se encontraron metadatos DWW en el PDF.';

            return $evidence;
        }

        return Fingerprint_Evidence::from_properties(
            'PDF',
            $properties
        );
    }

    private static function build_keywords(array $properties): string
    {
        $parts = [];

        foreach ($properties as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return implode('; ', $parts);
    }

    private static function extract_properties_from_pdf_content(string $content): array
    {
        $properties = [];

        $subject = self::extract_pdf_info_value($content, 'Subject');
        $keywords = self::extract_pdf_info_value($content, 'Keywords');

        if (
            $subject !== '' &&
            preg_match('/DWW-[A-Z0-9]{16}/', $subject, $matches)
        ) {
            $properties['DWW Fingerprint'] = $matches[0];
        }

        if ($keywords !== '') {
            foreach (explode(';', $keywords) as $part) {
                $pair = explode('=', trim($part), 2);

                if (count($pair) !== 2) {
                    continue;
                }

                $key = trim($pair[0]);
                $value = trim($pair[1]);

                if (str_starts_with($key, 'DWW ') && $value !== '') {
                    $properties[$key] = $value;
                }
            }

            if (
                empty($properties['DWW Fingerprint']) &&
                preg_match('/fingerprint:(DWW-[A-Z0-9]{16})/', $keywords, $matches)
            ) {
                $properties['DWW Fingerprint'] = $matches[1];
            }
        }

        return $properties;
    }

    private static function extract_pdf_info_value(
        string $content,
        string $key
    ): string {
        if (!preg_match('/\/' . preg_quote($key, '/') . '\s*\((.*?)\)/s', $content, $matches)) {
            return '';
        }

        return self::decode_pdf_string($matches[1]);
    }

    private static function decode_pdf_string(string $value): string
    {
        $value = str_replace(
            ['\\(', '\\)', '\\\\'],
            ['(', ')', '\\'],
            $value
        );

        return trim($value);
    }
}