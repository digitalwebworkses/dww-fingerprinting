<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Health_Check
{
    public static function run(): array
    {
        $results = [];

        foreach (self::checks() as $check) {
            if (!$check instanceof Health_Check_Interface) {
                continue;
            }

            try {
                $results[] = $check->run();
            } catch (\Throwable $exception) {
                $results[] = [
                    'name' => get_class($check),
                    'status' => 'error',
                    'message' => 'Health check exception: ' . $exception->getMessage(),
                ];
            }
        }

        return [
            'score' => self::score($results),
            'checks' => $results,
        ];
    }

    /**
     * @return Health_Check_Interface[]
     */
    private static function checks(): array
    {
        Health_Check_Registry::register();

        return Health_Check_Manager::all();
    }

    private static function score(array $checks): int
    {
        if (empty($checks)) {
            return 0;
        }

        $score = 100;

        foreach ($checks as $check) {
            $status = (string) ($check['status'] ?? '');
            $severity = (string) ($check['severity'] ?? '');

            if ($status === 'ok') {
                continue;
            }

            $score -= match ($severity) {
                'critical' => 30,
                'warning' => 10,
                'info' => 3,
                default => 15,
            };
        }

        return max(0, min(100, $score));
    }
}
