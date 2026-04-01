<?php

namespace NabaHdwp\Model;

defined('ABSPATH') || exit;

class Settings
{
    public const DB_GROUP_NAME = 'naba_hdwp_db_settings_group';
    public const FIELD_API_KEY = 'naba_hdwp_db_setting__api_key';
    public const FIELD_HD_API_KEY = 'naba_hdwp_db_setting__hd_api_key';
    public const FIELD_HD_REFERRER = 'naba_hdwp_db_setting__hd_referrer';
    public const FIELD_LEAGUE_SETTINGS = 'naba_hdwp_db_setting__league_settings';

    public function __construct()
    {
        add_action('admin_init', [$this, 'setup_db_fields']);
    }

    public function getApiKey(): string
    {
        return get_option(self::FIELD_API_KEY, '');
    }

    public function getLeagueConfig(): array
    {
        return json_decode(
            get_option(self::FIELD_LEAGUE_SETTINGS, '[]'),
            true
        );
    }

    public function getLeagueConfigAsString(): string
    {
        return json_encode($this->getLeagueConfig());
    }

    public function getHockeyDataApiKey(): string
    {
        return get_option(self::FIELD_HD_API_KEY, '');
    }

    public function getHockeyDataReferrer(): string
    {
        return get_option(self::FIELD_HD_REFERRER, '');
    }

    public function setup_db_fields(): void
    {
        register_setting(
            self::DB_GROUP_NAME,
            self::FIELD_API_KEY,
            [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
      ]
        );

        register_setting(
            self::DB_GROUP_NAME,
            self::FIELD_HD_API_KEY,
            [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
      ]
        );

        register_setting(
            self::DB_GROUP_NAME,
            self::FIELD_HD_REFERRER,
            [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
      ]
        );

        register_setting(
            self::DB_GROUP_NAME,
            self::FIELD_LEAGUE_SETTINGS,
            [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_json_input'],
            'default' => json_encode([]),
      ]
        );
    }

    public function sanitize_json_input($input): string
    {
        json_decode($input);

        return json_last_error() === JSON_ERROR_NONE ? $input : json_encode([]);
    }

    public function getSessionInitialData(): array
    {
        return [
          "apiKey" => $this->getApiKey(),
          "hockeydata" => [
            "apiKey" => $this->getHockeyDataApiKey(),
            "referrer" => $this->getHockeyDataReferrer(),
          ],
          "leagues" => $this->getLeagueConfig(),
        ];
    }
}
