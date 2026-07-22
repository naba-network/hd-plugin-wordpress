<?php

/**
 * Plugin Name: NovaStats HockeyData
 * Plugin URI:  https://nova-stats.com/
 * Description: The hockey game center widget for WordPress.
 * Version:     0.0.4
 * Author:      Nachbauer GmbH
 * Author URI:  https://www.nachbauer.gmbh
 * Text Domain: hd-plugin-wordpress
 * Requires PHP: 8.3
 * Requires at least: 5.8
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

namespace NabaHdwp;

defined('ABSPATH') || exit;

// Autoload dependencies using Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback if composer install was not run
    spl_autoload_register(function ($class) {
        $prefix = __NAMESPACE__ . '\\';
        $base_dir = __DIR__ . '/src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relative_class_name = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class_name) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}

class Plugin
{
    private static ?Plugin $instance = null;

    public static function getInstance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->defineConstants();
        $this->initHooks();
    }

    private function defineConstants(): void
    {
        define('NABA_HDWP_VERSION', '0.0.4');
        define('NABA_HDWP_PLUGIN_NAME', 'hd-plugin-wordpress');
        define('NABA_HDWP_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('NABA_HDWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
    }

    private function initHooks(): void
    {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'initUpdater']);
    }

    public function initUpdater(): void
    {
        if (!class_exists(\YahnisElsts\PluginUpdateChecker\v5\PucFactory::class)) {
            return;
        }

        // Local/staging: point the update checker at a self-hosted metadata JSON
        // (NABA_HDWP_UPDATE_SOURCE) so the WordPress update flow can be exercised
        // without cutting a GitHub release. This builds a generic (non-VCS) checker,
        // so the release-asset/token setup below does not apply and we return early.
        // The define is absent in production -> the GitHub release-asset path is used.
        $localUpdateSource = defined(Constant\PluginConstants::LOCAL_UPDATE_SOURCE_DEFINE)
            ? constant(Constant\PluginConstants::LOCAL_UPDATE_SOURCE_DEFINE)
            : null;

        if (is_string($localUpdateSource) && $localUpdateSource !== '') {
            \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                $localUpdateSource,
                __FILE__,
                'novastats-hockeydata'
            );

            return;
        }

        $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/naba-network/hd-plugin-wordpress/',
            __FILE__,
            'novastats-hockeydata'
        );

        if (!$updateChecker instanceof \YahnisElsts\PluginUpdateChecker\v5p6\Vcs\BaseChecker) {
            return;
        }

        $vcsApi = $updateChecker->getVcsApi();

        if ($vcsApi instanceof \YahnisElsts\PluginUpdateChecker\v5p6\Vcs\GitHubApi) {
            // Update from the built zip attached to GitHub releases by the CI
            // pipeline (.github/workflows/release.yml). Branch zipballs must not
            // be used: they lack the gitignored vendor/ and frontend/ artifacts.
            $vcsApi->enableReleaseAssets();
        }

        // The GitHub repository must be public for unauthenticated update checks.
        // If it is (or becomes) private, define NABA_HDWP_GITHUB_TOKEN in
        // wp-config.php with a GitHub token that has read access to the repo.
        if (defined('NABA_HDWP_GITHUB_TOKEN')) {
            $token = constant('NABA_HDWP_GITHUB_TOKEN');
            if (is_string($token) && $token !== '') {
                $updateChecker->setAuthentication($token);
            }
        }
    }

    public function init(): void
    {
        new Controller\AdminController();
        new Shortcode\Gamecenter();
        new Shortcode\ScheduleSlider();
        new Shortcode\TeamPage();
    }
}

Plugin::getInstance();
