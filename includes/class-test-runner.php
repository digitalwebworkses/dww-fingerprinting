<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Test_Runner
{
    public static function init(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_init', [self::class, 'maybe_run_pdf_test']);
    }

    public static function maybe_run_pdf_test(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_GET['dww_fp_test_pdf'])) {
            return;
        }

        $source = DWW_FP_PLUGIN_DIR . 'tests/sample.pdf';

        $upload_dir = wp_upload_dir();

        $fingerprint_dir = trailingslashit($upload_dir['basedir']) . 'dww-fingerprinting';

        if (!file_exists($fingerprint_dir)) {
            wp_mkdir_p($fingerprint_dir);
        }

        $destination = trailingslashit($fingerprint_dir) . 'sample-personalized.pdf';

        $customer_name  = 'Carlos Márquez';
        $customer_email = 'idosknet@gmail.com';
        $order_id       = '12345';
        $product_id     = 'TEST-PRODUCT-1';

        $result = PDF_Processor::personalize_pdf(
            $source,
            $destination,
            $customer_name,
            $customer_email,
            $order_id
        );

        if (!$result) {
            wp_die('Error generando PDF personalizado.');
        }

        $fingerprint_id = 'DWW-' . strtoupper(substr(hash('sha256', $customer_email . '|' . $order_id), 0, 16));

        if (Fingerprint_DB::exists($fingerprint_id)) {
            wp_die('PDF generado correctamente. El fingerprint ya estaba registrado.');
        }

        $registry_id = Fingerprint_DB::insert([
            'fingerprint_id' => $fingerprint_id,
            'customer_email' => $customer_email,
            'order_id'       => $order_id,
            'product_id'     => $product_id,
            'source_file'    => $source,
            'generated_file' => $destination,
        ]);

        wp_die(
            $registry_id > 0
                ? 'PDF personalizado generado correctamente y fingerprint registrado.'
                : 'PDF generado, pero no se pudo registrar el fingerprint.'
        );
    }
}
