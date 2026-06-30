<?php

namespace DWW_Fingerprinting;

if (!defined('ABSPATH')) {
    exit;
}

class Product_Settings
{
    public static function init(): void
    {
        add_action(
            'woocommerce_product_options_general_product_data',
            [self::class, 'render_fields']
        );

        add_action(
            'woocommerce_admin_process_product_object',
            [self::class, 'save_fields']
        );

        add_action(
            'admin_enqueue_scripts',
            [self::class, 'enqueue_admin_scripts']
        );
    }

    public static function render_fields(): void
    {
        global $post;

        $product = $post ? wc_get_product($post->ID) : null;

        $assets = Product_Assets::get_assets($product);
        $format_options = Fingerprint_Manager::get_supported_format_options();

        echo '<div class="options_group">';

        woocommerce_wp_checkbox([
            'id'          => '_dww_fingerprinting_enabled',
            'label'       => 'Activar DWW Fingerprinting',
            'description' => 'Las descargas de este producto serán gestionadas por DWW Fingerprinting.',
            'desc_tip'    => true,
        ]);

        echo '<p class="form-field dww-fingerprinting-assets-field">';
        echo '<label>Activos maestros</label>';

        echo '<span class="description">';
        echo 'Añade los archivos origen que se usarán para generar copias personalizadas.';
        echo '</span>';

        echo '<table class="widefat dww-fp-assets-table" style="margin-top:10px; max-width:850px;">';

        echo '<thead>';
        echo '<tr>';
        echo '<th style="width:180px;">Formato</th>';
        echo '<th>Archivo</th>';
        echo '<th style="width:220px;">Acciones</th>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody class="dww-fp-assets-rows">';

        if (empty($assets)) {
            self::render_asset_row(0, null, $format_options);
        } else {
            foreach ($assets as $index => $asset) {
                self::render_asset_row((int) $index, $asset, $format_options);
            }
        }

        echo '</tbody>';

        echo '</table>';

        echo '<button type="button" class="button dww-fp-add-asset" style="margin-top:10px;">+ Añadir activo</button>';

        echo '</p>';

        echo '<p class="form-field dww-fp-native-downloads-notice" style="display:none;">';
        echo '<label></label>';
        echo '<span class="description" style="color:#996800;">';
        echo 'DWW Fingerprinting está activo: los archivos descargables nativos de WooCommerce quedan ocultos para evitar duplicidades.';
        echo '</span>';
        echo '</p>';

        echo '</div>';
    }

    private static function render_asset_row(
        int $index,
        ?Product_Asset $asset,
        array $format_options
    ): void {
        $format = $asset?->get_format() ?? '';
        $attachment_id = $asset?->get_attachment_id() ?? 0;

        $attachment_url = $attachment_id > 0
            ? wp_get_attachment_url($attachment_id)
            : '';

        $attachment_name = $attachment_id > 0
            ? basename((string) get_attached_file($attachment_id))
            : '';

        echo '<tr class="dww-fp-asset-row">';

        echo '<td>';
        echo '<select name="dww_fingerprinting_assets[' . esc_attr((string) $index) . '][format]" class="dww-fp-asset-format">';

        foreach ($format_options as $extension => $label) {
            echo '<option value="' . esc_attr($extension) . '" ' . selected($format, $extension, false) . '>';
            echo esc_html($label);
            echo '</option>';
        }

        echo '</select>';
        echo '</td>';

        echo '<td>';
        echo '<input type="hidden" class="dww-fp-asset-id" name="dww_fingerprinting_assets[' . esc_attr((string) $index) . '][attachment_id]" value="' . esc_attr((string) $attachment_id) . '" />';
        echo '<input type="text" class="dww-fp-asset-file" value="' . esc_attr($attachment_name) . '" readonly style="width:95%;" placeholder="Ningún archivo seleccionado" />';
        echo '</td>';

        echo '<td>';

        if (!empty($attachment_url)) {
            echo '<a href="' . esc_url($attachment_url) . '" target="_blank" class="button dww-fp-view-asset">Ver</a> ';
        } else {
            echo '<a href="#" target="_blank" class="button dww-fp-view-asset" style="display:none;">Ver</a> ';
        }

        echo '<button type="button" class="button dww-fp-select-asset">Seleccionar</button> ';
        echo '<button type="button" class="button dww-fp-remove-asset">Quitar</button>';

        echo '</td>';

        echo '</tr>';
    }

    public static function save_fields($product): void
    {
        $enabled = isset($_POST['_dww_fingerprinting_enabled']) ? 'yes' : 'no';

        $assets = isset($_POST['dww_fingerprinting_assets']) && is_array($_POST['dww_fingerprinting_assets'])
            ? wp_unslash($_POST['dww_fingerprinting_assets'])
            : [];

        $assets = Product_Assets::sanitize_assets($assets);

        $product->update_meta_data(
            '_dww_fingerprinting_enabled',
            $enabled
        );

        if ($enabled === 'yes') {
            $product->set_downloads([]);
        }

        if ($enabled === 'yes' && empty($assets)) {
            WC_Admin_Meta_Boxes::add_error(
                'DWW Fingerprinting está activo, pero no se ha seleccionado ningún activo maestro.'
            );
        }

        if ($enabled === 'no' && !empty($assets)) {
            WC_Admin_Meta_Boxes::add_error(
                'Hay activos maestros seleccionados, pero DWW Fingerprinting está desactivado.'
            );
        }

        foreach ($assets as $asset) {

            if (!$asset instanceof Product_Asset) {
                continue;
            }

            if (!$asset->exists()) {

                WC_Admin_Meta_Boxes::add_error(
                    'Uno de los activos maestros seleccionados no existe o no está disponible.'
                );

                continue;
            }

            if (!$asset->is_processable()) {

                WC_Admin_Meta_Boxes::add_error(
                    'Uno de los activos maestros seleccionados no está soportado por ningún handler activo.'
                );
            }
        }

        Product_Assets::save_assets($product, $assets);
    }

    public static function enqueue_admin_scripts(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== 'product') {
            return;
        }

        wp_enqueue_media();

        wp_add_inline_script(
            'jquery',
            self::get_media_uploader_script()
        );
    }

    public static function is_enabled($product): bool
    {
        if (!$product) {
            return false;
        }

        return $product->get_meta('_dww_fingerprinting_enabled') === 'yes';
    }

    /**
     * @deprecated
     * Usar Product_Assets::get_supported_assets().
     */

    public static function get_source_file($product): string
    {
        $assets = Product_Assets::get_supported_assets($product);

        if (empty($assets)) {
            return '';
        }

        return $assets[0]->get_file_path();
    }


    private static function get_media_uploader_script(): string
    {
        return "
            jQuery(function($) {
                var frame;
                var currentRow;

                function toggleNativeDownloadsPanel() {
                    var enabled = $('#_dww_fingerprinting_enabled').is(':checked');

                    $('.dww-fp-native-downloads-notice').toggle(enabled);

                    var nativeDownloadFields = [
                        '#_downloadable_files',
                        '#_download_limit',
                        '#_download_expiry'
                    ];

                    nativeDownloadFields.forEach(function(selector) {
                        var field = $(selector);

                        if (!field.length) {
                            return;
                        }

                        field.closest('p.form-field, .form-field, tr, .wc-metaboxes-wrapper')
                            .toggle(!enabled);
                    });

                    $('label')
                        .filter(function() {
                            return $(this).text().trim() === 'Archivos descargables';
                        })
                        .closest('p.form-field, .form-field, tr')
                        .toggle(!enabled);
                }

                function reindexRows() {
                    $('.dww-fp-assets-rows .dww-fp-asset-row').each(function(index) {
                        $(this).find('.dww-fp-asset-format').attr('name', 'dww_fingerprinting_assets[' + index + '][format]');
                        $(this).find('.dww-fp-asset-id').attr('name', 'dww_fingerprinting_assets[' + index + '][attachment_id]');
                    });
                }

                toggleNativeDownloadsPanel();

                $('#_dww_fingerprinting_enabled').on('change', function() {
                    toggleNativeDownloadsPanel();
                });

                $('.dww-fp-add-asset').on('click', function(e) {
                    e.preventDefault();

                    var row = $('.dww-fp-assets-rows .dww-fp-asset-row:first').clone();

                    row.find('.dww-fp-asset-id').val('');
                    row.find('.dww-fp-asset-file').val('');
                    row.find('.dww-fp-view-asset').attr('href', '#').hide();

                    $('.dww-fp-assets-rows').append(row);

                    reindexRows();
                });

                $(document).on('click', '.dww-fp-select-asset', function(e) {
                    e.preventDefault();

                    currentRow = $(this).closest('.dww-fp-asset-row');

                    if (frame) {
                        frame.open();
                        return;
                    }

                    frame = wp.media({
                        title: 'Seleccionar archivo protegido',
                        button: {
                            text: 'Usar este archivo'
                        },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();

                        if (!currentRow) {
                            return;
                        }

                        currentRow.find('.dww-fp-asset-id').val(attachment.id);
                        currentRow.find('.dww-fp-asset-file').val(attachment.filename || attachment.title || attachment.url);
                        currentRow.find('.dww-fp-view-asset').attr('href', attachment.url).show();
                    });

                    frame.open();
                });

                $(document).on('click', '.dww-fp-remove-asset', function(e) {
                    e.preventDefault();

                    var rows = $('.dww-fp-assets-rows .dww-fp-asset-row');

                    if (rows.length > 1) {
                        $(this).closest('.dww-fp-asset-row').remove();
                        reindexRows();
                        return;
                    }

                    var row = $(this).closest('.dww-fp-asset-row');

                    row.find('.dww-fp-asset-id').val('');
                    row.find('.dww-fp-asset-file').val('');
                    row.find('.dww-fp-view-asset').attr('href', '#').hide();
                });
            });
        ";
    }
}
