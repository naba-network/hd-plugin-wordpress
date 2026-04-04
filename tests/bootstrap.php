<?php

/**
 * PHPUnit test bootstrap file
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define WordPress constants used in the plugin
define('ABSPATH', true);
define('NABA_HDWP_VERSION', '0.0.3');
define('NABA_HDWP_PLUGIN_NAME', 'hd-plugin-wordpress');
define('NABA_HDWP_PLUGIN_URL', 'http://example.com/wp-content/plugins/hd-plugin-wordpress/');
define('NABA_HDWP_PLUGIN_PATH', dirname(__DIR__) . '/');
