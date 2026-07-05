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

    public static function register_handler(
        Fingerprint_Handler_Interface $handler
    ): void {
        self::$handlers[] = $handler;
    }

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

        do_action('dww_fingerprinting_register_handlers');
    }

    /**
     * @return Fingerprint_Handler_Interface[]
     */
    public static function get_handlers(): array
    {
        return self::$handlers;
    }

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

        $validation = File_Validator::validate($source_file);

        if (!$validation['valid']) {
            Logger::log(
                'Source file validation failed: ' .
                    implode(' | ', $validation['errors'])
            );

            return false;
        }

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

        $processed = $handler->process(
            $source_file,
            $destination_file,
            $context
        );

        if ($processed) {
            do_action(
                'dww_fingerprint_file_processed',
                $source_file,
                $destination_file,
                $context,
                $handler
            );
        }

        return $processed;
    }

    public static function count(): int
    {
        return count(self::$handlers);
    }

    public static function get_handler_names(): array
    {
        return array_map(
            static fn(Fingerprint_Handler_Interface $handler) => $handler->get_name(),
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

    public static function get_supported_format_options(): array
    {
        $options = [];

        foreach (self::$handlers as $handler) {
            foreach ($handler->get_supported_extensions() as $extension) {
                $extension = strtolower((string) $extension);

                if ($extension === '') {
                    continue;
                }

                $options[$extension] = sprintf(
                    '%s (.%s)',
                    $handler->get_name(),
                    $extension
                );
            }
        }

        ksort($options);

        return $options;
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