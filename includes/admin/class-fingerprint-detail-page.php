<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Detail_Page
{
    public static function render(): void
    {
        $fingerprint_id = isset($_GET['fingerprint'])
            ? sanitize_text_field(wp_unslash($_GET['fingerprint']))
            : '';

        if ($fingerprint_id === '') {

            self::render_error(
                'Fingerprint no especificado.'
            );

            return;
        }

        $fingerprint = Fingerprint_DB::get_by_fingerprint(
            $fingerprint_id
        );

        if (!$fingerprint) {

            self::render_error(
                'El fingerprint solicitado no existe.'
            );

            return;
        }

        $token = Download_Token_DB::get_by_fingerprint(
            $fingerprint->fingerprint_id
        );

        $back_url = admin_url(
            'admin.php?page=' . Admin_Menu::get_fingerprints_slug()
        );

        $source_filename = basename((string) $fingerprint->source_file);
        $generated_filename = basename((string) $fingerprint->generated_file);

        $download_url = $token
            ? add_query_arg(
                [
                    'dww-download' => $token->token,
                ],
                home_url('/')
            )
            : '';

?>
        <div class="wrap">
            <p>
                <a class="button" href="<?php echo esc_url($back_url); ?>">
                    ← Volver al listado
                </a>
            </p>

            <h1>Detalle del fingerprint</h1>

            <p>
                <code><?php echo esc_html($fingerprint->fingerprint_id); ?></code>
            </p>

            <?php

            Admin_UI::section(
                'Documento',
                function () use ($fingerprint, $source_filename, $generated_filename) {

            ?>

                <table class="widefat striped">

                    <tbody>

                        <tr>
                            <td><strong>Cliente</strong></td>
                            <td><?php echo esc_html($fingerprint->customer_email); ?></td>
                        </tr>

                        <tr>
                            <td><strong>Pedido</strong></td>
                            <td><?php echo esc_html($fingerprint->order_id); ?></td>
                        </tr>

                        <tr>
                            <td><strong>Producto</strong></td>
                            <td>
                                <?php

                                $product = $fingerprint->product_name;

                                if ($product === '') {

                                    $product = 'Producto #' . $fingerprint->product_id;
                                }

                                echo esc_html($product);

                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Archivo origen</strong></td>
                            <td>
                                <code title="<?php echo esc_attr($fingerprint->source_file); ?>">
                                    <?php echo esc_html($source_filename); ?>
                                </code>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Archivo generado</strong></td>
                            <td>
                                <code title="<?php echo esc_attr($fingerprint->generated_file); ?>">
                                    <?php echo esc_html($generated_filename); ?>
                                </code>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Fecha</strong></td>
                            <td><?php echo esc_html($fingerprint->created_at); ?></td>
                        </tr>

                    </tbody>

                </table>

            <?php

                }
            );

            Admin_UI::section(
                'Token de descarga',
                function () use ($token) {

                    if (!$token) {

                        Admin_UI::empty_state(
                            'No existe token',
                            'Este documento todavía no dispone de un token de descarga.'
                        );

                        return;
                    }

            ?>

                <table class="widefat striped">

                    <tbody>

                        <tr>

                            <td width="220"><strong>Token</strong></td>

                            <td>

                                <code>

                                    <?php

                                    $short_token = strlen($token->token) > 24
                                        ? substr($token->token, 0, 24) . '…'
                                        : $token->token;

                                    ?>

                                    <code title="<?php echo esc_attr($token->token); ?>">

                                        <?php echo esc_html($short_token); ?>

                                    </code>

                                </code>

                            </td>

                        </tr>

                        <tr>
                            <td><strong>Estado</strong></td>

                            <td>

                                <?php

                                if (strtotime($token->expires_at) < time()) {

                                    Admin_UI::badge(
                                        'Caducado',
                                        'danger'
                                    );

                                    $status_message = 'La fecha de expiración ha finalizado.';
                                } elseif (
                                    (int) $token->downloads_count >=
                                    (int) $token->max_downloads
                                ) {

                                    Admin_UI::badge(
                                        'Agotado',
                                        'warning'
                                    );

                                    $status_message = 'Se alcanzó el número máximo de descargas.';
                                } else {

                                    Admin_UI::badge(
                                        'Activo',
                                        'success'
                                    );

                                    $status_message = 'El documento todavía puede descargarse.';
                                }

                                ?>

                                <br>

                                <small style="display:block;margin-top:6px;color:#646970;">

                                    <?php echo esc_html($status_message); ?>

                                </small>

                            </td>

                        </tr>

                        <tr>

                            <td><strong>Descargas</strong></td>

                            <td>

                                <?php

                                echo esc_html(
                                    $token->downloads_count .
                                        ' / ' .
                                        $token->max_downloads
                                );

                                ?>

                            </td>

                        </tr>

                        <tr>

                            <td><strong>Caduca</strong></td>

                            <td>

                                <?php echo esc_html($token->expires_at); ?>

                            </td>

                        </tr>

                    </tbody>

                </table>

            <?php

                }
            );

            Admin_UI::section(
                'Acciones',
                function () use ($download_url, $token) {

            ?>

                <p>

                    <?php if ($token && Download_Token_DB::is_valid($token)) : ?>

                        <a
                            class="button button-primary"
                            href="<?php echo esc_url($download_url); ?>">

                            Descargar PDF

                        </a>

                    <?php else : ?>

                        <button
                            class="button button-primary"
                            disabled>

                            Descargar PDF

                        </button>

                    <?php endif; ?>

                    <button
                        class="button"
                        disabled>

                        Regenerar token

                    </button>

                    <button
                        class="button"
                        disabled>

                        Revocar

                    </button>

                </p>

                <p>

                    <em>

                        Regenerar token y revocar estarán disponibles en una próxima versión.

                    </em>

                </p>

            <?php

                }
            );

            ?>

        </div>

    <?php

    }

    private static function render_error(
        string $message
    ): void {

    ?>

        <div class="wrap">

            <h1>DWW Fingerprinting</h1>

            <?php

            Admin_UI::empty_state(
                'No se ha podido abrir el fingerprint.',
                $message
            );

            ?>

        </div>

<?php

    }
}
