<?php

namespace NabaHdwp\Service;

defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;

class VueService
{
    private const string SCRIPT_PREFIX = 'nova-stats';

    private bool $hooksRegistered = false;

    /**
     * Enqueues the `embed` build's script/style from the CDN. Safe to call from every
     * shortcode: `wp_enqueue_script`/`wp_enqueue_style` dedupe by handle, so the assets are
     * still only ever printed once per page no matter how many shortcodes (or shortcode
     * instances) triggered it - and only pages that actually render a shortcode call this at
     * all, since it's only ever invoked from a shortcode callback.
     */
    public function enqueueEmbedAssets(): void
    {
        $version = PluginConstants::VERSION;
        $cdnBase = rtrim($this->getEmbedCdnHost(), '/') . '/embed/gamecenter/' . PluginConstants::EMBED_CDN_VERSION . '/';

        $scriptName = self::SCRIPT_PREFIX . '--embed-scripts';
        wp_enqueue_script($scriptName, $cdnBase . 'gamecenter.js', [], $version, true);
        $this->enqueueRuntimeOverrides($scriptName);

        wp_enqueue_style(self::SCRIPT_PREFIX . '--embed-styles', $cdnBase . 'gamecenter.css', [], $version);

        if (!$this->hooksRegistered) {
            add_filter('script_loader_tag', [$this, 'addModuleTypeToScript'], 10, 2);
            $this->hooksRegistered = true;
        }
    }

    /**
     * Inject optional host-level API overrides into window.initialData before the
     * widget bundle runs. Only emitted when the matching wp-config defines are set
     * (local/staging testing, see docs/local-development.md); production has neither
     * define, so nothing is printed and the bundle uses its built-in production URLs.
     *
     * Printed as a classic <script> before the module entry, so the globals exist
     * when the deferred module bundle evaluates the widget's config.ts.
     */
    private function enqueueRuntimeOverrides(string $scriptName): void
    {
        $overrides = [];

        $apiBaseUrl = defined(PluginConstants::LOCAL_API_BASE_URL_DEFINE) ? constant(PluginConstants::LOCAL_API_BASE_URL_DEFINE) : null;
        if (is_string($apiBaseUrl) && $apiBaseUrl !== '') {
            $overrides[PluginConstants::INITIAL_DATA_KEY_API_BASE_URL] = $apiBaseUrl;
        }

        $hockeyDataUrl = defined(PluginConstants::LOCAL_HOCKEYDATA_URL_DEFINE) ? constant(PluginConstants::LOCAL_HOCKEYDATA_URL_DEFINE) : null;
        if (is_string($hockeyDataUrl) && $hockeyDataUrl !== '') {
            $overrides[PluginConstants::INITIAL_DATA_KEY_HOCKEYDATA_URL] = $hockeyDataUrl;
        }

        if ($overrides === []) {
            return;
        }

        $json = wp_json_encode($overrides);

        if ($json === false) {
            return;
        }

        $script = 'window.initialData = window.initialData || {}; Object.assign(window.initialData, ' . $json . ');';
        wp_add_inline_script($scriptName, $script, 'before');
    }

    public function addModuleTypeToScript(string $tag, string $handle): string
    {
        if (strpos($handle, self::SCRIPT_PREFIX) !== false) {
            return str_replace('<script ', '<script type="module" ', $tag);
        }

        return $tag;
    }

    /**
     * The optional NABA_HDWP_EMBED_CDN_HOST define (local/staging testing) repoints the embed
     * build at a locally-served one instead of the production CDN; production has no define
     * and keeps the hardcoded CDN host.
     */
    private function getEmbedCdnHost(): string
    {
        $override = defined(PluginConstants::LOCAL_EMBED_CDN_HOST_DEFINE) ? constant(PluginConstants::LOCAL_EMBED_CDN_HOST_DEFINE) : null;

        return is_string($override) && $override !== '' ? $override : PluginConstants::EMBED_CDN_HOST;
    }
}
