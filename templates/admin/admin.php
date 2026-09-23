<?php
defined('ABSPATH') || exit;

/**
 * @var string $form_action
 * @var string $portal_url
 * @var array{api_key: string, gamecenter_base_url: string} $form_data
 * @var array{group_name: string, option_api_key: string, group_name_gamecenter: string, option_gamecenter_base_url: string} $options
 */
?>
<div class="container-fluid px-0 py-3">

  <div class="card p-0 mb-4">
    <div class="card-header">Nova Stats HockeyData</div>
    <div class="card-body">
      <p class="card-text">
        Leagues, seasons and your HockeyData credentials are managed in the Nova Stats
        client portal. This site only stores your personal API token.
      </p>
      <a class="btn btn-primary" href="<?php echo esc_url($portal_url); ?>" target="_blank" rel="noopener">
        Open client portal
      </a>
    </div>
  </div>

  <form action="<?php echo esc_attr($form_action); ?>" method="post">
    <div class="card p-0">
      <div class="card-header">API Token</div>
      <div class="card-body">
        <?php settings_fields($options['group_name']); ?>

        <div class="mb-3">
          <label for="nova-stats-api-token" class="form-label fw-bold">API Token</label>
          <div class="input-group" style="max-width: 480px;">
            <input
              type="password"
              class="form-control"
              id="nova-stats-api-token"
              name="<?php echo esc_attr($options['option_api_key']); ?>"
              value="<?php echo esc_attr($form_data['api_key']); ?>"
              autocomplete="off"
            >
            <button type="button" class="btn btn-outline-secondary" data-nova-stats-toggle="nova-stats-api-token">
              Show
            </button>
          </div>
        </div>

        <button type="submit" id="submit" class="btn btn-primary">Save Token</button>
      </div>
    </div>
  </form>

  <div class="card p-0 mt-4">
    <div class="card-header">Gamecenter Link</div>
    <div class="card-body">
      <p class="card-text">
        The page on this site where the Gamecenter shortcode (<code>[Gamecenter]</code>) is
        placed. Set this once and the schedule slider and team page shortcodes will link back to it.
        Leave empty to hide that link.
      </p>

      <form action="<?php echo esc_attr($form_action); ?>" method="post">
        <?php settings_fields($options['group_name_gamecenter']); ?>

        <div class="mb-3">
          <label for="nova-stats-gamecenter-base-url" class="form-label fw-bold">Gamecenter page URL</label>
          <input
            type="url"
            class="form-control"
            id="nova-stats-gamecenter-base-url"
            name="<?php echo esc_attr($options['option_gamecenter_base_url']); ?>"
            value="<?php echo esc_attr($form_data['gamecenter_base_url']); ?>"
            placeholder="https://example.com/gamecenter"
            style="max-width: 480px;"
          >
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
      </form>
    </div>
  </div>

</div>

<script>
  document.querySelectorAll('[data-nova-stats-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.getElementById(button.getAttribute('data-nova-stats-toggle'));
      if (!input) {
        return;
      }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.textContent = show ? 'Hide' : 'Show';
    });
  });
</script>
