<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Storage_Security
{
    public static function protect_directory(string $directory): bool
    {
        if ($directory === '') {
            return false;
        }

        if (!file_exists($directory)) {

            if (!wp_mkdir_p($directory)) {
                Logger::log(
                    'Unable to create directory: ' . $directory
                );

                return false;
            }
        }

        if (!is_dir($directory)) {
            return false;
        }

        self::create_index_file($directory);

        self::create_htaccess($directory);

        self::set_permissions($directory);

        return true;
    }

    private static function create_index_file(
        string $directory
    ): void {

        $file = trailingslashit($directory) . 'index.php';

        if (file_exists($file)) {
            return;
        }

        file_put_contents(
            $file,
            "<?php\n// Silence is golden.\n"
        );
    }

    private static function create_htaccess(
        string $directory
    ): void {

        $file = trailingslashit($directory) . '.htaccess';

        if (file_exists($file)) {
            return;
        }

        $rules = implode(
            PHP_EOL,
            [
                'Options -Indexes',
                '',
                '<IfModule mod_php.c>',
                'php_flag engine off',
                '</IfModule>',
                '',
                '<IfModule mod_php8.c>',
                'php_flag engine off',
                '</IfModule>',
                '',
                '<FilesMatch "\.(php|phtml|php[0-9]*)$">',
                'Require all denied',
                '</FilesMatch>',
                '',
            ]
        );

        file_put_contents(
            $file,
            $rules
        );
    }

    private static function set_permissions(
        string $directory
    ): void {

        if (!function_exists('wp_is_writable')) {
            return;
        }

        if (!wp_is_writable($directory)) {

            Logger::log(
                'Storage directory is not writable: ' .
                    $directory
            );
        }

        @chmod($directory, 0755);

        $index = trailingslashit($directory) . 'index.php';

        if (file_exists($index)) {
            @chmod($index, 0644);
        }

        $htaccess = trailingslashit($directory) . '.htaccess';

        if (file_exists($htaccess)) {
            @chmod($htaccess, 0644);
        }
    }

    public static function verify_directory(string $directory): array
    {
        $status = [
            'exists'   => is_dir($directory),
            'writable' => false,
            'protected' => false,
        ];

        if (!$status['exists']) {
            return $status;
        }

        $status['writable'] = wp_is_writable($directory);

        $status['protected'] =
            file_exists(trailingslashit($directory) . 'index.php') &&
            file_exists(trailingslashit($directory) . '.htaccess');

        return $status;
    }

    public static function verify_upload_storage(): array
    {
        $upload_dir = wp_upload_dir();

        $base = trailingslashit($upload_dir['basedir'])
            . 'dww-fingerprinting';

        return [
            'base' => self::verify_directory($base),
            'storage' => self::verify_directory(
                trailingslashit($base) . 'storage'
            ),
            'generated' => self::verify_directory(
                trailingslashit($base) . 'storage/generated'
            ),
        ];
    }
}
