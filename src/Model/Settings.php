<?php

namespace NabaHdwp\Model;

defined('ABSPATH') || exit;

class Settings
{
    public const DB_GROUP_NAME = 'naba_hdwp_db_settings_group';
    public const FIELD_API_KEY = 'naba_hdwp_db_setting__api_key';

    public function __construct()
    {
        add_action('admin_init', [$this, 'setup_db_fields']);
    }

    public function getApiKey(): string
    {
        return get_option(self::FIELD_API_KEY, '');
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
    }

    /**
     * The Gamecenter Vue app reads the token from `session.apiToken` and fetches
     * league config + hockeydata credentials from the Nova Stats backend itself.
     * The token is the only value WordPress needs to inject.
     *
     * @return array<string, mixed>
     */
    public function getSessionInitialData(): array
    {
        return [
          "apiToken" => $this->getApiKey(),
        ];
    }
}
