<?php

/**
 * Plugin Name: DWW Fingerprinting
 * Plugin URI: https://www.digitalwebworks.es
 * Description: Professional document fingerprinting for WooCommerce.
 * Version: 0.9.4
 * Requires at least: 6.8
 * Requires PHP: 8.1
 * Author: Digital Web Works
 * Author URI: https://www.digitalwebworks.es
 * Text Domain: dww-fingerprinting
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DWW_FP_VERSION', '0.9.4');
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
require_once DWW_FP_PLUGIN_DIR . 'includes/class-open-document-processor.php';

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
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-odt-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-ods-fingerprint-handler.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/handlers/class-odp-fingerprint-handler.php';

/*
|--------------------------------------------------------------------------
| Fingerprinting core
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-payload.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-evidence.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-extractor.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-trust-score.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-integrity.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-verifier.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-file-validator.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-generator.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-fingerprint-log-db.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-storage-security.php';

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
| Migrations
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/class-migration-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-020.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-030.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-040.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-050.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/migrations/class-migration-060.php';

/*
|--------------------------------------------------------------------------
| Health Checks
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/interface-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/interface-repairable-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-health-check-abstract.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-php-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-wordpress-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-woocommerce-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-database-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-storage-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-integrity-key-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-handlers-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-formats-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/health-checks/class-migrations-health-check.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-health-check-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-health-check-registry.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/class-health-check.php';

/*
|--------------------------------------------------------------------------
| REST API
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-rest-api-auth.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/interface-rest-endpoint.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-rest-endpoint-abstract.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-rest-api-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-rest-api-registry.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-health-rest-endpoint.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-stats-rest-endpoint.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-rest-verification-response.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-verify-rest-endpoint.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/rest/class-fingerprint-rest-endpoint.php';

/*
|--------------------------------------------------------------------------
| Dashboard Pro
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/class-dashboard-widget-interface.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/class-dashboard-widget-abstract.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/class-dashboard-service.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/class-dashboard-manager.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/class-dashboard-registry.php';

require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-hero-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-alerts-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-kpi-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-health-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-quick-actions-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-api-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-recent-fingerprints-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-activity-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-formats-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-tokens-dashboard-widget.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/dashboard/widgets/class-system-info-dashboard-widget.php';

/*
|--------------------------------------------------------------------------
| Admin pages
|--------------------------------------------------------------------------
*/

require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-dashboard-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-fingerprints-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-fingerprint-detail-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-verify-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-verification-report.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-doctor-page.php';
require_once DWW_FP_PLUGIN_DIR . 'includes/admin/class-rest-api-page.php';

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
