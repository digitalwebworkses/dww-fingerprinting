<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Health_Check_Manager
{
    /**
     * @var Health_Check_Interface[]
     */
    private static array $checks = [];

    public static function clear(): void
    {
        self::$checks = [];
    }

    public static function register(Health_Check_Interface $check): void
    {
        self::$checks[] = $check;
    }

    /**
     * @return Health_Check_Interface[]
     */
    public static function all(): array
    {
        return self::$checks;
    }

    public static function count(): int
    {
        return count(self::$checks);
    }

    public static function repair(string $check_id): array
    {
        Health_Check_Registry::register();

        foreach (self::all() as $check) {
            $result = $check->run();

            if (($result['id'] ?? '') !== $check_id) {
                continue;
            }

            if (!$check instanceof Repairable_Health_Check_Interface) {
                return [
                    'success' => false,
                    'message' => 'Este diagnóstico no admite reparación automática.',
                ];
            }

            return $check->repair();
        }

        return [
            'success' => false,
            'message' => 'Diagnóstico no encontrado.',
        ];
    }
}
