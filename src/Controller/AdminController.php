<?php

namespace NovaStats\Gamecenter\Controller;

defined('ABSPATH') || exit;

use NovaStats\Gamecenter\Helper\TemplateEngine;
use NovaStats\Gamecenter\Model\Settings;
use NovaStats\Gamecenter\Service\StatusService;

class AdminController
{
    private Settings $settingsModel;
    private StatusService $statusService;

    public function __construct()
    {
        $this->settingsModel = new Settings();
        $this->statusService = new StatusService($this->settingsModel);

        add_action('admin_menu', [$this, 'nova_stats_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'nova_stats_admin_enqueue_scripts']);
    }

    public function nova_stats_admin_enqueue_scripts(string $hook_suffix): void
    {
        // Check if we are on one of our plugin pages
        if (strpos($hook_suffix, 'nova_stats_options') === false) {
            return;
        }

        // Bundled locally (admin/vendor/) instead of loading from a CDN.
        // Used on all plugin admin pages (Configuration, Debug).
        wp_enqueue_style('nova-stats-bootstrap', NOVA_STATS_PLUGIN_URL . 'admin/vendor/bootstrap.min.css', [], '5.3.8');
    }

    public function nova_stats_admin_menu(): void
    {
        add_menu_page(
            'Gamecenter Configuration',
            'nova stats',
            'edit_theme_options',
            'nova_stats_options',
            [$this, 'nova_stats_admin_settings_page'],
            $this->getMenuIconDataUri()
        );
        add_submenu_page(
            'nova_stats_options',
            'nova stats - Setup',
            'Setup',
            'edit_theme_options',
            'nova_stats_options',
            [$this, 'nova_stats_admin_settings_page']
        );
        // The debug page is a development aid; only expose it when WP_DEBUG is on.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_submenu_page(
                'nova_stats_options',
                'Debug',
                'Debug',
                'edit_theme_options',
                'nova_stats_options_overview',
                [$this, 'nova_stats_admin_settings_overview']
            );
        }
    }

    public function nova_stats_admin_settings_page(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $data = [
          'plugin_path' => NOVA_STATS_PLUGIN_URL,
          'form_action' => 'options.php',
          'portal_url' => $this->statusService->getClientPortalUrl(),
          'form_data' => [
            'api_key' => $this->settingsModel->getApiKey(),
          ],
          'options' => [
            'group_name' => Settings::DB_GROUP_NAME,
            'option_api_key' => Settings::FIELD_API_KEY,
          ],
        ];

        TemplateEngine::render('/templates/admin/admin.php', $data);
    }

    public function nova_stats_admin_settings_overview(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        // A "Re-check" link busts the short connection-check cache. GET is fine
        // (read-only), guarded by a nonce.
        $forceRefresh = isset($_GET['nova_stats_recheck'])
            && check_admin_referer('nova_stats_recheck');

        $data = [
          'raw' => $this->statusService->getRawValidation(),
          'recheck_url' => wp_nonce_url(
              add_query_arg('nova_stats_recheck', '1', admin_url('admin.php?page=nova_stats_options_overview')),
              'nova_stats_recheck'
          ),
          'connection' => $this->statusService->checkConnection($forceRefresh),
          'diagnostics' => $this->statusService->getDiagnostics(),
        ];

        TemplateEngine::render('/templates/admin/debug.php', $data);
    }

    /**
     * A `data:image/svg+xml` icon_url (rather than a plain file URL) makes WordPress add its own
     * `.svg` class to the menu icon, which is what applies `background-size: 20px auto` in core
     * admin CSS - a plain URL gets no such sizing and renders at the SVG's native size instead.
     */
    private function getMenuIconDataUri(): string
    {
        $svg = (string) file_get_contents(NOVA_STATS_PLUGIN_PATH . 'admin/nova-stats-brand-logo-monochrom.svg');

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
