<?php

namespace Tests\Unit\Service;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NovaStats\Gamecenter\Constant\PluginConstants;
use NovaStats\Gamecenter\Service\VueService;
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

    public function test_enqueueEmbedAssets_registers_module_type_filter(): void
    {
        $service = new VueService();

        Functions\when('wp_enqueue_script')->justReturn();
        Functions\when('wp_enqueue_style')->justReturn();

        $service->enqueueEmbedAssets();

        $this->assertNotFalse(has_filter('script_loader_tag', [$service, 'addModuleTypeToScript']));
    }

    public function test_addModuleTypeToScript_adds_module_type_to_correct_scripts(): void
    {
        $service = new VueService();

        $originalTag = '<script src="http://example.com/script.js" id="nova-stats--embed-scripts-js"></script>';
        $expectedTag = '<script type="module" src="http://example.com/script.js" id="nova-stats--embed-scripts-js"></script>';

        $this->assertSame($expectedTag, $service->addModuleTypeToScript($originalTag, 'nova-stats--embed-scripts'));

        $otherTag = '<script src="http://example.com/other.js" id="other-js"></script>';
        $this->assertSame($otherTag, $service->addModuleTypeToScript($otherTag, 'other-script'));
    }

    public function test_enqueueEmbedAssets_enqueues_cdn_script_and_style(): void
    {
        $service = new VueService();

        $expectedBase = PluginConstants::EMBED_CDN_HOST . '/embed/gamecenter/' . PluginConstants::EMBED_CDN_VERSION . '/';

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with(
                'nova-stats--embed-scripts',
                $expectedBase . 'gamecenter.js',
                [],
                PluginConstants::VERSION,
                true
            );

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with(
                'nova-stats--embed-styles',
                $expectedBase . 'gamecenter.css',
                [],
                PluginConstants::VERSION
            );

        $service->enqueueEmbedAssets();
    }
}
