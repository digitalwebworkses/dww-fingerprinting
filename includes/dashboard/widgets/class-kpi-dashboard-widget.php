<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class KPI_Dashboard_Widget extends Dashboard_Widget_Abstract
{
    public function id(): string
    {
        return 'kpi';
    }

    public function title(): string
    {
        return 'Indicadores principales';
    }

    public function render(): void
    {
        $stats = $this->stats();

        $this->grid_start();

        $this->metric((string) ($stats['fingerprints'] ?? 0), 'Fingerprints', '📄');
        $this->metric((string) ($stats['downloads'] ?? 0), 'Descargas', '📥');
        $this->metric((string) ($stats['tokens'] ?? 0), 'Tokens', '🔐');
        $this->metric((string) ($stats['formats'] ?? 0), 'Formatos', '🧩');

        $this->grid_end();
    }
}
