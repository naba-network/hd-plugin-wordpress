<?php

/**
 * Fired when the plugin is uninstalled.
 */
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('naba_hdwp_db_setting__api_key');
delete_option('naba_hdwp_db_setting__hd_api_key');
delete_option('naba_hdwp_db_setting__hd_referrer');
delete_option('naba_hdwp_db_setting__league_settings');
