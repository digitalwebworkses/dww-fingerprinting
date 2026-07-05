<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

interface Health_Check_Interface
{
    /**
     * Ejecuta la comprobación de salud.
     *
     * @return array{
     *     id:string,
     *     name:string,
     *     category:string,
     *     status:string,
     *     severity:string,
     *     message:string,
     *     description:string,
     *     fix:string
     * }
     */
    public function run(): array;
}