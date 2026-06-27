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
            self::render_error('Fingerprint no especificado.');
            return;
        }

        self::maybe_regenerate_token($fingerprint_id);
        self::maybe_revoke_token($fingerprint_id);

        $fingerprint = Fingerprint_DB::get_by_fingerprint($fingerprint_id);

        if (!$fingerprint) {
            self::render_error('El fingerprint solicitado no existe.');
            return;
        }

        $token = Download_Token_DB::get_by_fingerprint($fingerprint->fingerprint_id);
        $token_history = Download_Token_DB::get_all_by_fingerprint($fingerprint->fingerprint_id);
        $activity_logs = Fingerprint_Log_DB::get_by_fingerprint($fingerprint->fingerprint_id);

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

        $current_url = add_query_arg(
            [
                'page' => Admin_Menu::get_fingerprints_slug(),
                'action' => 'view',
                'fingerprint' => $fingerprint->fingerprint_id,
            ],
            admin_url('admin.php')
        );

?>

        <div class="wrap">

            <?php self::render_admin_notice(); ?>

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

                    $short_token = strlen($token->token) > 24
                        ? substr($token->token, 0, 24) . '…'
                        : $token->token;

            ?>

                <table class="widefat striped">
                    <tbody>
                        <tr>
                            <td width="220"><strong>Token</strong></td>
                            <td>
                                <code title="<?php echo esc_attr($token->token); ?>">
                                    <?php echo esc_html($short_token); ?>
                                </code>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Estado</strong></td>
                            <td>
                                <?php self::render_token_status($token); ?>
                            </td>
                        </tr>

                        <?php if (!empty($token->revoked_at)) : ?>
                            <tr>
                                <td><strong>Revocado</strong></td>
                                <td><?php echo esc_html($token->revoked_at); ?></td>
                            </tr>
                        <?php endif; ?>

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
                            <td><?php echo esc_html($token->expires_at); ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php
                }
            );

            Admin_UI::section(
                'Historial de tokens',
                function () use ($token_history) {
                    if (empty($token_history)) {
                        Admin_UI::empty_state(
                            'Sin historial',
                            'Todavía no hay tokens asociados a este fingerprint.'
                        );

                        return;
                    }

            ?>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Estado</th>
                            <th>Descargas</th>
                            <th>Caduca</th>
                            <th>Revocado</th>
                            <th>Creado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($token_history as $index => $history_token) :

                            $short_history_token = strlen($history_token->token) > 18
                                ? substr($history_token->token, 0, 18) . '…'
                                : $history_token->token;

                        ?>

                            <tr<?php echo $index === 0 ? ' class="dww-current-token"' : ''; ?>>
                                <td>
                                    <?php if ($index === 0) : ?>
                                        <span class="dww-badge-current">Actual</span><br>
                                    <?php endif; ?>

                                    <code title="<?php echo esc_attr($history_token->token); ?>">
                                        <?php echo esc_html($short_history_token); ?>
                                    </code>
                                </td>

                                <td>
                                    <?php self::render_token_badge($history_token); ?>
                                </td>

                                <td>
                                    <?php
                                    echo esc_html(
                                        $history_token->downloads_count .
                                            ' / ' .
                                            $history_token->max_downloads
                                    );
                                    ?>
                                </td>

                                <td><?php echo esc_html($history_token->expires_at); ?></td>

                                <td>
                                    <?php echo !empty($history_token->revoked_at)
                                        ? esc_html($history_token->revoked_at)
                                        : '—'; ?>
                                </td>

                                <td><?php echo esc_html($history_token->created_at); ?></td>

                                </tr>

                            <?php endforeach; ?>
                    </tbody>
                </table>

            <?php
                }
            );

            Admin_UI::section(
                'Historial de actividad',
                function () use ($activity_logs) {
                    if (empty($activity_logs)) {
                        Admin_UI::empty_state(
                            'Sin actividad registrada',
                            'Todavía no hay eventos de auditoría asociados a este fingerprint.'
                        );

                        return;
                    }

            ?>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Acción</th>
                            <th>Mensaje</th>
                            <th>Usuario</th>
                            <th>IP</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($activity_logs as $log) : ?>

                            <tr>
                                <td><?php echo esc_html($log->created_at); ?></td>

                                <td>
                                    <code><?php echo esc_html($log->action); ?></code>
                                </td>

                                <td><?php echo esc_html($log->message); ?></td>

                                <td>
                                    <?php echo esc_html((string) $log->user_id); ?>
                                </td>

                                <td>
                                    <?php echo esc_html((string) $log->ip_address); ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php
                }
            );

            Admin_UI::section(
                'Acciones',
                function () use ($download_url, $token, $fingerprint, $current_url) {
            ?>

                <p>
                    <?php if ($token && Download_Token_DB::is_valid($token)) : ?>

                        <a
                            class="button button-primary"
                            href="<?php echo esc_url($download_url); ?>">

                            Descargar PDF
                        </a>

                    <?php else : ?>

                        <button class="button button-primary" disabled>
                            Descargar PDF
                        </button>

                    <?php endif; ?>

                <form
                    method="post"
                    action="<?php echo esc_url($current_url); ?>"
                    style="display:inline-block;margin:0 4px;">

                    <?php wp_nonce_field('dww_fp_regenerate_token', 'dww_fp_nonce'); ?>

                    <input type="hidden" name="dww_fp_action" value="regenerate_token">
                    <input type="hidden" name="fingerprint" value="<?php echo esc_attr($fingerprint->fingerprint_id); ?>">

                    <button
                        type="submit"
                        class="button"
                        onclick="return confirm('¿Regenerar el token de descarga? El token anterior quedará revocado.');">

                        Regenerar token
                    </button>

                </form>

                <?php if ($token && Download_Token_DB::is_valid($token)) : ?>

                    <form
                        method="post"
                        action="<?php echo esc_url($current_url); ?>"
                        style="display:inline-block;margin:0 4px;">

                        <?php wp_nonce_field('dww_fp_revoke_token', 'dww_fp_nonce'); ?>

                        <input type="hidden" name="dww_fp_action" value="revoke_token">
                        <input type="hidden" name="fingerprint" value="<?php echo esc_attr($fingerprint->fingerprint_id); ?>">

                        <button
                            type="submit"
                            class="button"
                            onclick="return confirm('¿Revocar este token? El enlace de descarga dejará de funcionar.');">

                            Revocar
                        </button>

                    </form>

                <?php else : ?>

                    <button class="button" disabled>
                        Revocar
                    </button>

                <?php endif; ?>
                </p>

            <?php
                }
            );

            ?>

        </div>

    <?php
    }

    private static function maybe_regenerate_token(string $fingerprint_id): void
    {
        if (
            empty($_POST['dww_fp_action']) ||
            $_POST['dww_fp_action'] !== 'regenerate_token'
        ) {
            return;
        }

        if (!self::validate_action_request($fingerprint_id, 'dww_fp_regenerate_token')) {
            return;
        }

        $new_token = Download_Token_DB::regenerate_token(
            $fingerprint_id,
            72,
            3
        );

        Logger::log(
            $new_token
                ? 'Download token regenerated for fingerprint: ' . $fingerprint_id
                : 'Download token regeneration failed for fingerprint: ' . $fingerprint_id
        );

        self::redirect_after_action(
            $fingerprint_id,
            $new_token ? 'token_regenerated' : 'token_regeneration_failed'
        );
    }

    private static function maybe_revoke_token(string $fingerprint_id): void
    {
        if (
            empty($_POST['dww_fp_action']) ||
            $_POST['dww_fp_action'] !== 'revoke_token'
        ) {
            return;
        }

        if (!self::validate_action_request($fingerprint_id, 'dww_fp_revoke_token')) {
            return;
        }

        $revoked = Download_Token_DB::revoke_by_fingerprint($fingerprint_id);

        Logger::log(
            $revoked
                ? 'Download token revoked for fingerprint: ' . $fingerprint_id
                : 'Download token revocation failed for fingerprint: ' . $fingerprint_id
        );

        self::redirect_after_action(
            $fingerprint_id,
            $revoked ? 'token_revoked' : 'token_revocation_failed'
        );
    }

    private static function validate_action_request(
        string $fingerprint_id,
        string $nonce_action
    ): bool {
        if (
            empty($_POST['dww_fp_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['dww_fp_nonce'])),
                $nonce_action
            )
        ) {
            self::redirect_after_action(
                $fingerprint_id,
                'invalid_nonce'
            );
        }

        if (!current_user_can('manage_options')) {
            self::redirect_after_action(
                $fingerprint_id,
                'forbidden'
            );
        }

        $posted_fingerprint = isset($_POST['fingerprint'])
            ? sanitize_text_field(wp_unslash($_POST['fingerprint']))
            : '';

        if ($posted_fingerprint !== $fingerprint_id) {
            self::redirect_after_action(
                $fingerprint_id,
                'fingerprint_mismatch'
            );
        }

        return true;
    }

    private static function render_token_status(object $token): void
    {
        self::render_token_badge($token);

        if (!empty($token->revoked_at)) {
            $status_message = 'Este token ha sido revocado manualmente.';
        } elseif (strtotime($token->expires_at) < time()) {
            $status_message = 'La fecha de expiración ha finalizado.';
        } elseif ((int) $token->downloads_count >= (int) $token->max_downloads) {
            $status_message = 'Se alcanzó el número máximo de descargas.';
        } else {
            $status_message = 'El documento todavía puede descargarse.';
        }

        echo '<br>';
        echo '<small style="display:block;margin-top:6px;color:#646970;">';
        echo esc_html($status_message);
        echo '</small>';
    }

    private static function render_token_badge(object $token): void
    {
        if (!empty($token->revoked_at)) {
            Admin_UI::badge('Revocado', 'danger');
            return;
        }

        if (strtotime($token->expires_at) < time()) {
            Admin_UI::badge('Caducado', 'danger');
            return;
        }

        if ((int) $token->downloads_count >= (int) $token->max_downloads) {
            Admin_UI::badge('Agotado', 'warning');
            return;
        }

        Admin_UI::badge('Activo', 'success');
    }

    private static function redirect_after_action(
        string $fingerprint_id,
        string $message
    ): void {
        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => Admin_Menu::get_fingerprints_slug(),
                    'action' => 'view',
                    'fingerprint' => $fingerprint_id,
                    'dww_fp_message' => $message,
                ],
                admin_url('admin.php')
            )
        );

        exit;
    }

    private static function render_admin_notice(): void
    {
        $message = isset($_GET['dww_fp_message'])
            ? sanitize_key(wp_unslash($_GET['dww_fp_message']))
            : '';

        if ($message === '') {
            return;
        }

        $messages = [
            'token_regenerated' => [
                'type' => 'success',
                'text' => 'Token regenerado correctamente.',
            ],
            'token_regeneration_failed' => [
                'type' => 'error',
                'text' => 'No se pudo regenerar el token.',
            ],
            'token_revoked' => [
                'type' => 'success',
                'text' => 'Token revocado correctamente.',
            ],
            'token_revocation_failed' => [
                'type' => 'error',
                'text' => 'No se pudo revocar el token.',
            ],
            'invalid_nonce' => [
                'type' => 'error',
                'text' => 'La solicitud no es válida. Vuelve a intentarlo.',
            ],
            'forbidden' => [
                'type' => 'error',
                'text' => 'No tienes permisos para realizar esta acción.',
            ],
            'fingerprint_mismatch' => [
                'type' => 'error',
                'text' => 'El fingerprint enviado no coincide con el registro actual.',
            ],
        ];

        if (!isset($messages[$message])) {
            return;
        }

        echo '<div class="notice notice-' . esc_attr($messages[$message]['type']) . ' is-dismissible">';
        echo '<p>' . esc_html($messages[$message]['text']) . '</p>';
        echo '</div>';
    }

    private static function render_error(string $message): void
    {
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
