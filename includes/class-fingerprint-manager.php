<?php

namespace DWW_Fingerprinting;

use DWW_Fingerprinting\Handlers\Fingerprint_Handler_Interface;
use DWW_Fingerprinting\Handlers\Pdf_Fingerprint_Handler;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Manager
{
    /**
     * @var Fingerprint_Handler_Interface[]
     */
    private static array $handlers = [];

    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::register_handler(
            new Pdf_Fingerprint_Handler()
        );

        self::$initialized = true;
    }

    public static function clear_handlers(): void
    {
        self::$handlers = [];
    }

    public static function register_handler(
        Fingerprint_Handler_Interface $handler
    ): void {
        self::$handlers[] = $handler;
    }

    public static function get_handlers(): array
    {
        self::init();

        return self::$handlers;
    }

    public static function get_handler_for_file(
        string $file_path
    ): ?Fingerprint_Handler_Interface {
        self::init();

        foreach (self::$handlers as $handler) {
            if ($handler->supports($file_path)) {
                return $handler;
            }
        }

        return null;
    }

    public static function can_process(
        string $file_path
    ): bool {
        return self::get_handler_for_file($file_path) !== null;
    }

    public static function process(
        string $source_file,
        string $destination_file,
        array $context = []
    ): bool {
        self::init();

        $handler = self::get_handler_for_file($source_file);

        if (!$handler) {
            Logger::log(
                'No fingerprint handler found for file: ' . $source_file
            );

            return false;
        }

        if (!$handler->validate($source_file)) {
            Logger::log(
                'Fingerprint handler validation failed for file: ' . $source_file
            );

            return false;
        }

        return $handler->process(
            $source_file,
            $destination_file,
            $context
        );
    }

    /**
     * Registra todos los handlers nativos y permite
     * que terceros registren los suyos.
     */
    public static function register_default_handlers(): void
    {
        self::$handlers = [];

        self::register_handler(
            new \DWW_Fingerprinting\Handlers\Pdf_Fingerprint_Handler()
        );

        /**
         * Permite registrar handlers externos.
         */
        do_action('dww_fingerprinting_register_handlers');
    }

    /**
     * Devuelve el número de handlers registrados.
     */
    public static function count(): int
    {
        return count(self::$handlers);
    }

    public static function get_handler_names(): array
    {
        return array_map(
            static fn($handler) => $handler->get_name(),
            self::$handlers
        );
    }

    public static function get_supported_extensions(): array
    {
        $extensions = [];

        foreach (self::$handlers as $handler) {
            $extensions = array_merge(
                $extensions,
                $handler->get_supported_extensions()
            );
        }

        return array_values(
            array_unique($extensions)
        );
    }

    public static function get_supported_mime_types(): array
    {
        $mime_types = [];

        foreach (self::$handlers as $handler) {
            $mime_types = array_merge(
                $mime_types,
                $handler->get_supported_mime_types()
            );
        }

        return array_values(
            array_unique($mime_types)
        );
    }
}
