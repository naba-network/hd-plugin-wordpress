<?php

/**
 * PHPUnit test bootstrap file
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define WordPress constants used in the plugin
define('ABSPATH', true);
define('NOVA_STATS_VERSION', '0.0.3');
define('NOVA_STATS_PLUGIN_NAME', 'hd-plugin-wordpress');
define('NOVA_STATS_PLUGIN_URL', 'http://example.com/wp-content/plugins/hd-plugin-wordpress/');
define('NOVA_STATS_PLUGIN_PATH', dirname(__DIR__) . '/');
