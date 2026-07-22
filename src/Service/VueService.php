<?php

namespace NabaHdwp\Service;

defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;

class VueService
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $manifests = [];

    private string $buildType = 'app';

    private const string SCRIPT_PREFIX = 'nova-stats';

    private bool $hooksRegistered = false;

    /** @var array<string> */
    private array $queuedManifestPaths = [];

    /** @var array<string, string> */
    private array $preloadLinks = [];

    public function enqueueAssets(string $type = 'app'): void
    {
        $version = PluginConstants::VERSION;
        $this->buildType = $type;

        if (!isset($this->manifests[$type])) {
            $this->manifests[$type] = $this->loadManifest();
        }

        $styleFile = $this->getVersionedDocument('style.css');
        $mainTsFileName = $type === 'app' ? 'src/main.ts' : 'src/main-compact.ts';
        $mainTsFile = $this->getVersionedDocument($mainTsFileName);

        // Add script files.
        $scriptName = $this->getAssetIncludeName('scripts');
        wp_enqueue_script($scriptName, $mainTsFile, [], $version, true);
        $this->enqueueRuntimeOverrides($scriptName);

        // Add style files.
        wp_enqueue_style($this->getAssetIncludeName('styles'), $styleFile, [], $version);

        $this->registerPreloadLinks($type, $mainTsFileName);

        $manifestPath = $this->getManifestPath(true);
        if (!in_array($manifestPath, $this->queuedManifestPaths, true)) {
            $this->queuedManifestPaths[] = $manifestPath;
        }

        if (!$this->hooksRegistered) {
            add_filter('script_loader_tag', [$this, 'addModuleTypeToScript'], 10, 2);
            add_action('wp_footer', [$this, 'printManifestLinks']);
            add_action('wp_head', [$this, 'printPreloadLinks']);
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

    public function printManifestLinks(): void
    {
        foreach ($this->queuedManifestPaths as $manifestPath) {
            echo "\n" . '<link rel="manifest" href="' . esc_url($manifestPath) . '" />' . "\n";
        }
    }

    public function printPreloadLinks(): void
    {
        foreach ($this->preloadLinks as $link) {
            echo "\n" . '<link rel="modulepreload" href="' . esc_url($link) . '" />' . "\n";
        }
    }

    private function registerPreloadLinks(string $type, string $mainTsFileName): void
    {
        if (!isset($this->manifests[$type]) || !isset($this->manifests[$type][$mainTsFileName])) {
            return;
        }

        $mainChunk = $this->manifests[$type][$mainTsFileName];

        // Only the entry's static imports are preloaded. Preloading 'dynamicImports' as
        // well would force browsers to download every lazily code-split route chunk up
        // front, defeating the app's lazy loading and increasing the initial page weight.
        if (!empty($mainChunk['imports'])) {
            foreach ($mainChunk['imports'] as $importKey) {
                if (isset($this->manifests[$type][$importKey]['file'])) {
                    $this->preloadLinks[$importKey] = $this->buildOutputPath($this->manifests[$type][$importKey]['file']);
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadManifest(): array
    {
        $path = $this->getManifestPath();

        if (file_exists($path)) {
            $content = file_get_contents($path);
            if ($content !== false) {
                $manifest = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($manifest)) {
                    return $manifest;
                }
            }
        }

        return [];
    }

    private function getManifestPath(bool $asUrl = false): string
    {
        $base = $asUrl ? PluginConstants::PLUGIN_URL : PluginConstants::PLUGIN_PATH;

        return rtrim($base, '/') . '/' . $this->getAssetPath() . '/' . PluginConstants::MANIFEST_NAME;
    }

    private function getVersionedDocument(string $fileName): string
    {
        if (!isset($this->manifests[$this->buildType]) || !isset($this->manifests[$this->buildType][$fileName]['file'])) {
            // This file will not be found and lead to 404 in the browser but this helpful for debugging.
            return $this->buildOutputPath($fileName);
        }

        return $this->buildOutputPath($this->manifests[$this->buildType][$fileName]['file']);
    }

    private function buildOutputPath(string $fileName): string
    {
        $buildBase = rtrim(PluginConstants::PLUGIN_URL, '/') . '/' . $this->getAssetPath() . '/';

        return $buildBase . $fileName;
    }

    private function getAssetPath(): string
    {
        return $this->buildType === 'app' ? PluginConstants::BUILD_PATH_APP : PluginConstants::BUILD_PATH_COMPACT;
    }

    private function getAssetIncludeName(string $suffix): string
    {
        return sprintf('%s--%s-%s', self::SCRIPT_PREFIX, $this->buildType, $suffix);
    }
}
