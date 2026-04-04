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

        if ($mainTsFile) {
            $scriptName = $this->getAssetIncludeName('scripts');
            wp_enqueue_script($scriptName, $mainTsFile, [], $version, true);
        }

        if ($styleFile) {
            wp_enqueue_style($this->getAssetIncludeName('styles'), $styleFile, [], $version);
        }

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

        if (!empty($mainChunk['imports'])) {
            foreach ($mainChunk['imports'] as $importKey) {
                if (isset($this->manifests[$type][$importKey]['file'])) {
                    $this->preloadLinks[$importKey] = $this->buildOutputPath($this->manifests[$type][$importKey]['file']);
                }
            }
        }

        if (!empty($mainChunk['dynamicImports'])) {
            foreach ($mainChunk['dynamicImports'] as $importKey) {
                if (isset($this->manifests[$type][$importKey]['file'])) {
                    $this->preloadLinks[$importKey] = $this->buildOutputPath($this->manifests[$type][$importKey]['file']);
                }
            }
        }
    }

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

    private function getVersionedDocument(string $fileName): ?string
    {
        if (!isset($this->manifests[$this->buildType]) || !isset($this->manifests[$this->buildType][$fileName]['file'])) {
            return null;
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
