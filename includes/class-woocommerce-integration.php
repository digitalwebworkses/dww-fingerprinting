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
            $customer_email = 'test@example.com';
            Logger::log('Empty customer email. Using fallback: ' . $customer_email);
        }

        $customer_name = trim(
            $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
        );

        if (empty($customer_name)) {
            $customer_name = 'Cliente WooCommerce';
        }

        Logger::log('Handling order: ' . $order_id);
        Logger::log('Customer email: ' . $customer_email);

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

            $source_file = Product_Settings::get_source_file($product);

            Logger::log('Source file: ' . $source_file);

            Logger::log(
                'Source exists: ' .
                (file_exists($source_file) ? 'yes' : 'no')
            );

            if (empty($source_file) || !file_exists($source_file)) {
                Logger::log('Invalid source file for product: ' . $product_id);
                continue;
            }

            if (!Fingerprint_Manager::can_process($source_file)) {
                Logger::log('Unsupported source file for fingerprinting: ' . $source_file);
                continue;
            }

            $fingerprint_id = Fingerprint_Generator::generate(
                $customer_email,
                (string) $order_id
            );

            Logger::log('Fingerprint: ' . $fingerprint_id);

            Logger::log(
                'Fingerprint exists: ' .
                (Fingerprint_DB::exists($fingerprint_id) ? 'yes' : 'no')
            );

            if (Fingerprint_DB::exists($fingerprint_id)) {
                Logger::log('Skipping existing fingerprint: ' . $fingerprint_id);
                continue;
            }

            $destination = self::build_generated_file_path(
                $order_id,
                $product_id,
                $fingerprint_id,
                $source_file
            );

            Logger::log('Destination file: ' . $destination);
            Logger::log('Generating protected file...');

            $generated = Fingerprint_Manager::process(
                $source_file,
                $destination,
                [
                    'customer_name'  => $customer_name,
                    'customer_email' => $customer_email,
                    'order_id'       => (string) $order_id,
                    'product_id'     => $product_id,
                    'product_name'   => $product_name,
                    'fingerprint_id' => $fingerprint_id,
                ]
            );

            if (!$generated) {
                Logger::log('File generation failed for fingerprint: ' . $fingerprint_id);
                continue;
            }

            Logger::log('Protected file generated successfully: ' . $destination);

            $registry_id = Fingerprint_DB::insert([
                'fingerprint_id' => $fingerprint_id,
                'customer_email' => $customer_email,
                'order_id'       => (string) $order_id,
                'product_id'     => $product_id,
                'product_name'   => $product_name,
                'source_file'    => $source_file,
                'generated_file' => $destination,
            ]);

            if ($registry_id <= 0) {
                Logger::log('Fingerprint insert failed: ' . $fingerprint_id);
                continue;
            }

            Logger::log('Fingerprint inserted: ' . $fingerprint_id);

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
    }

    private static function build_generated_file_path(
        int $order_id,
        string $product_id,
        string $fingerprint_id,
        string $source_file
    ): string {
        $upload_dir = wp_upload_dir();

        $generated_dir = trailingslashit($upload_dir['basedir'])
            . 'dww-fingerprinting/generated';

        if (!file_exists($generated_dir)) {
            wp_mkdir_p($generated_dir);
        }

        $extension = strtolower(pathinfo($source_file, PATHINFO_EXTENSION));

        if ($extension === '') {
            $extension = 'bin';
        }

        $filename = sprintf(
            'order-%s-%s-product-%s.%s',
            $order_id,
            sanitize_file_name($fingerprint_id),
            sanitize_file_name($product_id),
            sanitize_file_name($extension)
        );

        return trailingslashit($generated_dir) . $filename;
    }
}