<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Admin_Page
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'register_menu']);
    }

    public static function register_menu(): void
    {
        add_management_page(
            'DWW Fingerprinting',
            'DWW Fingerprinting',
            'manage_options',
            'dww-fingerprinting',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dww_fingerprints';

        $search = isset($_GET['dww_fp_search'])
            ? sanitize_text_field(wp_unslash($_GET['dww_fp_search']))
            : '';

        if ($search !== '') {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name}
             WHERE fingerprint_id LIKE %s
                OR customer_email LIKE %s
                OR order_id LIKE %s
             ORDER BY created_at DESC",
                    '%' . $wpdb->esc_like($search) . '%',
                    '%' . $wpdb->esc_like($search) . '%',
                    '%' . $wpdb->esc_like($search) . '%'
                )
            );
        } else {
            $rows = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC"
            );
        }

?>
        <div class="wrap">
            <h1>DWW Fingerprinting</h1>

            <p>
                Registros de fingerprints generados por el sistema.
            </p>

            <form method="get" style="margin: 16px 0;">
                <input type="hidden" name="page" value="dww-fingerprinting">

                <input
                    type="search"
                    name="dww_fp_search"
                    value="<?php echo esc_attr($search); ?>"
                    placeholder="Buscar por fingerprint, email o pedido"
                    style="min-width: 320px;">

                <button class="button button-primary" type="submit">
                    Buscar
                </button>

                <?php if ($search !== '') : ?>
                    <a class="button" href="<?php echo esc_url(admin_url('tools.php?page=dww-fingerprinting')); ?>">
                        Limpiar
                    </a>
                <?php endif; ?>
            </form>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fingerprint</th>
                        <th>Cliente</th>
                        <th>Pedido</th>
                        <th>Producto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (empty($rows)) : ?>

                        <tr>
                            <td colspan="6">
                                No hay registros.
                            </td>
                        </tr>

                    <?php else : ?>

                        <?php foreach ($rows as $row) : ?>

                            <tr>
                                <td><?php echo esc_html($row->id); ?></td>
                                <td><?php echo esc_html($row->fingerprint_id); ?></td>
                                <td><?php echo esc_html($row->customer_email); ?></td>
                                <td><?php echo esc_html($row->order_id); ?></td>
                                <td><?php echo esc_html($row->product_id); ?></td>
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
