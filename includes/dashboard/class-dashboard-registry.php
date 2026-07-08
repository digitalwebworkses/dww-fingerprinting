<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Registry
{
    public static function register(): void
    {
        Dashboard_Manager::clear();

        Dashboard_Manager::register(
            new Hero_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new KPI_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Health_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Quick_Actions_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new API_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Recent_Fingerprints_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Activity_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Alerts_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Formats_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new Tokens_Dashboard_Widget()
        );

        Dashboard_Manager::register(
            new System_Info_Dashboard_Widget()
        );
    }
}
