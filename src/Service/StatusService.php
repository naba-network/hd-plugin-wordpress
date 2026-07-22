<?php

namespace NabaHdwp\Service;

defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;
use NabaHdwp\Model\Settings;
use NabaHdwp\Shortcode\Gamecenter;
use NabaHdwp\Shortcode\ScheduleSlider;
use NabaHdwp\Shortcode\TeamPage;

/**
 * Builds the data for the admin status/health panel:
 *  - a live connection check against the Nova Stats backend, and
 *  - local environment diagnostics.
 */
class StatusService
{
    private const string CONNECTION_TRANSIENT = 'naba_hdwp_connection_check';
    private const int CONNECTION_TTL = 60;

    public function __construct(private Settings $settings)
    {
    }

    /**
     * Live token validation against the backend.
     *
     * The `Referer` header is required: in production the backend rejects the
     * request unless the request origin/referer contains the token's configured
     * referrer URL. Sending the site URL therefore also verifies the referrer is
     * correctly configured in the client portal for THIS domain.
     *
     * @return array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string}
     */
    public function checkConnection(bool $forceRefresh = false): array
    {
        $token = $this->settings->getApiKey();

        if ($token === '') {
            return $this->connectionResult(false, false, '', 0, [], null);
        }

        if (!$forceRefresh) {
            $cached = get_transient(self::CONNECTION_TRANSIENT);
            if (is_array($cached) && ($cached['token_hash'] ?? '') === md5($token)) {
                unset($cached['token_hash']);

                /** @var array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string} $cached */
                return $cached;
            }
        }

        $result = $this->requestValidation($token);

        set_transient(
            self::CONNECTION_TRANSIENT,
            array_merge($result, ['token_hash' => md5($token)]),
            self::CONNECTION_TTL
        );

        return $result;
    }

    /**
     * The decoded backend response body, for the WP_DEBUG raw-dump page.
     *
     * @return array{url: string, httpError: ?string, status: ?int, body: mixed}
     */
    public function getRawValidation(): array
    {
        $token = $this->settings->getApiKey();
        $url = $this->getValidationUrl($token);

        if ($token === '') {
            return ['url' => $url, 'httpError' => 'No API token configured.', 'status' => null, 'body' => null];
        }

        $response = $this->remoteGet($url);

        if (is_wp_error($response)) {
            return ['url' => $url, 'httpError' => $response->get_error_message(), 'status' => null, 'body' => null];
        }

        return [
          'url' => $url,
          'httpError' => null,
          'status' => (int) wp_remote_retrieve_response_code($response),
          'body' => json_decode(wp_remote_retrieve_body($response), true),
        ];
    }

    /**
     * @return array{
     *   plugin: array{version: string, updateAvailable: bool, latestVersion: ?string},
     *   php: array{version: string, ok: bool, required: string},
     *   wp: array{version: string},
     *   build: array{app: bool, compact: bool},
     *   referrer: string,
     *   shortcodes: list<string>
     * }
     */
    public function getDiagnostics(): array
    {
        return [
          'plugin' => $this->getUpdateStatus(),
          'php' => [
            'version' => PHP_VERSION,
            'ok' => version_compare(PHP_VERSION, PluginConstants::MIN_PHP_VERSION, '>='),
            'required' => PluginConstants::MIN_PHP_VERSION,
          ],
          'wp' => ['version' => get_bloginfo('version')],
          'build' => [
            'app' => file_exists(PluginConstants::PLUGIN_PATH . 'frontend/app/' . PluginConstants::MANIFEST_NAME),
            'compact' => file_exists(PluginConstants::PLUGIN_PATH . 'frontend/compact/' . PluginConstants::MANIFEST_NAME),
          ],
          'referrer' => home_url('/'),
          'shortcodes' => [
            Gamecenter::SHORTCODE_NAME,
            ScheduleSlider::SHORTCODE_NAME,
            TeamPage::SHORTCODE_NAME,
          ],
        ];
    }

    /**
     * Locale-prefixed client portal URL, e.g. https://datahub.h-sc.at/de/client-portal.
     */
    public function getClientPortalUrl(): string
    {
        $locale = str_starts_with(get_locale(), 'de') ? 'de' : 'en';

        return $this->getApiBaseUrl() . '/' . $locale . PluginConstants::CLIENT_PORTAL_PATH;
    }

    /**
     * @return array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string}
     */
    private function requestValidation(string $token): array
    {
        $response = $this->remoteGet($this->getValidationUrl($token));

        if (is_wp_error($response)) {
            return $this->connectionResult(true, false, '', 0, [], $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($body)) {
            $status = wp_remote_retrieve_response_code($response);

            return $this->connectionResult(true, false, '', 0, [], sprintf('Unexpected backend response (HTTP %s).', $status));
        }

        // The backend wraps every API payload in a `{ data, notifications }` envelope
        // (ApiResponseWrapperSubscriber). Unwrap to the payload; fall back to the top
        // level so the check still works if the envelope is ever absent.
        $payload = is_array($body['data'] ?? null) ? $body['data'] : $body;

        $message = is_string($payload['message'] ?? null) ? $payload['message'] : '';
        $leagues = is_array($payload['leagues'] ?? null) ? $payload['leagues'] : [];
        $features = is_array($payload['features'] ?? null) ? array_values(array_filter($payload['features'], 'is_string')) : [];

        return $this->connectionResult(true, $message === '', $message, count($leagues), $features, null);
    }

    /**
     * @return \WP_Error|array<string, mixed>
     */
    private function remoteGet(string $url)
    {
        return wp_remote_get($url, [
          'timeout' => 8,
          'headers' => ['Referer' => home_url('/'), 'Accept' => 'application/json'],
        ]);
    }

    private function getValidationUrl(string $token): string
    {
        return $this->getApiBaseUrl() . PluginConstants::VALIDATE_TOKEN_PATH . '?token=' . rawurlencode($token);
    }

    private function getApiBaseUrl(): string
    {
        // The optional NABA_HDWP_API_BASE_URL define (local/staging testing) repoints the
        // server-side health-check to the same backend the widget uses; production has no
        // define and keeps the hardcoded prod URL. The existing filter still wins over both.
        $default = PluginConstants::NOVA_STATS_API_BASE_URL;
        $override = defined(PluginConstants::LOCAL_API_BASE_URL_DEFINE) ? constant(PluginConstants::LOCAL_API_BASE_URL_DEFINE) : null;

        if (is_string($override) && $override !== '') {
            $default = $override;
        }

        $url = apply_filters('naba_hdwp_api_base_url', $default);

        return rtrim(is_string($url) ? $url : $default, '/');
    }

    /**
     * @return array{version: string, updateAvailable: bool, latestVersion: ?string}
     */
    private function getUpdateStatus(): array
    {
        $current = PluginConstants::VERSION;
        $updates = get_site_transient('update_plugins');

        if (is_object($updates) && !empty($updates->response) && is_array($updates->response)) {
            foreach ($updates->response as $key => $info) {
                if (str_contains((string) $key, PluginConstants::PLUGIN_NAME) && !empty($info->new_version)) {
                    return ['version' => $current, 'updateAvailable' => true, 'latestVersion' => (string) $info->new_version];
                }
            }
        }

        return ['version' => $current, 'updateAvailable' => false, 'latestVersion' => null];
    }

    /**
     * @param list<string> $features
     *
     * @return array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string}
     */
    private function connectionResult(bool $configured, bool $connected, string $message, int $leagueCount, array $features, ?string $httpError): array
    {
        return [
          'configured' => $configured,
          'connected' => $connected,
          'message' => $message,
          'leagueCount' => $leagueCount,
          'features' => $features,
          'httpError' => $httpError,
        ];
    }
}
