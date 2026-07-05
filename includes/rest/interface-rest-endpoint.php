<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

interface REST_Endpoint_Interface
{
    public function register(): void;
}