<?php

namespace Tests\Unit\Service;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NabaHdwp\Constant\PluginConstants;
use NabaHdwp\Service\VueService;
use PHPUnit\Framework\TestCase;

class VueServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_enqueueAssets_registers_hooks(): void
    {
        $service = new VueService();

        Functions\when('wp_enqueue_script')->justReturn();
        Functions\when('wp_enqueue_style')->justReturn();
        Functions\when('esc_url')->returnArg();

        $service->enqueueAssets('app');

        $this->assertNotFalse(has_filter('script_loader_tag', [$service, 'addModuleTypeToScript']));
        $this->assertNotFalse(has_action('wp_footer', [$service, 'printManifestLinks']));
        $this->assertNotFalse(has_action('wp_head', [$service, 'printPreloadLinks']));
    }

    public function test_addModuleTypeToScript_adds_module_type_to_correct_scripts(): void
    {
        $service = new VueService();

        $originalTag = '<script src="http://example.com/script.js" id="nova-stats--app-scripts-js"></script>';
        $expectedTag = '<script type="module" src="http://example.com/script.js" id="nova-stats--app-scripts-js"></script>';

        $this->assertSame($expectedTag, $service->addModuleTypeToScript($originalTag, 'nova-stats--app-scripts'));

        $otherTag = '<script src="http://example.com/other.js" id="other-js"></script>';
        $this->assertSame($otherTag, $service->addModuleTypeToScript($otherTag, 'other-script'));
    }

    public function test_printManifestLinks_outputs_queued_links(): void
    {
        $service = new VueService();

        Functions\when('wp_enqueue_script')->justReturn();
        Functions\when('wp_enqueue_style')->justReturn();
        Functions\when('esc_url')->returnArg();

        $service->enqueueAssets('app');

        $manifestPath = rtrim(PluginConstants::PLUGIN_URL, '/') . '/' . PluginConstants::BUILD_PATH_APP . '/' . PluginConstants::MANIFEST_NAME;
        $expectedOutput = "\n" . '<link rel="manifest" href="' . $manifestPath . '" />' . "\n";

        ob_start();
        $service->printManifestLinks();
        $output = ob_get_clean();

        $this->assertSame($expectedOutput, $output);
    }

    public function test_enqueueAssets_loads_manifest_and_enqueues_correct_files(): void
    {
        $service = new VueService();

        Functions\when('esc_url')->returnArg();

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with(
                'nova-stats--app-scripts',
                \Mockery::type('string'),
                [],
                PluginConstants::VERSION,
                true
            );

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with(
                'nova-stats--app-styles',
                \Mockery::type('string'),
                [],
                PluginConstants::VERSION
            );

        $service->enqueueAssets('app');
    }
}
