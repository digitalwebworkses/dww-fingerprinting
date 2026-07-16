<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprints_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes.'));
        }

        $action = isset($_GET['action'])
            ? sanitize_key(wp_unslash($_GET['action']))
            : '';

        if ($action === 'view') {
            Fingerprint_Detail_Page::render();
            return;
        }

        $search = isset($_GET['dww_fp_search'])
            ? sanitize_text_field(
                wp_unslash($_GET['dww_fp_search'])
            )
            : '';

        $rows = Fingerprint_DB::search($search);
        $results_count = count($rows);

        ?>

        <div class="wrap dww-fingerprints-page">

            <h1>Fingerprints generados</h1>

            <p>
                Consulta los documentos protegidos, su estado de descarga
                y la trazabilidad asociada a cada pedido.
            </p>

            <form
                method="get"
                class="dww-fingerprints-search">

                <input
                    type="hidden"
                    name="page"
                    value="<?php echo esc_attr(
                        Admin_Menu::get_fingerprints_slug()
                    ); ?>">

                <input
                    type="search"
                    name="dww_fp_search"
                    value="<?php echo esc_attr($search); ?>"
                    placeholder="Buscar por fingerprint, cliente, pedido o producto"
                    class="regular-text">

                <button
                    type="submit"
                    class="button button-primary">
                    Buscar
                </button>

                <?php if ($search !== '') : ?>

                    <a
                        class="button"
                        href="<?php echo esc_url(
                            admin_url(
                                'admin.php?page=' .
                                Admin_Menu::get_fingerprints_slug()
                            )
                        ); ?>">
                        Limpiar búsqueda
                    </a>

                <?php endif; ?>

            </form>

            <p class="description">

                <?php

                if ($search !== '') {
                    echo esc_html(
                        sprintf(
                            '%d resultado(s) para “%s”.',
                            $results_count,
                            $search
                        )
                    );
                } else {
                    echo esc_html(
                        sprintf(
                            '%d fingerprint(s) registrado(s).',
                            $results_count
                        )
                    );
                }

                ?>

            </p>

            <?php if (empty($rows)) : ?>

                <?php

                Admin_UI::empty_state(
                    $search !== ''
                        ? 'No se han encontrado resultados.'
                        : 'Todavía no hay fingerprints registrados.',
                    $search !== ''
                        ? 'Prueba con otro fingerprint, email, número de pedido o producto.'
                        : 'Los documentos protegidos aparecerán aquí automáticamente cuando se procese una compra.'
                );

                ?>

            <?php else : ?>

                <div class="dww-admin-table-wrap">

                    <table class="widefat striped dww-admin-table">

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Formato</th>
                                <th>Fingerprint</th>
                                <th>Descargas</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($rows as $row) :

                                $fingerprint_id = (string) (
                                    $row->fingerprint_id ?? ''
                                );

                                $short_fp = strlen($fingerprint_id) > 18
                                    ? substr($fingerprint_id, 0, 18) . '…'
                                    : $fingerprint_id;

                                $detail_url = add_query_arg(
                                    [
                                        'page' => Admin_Menu::get_fingerprints_slug(),
                                        'action' => 'view',
                                        'fingerprint' => $fingerprint_id,
                                    ],
                                    admin_url('admin.php')
                                );

                                $product = !empty($row->product_name)
                                    ? (string) $row->product_name
                                    : 'Producto #' .
                                        (string) $row->product_id;

                                $asset_format = !empty($row->asset_format)
                                    ? strtoupper(
                                        (string) $row->asset_format
                                    )
                                    : strtoupper(
                                        pathinfo(
                                            (string) $row->generated_file,
                                            PATHINFO_EXTENSION
                                        )
                                    );

                                if ($asset_format === '') {
                                    $asset_format = 'ARCHIVO';
                                }

                                ?>

                                <tr>

                                    <td>
                                        <?php echo esc_html(
                                            (string) $row->id
                                        ); ?>
                                    </td>

                                    <td>
                                        <strong>
                                            #<?php echo esc_html(
                                                (string) $row->order_id
                                            ); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?php echo esc_html(
                                            (string) $row->customer_email
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo esc_html($product); ?>
                                    </td>

                                    <td>
                                        <?php Admin_UI::badge(
                                            $asset_format,
                                            'info'
                                        ); ?>
                                    </td>

                                    <td>

                                        <code
                                            title="<?php echo esc_attr(
                                                $fingerprint_id
                                            ); ?>">

                                            <?php echo esc_html($short_fp); ?>

                                        </code>

                                    </td>

                                    <td>

                                        <?php

                                        if (!empty($row->max_downloads)) {
                                            echo esc_html(
                                                (string) (int) $row->downloads_count .
                                                ' / ' .
                                                (string) (int) $row->max_downloads
                                            );
                                        } else {
                                            echo '—';
                                        }

                                        ?>

                                    </td>

                                    <td>
                                        <?php self::render_status($row); ?>
                                    </td>

                                    <td>
                                        <?php echo esc_html(
                                            (string) $row->created_at
                                        ); ?>
                                    </td>

                                    <td>

                                        <a
                                            class="button button-secondary"
                                            href="<?php echo esc_url(
                                                $detail_url
                                            ); ?>">
                                            Ver detalle
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

        <?php
    }

    private static function render_status(object $row): void
    {
        if (empty($row->expires_at)) {
            Admin_UI::badge('Sin token', 'info');
            return;
        }

        if (!empty($row->revoked_at)) {
            Admin_UI::badge('Revocado', 'danger');
            return;
        }

        if (strtotime((string) $row->expires_at) < time()) {
            Admin_UI::badge('Caducado', 'danger');
            return;
        }

        if (
            (int) $row->downloads_count >=
            (int) $row->max_downloads
        ) {
            Admin_UI::badge('Agotado', 'warning');
            return;
        }

        Admin_UI::badge('Activo', 'success');
    }
}