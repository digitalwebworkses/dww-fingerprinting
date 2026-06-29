<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Product_Assets
{
    public const META_KEY = '_dww_fingerprinting_assets';

    public static function get_assets($product): array
    {
        if (!$product) {
            return [];
        }

        $assets = $product->get_meta(self::META_KEY);

        if (!is_array($assets) || empty($assets)) {

            $legacy_attachment_id = (int) $product->get_meta(
                '_dww_fingerprinting_source_attachment_id'
            );

            if ($legacy_attachment_id > 0) {

                $file_path = get_attached_file($legacy_attachment_id);

                return [
                    new Product_Asset(
                        $file_path
                            ? strtolower(pathinfo((string) $file_path, PATHINFO_EXTENSION))
                            : '',
                        $legacy_attachment_id
                    ),
                ];
            }

            return [];
        }

        $result = [];

        foreach ($assets as $asset) {

            if (!is_array($asset)) {
                continue;
            }

            $result[] = Product_Asset::from_array($asset);
        }

        return $result;
    }

    public static function save_assets($product, array $assets): void
    {
        if (!$product) {
            return;
        }

        $serialized = [];

        foreach ($assets as $asset) {

            if ($asset instanceof Product_Asset) {
                $serialized[] = $asset->to_array();
                continue;
            }

            if (is_array($asset)) {
                $serialized[] = Product_Asset::from_array($asset)->to_array();
            }
        }

        $product->update_meta_data(
            self::META_KEY,
            $serialized
        );
    }

    public static function has_assets($product): bool
    {
        return !empty(self::get_assets($product));
    }

    public static function get_supported_assets($product): array
    {
        return array_values(
            array_filter(
                self::get_assets($product),
                static fn(Product_Asset $asset) => $asset->is_processable()
            )
        );
    }

    public static function sanitize_assets(array $assets): array
    {
        $result = [];

        foreach ($assets as $asset) {

            if ($asset instanceof Product_Asset) {
                $result[] = $asset;
                continue;
            }

            if (!is_array($asset)) {
                continue;
            }

            $object = Product_Asset::from_array($asset);

            if ($object->get_attachment_id() <= 0) {
                continue;
            }

            $result[] = $object;
        }

        return $result;
    }
}