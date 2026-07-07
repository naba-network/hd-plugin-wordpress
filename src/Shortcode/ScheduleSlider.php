<?php

namespace NabaHdwp\Shortcode;

defined('ABSPATH') || exit;

use NabaHdwp\Helper\TemplateEngine;
use NabaHdwp\Model\Settings;
use NabaHdwp\Service\VueService;

class ScheduleSlider
{
    public const SHORTCODE_NAME = 'Naba-Hdwp-Schedule-Slider';
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
              'sessionData' => $this->settings->getSessionInitialData()
            ];

            ob_start();

            TemplateEngine::render('/templates/shortcodes/schedule-slider.php', $data);
            $this->vueService->enqueueAssets('compact');

            return ob_get_clean() ?: '';
        });
    }
}
