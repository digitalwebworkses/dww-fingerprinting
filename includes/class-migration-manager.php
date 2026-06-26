<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_Manager
{
    private const DB_OPTION = 'dww_fingerprinting_db_version';

    private const INITIAL_VERSION = '0.1.0';

    public static function run(): void
    {
        $installed_version = self::get_installed_version();

        $migrations = [
            '0.2.0' => Migration_020::class,
            '0.3.0' => Migration_030::class,
        ];

        foreach ($migrations as $version => $migration_class) {

            if (version_compare($installed_version, $version, '>=')) {
                continue;
            }

            if (!class_exists($migration_class)) {
                Logger::log(
                    sprintf(
                        'Migration class not found: %s',
                        $migration_class
                    )
                );

                continue;
            }

            Logger::log(
                sprintf(
                    'Running migration %s',
                    $version
                )
            );

            try {

                $migration_class::up();

                self::set_installed_version($version);

                $installed_version = $version;

                Logger::log(
                    sprintf(
                        'Migration %s completed successfully.',
                        $version
                    )
                );
            } catch (\Throwable $exception) {

                Logger::log(
                    sprintf(
                        'Migration %s failed: %s',
                        $version,
                        $exception->getMessage()
                    )
                );

                break;
            }
        }
    }

    public static function get_installed_version(): string
    {
        return (string) get_option(
            self::DB_OPTION,
            self::INITIAL_VERSION
        );
    }

    private static function set_installed_version(string $version): void
    {
        update_option(
            self::DB_OPTION,
            $version,
            false
        );
    }
}
