<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

interface Repairable_Health_Check_Interface
{
    public function repair(): array;
}