<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Fingerprint_Verifier
{
    public static function verify(string $file_path): array
    {
        $evidence = Fingerprint_Extractor::extract($file_path);

        $result = [
            'valid'    => false,
            'status'   => 'unknown',
            'evidence' => $evidence,
            'record'   => null,
            'warnings' => $evidence['warnings'] ?? [],
            'errors'   => $evidence['errors'] ?? [],
        ];

        if (!empty($result['errors'])) {
            $result['status'] = 'extract_error';
            return self::with_trust($result);
        }

        $record = self::find_record($evidence);

        if (!$record) {
            $result['status'] = self::has_evidence($evidence)
                ? 'not_found'
                : 'missing_evidence';

            $result['errors'][] = self::has_evidence($evidence)
                ? 'No existe ningún registro coincidente en la base de datos.'
                : 'No se ha encontrado fingerprint ni hash en el documento.';

            return self::with_trust($result);
        }

        $result['record'] = $record;

        $integrity = Fingerprint_Integrity::verify(
            $evidence['properties'] ?? [],
            $record
        );

        $result['warnings'] = array_merge(
            $result['warnings'],
            $integrity['warnings']
        );

        $result['errors'] = array_merge(
            $result['errors'],
            $integrity['errors']
        );

        $result['valid'] = (bool) $integrity['valid'];
        $result['status'] = $result['valid']
            ? 'verified'
            : self::classify_integrity_failure($integrity['errors']);

        return self::with_trust($result);
    }

    private static function find_record(array $evidence): ?object
    {
        $payload_hash = trim((string) ($evidence['payload_hash'] ?? ''));
        $fingerprint_id = trim((string) ($evidence['fingerprint_id'] ?? ''));

        if ($payload_hash !== '') {
            $record = Fingerprint_DB::get_by_payload_hash($payload_hash);

            if ($record) {
                return $record;
            }
        }

        if ($fingerprint_id !== '') {
            return Fingerprint_DB::get_by_fingerprint($fingerprint_id);
        }

        return null;
    }

    private static function has_evidence(array $evidence): bool
    {
        return trim((string) ($evidence['payload_hash'] ?? '')) !== ''
            || trim((string) ($evidence['fingerprint_id'] ?? '')) !== '';
    }

    private static function classify_integrity_failure(array $errors): string
    {
        $joined = implode(' ', $errors);

        if (stripos($joined, 'payload reconstruido') !== false) {
            return 'payload_tampered';
        }

        if (stripos($joined, 'Hash') !== false) {
            return 'hash_mismatch';
        }

        if (stripos($joined, 'Fingerprint') !== false) {
            return 'fingerprint_mismatch';
        }

        return 'integrity_failed';
    }

    private static function with_trust(array $result): array
    {
        $result['trust'] = Trust_Score::calculate($result);

        return $result;
    }
}
