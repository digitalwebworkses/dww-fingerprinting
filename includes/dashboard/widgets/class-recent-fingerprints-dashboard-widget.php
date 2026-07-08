<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Recent_Fingerprints_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'recent_fingerprints';
    }

    public function title(): string
    {
        return 'Últimos fingerprints';
    }

    public function render(): void
    {
        $items = $this->snapshot['recent_fingerprints'] ?? [];

        $this->card_start();

        $this->title('📄 Últimos fingerprints');

        if (empty($items)) {
            $this->empty_state('No hay fingerprints recientes.');
            $this->card_end();
            return;
        }

        $this->table_start();
        echo '<thead><tr>';
        echo '<th>Pedido</th>';
        echo '<th>Cliente</th>';
        echo '<th>Producto</th>';
        echo '<th>Formato</th>';
        echo '<th>Fingerprint</th>';
        echo '<th>Fecha</th>';
        echo '</tr></thead>';
        $this->table_body_start();

        foreach ($items as $item) {
            $fingerprint = (string) ($item->fingerprint_id ?? '');
            $short = strlen($fingerprint) > 18
                ? substr($fingerprint, 0, 18) . '…'
                : $fingerprint;

            echo '<tr>';
            echo '<td>' . esc_html((string) ($item->order_id ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($item->customer_email ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($item->product_name ?? $item->product_id ?? '')) . '</td>';
            echo '<td>' . esc_html(strtoupper((string) ($item->asset_format ?? ''))) . '</td>';
            echo '<td><code title="' . esc_attr($fingerprint) . '">' . esc_html($short) . '</code></td>';
            echo '<td>' . esc_html((string) ($item->created_at ?? '')) . '</td>';
            echo '</tr>';
        }

        $this->table_body_end();
        $this->table_end();

        $this->card_end();
    }
}