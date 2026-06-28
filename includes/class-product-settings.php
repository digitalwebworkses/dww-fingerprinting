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

        $attachment_id = $post
            ? (int) get_post_meta($post->ID, '_dww_fingerprinting_source_attachment_id', true)
            : 0;

        $attachment_url = $attachment_id > 0
            ? wp_get_attachment_url($attachment_id)
            : '';

        $attachment_name = $attachment_id > 0
            ? basename((string) get_attached_file($attachment_id))
            : '';

        echo '<div class="options_group">';

        woocommerce_wp_checkbox([
            'id'          => '_dww_fingerprinting_enabled',
            'label'       => 'Activar DWW Fingerprinting',
            'description' => 'Generar fingerprint documental para las compras de este producto.',
            'desc_tip'    => true,
        ]);

        woocommerce_wp_hidden_input([
            'id'    => '_dww_fingerprinting_source_attachment_id',
            'value' => $attachment_id,
        ]);

        echo '<p class="form-field dww-fingerprinting-source-file-field">';
        echo '<label for="_dww_fingerprinting_source_file">Documento protegido</label>';

        echo '<input type="text" id="_dww_fingerprinting_source_file" value="' . esc_attr($attachment_name) . '" readonly style="width:40%;" placeholder="Ningún archivo seleccionado" />';

        if (!empty($attachment_url)) {
            echo ' <a href="' . esc_url($attachment_url) . '" target="_blank" class="button">Ver archivo</a>';
        }

        echo ' <button type="button" class="button dww-fp-select-file">Seleccionar archivo</button>';
        echo ' <button type="button" class="button dww-fp-remove-file">Quitar</button>';

        echo '<span class="description">Selecciona el archivo origen que se usará para generar la copia personalizada.</span>';
        echo '</p>';

        echo '</div>';
    }

    public static function save_fields($product): void
    {
        $enabled = isset($_POST['_dww_fingerprinting_enabled']) ? 'yes' : 'no';

        $product->update_meta_data(
            '_dww_fingerprinting_enabled',
            $enabled
        );

        $attachment_id = isset($_POST['_dww_fingerprinting_source_attachment_id'])
            ? absint($_POST['_dww_fingerprinting_source_attachment_id'])
            : 0;

        $product->update_meta_data(
            '_dww_fingerprinting_source_attachment_id',
            $attachment_id
        );
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

    public static function get_source_file($product): string
    {
        if (!$product) {
            return '';
        }

        $attachment_id = (int) $product->get_meta('_dww_fingerprinting_source_attachment_id');

        if ($attachment_id <= 0) {
            return '';
        }

        $file_path = get_attached_file($attachment_id);

        return $file_path ? (string) $file_path : '';
    }

    public static function get_source_pdf($product): string
    {
        return self::get_source_file($product);
    }

    private static function get_media_uploader_script(): string
    {
        return "
            jQuery(function($) {
                var frame;

                $('.dww-fp-select-file').on('click', function(e) {
                    e.preventDefault();

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

                        $('#_dww_fingerprinting_source_attachment_id').val(attachment.id);
                        $('#_dww_fingerprinting_source_file').val(attachment.filename || attachment.title || attachment.url);
                    });

                    frame.open();
                });

                $('.dww-fp-remove-file').on('click', function(e) {
                    e.preventDefault();

                    $('#_dww_fingerprinting_source_attachment_id').val('');
                    $('#_dww_fingerprinting_source_file').val('');
                });
            });
        ";
    }
}