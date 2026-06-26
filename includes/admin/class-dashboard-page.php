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

        $total_documents = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$fingerprints_table}");
        $total_tokens = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$tokens_table}");
        $total_downloads = (int) $wpdb->get_var("SELECT COALESCE(SUM(downloads_count), 0) FROM {$tokens_table}");

        $active_tokens = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$tokens_table}
             WHERE downloads_count < max_downloads
             AND expires_at >= UTC_TIMESTAMP()"
        );

        $expired_tokens = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$tokens_table}
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

            <p>Sistema de trazabilidad documental.</p>

            <hr>

            <h2>Resumen</h2>

            <table class="widefat striped" style="max-width: 700px;">
                <tbody>
                    <tr>
                        <td><strong>Versión plugin</strong></td>
                        <td><?php echo esc_html(DWW_FP_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Versión base de datos</strong></td>
                        <td><?php echo esc_html(Migration_Manager::get_installed_version()); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Documentos protegidos</strong></td>
                        <td><?php echo esc_html($total_documents); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tokens creados</strong></td>
                        <td><?php echo esc_html($total_tokens); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tokens activos</strong></td>
                        <td><?php echo esc_html($active_tokens); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tokens caducados o agotados</strong></td>
                        <td><?php echo esc_html($expired_tokens); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Descargas realizadas</strong></td>
                        <td><?php echo esc_html($total_downloads); ?></td>
                    </tr>
                </tbody>
            </table>

            <br>

            <h2>Últimos documentos generados</h2>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Fingerprint</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($last_documents)) : ?>
                        <tr>
                            <td colspan="5">No hay documentos generados.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($last_documents as $row) : ?>
                            <?php
                            $product_name = property_exists($row, 'product_name') && $row->product_name !== ''
                                ? $row->product_name
                                : $row->product_id;
                            ?>
                            <tr>
                                <td><?php echo esc_html($row->order_id); ?></td>
                                <td><?php echo esc_html($row->customer_email); ?></td>
                                <td><?php echo esc_html($product_name); ?></td>
                                <td><code><?php echo esc_html($row->fingerprint_id); ?></code></td>
                                <td><?php echo esc_html($row->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
    }
}