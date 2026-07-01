<?php

/**
 * Plugin Name: DWW Fingerprinting
 * Plugin URI: https://www.digitalwebworks.es
 * Description: Trazabilidad documental para WooCommerce.
 * Version: 0.9.1
 * Author: Digital Web Works
 * Author URI: https://www.digitalwebworks.es
 * Text Domain: dww-fingerprinting
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DWW_FP_VERSION', '0.9.1');
define('DWW_FP_PLUGIN_FILE', __FILE__);
define('DWW_FP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DWW_FP_PLUGIN_URL', plugin_dir_url(__FILE__));

if (file_exists(DWW_FP_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once DWW_FP_PLUGIN_DIR . 'vendor/autoload.php';
}

/*
|--------------------------------------------------------------------------
| Core utilities
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-installer.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-logger.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-upload-mimes.php';

/*
|--------------------------------------------------------------------------
| Admin UI base
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-assets.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-ui.php';

/*
|--------------------------------------------------------------------------
| Processors
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-pdf-processor.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-epub-processor.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-office-open-xml-processor.php';

/*
|--------------------------------------------------------------------------
| Handlers
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/interface-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/abstract-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-pdf-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-epub-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-docx-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-xlsx-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-pptx-fingerprint-handler.php';

/*
|--------------------------------------------------------------------------
| Fingerprinting core
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-generator.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-log-db.php';

/*
|--------------------------------------------------------------------------
| Downloads
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-download-token-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-download-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-order-downloads.php';

/*
|--------------------------------------------------------------------------
| Admin pages
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-dashboard-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-fingerprints-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-fingerprint-detail-page.php';

/*
|--------------------------------------------------------------------------
| WooCommerce integration
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-product-asset.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-product-assets.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-product-settings.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-woocommerce-integration.php';

/*
|--------------------------------------------------------------------------
| Plugin bootstrap
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-plugin.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-test-runner.php';

/*
|--------------------------------------------------------------------------
| Migrations
|--------------------------------------------------------------------------
*/

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
DWW_Fingerprinting\Upload_Mimes::init();

DWW_Fingerprinting\WooCommerce_Integration::init();
DWW_Fingerprinting\Product_Settings::init();

DWW_Fingerprinting\Download_Handler::init();
DWW_Fingerprinting\Order_Downloads::init();

DWW_Fingerprinting\Plugin::init();
DWW_Fingerprinting\Test_Runner::init();