<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_040
{
    public static function up(): void
    {
        Fingerprint_Log_DB::create_table();
    }
}