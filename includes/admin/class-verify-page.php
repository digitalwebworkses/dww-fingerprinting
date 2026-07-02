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

            if (
                !empty($_FILES['document']['tmp_name']) &&
                is_uploaded_file($_FILES['document']['tmp_name'])
            ) {

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

                    copy($tmp_file, $verify_file);
                }

                $result = Fingerprint_Verifier::verify(
                    $verify_file
                );

                if (
                    $verify_file !== $tmp_file &&
                    file_exists($verify_file)
                ) {
                    unlink($verify_file);
                }

            } else {

                $result = [
                    'valid' => false,
                    'status' => 'upload_error',
                    'warnings' => [],
                    'errors' => [
                        'No se ha seleccionado ningún archivo.'
                    ],
                    'record' => null,
                    'evidence' => [],
                ];
            }
        }

        ?>

        <div class="wrap">

            <h1>Verificar documento</h1>

            <p>
                Sube un documento generado por DWW Fingerprinting
                para comprobar su autenticidad.
            </p>

            <form
                method="post"
                enctype="multipart/form-data">

                <?php wp_nonce_field('dww_verify_document'); ?>

                <table class="form-table">

                    <tr>

                        <th>Documento</th>

                        <td>

                            <input
                                type="file"
                                name="document"
                                required>

                        </td>

                    </tr>

                </table>

                <p>

                    <input
                        type="submit"
                        name="dww_verify_document"
                        class="button button-primary"
                        value="Verificar documento">

                </p>

            </form>

            <?php

            if ($result !== null) {

                echo '<hr>';

                echo '<h2>Resultado</h2>';

                Verification_Report::render($result);
            }

            ?>

        </div>

        <?php
    }
}