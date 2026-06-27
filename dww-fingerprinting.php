<?php

/**
 * Plugin Name: DWW Fingerprinting
 * Plugin URI: https://www.digitalwebworks.es
 * Description: Trazabilidad documental para WooCommerce.
 * Version: 0.4.0
 * Author: Digital Web Works
 * Author URI: https://www.digitalwebworks.es
 * Text Domain: dww-fingerprinting
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DWW_FP_VERSION', '0.4.0');
define('DWW_FP_PLUGIN_FILE', __FILE__);
define('DWW_FP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DWW_FP_PLUGIN_URL', plugin_dir_url(__FILE__));

if (file_exists(DWW_FP_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once DWW_FP_PLUGIN_DIR . 'vendor/autoload.php';
}

require_once DWW_FP_PLUGIN_DIR . 'includes/class-installer.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-logger.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-assets.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-ui.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-generator.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-download-token-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-log-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-download-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-order-downloads.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-dashboard-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-fingerprints-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-fingerprint-detail-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-woocommerce-integration.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-product-settings.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-plugin.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-pdf-processor.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-test-runner.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-migration-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-020.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-030.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-040.php';

register_activation_hook(
    __FILE__,
    ['DWW_Fingerprinting\\Installer', 'install']
);

DWW_Fingerprinting\Admin_Menu::init();
DWW_Fingerprinting\Admin_Assets::init();
DWW_Fingerprinting\WooCommerce_Integration::init();
DWW_Fingerprinting\Product_Settings::init();
DWW_Fingerprinting\Download_Handler::init();
DWW_Fingerprinting\Order_Downloads::init();
DWW_Fingerprinting\Plugin::init();
DWW_Fingerprinting\Test_Runner::init();
