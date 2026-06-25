<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Order_Downloads
{
    public static function init(): void
    {
        add_action(
            'woocommerce_order_details_after_order_table',
            [self::class, 'render_downloads'],
            20,
            1
        );
    }

    public static function render_downloads($order): void
    {
        if (!$order || !method_exists($order, 'get_id')) {
            return;
        }

        $order_id = (string) $order->get_id();

        Logger::log('Rendering downloads for order: ' . $order_id);

        $fingerprints = Fingerprint_DB::get_by_order($order_id);

        Logger::log(
            'Fingerprints found: ' . count($fingerprints)
        );

        if (empty($fingerprints)) {
            return;
        }

        echo '<section class="woocommerce-order-downloads dww-fingerprinting-downloads">';
        echo '<h2>Documentos protegidos</h2>';

        echo '<table class="woocommerce-table shop_table shop_table_responsive">';

        echo '<thead>';
        echo '<tr>';
        echo '<th>Producto</th>';
        echo '<th>Fingerprint</th>';
        echo '<th>Descarga</th>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';

        foreach ($fingerprints as $fingerprint) {

            Logger::log(
                'Rendering fingerprint: ' .
                $fingerprint->fingerprint_id
            );

            $token_row = Download_Token_DB::get_by_fingerprint(
                $fingerprint->fingerprint_id
            );

            echo '<tr>';

            echo '<td data-title="Producto">';
            echo esc_html($fingerprint->product_name);
            echo '</td>';

            echo '<td data-title="Fingerprint">';
            echo '<code>';
            echo esc_html($fingerprint->fingerprint_id);
            echo '</code>';
            echo '</td>';

            echo '<td data-title="Descarga">';

            if (
                $token_row &&
                Download_Token_DB::is_valid($token_row)
            ) {

                $download_url = add_query_arg(
                    [
                        'dww-download' => $token_row->token,
                    ],
                    home_url('/')
                );

                echo '<a class="button" href="' .
                    esc_url($download_url) .
                    '">⬇ Descargar</a>';

            } else {

                echo '<span style="color:#777;">No disponible</span>';

            }

            echo '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '</section>';
    }
}