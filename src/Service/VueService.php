<?php

namespace NabaHdwp\Service;

defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;

class VueService
{
    public function enqueueAssets(string $type = 'app'): void
    {
        $asset_manifest = $this->getManifestArray($type);
        $buildType = $type === 'app' ? PluginConstants::BUILD_PATH_WP : PluginConstants::BUILD_PATH_WPC;
        $buildBase = PluginConstants::PLUGIN_URL . '/' . $buildType . '/';
        $styleFile = 'style.css';
        $mainTsFile = $type === 'app' ? 'src/main.ts' : 'src/main-compact.ts';
        $version = defined('NABA_HDWP_VERSION') ? NABA_HDWP_VERSION : '0.0.1';

        $manifestPath = $this->getManifestPath($type, true);

        if (!empty($asset_manifest[$mainTsFile]['file'])) {
            $appUrl = $buildBase . $asset_manifest[$mainTsFile]['file'];
            $handle = 'naba-hdwp-app-' . $type;
            wp_enqueue_script($handle, $appUrl, [], $version, true);

            // Add type="module" to the script tag
            add_filter('script_loader_tag', function ($tag, $handle_name) use ($handle) {
                if ($handle_name === $handle) {
                    return str_replace('<script ', '<script type="module" ', $tag);
                }
                return $tag;
            }, 10, 2);
        }

        if (!empty($asset_manifest[$styleFile]['file'])) {
            $styleUrl = $buildBase . $asset_manifest[$styleFile]['file'];
            wp_enqueue_style('naba-hdwp-styles-' . $type, $styleUrl, [], $version);
        }

        // WordPress doesn't have a standard way to enqueue web app manifests,
        // so we can add it to wp_head via an action hook
        add_action('wp_head', function () use ($manifestPath) {
            echo '<link rel="manifest" href="' . esc_url($manifestPath) . '" />' . "\n";
        });
    }

    public function getManifestPath(string $type = 'wp', bool $asUrl = false): string
    {
        return implode('/', [
          $asUrl ? PluginConstants::PLUGIN_URL : PluginConstants::PLUGIN_PATH,
          $type === 'wp' ? PluginConstants::BUILD_PATH_WP : PluginConstants::BUILD_PATH_WPC,
          PluginConstants::MANIFEST_NAME
        ]);
    }

    public function getManifestArray(string $type = 'wp'): array
    {
        if (file_exists($this->getManifestPath($type))) {
            $manifest = json_decode(file_get_contents($this->getManifestPath($type)), true);

            if (!empty($manifest)) {
                return $manifest;
            }
        }

        return [];
    }
}
