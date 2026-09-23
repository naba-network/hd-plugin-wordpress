<?php

namespace NovaStats\Gamecenter\Shortcode;

defined('ABSPATH') || exit;

use NovaStats\Gamecenter\Constant\PluginConstants;
use NovaStats\Gamecenter\Helper\TemplateEngine;
use NovaStats\Gamecenter\Model\Settings;
use NovaStats\Gamecenter\Service\VueService;

class TeamPage
{
    public const SHORTCODE_NAME = 'Gamecenter-Team-Page';

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
            $default_atts = [
              'division' => '',
              'team' => '',
              'image_path' => '',
            ];
            $args = shortcode_atts($default_atts, $atts);

            $data = [
              'sessionData' => $this->settings->getSessionInitialData(),
              PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL => $this->settings->getGamecenterBaseUrl(),
              'divisionId' => $args['division'],
              'teamId' => $args['team'],
              'playerImagePath' => $args['image_path'],
            ];

            ob_start();

            TemplateEngine::render('/templates/shortcodes/team-page.php', $data);
            $this->vueService->enqueueEmbedAssets();

            return ob_get_clean() ?: '';
        });
    }
}
