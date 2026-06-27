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
        add_action('admin_init', [self::class, 'maybe_run_epub_test']);
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

    public static function maybe_run_epub_test(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_GET['dww_fp_test_epub'])) {
            return;
        }

        $source = DWW_FP_PLUGIN_DIR . 'tests/sample.epub';

        $upload_dir = wp_upload_dir();

        $test_dir = trailingslashit($upload_dir['basedir']) . 'dww-fingerprinting/temp';

        if (!file_exists($test_dir)) {
            wp_mkdir_p($test_dir);
        }

        $destination = trailingslashit($test_dir) . 'sample-epub-test.epub';

        copy($source, $destination);

        $source = $destination;

        if (!file_exists($source)) {
            wp_die('No existe el EPUB de prueba: ' . esc_html($source));
        }

        $result = Epub_Processor::open($source);

        if ($result === null) {
            wp_die('No se pudo abrir el EPUB o localizar el package OPF.');
        }

        $opf = $result['opf'] ?? '';

        $metadata = Epub_Processor::read_metadata(
            $result['zip'],
            $opf
        );

        $write_test = Epub_Processor::set_metadata(
            $result['zip'],
            $opf,
            'dww:test',
            'Hello World'
        );

        Epub_Processor::close($result['zip']);

        $verify = Epub_Processor::open($source);

        $verified_value = null;

        if ($verify !== null) {
            $verified_value = Epub_Processor::get_metadata_property(
                $verify['zip'],
                $verify['opf'],
                'dww:test'
            );

            Epub_Processor::close($verify['zip']);
        }

        if ($metadata === null) {
            wp_die('El EPUB se abrió, pero no se pudieron leer los metadatos.');
        }

        '<p><strong>Escritura metadata:</strong> ' .
            esc_html($write_test ? 'OK' : 'ERROR') .
            '</p>' .

            wp_die(
                '<h1>Test EPUB correcto</h1>' .
                    '<p>El EPUB se ha abierto correctamente.</p>' .
                    '<p><strong>Package OPF:</strong> <code>' . esc_html($opf) . '</code></p>' .
                    '<hr>' .
                    '<h2>Metadatos</h2>' .
                    '<ul>' .
                    '<li><strong>Título:</strong> ' . esc_html($metadata['title']) . '</li>' .
                    '<li><strong>Autor:</strong> ' . esc_html($metadata['creator']) . '</li>' .
                    '<li><strong>Identificador:</strong> <code>' . esc_html($metadata['identifier']) . '</code></li>' .
                    '<li><strong>Idioma:</strong> ' . esc_html($metadata['language']) . '</li>' .
                    '</ul>' .
                    '<hr>' .
                    '<h2>Prueba de escritura</h2>' .
                    '<p><strong>Escritura metadata:</strong> ' .
                    esc_html($write_test ? 'OK' : 'ERROR') .
                    '</p>' .
                    '<p><strong>Verificación:</strong> <code>' .
                    esc_html((string) $verified_value) .
                    '</code></p>'
            );
    }
}
