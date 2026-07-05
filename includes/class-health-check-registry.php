<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Health_Check_Registry
{
    public static function register(): void
    {
        Health_Check_Manager::clear();

        Health_Check_Manager::register(new PHP_Health_Check());

        Health_Check_Manager::register(new WordPress_Health_Check());

        Health_Check_Manager::register(new WooCommerce_Health_Check());

        Health_Check_Manager::register(new Database_Health_Check());

        Health_Check_Manager::register(new Storage_Health_Check());

        Health_Check_Manager::register(new Handlers_Health_Check());

        Health_Check_Manager::register(new Formats_Health_Check());

        Health_Check_Manager::register(new Migrations_Health_Check());

        /**
         * Permite registrar checks adicionales.
         */
        do_action('dww_fingerprinting_register_health_checks');
    }
}