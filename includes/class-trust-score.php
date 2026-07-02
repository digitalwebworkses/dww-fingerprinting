<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Trust_Score
{
    public static function calculate(array $verification_result): array
    {
        $status = (string) ($verification_result['status'] ?? 'unknown');
        $valid = !empty($verification_result['valid']);
        $evidence = $verification_result['evidence'] ?? [];
        $record = $verification_result['record'] ?? null;
        $warnings = $verification_result['warnings'] ?? [];
        $errors = $verification_result['errors'] ?? [];

        $score = $valid ? 100 : 50;

        $score -= count($warnings) * 5;
        $score -= count($errors) * 15;

        if ($record === null) {
            $score -= 30;
        }

        if (empty($evidence['fingerprint_id'])) {
            $score -= 15;
        }

        if (empty($evidence['payload_hash'])) {
            $score -= 20;
        }

        $score += match ($status) {
            'verified' => 0,
            'payload_tampered' => -50,
            'hash_mismatch' => -60,
            'fingerprint_mismatch' => -45,
            'integrity_failed' => -40,
            'not_found' => -60,
            'missing_evidence' => -80,
            'extract_error' => -70,
            'upload_error' => -70,
            default => -30,
        };

        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'level' => self::level($score),
            'label' => self::label($score),
        ];
    }

    private static function level(int $score): string
    {
        if ($score >= 90) {
            return 'high';
        }

        if ($score >= 60) {
            return 'medium';
        }

        return 'low';
    }

    private static function label(int $score): string
    {
        if ($score >= 90) {
            return 'Alta confianza';
        }

        if ($score >= 60) {
            return 'Confianza media';
        }

        return 'Baja confianza';
    }
}