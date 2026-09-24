<?php

namespace Tests\Unit\Model;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NovaStats\Gamecenter\Model\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        Functions\when('add_action')->justReturn(true);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_getApiKey_reads_its_own_option(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with(Settings::FIELD_API_KEY, '')
            ->andReturn('token-123');

        $this->assertSame('token-123', (new Settings())->getApiKey());
    }

    public function test_setup_db_fields_registers_the_api_key_under_the_main_group(): void
    {
        $registered = [];
        Functions\expect('register_setting')
            ->once()
            ->andReturnUsing(function (string $group, string $name) use (&$registered): void {
                $registered[] = [$group, $name];
            });

        (new Settings())->setup_db_fields();

        $this->assertContains([Settings::DB_GROUP_NAME, Settings::FIELD_API_KEY], $registered);
    }

    public function test_getSessionInitialData_only_contains_the_api_token(): void
    {
        Functions\when('get_option')->justReturn('token-123');

        $this->assertSame(['apiToken' => 'token-123'], (new Settings())->getSessionInitialData());
    }
}
