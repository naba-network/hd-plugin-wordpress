<?php

namespace NabaHdwp\Shortcode;

defined('ABSPATH') || exit;

use NabaHdwp\Helper\TemplateEngine;
use NabaHdwp\Model\Settings;
use NabaHdwp\Service\VueService;

class TeamPage
{
    public const SHORTCODE_NAME = 'Naba-Hdwp-Team-Page';

    private VueService $vueService;
    private Settings $settings;

    public function __construct()
    {
        $this->vueService = new VueService();
        $this->settings = new Settings();

        $this->assignShortCodeToWordpress(self::SHORTCODE_NAME);
    }

    private function assignShortCodeToWordpress(string $name): void
    {
        add_shortcode($name, function ($atts) {
            $default_atts = [
              'division' => '',
              'team' => '',
              'image_path' => '',
            ];
            $args = shortcode_atts($default_atts, $atts);

            $data = [
              'sessionData' => $this->settings->getSessionInitialData(),
              'divisionId' => $args['division'],
              'teamId' => $args['team'],
              'playerImagePath' => $args['image_path'],
            ];

            ob_start();

            TemplateEngine::render('/templates/shortcodes/team-page.php', $data);
            $this->vueService->enqueueAssets('compact');

            return ob_get_clean();
        });
    }
}
