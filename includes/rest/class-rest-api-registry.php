<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class REST_API_Registry
{
    public static function register(): void
    {
        REST_API_Manager::register(
            new Health_REST_Endpoint()
        );

        REST_API_Manager::register(
            new Stats_REST_Endpoint()
        );

        REST_API_Manager::register(

            new Verify_REST_Endpoint()

        );

        REST_API_Manager::register(
            new Fingerprint_REST_Endpoint()
        );
    }
}
