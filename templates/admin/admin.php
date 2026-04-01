<?php
defined('ABSPATH') || exit;
?>
<div class="naba-hdwp-admin-container">
  <form action="<?php echo esc_html($form_action); ?>" method="post">
    <button type="submit" id="submit" class="naba-hdwp-save-button">
      Save Changes
    </button>

    <div class="naba-hdwp-admin-panel naba-hdwp-leagues">
      <div class="naba-hdwp-admin-panel-header">
        Naba HDWP Settings
      </div>
      <div class="naba-hdwp-admin-panel-body">
        <?php settings_fields('naba_hdwp_db_settings_group'); ?>

        <div class="naba-hdwp-panel-box">
          <label for="api-key">API Key</label>
          <input
            type="text"
            id="api-key"
            name="<?php echo esc_html($options['option_api_key']); ?>"
            value="<?php echo esc_html($form_data['api_key']); ?>"
          >
        </div>

        <div class="naba-hdwp-panel-box">
          <label for="api-key">Hockeydata API Key</label>
          <input
            type="text"
            id="api-key"
            name="<?php echo esc_html($options['option_hd_api_key']); ?>"
            value="<?php echo esc_html($form_data['hd_api_key']); ?>"
          >
        </div>

        <div class="naba-hdwp-panel-box">
          <label for="api-key">Hockeydata referrer</label>
          <input
            type="text"
            id="api-key"
            name="<?php echo esc_html($options['option_hd_referrer']); ?>"
            value="<?php echo esc_html($form_data['hd_referrer']); ?>"
          >
        </div>

        <hr/>

        <h2>Ligen</h2>
        <div id="league-editor"></div>

        <textarea
          name="<?php echo esc_html($options['option_leagues']); ?>"
          id="leagues-json"
          style="display:none;"
        ><?php echo esc_html($form_data['leagues']); ?></textarea>
      </div>
    </div>

    <button type="submit" id="submit" class="naba-hdwp-save-button">
      Save Changes
    </button>
  </form>
</div>
