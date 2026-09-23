<?php

/**
 * Fired when the plugin is uninstalled.
 */
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('nova_stats_db_setting__api_key');
delete_option('nova_stats_db_setting__gamecenter_base_url');
