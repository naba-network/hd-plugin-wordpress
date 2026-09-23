<?php

namespace NovaStats\Gamecenter\Shortcode;

defined('ABSPATH') || exit;

use NovaStats\Gamecenter\Constant\PluginConstants;
use NovaStats\Gamecenter\Helper\TemplateEngine;
use NovaStats\Gamecenter\Model\Settings;
use NovaStats\Gamecenter\Service\VueService;

class Gamecenter
{
    public const SHORTCODE_NAME = 'Gamecenter';

    private VueService $vueService;
    private Settings $settings;

    public function __construct()
    {
        $this->vueService = new VueService();
        $this->settings = new Settings();

        $this->assignShortCodeToWordpress(self::SHORTCODE_NAME);
    }

    /**
     * @param non-empty-string $name
     */
    private function assignShortCodeToWordpress(string $name): void
    {
        add_shortcode($name, function (array $atts): string {
            $data = [
              'sessionData' => $this->settings->getSessionInitialData(),
              PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL => $this->settings->getGamecenterBaseUrl(),
            ];

            ob_start();

            TemplateEngine::render('/templates/shortcodes/gamecenter.php', $data);
            $this->vueService->enqueueEmbedAssets();

            return ob_get_clean() ?: '';
        });
    }
}
