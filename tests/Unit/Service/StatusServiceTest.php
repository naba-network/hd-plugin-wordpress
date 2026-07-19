<?php

namespace Tests\Unit\Service;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NabaHdwp\Model\Settings;
use NabaHdwp\Service\StatusService;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        Functions\when('add_action')->justReturn(true);
        Functions\when('get_transient')->justReturn(false);
        Functions\when('set_transient')->justReturn(true);
        Functions\when('home_url')->justReturn('https://mysite.test/');
        Functions\when('apply_filters')->alias(static fn (string $tag, $value) => $value);
        Functions\when('wp_remote_retrieve_body')->alias(static fn (array $r): string => $r['body']);
        Functions\when('wp_remote_retrieve_response_code')->alias(static fn (array $r): int => $r['code']);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    private function service(string $token): StatusService
    {
        Functions\when('get_option')->justReturn($token);

        return new StatusService(new Settings());
    }

    public function test_checkConnection_success_reports_connected_and_sends_referer(): void
    {
        Functions\when('is_wp_error')->justReturn(false);

        $captured = [];
        Functions\expect('wp_remote_get')
            ->once()
            ->andReturnUsing(function (string $url, array $args) use (&$captured): array {
                $captured = ['url' => $url, 'args' => $args];

                return ['body' => json_encode(['message' => '', 'leagues' => [['id' => 1], ['id' => 2]], 'features' => ['stats']]), 'code' => 200];
            });

        $result = $this->service('valid-token')->checkConnection();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertSame(2, $result['leagueCount']);
        $this->assertSame(['stats'], $result['features']);
        $this->assertStringContainsString('token=valid-token', $captured['url']);
        $this->assertSame('https://mysite.test/', $captured['args']['headers']['Referer']);
    }

    public function test_checkConnection_rejected_reports_backend_message(): void
    {
        Functions\when('is_wp_error')->justReturn(false);
        Functions\when('wp_remote_get')->justReturn(['body' => json_encode(['message' => 'Invalid token.', 'leagues' => [], 'features' => []]), 'code' => 200]);

        $result = $this->service('bad-token')->checkConnection();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertSame('Invalid token.', $result['message']);
        $this->assertNull($result['httpError']);
    }

    public function test_checkConnection_transport_error_reports_httpError(): void
    {
        Functions\when('is_wp_error')->justReturn(true);
        $error = new class () {
            public function get_error_message(): string
            {
                return 'Connection timed out';
            }
        };
        Functions\when('wp_remote_get')->justReturn($error);

        $result = $this->service('any-token')->checkConnection();

        $this->assertFalse($result['connected']);
        $this->assertSame('Connection timed out', $result['httpError']);
    }

    public function test_checkConnection_without_token_skips_request(): void
    {
        Functions\expect('wp_remote_get')->never();

        $result = $this->service('')->checkConnection();

        $this->assertFalse($result['configured']);
        $this->assertFalse($result['connected']);
    }

    public function test_getClientPortalUrl_uses_locale_prefix(): void
    {
        Functions\when('get_locale')->justReturn('de_DE');

        $this->assertSame(
            'https://datahub.h-sc.at/de/client-portal',
            $this->service('t')->getClientPortalUrl()
        );
    }
}
