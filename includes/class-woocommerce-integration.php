<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Integration
{
    public static function init(): void
    {
        add_action(
            'woocommerce_order_status_processing',
            [self::class, 'handle_order'],
            10,
            1
        );

        add_action(
            'woocommerce_order_status_completed',
            [self::class, 'handle_order'],
            10,
            1
        );

        add_action(
            'woocommerce_order_status_changed',
            [self::class, 'handle_order_status_changed'],
            10,
            4
        );
    }

    public static function handle_order_status_changed(
        int $order_id,
        string $old_status,
        string $new_status,
        $order
    ): void {
        if (!in_array($new_status, ['processing', 'completed'], true)) {
            return;
        }

        self::handle_order($order_id);
    }

    public static function handle_order(int $order_id): void
    {
        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            Logger::log('Order not found: ' . $order_id);
            return;
        }

        $customer_email = $order->get_billing_email();

        if (empty($customer_email)) {
            Logger::log('Order has no customer email: ' . $order_id);
            return;
        }

        $customer_name = trim(
            $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
        );

        if (empty($customer_name)) {
            $customer_name = 'Cliente WooCommerce';
        }

        Logger::log('Handling order: ' . $order_id);

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            if (!$product) {
                Logger::log('Order item without valid product. Order: ' . $order_id);
                continue;
            }

            $product_id = (string) $product->get_id();
            $product_name = $product->get_name();

            Logger::log(
                sprintf(
                    'Product: %s (%s)',
                    $product_name,
                    $product_id
                )
            );

            if (!Product_Settings::is_enabled($product)) {
                Logger::log('Product not enabled for fingerprinting: ' . $product_id);
                continue;
            }

            $assets = Product_Assets::get_supported_assets($product);

            if (empty($assets)) {
                Logger::log('No supported assets for product: ' . $product_id);
                continue;
            }

            foreach ($assets as $asset) {
                self::process_product_asset(
                    $order_id,
                    $product,
                    $asset,
                    $customer_name,
                    $customer_email
                );
            }
        }
    }

    private static function process_product_asset(
        int $order_id,
        $product,
        Product_Asset $asset,
        string $customer_name,
        string $customer_email
    ): void {
        $product_id = (string) $product->get_id();
        $product_name = $product->get_name();

        $source_file = $asset->get_file_path();
        $format = $asset->get_format();
        $asset_id = (string) $asset->get_attachment_id();

        Logger::log('Processing asset for product: ' . $product_id);
        Logger::log('Asset format: ' . $format);
        Logger::log('Source file: ' . $source_file);

        if (empty($source_file) || !file_exists($source_file)) {
            Logger::log('Invalid source file for product asset: ' . $product_id);
            return;
        }

        if (!Fingerprint_Manager::can_process($source_file)) {
            Logger::log('Unsupported source file for fingerprinting: ' . $source_file);
            return;
        }

        $fingerprint_id = Fingerprint_Generator::generate(
            $customer_email,
            (string) $order_id,
            $product_id,
            $format,
            $asset_id
        );

        $context = [
            'customer_name'  => $customer_name,
            'customer_email' => $customer_email,
            'order_id'       => (string) $order_id,
            'product_id'     => $product_id,
            'product_name'   => $product_name,
            'asset_format'   => $format,
            'asset_id'       => $asset_id,
            'fingerprint_id' => $fingerprint_id,
            'generated_at'   => current_time('mysql'),
            'plugin_version' => DWW_FP_VERSION,
        ];

        $payload_hash = Fingerprint_Payload::hash($context);

        Logger::log('Fingerprint: ' . $fingerprint_id);
        Logger::log('Payload hash: ' . $payload_hash);

        if (!self::acquire_fingerprint_lock($fingerprint_id)) {
            Logger::log('Fingerprint is already being processed: ' . $fingerprint_id);
            return;
        }

        try {
            self::process_locked_asset(
                $order_id,
                $product_id,
                $product_name,
                $customer_email,
                $source_file,
                $format,
                $asset_id,
                $fingerprint_id,
                $payload_hash,
                $context
            );
        } catch (\Throwable $exception) {
            Logger::log(
                'Fingerprint processing failed [' . $fingerprint_id . ']: ' .
                $exception->getMessage()
            );
        } finally {
            self::release_fingerprint_lock($fingerprint_id);
        }
    }

    private static function process_locked_asset(
        int $order_id,
        string $product_id,
        string $product_name,
        string $customer_email,
        string $source_file,
        string $format,
        string $asset_id,
        string $fingerprint_id,
        string $payload_hash,
        array $context
    ): void {
        $existing = Fingerprint_DB::get_by_fingerprint($fingerprint_id);

        if ($existing && is_file((string) $existing->generated_file)) {
            self::ensure_download_token($fingerprint_id);
            Logger::log('Fingerprint already complete: ' . $fingerprint_id);
            return;
        }

        $destination = self::build_generated_file_path(
            $order_id,
            $product_id,
            $fingerprint_id,
            $source_file
        );

        Logger::log('Destination file: ' . $destination);
        Logger::log('Generating protected file...');

        try {
            $generated = Fingerprint_Manager::process($source_file, $destination, $context);
        } catch (\Throwable $exception) {
            self::cleanup_generated_file($destination);
            Logger::log(
                'File generation exception for fingerprint ' .
                    $fingerprint_id . ': ' . $exception->getMessage()
            );
            return;
        }

        if (!$generated) {
            self::cleanup_generated_file($destination);
            Logger::log('File generation failed for fingerprint: ' . $fingerprint_id);
            return;
        }

        Logger::log('Protected file generated successfully: ' . $destination);

        $record_data = [
            'fingerprint_id' => $fingerprint_id,
            'payload_hash'   => $payload_hash,
            'customer_email' => $customer_email,
            'order_id'       => (string) $order_id,
            'product_id'     => $product_id,
            'product_name'   => $product_name,
            'asset_format'   => $format,
            'asset_id'       => $asset_id,
            'source_file'    => $source_file,
            'generated_file' => $destination,
        ];

        $registered = $existing
            ? Fingerprint_DB::update_generated_asset($fingerprint_id, $record_data)
            : Fingerprint_DB::insert($record_data) > 0;

        if (!$registered) {
            self::cleanup_generated_file($destination);
            Logger::log('Fingerprint registration failed: ' . $fingerprint_id);
            return;
        }

        Logger::log($existing
            ? 'Fingerprint recovered: ' . $fingerprint_id
            : 'Fingerprint inserted: ' . $fingerprint_id);

        self::ensure_download_token($fingerprint_id);
    }

    private static function ensure_download_token(string $fingerprint_id): void
    {
        if (Download_Token_DB::get_all_by_fingerprint($fingerprint_id) !== []) {
            return;
        }

        $download_token = Download_Token_DB::create_token(
            $fingerprint_id,
            72,
            3
        );

        if (!empty($download_token)) {
            Logger::log(
                'Download token created for fingerprint: ' .
                    $fingerprint_id
            );
        } else {
            Logger::log(
                'Download token creation failed for fingerprint: ' .
                    $fingerprint_id
            );
        }
    }

    private static function cleanup_generated_file(string $file): void
    {
        if (is_file($file)) {
            unlink($file);
        }
    }

    private static function acquire_fingerprint_lock(string $fingerprint_id): bool
    {
        global $wpdb;

        $lock = self::lock_name($fingerprint_id);
        $result = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 10)', $lock));

        return $result === null || (int) $result === 1;
    }

    private static function release_fingerprint_lock(string $fingerprint_id): void
    {
        global $wpdb;

        $wpdb->get_var(
            $wpdb->prepare('SELECT RELEASE_LOCK(%s)', self::lock_name($fingerprint_id))
        );
    }

    private static function lock_name(string $fingerprint_id): string
    {
        return 'dww_fp_' . substr(hash('sha256', $fingerprint_id), 0, 56);
    }

    private static function build_generated_file_path(
        int $order_id,
        string $product_id,
        string $fingerprint_id,
        string $source_file
    ): string {
        $generated_dir = Storage_Security::get_generated_directory();

        Storage_Security::protect_directory($generated_dir);

        $extension = strtolower(pathinfo($source_file, PATHINFO_EXTENSION));

        if ($extension === '') {
            $extension = 'bin';
        }

        $filename = sprintf(
            '%s.%s',
            bin2hex(random_bytes(16)),
            sanitize_file_name($extension)
        );

        return trailingslashit($generated_dir) . $filename;
    }
}
