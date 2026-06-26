<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprints_Page
{
    public static function render(): void
    {
        $action = isset($_GET['action'])
            ? sanitize_key(wp_unslash($_GET['action']))
            : '';

        if ($action === 'view') {
            Fingerprint_Detail_Page::render();
            return;
        }

        $search = isset($_GET['dww_fp_search'])
            ? sanitize_text_field(wp_unslash($_GET['dww_fp_search']))
            : '';

        $rows = Fingerprint_DB::search($search);

?>

<div class="wrap">

    <h1>Fingerprints</h1>

    <p>Registros de fingerprints generados por el sistema.</p>

    <form method="get" style="margin:20px 0;">

        <input
            type="hidden"
            name="page"
            value="<?php echo esc_attr(Admin_Menu::get_fingerprints_slug()); ?>">

        <input
            type="search"
            name="dww_fp_search"
            value="<?php echo esc_attr($search); ?>"
            placeholder="Buscar por fingerprint, email, pedido o producto"
            style="min-width:360px;">

        <button type="submit" class="button button-primary">
            Buscar
        </button>

        <?php if ($search !== '') : ?>

            <a
                class="button"
                href="<?php echo esc_url(admin_url('admin.php?page=' . Admin_Menu::get_fingerprints_slug())); ?>">

                Limpiar
            </a>

        <?php endif; ?>

    </form>

    <table class="widefat striped">

        <thead>
            <tr>
                <th>ID</th>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th>Fingerprint</th>
                <th>Descargas</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($rows)) : ?>

            <tr>
                <td colspan="9">No hay registros.</td>
            </tr>

        <?php else : ?>

            <?php foreach ($rows as $row) :

                $short_fp = strlen($row->fingerprint_id) > 18
                    ? substr($row->fingerprint_id, 0, 18) . '…'
                    : $row->fingerprint_id;

                $detail_url = add_query_arg(
                    [
                        'page'        => Admin_Menu::get_fingerprints_slug(),
                        'action'      => 'view',
                        'fingerprint' => $row->fingerprint_id,
                    ],
                    admin_url('admin.php')
                );

                $product = !empty($row->product_name)
                    ? $row->product_name
                    : 'Producto #' . $row->product_id;

            ?>

                <tr>

                    <td><?php echo esc_html($row->id); ?></td>

                    <td><?php echo esc_html($row->order_id); ?></td>

                    <td><?php echo esc_html($row->customer_email); ?></td>

                    <td><?php echo esc_html($product); ?></td>

                    <td>
                        <code title="<?php echo esc_attr($row->fingerprint_id); ?>">
                            <?php echo esc_html($short_fp); ?>
                        </code>
                    </td>

                    <td>
                        <?php

                        if ($row->max_downloads) {
                            echo esc_html(
                                (int) $row->downloads_count .
                                ' / ' .
                                (int) $row->max_downloads
                            );
                        } else {
                            echo '—';
                        }

                        ?>
                    </td>

                    <td>
                        <?php

                        if (!$row->expires_at) {

                            Admin_UI::badge('Sin token', 'info');

                        } elseif (!empty($row->revoked_at)) {

                            Admin_UI::badge('Revocado', 'danger');

                        } elseif (strtotime($row->expires_at) < time()) {

                            Admin_UI::badge('Caducado', 'danger');

                        } elseif (
                            (int) $row->downloads_count >=
                            (int) $row->max_downloads
                        ) {

                            Admin_UI::badge('Agotado', 'warning');

                        } else {

                            Admin_UI::badge('Activo', 'success');

                        }

                        ?>
                    </td>

                    <td><?php echo esc_html($row->created_at); ?></td>

                    <td>
                        <a
                            class="button button-secondary"
                            href="<?php echo esc_url($detail_url); ?>">

                            Ver
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<?php

    }
}