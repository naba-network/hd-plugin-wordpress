<?php

namespace NabaHdwp\Shortcode;

defined('ABSPATH') || exit;

use NabaHdwp\Helper\TemplateEngine;
use NabaHdwp\Model\Settings;
use NabaHdwp\Service\VueService;

class Gamecenter
{
    public const SHORTCODE_NAME = 'Naba-Hdwp-Gamecenter';

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
            $data = [
              'sessionData' => $this->settings->getSessionInitialData()
            ];

            ob_start();

            TemplateEngine::render('/templates/shortcodes/gamecenter.php', $data);
            $this->vueService->enqueueAssets();

            return ob_get_clean();
        });
    }
}
