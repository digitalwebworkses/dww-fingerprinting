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
        string $order_id
    ): bool {
        if (!file_exists($source_path)) {
            return false;
        }

        $pdf = new Fpdi();

        $fingerprint_id = Fingerprint_Generator::generate(
            $customer_email,
            $order_id
        );

        $pdf->SetTitle('DWW Fingerprinted Document');
        $pdf->SetAuthor('DWW Fingerprinting');
        $pdf->SetSubject('Document fingerprint: ' . $fingerprint_id);
        $pdf->SetKeywords(
            sprintf(
                'DWW Fingerprinting, fingerprint:%s, order:%s, customer:%s',
                $fingerprint_id,
                $order_id,
                $customer_email
            )
        );
        $pdf->SetCreator('DWW Fingerprinting');

        $page_count = $pdf->setSourceFile($source_path);

        for ($page_number = 1; $page_number <= $page_count; $page_number++) {
            $template_id = $pdf->importPage($page_number);
            $size = $pdf->getTemplateSize($template_id);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template_id);

            $fingerprint_text = sprintf(
                'Licencia personal: %s | %s | Pedido #%s',
                $customer_name,
                $customer_email,
                $order_id
            );

            //self::add_header_fingerprint($pdf, $fingerprint_text, $size);
            //self::add_footer_fingerprint($pdf, $fingerprint_text, $size);
            //self::add_diagonal_fingerprint($pdf, $customer_email, $order_id, $size);
        }

        $pdf->Output('F', $destination_path);

        return file_exists($destination_path);
    }

    private static function add_header_fingerprint(Fpdi $pdf, string $text, array $size): void
    {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(130, 130, 130);
        $pdf->SetXY(10, 6);
        $pdf->Cell(0, 5, $text);
    }

    private static function add_footer_fingerprint(Fpdi $pdf, string $text, array $size): void
    {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(90, 90, 90);
        $pdf->SetXY(10, $size['height'] - 10);
        $pdf->Cell(0, 5, $text);
    }

    private static function add_diagonal_fingerprint(Fpdi $pdf, string $customer_email, string $order_id, array $size): void
    {
        // Pendiente: implementar rotación con clase extendida de FPDF/FPDI.
        // De momento no hacemos nada para mantener estable la PoC.
    }
}