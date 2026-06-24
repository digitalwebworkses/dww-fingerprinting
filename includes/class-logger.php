<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Logger
{
    public static function log(string $message): void
    {
        error_log('[DWW FP] ' . $message);
    }
}