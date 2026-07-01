<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Page
{
    public static function render(): void
    {
        global $wpdb;

        $fingerprints_table = Fingerprint_DB::get_table_name();
        $tokens_table = Download_Token_DB::get_table_name();

        $total_documents = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$fingerprints_table}"
        );

        $total_tokens = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$tokens_table}"
        );

        $total_downloads = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(downloads_count),0)
             FROM {$tokens_table}"
        );

        $active_tokens = (int) $wpdb->get_var(
            "SELECT COUNT(*)
             FROM {$tokens_table}
             WHERE downloads_count < max_downloads
             AND expires_at >= UTC_TIMESTAMP()"
        );

        $expired_tokens = (int) $wpdb->get_var(
            "SELECT COUNT(*)
             FROM {$tokens_table}
             WHERE downloads_count >= max_downloads
             OR expires_at < UTC_TIMESTAMP()"
        );

        $last_documents = $wpdb->get_results(
            "SELECT *
             FROM {$fingerprints_table}
             ORDER BY created_at DESC
             LIMIT 10"
        );

?>

        <div class="wrap">

            <h1>DWW Fingerprinting</h1>

            <p>
                Sistema de trazabilidad documental para WooCommerce.
            </p>

            <div class="dww-dashboard-top">

                <div class="dww-dashboard-main">

                    <?php

                    Admin_UI::section(
                        'Resumen',
                        function () use (
                            $total_documents,
                            $total_downloads,
                            $active_tokens,
                            $expired_tokens
                        ) {

                            Admin_UI::stat_card(
                                'Archivos protegidos',
                                (string) $total_documents,
                                'blue'
                            );

                            Admin_UI::stat_card(
                                'Descargas',
                                (string) $total_downloads,
                                'green'
                            );

                            Admin_UI::stat_card(
                                'Tokens activos',
                                (string) $active_tokens,
                                'orange'
                            );

                            Admin_UI::stat_card(
                                'Caducados',
                                (string) $expired_tokens,
                                'red'
                            );
                        }
                    );

                    ?>

                </div>

                <aside class="dww-dashboard-side">

                    <?php

                    Admin_UI::section(
                        'Información del sistema',
                        function () use ($total_tokens) {

                    ?>

                        <table class="widefat striped">

                            <tbody>

                                <tr>
                                    <td><strong>Versión del plugin</strong></td>
                                    <td><?php echo esc_html(DWW_FP_VERSION); ?></td>
                                </tr>

                                <tr>
                                    <td><strong>Versión BD</strong></td>
                                    <td><?php echo esc_html(Migration_Manager::get_installed_version()); ?></td>
                                </tr>

                                <tr>
                                    <td><strong>PHP</strong></td>
                                    <td><?php echo esc_html(PHP_VERSION); ?></td>
                                </tr>

                                <tr>
                                    <td><strong>WordPress</strong></td>
                                    <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                                </tr>

                                <tr>
                                    <td><strong>WooCommerce</strong></td>
                                    <td>

                                        <?php

                                        echo defined('WC_VERSION')
                                            ? esc_html(WC_VERSION)
                                            : 'No instalado';

                                        ?>

                                    </td>

                                </tr>

                                <tr>
                                    <td><strong>Tokens</strong></td>
                                    <td><?php echo esc_html($total_tokens); ?></td>
                                </tr>

                            </tbody>

                        </table>

                    <?php

                        }
                    );

                    Admin_UI::section(
                        'Motor de fingerprinting',
                        function () {

                            $handler_names = Fingerprint_Manager::get_handler_names();
                            $extensions = Fingerprint_Manager::get_supported_extensions();
                            $mime_types = Fingerprint_Manager::get_supported_mime_types();

                    ?>

                        <table class="widefat striped">

                            <tbody>

                                <tr>
                                    <td><strong>Estado</strong></td>
                                    <td>
                                        <?php Admin_UI::badge('Activo', 'success'); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Handlers</strong></td>
                                    <td><?php echo esc_html((string) Fingerprint_Manager::count()); ?></td>
                                </tr>

                                <tr>
                                    <td><strong>Formatos</strong></td>
                                    <td>
                                        <?php echo esc_html(implode(', ', $handler_names)); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Extensiones</strong></td>
                                    <td>
                                        <code><?php echo esc_html(implode(', ', $extensions)); ?></code>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>MIME types</strong></td>
                                    <td>
                                        <code><?php echo esc_html(implode(', ', $mime_types)); ?></code>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    <?php

                        }
                    );

                    ?>

                </aside>

            </div>

            <?php

            Admin_UI::section(
                'Últimos archivos generados',
                function () use ($last_documents) {

                    if (empty($last_documents)) {

                        Admin_UI::empty_state(
                            'Todavía no hay archivos.',
                            'Los archivos protegidos aparecerán aquí automáticamente.'
                        );

                        return;
                    }

            ?>

                <table class="widefat striped">

                    <thead>

                        <tr>

                            <th>Pedido</th>

                            <th>Cliente</th>

                            <th>Producto</th>

                            <th>Formato</th>

                            <th>Fingerprint</th>

                            <th>Fecha</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($last_documents as $row) :

                            $product_name = !empty($row->product_name)
                                ? $row->product_name
                                : $row->product_id;

                            $short_fp = strlen($row->fingerprint_id) > 18
                                ? substr($row->fingerprint_id, 0, 18) . '…'
                                : $row->fingerprint_id;

                            $asset_format = !empty($row->asset_format)
                                ? strtoupper((string) $row->asset_format)
                                : strtoupper(pathinfo((string) $row->generated_file, PATHINFO_EXTENSION));

                            if ($asset_format === '') {
                                $asset_format = 'ARCHIVO';
                            }

                        ?>

                            <tr>

                                <td><?php echo esc_html($row->order_id); ?></td>

                                <td><?php echo esc_html($row->customer_email); ?></td>

                                <td><?php echo esc_html($product_name); ?></td>

                                <td><?php echo esc_html($asset_format); ?></td>

                                <td>

                                    <code title="<?php echo esc_attr($row->fingerprint_id); ?>">

                                        <?php echo esc_html($short_fp); ?>

                                    </code>

                                </td>

                                <td><?php echo esc_html($row->created_at); ?></td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php

                }
            );

            ?>

        </div>

<?php

    }
}
