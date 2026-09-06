<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Logger
{
    public static function log(string $message): void
    {
        if (!defined('DWW_FP_DEBUG') || DWW_FP_DEBUG !== true) {
            return;
        }

        error_log('[DWW FP] ' . $message);
    }
}
