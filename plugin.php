<?php

/**
 * Plugin Name: NovaStats HockeyData
 * Plugin URI:  https://nova-stats.com/
 * Description: The hockey game center widget for WordPress.
 * Version:     0.0.1
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
        if (class_exists(\YahnisElsts\PluginUpdateChecker\v5\PucFactory::class)) {
            $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                'https://github.com/naba-network/hd-plugin-wordpress/',
                __FILE__,
                'novastats-hockeydata'
            );
            $updateChecker->setBranch('main');
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
