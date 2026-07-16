<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Activity_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'activity';
    }

    public function title(): string
    {
        return 'Actividad reciente';
    }

    public function render(): void
    {
        $items = $this->recent_fingerprints();

        $this->card_start();

        $this->card_title('🕒 Actividad reciente');

        if (empty($items)) {
            $this->empty_state('No hay actividad reciente.');
            $this->card_end();
            return;
        }

        $this->list_start();

        foreach (array_slice($items, 0, 5) as $item) {
            $format = strtoupper((string) ($item->asset_format ?? 'DOC'));
            $order = (string) ($item->order_id ?? '—');
            $product = (string) ($item->product_name ?? 'Producto');
            $date = (string) ($item->created_at ?? '');

            $this->list_item(
                '<strong>' .
                    esc_html($format) .
                    '</strong> generado para pedido <strong>#' .
                    esc_html($order) .
                    '</strong><br><span class="dww-dashboard-muted">' .
                    esc_html($product) .
                    ' · ' .
                    esc_html($date) .
                    '</span>'
            );
        }

        $this->list_end();

        $this->card_end();
    }
}
