<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Product_Asset
{
    private string $format;
    private int $attachment_id;
    private bool $enabled;

    public function __construct(
        string $format,
        int $attachment_id,
        bool $enabled = true
    ) {
        $this->format = sanitize_key($format);
        $this->attachment_id = absint($attachment_id);
        $this->enabled = $enabled;
    }

    public static function from_array(array $data): self
    {
        return new self(
            (string) ($data['format'] ?? ''),
            (int) ($data['attachment_id'] ?? 0),
            !isset($data['enabled']) || $data['enabled'] === true || $data['enabled'] === 'yes'
        );
    }

    public function to_array(): array
    {
        return [
            'format'        => $this->format,
            'attachment_id' => $this->attachment_id,
            'enabled'       => $this->enabled,
        ];
    }

    public function get_format(): string
    {
        return $this->format;
    }

    public function get_attachment_id(): int
    {
        return $this->attachment_id;
    }

    public function is_enabled(): bool
    {
        return $this->enabled;
    }

    public function get_file_path(): string
    {
        if ($this->attachment_id <= 0) {
            return '';
        }

        $file_path = get_attached_file($this->attachment_id);

        return $file_path ? (string) $file_path : '';
    }

    public function exists(): bool
    {
        $file_path = $this->get_file_path();

        return $file_path !== '' && file_exists($file_path);
    }

    public function is_processable(): bool
    {
        $file_path = $this->get_file_path();

        return $file_path !== ''
            && file_exists($file_path)
            && Fingerprint_Manager::can_process($file_path);
    }
}