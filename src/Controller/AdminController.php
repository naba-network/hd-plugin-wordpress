<?php

namespace NabaHdwp\Controller;

defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;
use NabaHdwp\Helper\TemplateEngine;
use NabaHdwp\Model\Settings;

class AdminController
{
    private Settings $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new Settings();

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
            wp_enqueue_style('naba-hdwp-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css', [], '5.3.8');
        } else {
            // Main config and debug pages
            wp_enqueue_style('naba-hdwp-admin-base', NABA_HDWP_PLUGIN_URL . 'admin/css/admin-base-styles.css', [], $version);
            wp_enqueue_style('naba-hdwp-admin-settings', NABA_HDWP_PLUGIN_URL . 'admin/css/admin-settings.css', ['naba-hdwp-admin-base'], $version);

            wp_enqueue_script('naba-hdwp-sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', [], '1.15.0', true);
            wp_enqueue_script('naba-hdwp-admin-configurator', NABA_HDWP_PLUGIN_URL . 'admin/js/admin-league-configurator.js', ['naba-hdwp-sortable'], $version, true);
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
        add_submenu_page(
            'naba_hdwp_options',
            'Debug',
            'Debug',
            'edit_theme_options',
            'naba_hdwp_options_overview',
            [$this, 'naba_hdwp_admin_settings_overview']
        );
    }

    public function naba_hdwp_admin_settings_page(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $data = [
          'plugin_path' => NABA_HDWP_PLUGIN_URL,
          'form_action' => 'options.php',
          'form_data' => [
            'api_key' => $this->settingsModel->getApiKey(),
            'leagues' => $this->settingsModel->getLeagueConfigAsString(),
            'hd_api_key' => $this->settingsModel->getHockeyDataApiKey(),
            'hd_referrer' => $this->settingsModel->getHockeyDataReferrer(),
          ],
          'options' => [
            'group_name' => Settings::DB_GROUP_NAME,
            'option_leagues' => Settings::FIELD_LEAGUE_SETTINGS,
            'option_api_key' => Settings::FIELD_API_KEY,
            'option_hd_api_key' => Settings::FIELD_HD_API_KEY,
            'option_hd_referrer' => Settings::FIELD_HD_REFERRER,
          ],
        ];

        TemplateEngine::render('/templates/admin/admin.php', $data);
    }

    public function naba_hdwp_admin_settings_overview(): void
    {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        $data = [
          'form_data' => [
            'leagues' => json_encode($this->settingsModel->getLeagueConfig(), JSON_PRETTY_PRINT),
          ],
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
