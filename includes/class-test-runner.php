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

        $result = PDF_Processor::personalize_pdf(
            $source,
            $destination,
            'Carlos Márquez',
            'idosknet@gmail.com',
            '12345'
        );

        wp_die($result ? 'PDF personalizado generado correctamente.' : 'Error generando PDF personalizado.');
    }
}
