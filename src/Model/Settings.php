<?php

namespace NabaHdwp\Model;

defined('ABSPATH') || exit;

class Settings
{
    public const DB_GROUP_NAME = 'naba_hdwp_db_settings_group';
    public const FIELD_API_KEY = 'naba_hdwp_db_setting__api_key';

    /**
     * Registered under its own settings group, deliberately not `DB_GROUP_NAME`: the "Gamecenter
     * Link" field is saved from a separate `<form>` on the Configuration page
     * (`templates/admin/admin.php`). WordPress's `options.php` resets every option registered in
     * the *submitted* group to empty if it isn't present in that particular form's `$_POST`, so
     * sharing a group across two independent forms would make saving either one wipe the other.
     */
    public const DB_GROUP_NAME_GAMECENTER = 'naba_hdwp_db_settings_group_gamecenter';
    public const FIELD_GAMECENTER_BASE_URL = 'naba_hdwp_db_setting__gamecenter_base_url';

    public function __construct()
    {
        add_action('admin_init', [$this, 'setup_db_fields']);
    }

    public function getApiKey(): string
    {
        return get_option(self::FIELD_API_KEY, '');
    }

    /**
     * The page on this site where the `[Naba-Hdwp-Gamecenter]` shortcode is placed, so the
     * schedule slider and team page shortcodes can link back to it. Set once, reused by every
     * shortcode - not a Nova Stats backend value, it describes this site's own page layout.
     */
    public function getGamecenterBaseUrl(): string
    {
        return get_option(self::FIELD_GAMECENTER_BASE_URL, '');
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
            self::DB_GROUP_NAME_GAMECENTER,
            self::FIELD_GAMECENTER_BASE_URL,
            [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_url',
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
