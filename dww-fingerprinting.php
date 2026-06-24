<?php
/**
 * Plugin Name: DWW Fingerprinting
 * Plugin URI: https://www.digitalwebworks.es
 * Description: Trazabilidad documental para WooCommerce.
 * Version: 0.1.0
 * Author: Digital Web Works
 * Author URI: https://www.digitalwebworks.es
 * Text Domain: dww-fingerprinting
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DWW_FP_VERSION', '0.1.0');
define('DWW_FP_PLUGIN_FILE', __FILE__);
define('DWW_FP_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once DWW_FP_PLUGIN_DIR . 'includes/class-plugin.php';

DWW_Fingerprinting\Plugin::init();

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}