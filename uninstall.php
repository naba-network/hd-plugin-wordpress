<?php

/**
 * Fired when the plugin is uninstalled.
 */
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('nova_stats_db_setting__api_key');
// The "Gamecenter Link" setting was removed from the plugin (the URL now comes from the Nova Stats
// backend instead) - this hardcoded string cleans up the leftover DB row on sites that had set it.
delete_option('nova_stats_db_setting__gamecenter_base_url');
