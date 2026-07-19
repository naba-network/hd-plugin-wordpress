<?php

namespace NabaHdwp\Controller;

defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;
use NabaHdwp\Helper\TemplateEngine;
use NabaHdwp\Model\Settings;
use NabaHdwp\Service\StatusService;

class AdminController
{
    private Settings $settingsModel;
    private StatusService $statusService;

    public function __construct()
    {
        $this->settingsModel = new Settings();
        $this->statusService = new StatusService($this->settingsModel);

        add_action('admin_menu', [$this, 'naba_hdwp_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'naba_hdwp_admin_enqueue_scripts']);
    }

    public function naba_hdwp_admin_enqueue_scripts(string $hook_suffix): void
    {
        // Check if we are on one of our plugin pages
        if (strpos($hook_suffix, 'naba_hdwp_options') === false) {
            return;
        }

        $version = PluginConstants::VERSION;

        if (strpos($hook_suffix, 'naba_hdwp_options_documentation') !== false) {
            // Bundled locally (admin/vendor/) instead of loading from a CDN.
            wp_enqueue_style('naba-hdwp-bootstrap', NABA_HDWP_PLUGIN_URL . 'admin/vendor/bootstrap.min.css', [], '5.3.8');
        } else {
            // Main config and debug pages
            wp_enqueue_style('naba-hdwp-admin-base', NABA_HDWP_PLUGIN_URL . 'admin/css/admin-base-styles.css', [], $version);
            wp_enqueue_style('naba-hdwp-admin-settings', NABA_HDWP_PLUGIN_URL . 'admin/css/admin-settings.css', ['naba-hdwp-admin-base'], $version);
        }
    }

    public function naba_hdwp_admin_menu(): void
    {
        add_menu_page(
            'Naba HDWP Configuration',
            'Naba HDWP',
            'edit_theme_options',
            'naba_hdwp_options',
            [$this, 'naba_hdwp_admin_settings_page']
        );
        add_submenu_page(
            'naba_hdwp_options',
            'Configuration',
            'Configuration',
            'edit_theme_options',
            'naba_hdwp_options',
            [$this, 'naba_hdwp_admin_settings_page']
        );
        add_submenu_page(
            'naba_hdwp_options',
            'Documentation',
            'Documentation',
            'edit_theme_options',
            'naba_hdwp_options_documentation',
            [$this, 'naba_hdwp_admin_settings_documentation']
        );
        // The debug page is a development aid; only expose it when WP_DEBUG is on.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_submenu_page(
                'naba_hdwp_options',
                'Debug',
                'Debug',
                'edit_theme_options',
                'naba_hdwp_options_overview',
                [$this, 'naba_hdwp_admin_settings_overview']
            );
        }
    }

    public function naba_hdwp_admin_settings_page(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        // A "Re-check" link busts the short connection-check cache. GET is fine
        // (read-only), guarded by a nonce.
        $forceRefresh = isset($_GET['naba_hdwp_recheck'])
            && check_admin_referer('naba_hdwp_recheck');

        $data = [
          'plugin_path' => NABA_HDWP_PLUGIN_URL,
          'form_action' => 'options.php',
          'portal_url' => $this->statusService->getClientPortalUrl(),
          'recheck_url' => wp_nonce_url(
              add_query_arg('naba_hdwp_recheck', '1', admin_url('admin.php?page=naba_hdwp_options')),
              'naba_hdwp_recheck'
          ),
          'form_data' => [
            'api_key' => $this->settingsModel->getApiKey(),
          ],
          'options' => [
            'group_name' => Settings::DB_GROUP_NAME,
            'option_api_key' => Settings::FIELD_API_KEY,
          ],
          'connection' => $this->statusService->checkConnection($forceRefresh),
          'diagnostics' => $this->statusService->getDiagnostics(),
        ];

        TemplateEngine::render('/templates/admin/admin.php', $data);
    }

    public function naba_hdwp_admin_settings_overview(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $data = [
          'raw' => $this->statusService->getRawValidation(),
        ];

        TemplateEngine::render('/templates/admin/debug.php', $data);
    }

    public function naba_hdwp_admin_settings_documentation(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $data = [];

        TemplateEngine::render('/templates/admin/documentation.php', $data);
    }
}
