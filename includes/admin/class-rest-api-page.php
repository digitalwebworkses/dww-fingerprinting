<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class REST_API_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes.'));
        }

        self::handle_actions();

        $key = REST_API_Auth::get_key();

        ?>
        <div class="wrap">

            <h1>REST API</h1>

            <p>
                Configuración de la API REST de DWW Fingerprinting.
            </p>

            <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin:20px 0;">

                <h2 style="margin-top:0;">Estado</h2>

                <p>
                    <strong><?php echo $key !== '' ? '🟢 API activa' : '🔴 API sin clave'; ?></strong>
                </p>

                <p>
                    La API permite consultar el estado del sistema, fingerprints y verificar documentos desde aplicaciones externas.
                </p>

            </div>

            <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin:20px 0;">

                <h2 style="margin-top:0;">API Key</h2>

                <?php if ($key !== '') : ?>

                    <p>
                        <code><?php echo esc_html(self::masked_key($key)); ?></code>
                    </p>

                <?php else : ?>

                    <p>No hay ninguna API Key configurada.</p>

                <?php endif; ?>

                <form method="post">

                    <?php wp_nonce_field('dww_rest_api_regenerate_key'); ?>

                    <button
                        type="submit"
                        name="dww_regenerate_api_key"
                        value="1"
                        class="button button-primary">
                        Regenerar API Key
                    </button>

                </form>

            </div>

            <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin:20px 0;">

                <h2 style="margin-top:0;">Endpoints disponibles</h2>

                <ul>
                    <li><code>GET /wp-json/dww/v1/health</code></li>
                    <li><code>GET /wp-json/dww/v1/stats</code></li>
                    <li><code>GET /wp-json/dww/v1/fingerprint/{fingerprint_id}</code></li>
                    <li><code>POST /wp-json/dww/v1/verify</code></li>
                </ul>

            </div>

        </div>
        <?php
    }

    private static function handle_actions(): void
    {
        if (empty($_POST['dww_regenerate_api_key'])) {
            return;
        }

        check_admin_referer('dww_rest_api_regenerate_key');

        REST_API_Auth::set_key(
            REST_API_Auth::generate_key()
        );

        echo '<div class="notice notice-success"><p><strong>API Key regenerada correctamente.</strong></p></div>';
    }

    private static function masked_key(string $key): string
    {
        if (strlen($key) <= 16) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 8) .
            str_repeat('*', max(0, strlen($key) - 16)) .
            substr($key, -8);
    }
}