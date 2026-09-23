<?php

namespace NabaHdwp\Constant;

defined('ABSPATH') || exit;

interface PluginConstants
{
    public const string PLUGIN_NAME = NABA_HDWP_PLUGIN_NAME;
    public const string PLUGIN_URL = NABA_HDWP_PLUGIN_URL;
    public const string PLUGIN_PATH = NABA_HDWP_PLUGIN_PATH;
    public const string VERSION = NABA_HDWP_VERSION;

    /**
     * The `embed` build's CDN, shared with the client portal's manual embed snippet
     * (`naba-hdwp-widgets`'s docs/cloudflare-cdn-deploy.md). `gamecenter.js`/`gamecenter.css`
     * have stable, unhashed filenames there, so no manifest lookup is needed.
     *
     * Pinned to `latest` rather than a specific released version - accepted trade-off to avoid
     * having to bump/test a pinned version on every widget release; revisit if a bad `latest`
     * deploy ever breaks WordPress sites with no corresponding plugin release to roll back to.
     */
    public const string EMBED_CDN_HOST = 'https://cdn.statistics.stream';
    public const string EMBED_CDN_VERSION = 'latest';

    /** Optional wp-config define to point at a local/staging embed build (see docs/local-development.md). */
    public const string LOCAL_EMBED_CDN_HOST_DEFINE = 'NABA_HDWP_EMBED_CDN_HOST';

    /**
     * Nova Stats backend base URL. Hardcoded to match the Gamecenter frontend
     * (naba-hdwp-widgets config.ts). Override via the `naba_hdwp_api_base_url` filter.
     */
    public const string NOVA_STATS_API_BASE_URL = 'https://nova-stats.com';

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

    /**
     * window.initialData key for the host's own Gamecenter page URL (naba-hdwp-widgets
     * config.ts's `getGamecenterBaseUrl()`), read by the schedule slider/team page widgets to
     * link back to it. Reusable/set-once, unlike the shortcodes' own per-instance attributes.
     */
    public const string INITIAL_DATA_KEY_GAMECENTER_BASE_URL = 'gamecenterBaseUrl';

    /** Minimum PHP version the plugin requires (see plugin.php header). */
    public const string MIN_PHP_VERSION = '8.3';
}
