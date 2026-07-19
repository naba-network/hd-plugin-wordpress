<?php

define('ABSPATH', true);

// Read the version dynamically from the plugin header so it cannot drift.
$pluginHeader = (string) file_get_contents(__DIR__ . '/plugin.php');
preg_match('/^\s*\*\s*Version:\s*(\S+)/m', $pluginHeader, $versionMatch);
define('NABA_HDWP_VERSION', $versionMatch[1] ?? '0.0.0');
define('NABA_HDWP_PLUGIN_NAME', 'hd-plugin-wordpress');
define('NABA_HDWP_PLUGIN_URL', 'http://localhost/wp-content/plugins/hd-plugin-wordpress');
define('NABA_HDWP_PLUGIN_PATH', __DIR__);

$scripts = [];
$styles = [];
$head_actions = [];
$script_filters = [];

function wp_enqueue_script($handle, $src, $deps, $ver, $in_footer)
{
    global $scripts;
    $scripts[$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver, 'in_footer' => $in_footer];
}

function wp_enqueue_style($handle, $src, $deps, $ver)
{
    global $styles;
    $styles[$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver];
}

function add_action($hook, $callback)
{
    global $head_actions;
    if ($hook === 'wp_head' || $hook === 'wp_footer') {
        $head_actions[] = $callback;
    }
}

function add_filter($hook, $callback, $priority, $accepted_args)
{
    global $script_filters;
    if ($hook === 'script_loader_tag') {
        $script_filters[] = $callback;
    }
}

function esc_url($url)
{
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/src/Constant/PluginConstants.php';
require_once __DIR__ . '/src/Service/VueService.php';

use NabaHdwp\Service\VueService;

$service = new VueService();

// Test the full app enqueue
$service->enqueueAssets('app');

// Also test the compact app enqueue just for demonstration
// $service->enqueueAssets('compact');

$html = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>VueService Test</title>\n";

// Render styles
foreach ($styles as $handle => $style) {
    $src = $style['src'] . '?ver=' . $style['ver'];
    $html .= "    <link rel=\"stylesheet\" id=\"{$handle}-css\" href=\"{$src}\" type=\"text/css\" media=\"all\" />\n";
}

// Render wp_head actions
foreach ($head_actions as $action) {
    ob_start();
    $action();
    $output = ob_get_clean();
    $html .= "    " . trim($output) . "\n";
}

$html .= "</head>\n<body>\n    <h1>VueService Local Test</h1>\n    <p>Check the source to see the generated script and style tags.</p>\n    <div id=\"app\"></div>\n\n";

// Render scripts
foreach ($scripts as $handle => $script) {
    $src = $script['src'] . '?ver=' . $script['ver'];
    $tag = "<script src=\"{$src}\" id=\"{$handle}-js\"></script>";

    // Apply filters
    foreach ($script_filters as $filter) {
        $tag = $filter($tag, $handle);
    }

    $html .= "    " . $tag . "\n";
}

$html .= "</body>\n</html>\n";

file_put_contents(__DIR__ . '/test.html', $html);
echo "test.html generated successfully!\n";
