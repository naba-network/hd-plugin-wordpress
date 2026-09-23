<?php

// Define constants for PHPStan analysis
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('NOVA_STATS_PLUGIN_NAME')) {
    define('NOVA_STATS_PLUGIN_NAME', 'hd-plugin-wordpress');
}

if (!defined('NOVA_STATS_PLUGIN_URL')) {
    define('NOVA_STATS_PLUGIN_URL', 'https://example.com/wp-content/plugins/hd-plugin-wordpress/');
}

if (!defined('NOVA_STATS_PLUGIN_PATH')) {
    define('NOVA_STATS_PLUGIN_PATH', __DIR__ . '/');
}

if (!defined('NOVA_STATS_VERSION')) {
    define('NOVA_STATS_VERSION', '0.0.1');
}
