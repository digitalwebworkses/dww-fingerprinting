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
        $is_active = $key !== '';

?>

        <div class="wrap dww-rest-api-page">

            <h1>API REST</h1>

            <p>
                Configura el acceso externo a DWW Fingerprinting
                y consulta los endpoints disponibles.
            </p>

            <div class="dww-rest-api-grid">

                <section class="dww-admin-card">

                    <h2>Estado</h2>

                    <p>
                        <?php

                        Admin_UI::badge(
                            $is_active ? 'API activa' : 'API sin clave',
                            $is_active ? 'success' : 'danger'
                        );

                        ?>
                    </p>

                    <p>
                        La API permite consultar el estado del sistema,
                        obtener fingerprints y verificar documentos
                        desde aplicaciones externas.
                    </p>

                </section>

                <section class="dww-admin-card">

                    <h2>API Key</h2>

                    <?php if ($is_active) : ?>

                        <p>
                            <code class="dww-rest-api-key">
                                <?php echo esc_html(
                                    self::masked_key($key)
                                ); ?>
                            </code>
                        </p>

                        <p class="description">
                            La clave completa no se muestra por seguridad.
                            Regenerarla invalidará inmediatamente la anterior.
                        </p>

                    <?php else : ?>

                        <?php

                        Admin_UI::empty_state(
                            'No hay ninguna API Key configurada.',
                            'Genera una clave para habilitar el acceso autenticado a la API.'
                        );

                        ?>

                    <?php endif; ?>

                    <form
                        method="post"
                        class="dww-rest-api-actions">

                        <?php wp_nonce_field(
                            'dww_rest_api_regenerate_key'
                        ); ?>

                        <button
                            type="submit"
                            name="dww_regenerate_api_key"
                            value="1"
                            class="button button-primary"
                            onclick="return confirm('¿Regenerar la API Key? La clave anterior dejará de funcionar inmediatamente.');">

                            <?php echo $is_active
                                ? 'Regenerar API Key'
                                : 'Generar API Key'; ?>

                        </button>

                    </form>

                </section>

            </div>

            <section class="dww-admin-card dww-rest-api-endpoints">

                <h2>Endpoints disponibles</h2>

                <div class="dww-rest-endpoint-list">

                    <?php

                    self::render_endpoint(
                        'GET',
                        '/wp-json/dww/v1/health',
                        'Consulta el estado general del sistema.'
                    );

                    self::render_endpoint(
                        'GET',
                        '/wp-json/dww/v1/stats',
                        'Devuelve estadísticas generales del plugin.'
                    );

                    self::render_endpoint(
                        'GET',
                        '/wp-json/dww/v1/fingerprint/{fingerprint_id}',
                        'Obtiene el registro asociado a un fingerprint.'
                    );

                    self::render_endpoint(
                        'POST',
                        '/wp-json/dww/v1/verify',
                        'Verifica la autenticidad e integridad de un documento.'
                    );

                    ?>

                </div>

                <p class="description">
                    Envía la API Key mediante la cabecera
                    <code>X-DWW-API-Key</code>.
                </p>

            </section>

        </div>

<?php
    }

    private static function handle_actions(): void
    {
        if (empty($_POST['dww_regenerate_api_key'])) {
            return;
        }

        check_admin_referer(
            'dww_rest_api_regenerate_key'
        );

        REST_API_Auth::set_key(
            REST_API_Auth::generate_key()
        );

        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>';
        echo 'API Key generada correctamente.';
        echo '</strong></p>';
        echo '</div>';
    }

    private static function render_endpoint(
        string $method,
        string $path,
        string $description
    ): void {
        $badge_type = $method === 'POST'
            ? 'warning'
            : 'info';

        echo '<div class="dww-rest-endpoint">';

        echo '<div class="dww-rest-endpoint-header">';

        Admin_UI::badge(
            $method,
            $badge_type
        );

        echo '<code>' .
            esc_html($path) .
            '</code>';

        echo '</div>';

        echo '<p>' .
            esc_html($description) .
            '</p>';

        echo '</div>';
    }

    private static function masked_key(
        string $key
    ): string {
        if (strlen($key) <= 16) {
            return str_repeat(
                '*',
                strlen($key)
            );
        }

        return substr($key, 0, 8) .
            str_repeat(
                '*',
                max(0, strlen($key) - 16)
            ) .
            substr($key, -8);
    }
}
