<?php

namespace DWW_Fingerprinting;

use DWW_Fingerprinting\Handlers\Abstract_Fingerprint_Handler;
use DWW_Fingerprinting\Handlers\Fingerprint_Handler_Interface;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Manager
{
    /**
     * @var Fingerprint_Handler_Interface[]
     */
    private static array $handlers = [];

    public static function clear_handlers(): void
    {
        self::$handlers = [];
    }

    /**
     * Registra un handler.
     */
    public static function register_handler(
        Fingerprint_Handler_Interface $handler
    ): void {
        self::$handlers[] = $handler;
    }

    /**
     * Descubre automáticamente todos los handlers cargados.
     */
    public static function register_default_handlers(): void
    {
        self::clear_handlers();

        foreach (get_declared_classes() as $class) {

            if (!is_subclass_of(
                $class,
                Abstract_Fingerprint_Handler::class
            )) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract()) {
                continue;
            }

            self::register_handler(
                $reflection->newInstance()
            );
        }

        /**
         * Permite a plugins externos registrar handlers.
         */
        do_action('dww_fingerprinting_register_handlers');
    }

    /**
     * Devuelve todos los handlers registrados.
     *
     * @return Fingerprint_Handler_Interface[]
     */
    public static function get_handlers(): array
    {
        return self::$handlers;
    }

    /**
     * Obtiene el handler adecuado para un fichero.
     */
    public static function get_handler_for_file(
        string $file_path
    ): ?Fingerprint_Handler_Interface {

        foreach (self::$handlers as $handler) {

            if ($handler->supports($file_path)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Indica si existe un handler para el fichero.
     */
    public static function can_process(
        string $file_path
    ): bool {
        return self::get_handler_for_file($file_path) !== null;
    }

    /**
     * Procesa un fichero utilizando el handler adecuado.
     */
    public static function process(
        string $source_file,
        string $destination_file,
        array $context = []
    ): bool {

        $handler = self::get_handler_for_file($source_file);

        if (!$handler) {

            Logger::log(
                'No fingerprint handler found for file: ' . $source_file
            );

            return false;
        }

        if (!$handler->validate($source_file)) {

            Logger::log(
                'Fingerprint handler validation failed for file: ' .
                $source_file
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
     * Número de handlers registrados.
     */
    public static function count(): int
    {
        return count(self::$handlers);
    }

    /**
     * Devuelve los nombres de los handlers.
     */
    public static function get_handler_names(): array
    {
        return array_map(
            static fn (Fingerprint_Handler_Interface $handler) => $handler->get_name(),
            self::$handlers
        );
    }

    /**
     * Devuelve todas las extensiones soportadas.
     */
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

    /**
     * Devuelve todos los MIME types soportados.
     */
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