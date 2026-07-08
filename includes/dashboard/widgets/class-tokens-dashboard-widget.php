<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Tokens_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'tokens';
    }

    public function title(): string
    {
        return 'Tokens';
    }

    public function render(): void
    {
        $stats = $this->stats();

        $this->card_start();

        $this->title('🔐 Tokens');

        $this->list_start();

        $this->list_item(
            '<strong>Total:</strong> ' .
                esc_html((string) ($stats['tokens'] ?? 0))
        );

        $this->list_item(
            '<strong>Activos:</strong> ' .
                esc_html((string) ($stats['active_tokens'] ?? 0))
        );

        $this->list_item(
            '<strong>Caducados:</strong> ' .
                esc_html((string) ($stats['expired_tokens'] ?? 0))
        );

        $this->list_end();

        $this->card_end();
    }
}
