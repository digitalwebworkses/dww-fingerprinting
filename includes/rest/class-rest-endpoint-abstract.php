<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

abstract class REST_Endpoint_Abstract implements REST_Endpoint_Interface
{
    protected function namespace(): string
    {
        return 'dww/v1';
    }

    protected function permissions(): callable
    {
        return REST_API_Auth::permission_callback();
    }
}
