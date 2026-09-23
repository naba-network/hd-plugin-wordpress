<?php

namespace Tests\Unit\Model;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NabaHdwp\Model\Settings;
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

    public function test_getGamecenterBaseUrl_reads_its_own_option(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with(Settings::FIELD_GAMECENTER_BASE_URL, '')
            ->andReturn('https://example.com/gamecenter');

        $this->assertSame('https://example.com/gamecenter', (new Settings())->getGamecenterBaseUrl());
    }

    public function test_getGamecenterBaseUrl_defaults_to_empty_string_when_unset(): void
    {
        Functions\when('get_option')->justReturn('');

        $this->assertSame('', (new Settings())->getGamecenterBaseUrl());
    }

    public function test_setup_db_fields_registers_the_api_key_under_the_main_group(): void
    {
        $registered = [];
        Functions\expect('register_setting')
            ->twice()
            ->andReturnUsing(function (string $group, string $name) use (&$registered): void {
                $registered[] = [$group, $name];
            });

        (new Settings())->setup_db_fields();

        $this->assertContains([Settings::DB_GROUP_NAME, Settings::FIELD_API_KEY], $registered);
    }

    /**
     * Each setting is saved from its own `<form>` on the Configuration page
     * (`templates/admin/admin.php`). WordPress's `options.php` resets every option registered in
     * the *submitted* group to empty if it isn't present in that form's `$_POST`, so both settings
     * sharing one group would make saving either form wipe the other's stored value.
     */
    public function test_setup_db_fields_registers_the_gamecenter_base_url_under_its_own_group(): void
    {
        $registered = [];
        Functions\expect('register_setting')
            ->twice()
            ->andReturnUsing(function (string $group, string $name) use (&$registered): void {
                $registered[] = [$group, $name];
            });

        (new Settings())->setup_db_fields();

        $this->assertContains([Settings::DB_GROUP_NAME_GAMECENTER, Settings::FIELD_GAMECENTER_BASE_URL], $registered);
        $this->assertNotSame(Settings::DB_GROUP_NAME, Settings::DB_GROUP_NAME_GAMECENTER);
    }

    public function test_getSessionInitialData_only_contains_the_api_token(): void
    {
        Functions\when('get_option')->justReturn('token-123');

        $this->assertSame(['apiToken' => 'token-123'], (new Settings())->getSessionInitialData());
    }
}
