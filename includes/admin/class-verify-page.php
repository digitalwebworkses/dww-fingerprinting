<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Verify_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes.'));
        }

        $result = null;

        if (
            isset($_POST['dww_verify_document']) &&
            check_admin_referer('dww_verify_document')
        ) {
            $result = self::process_upload();
        }

        $supported_formats = Fingerprint_Manager::get_supported_extensions();

        ?>

        <div class="wrap dww-verify-page">

            <h1>Verificar documento</h1>

            <p>
                Sube un archivo generado por DWW Fingerprinting para comprobar
                su autenticidad, integridad y trazabilidad.
            </p>

            <div class="dww-verify-panel">

                <form
                    method="post"
                    enctype="multipart/form-data"
                    class="dww-verify-form">

                    <?php wp_nonce_field('dww_verify_document'); ?>

                    <label
                        for="dww-verify-document"
                        class="dww-verify-label">
                        Seleccionar documento
                    </label>

                    <input
                        id="dww-verify-document"
                        type="file"
                        name="document"
                        accept="<?php echo esc_attr(
                            self::accepted_extensions($supported_formats)
                        ); ?>"
                        required>

                    <p class="description">
                        Formatos admitidos:
                        <?php echo esc_html(
                            strtoupper(
                                implode(', ', $supported_formats)
                            )
                        ); ?>.
                    </p>

                    <button
                        type="submit"
                        name="dww_verify_document"
                        class="button button-primary">
                        Verificar documento
                    </button>

                </form>

            </div>

            <?php if ($result !== null) : ?>

                <div class="dww-verify-result">

                    <h2>Resultado de la verificación</h2>

                    <?php Verification_Report::render($result); ?>

                </div>

            <?php endif; ?>

        </div>

        <?php
    }

    private static function process_upload(): array
    {
        if (
            empty($_FILES['document']['tmp_name']) ||
            !is_uploaded_file($_FILES['document']['tmp_name'])
        ) {
            return self::upload_error(
                'No se ha seleccionado ningún archivo válido.'
            );
        }

        $tmp_file = (string) $_FILES['document']['tmp_name'];

        $original_name = sanitize_file_name(
            (string) ($_FILES['document']['name'] ?? '')
        );

        $extension = strtolower(
            pathinfo($original_name, PATHINFO_EXTENSION)
        );

        $verify_file = $tmp_file;

        if ($extension !== '') {
            $verify_file = $tmp_file . '.' . $extension;

            if (!copy($tmp_file, $verify_file)) {
                return self::upload_error(
                    'No se ha podido preparar el archivo para su verificación.'
                );
            }
        }

        try {
            return Fingerprint_Verifier::verify($verify_file);
        } finally {
            if (
                $verify_file !== $tmp_file &&
                file_exists($verify_file)
            ) {
                unlink($verify_file);
            }
        }
    }

    private static function accepted_extensions(
        array $extensions
    ): string {
        $accepted = array_map(
            static fn($extension): string =>
                '.' . strtolower((string) $extension),
            $extensions
        );

        return implode(',', $accepted);
    }

    private static function upload_error(
        string $message
    ): array {
        return [
            'valid' => false,
            'status' => 'upload_error',
            'warnings' => [],
            'errors' => [$message],
            'record' => null,
            'evidence' => [],
        ];
    }
}