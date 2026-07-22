<?php

namespace NabaHdwp\Constant;

defined('ABSPATH') || exit;

interface PluginConstants
{
    public const string PLUGIN_NAME = NABA_HDWP_PLUGIN_NAME;
    public const string BUILD_PATH_APP = 'frontend/app';
    public const string BUILD_PATH_COMPACT = 'frontend/compact';
    public const string MANIFEST_NAME = 'manifest.json';
    public const string PLUGIN_URL = NABA_HDWP_PLUGIN_URL;
    public const string PLUGIN_PATH = NABA_HDWP_PLUGIN_PATH;
    public const string VERSION = NABA_HDWP_VERSION;

    /**
     * Nova Stats backend base URL. Hardcoded to match the Gamecenter frontend
     * (naba-hdwp-widgets config.ts). Override via the `naba_hdwp_api_base_url` filter.
     */
    public const string NOVA_STATS_API_BASE_URL = 'https://datahub.h-sc.at';

    /** Public, unauthenticated token-validation endpoint (expects `?token=`). */
    public const string VALIDATE_TOKEN_PATH = '/api/v1/validate-api-token';

    /** Client portal app path; a `/{locale}` prefix is prepended at runtime. */
    public const string CLIENT_PORTAL_PATH = '/client-portal';

    /**
     * Optional wp-config defines for local/staging testing (see docs/local-development.md).
     * All are absent in production, so production behaviour is unchanged.
     */
    public const string LOCAL_API_BASE_URL_DEFINE = 'NABA_HDWP_API_BASE_URL';
    public const string LOCAL_HOCKEYDATA_URL_DEFINE = 'NABA_HDWP_HOCKEYDATA_URL';
    public const string LOCAL_UPDATE_SOURCE_DEFINE = 'NABA_HDWP_UPDATE_SOURCE';

    /** window.initialData keys the widget reads for runtime API overrides (naba-hdwp-widgets config.ts). */
    public const string INITIAL_DATA_KEY_API_BASE_URL = 'novaStatsApiUrl';
    public const string INITIAL_DATA_KEY_HOCKEYDATA_URL = 'hockeyDataApiUrl';

    /** Minimum PHP version the plugin requires (see plugin.php header). */
    public const string MIN_PHP_VERSION = '8.3';
}
