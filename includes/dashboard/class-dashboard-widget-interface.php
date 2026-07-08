<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

interface Dashboard_Widget_Interface
{
    public function id(): string;

    public function title(): string;

    public function set_snapshot(array $snapshot): void;

    public function render(): void;
}