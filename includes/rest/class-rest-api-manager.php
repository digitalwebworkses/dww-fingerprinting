<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class REST_API_Manager
{
    /**
     * @var REST_Endpoint_Interface[]
     */
    private static array $endpoints = [];

    public static function register(
        REST_Endpoint_Interface $endpoint
    ): void {

        self::$endpoints[] = $endpoint;
    }

    public static function boot(): void
    {
        foreach (self::$endpoints as $endpoint) {
            $endpoint->register();
        }
    }
}