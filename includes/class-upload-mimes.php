<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Upload_Mimes
{
    public static function init(): void
    {
        add_filter('upload_mimes', [self::class, 'allow_custom_mimes']);
    }

    public static function allow_custom_mimes(array $mimes): array
    {
        $mimes['epub'] = 'application/epub+zip';

        return $mimes;
    }
}