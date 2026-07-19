<?php
defined('ABSPATH') || exit;

/**
 * @var string $form_action
 * @var string $portal_url
 * @var string $recheck_url
 * @var array{api_key: string} $form_data
 * @var array{group_name: string, option_api_key: string} $options
 * @var array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string} $connection
 * @var array{plugin: array{version: string, updateAvailable: bool, latestVersion: ?string}, php: array{version: string, ok: bool, required: string}, wp: array{version: string}, build: array{app: bool, compact: bool}, referrer: string, shortcodes: list<string>} $diagnostics
 */

/** Render an OK/warn/error status dot with a label. */
$statusDot = static function (string $state, string $label): void {
    printf(
        '<span class="naba-hdwp-badge naba-hdwp-badge--%s">%s</span>',
        esc_attr($state),
        esc_html($label)
    );
};
?>
<div class="naba-hdwp-admin-container">

  <div class="naba-hdwp-admin-panel">
    <div class="naba-hdwp-admin-panel-header">Nova Stats HockeyData</div>
    <div class="naba-hdwp-admin-panel-body">
      <p>
        Leagues, seasons and your HockeyData credentials are managed in the Nova Stats
        client portal. This site only stores your personal API token.
      </p>
      <a class="naba-hdwp-save-button" href="<?php echo esc_url($portal_url); ?>" target="_blank" rel="noopener">
        Open client portal
      </a>
    </div>
  </div>

  <form action="<?php echo esc_attr($form_action); ?>" method="post">
    <div class="naba-hdwp-admin-panel naba-hdwp-panel--spaced">
      <div class="naba-hdwp-admin-panel-header">API Token</div>
      <div class="naba-hdwp-admin-panel-body">
        <?php settings_fields($options['group_name']); ?>

        <div class="naba-hdwp-panel-box">
          <label for="naba-hdwp-api-token">API Token</label>
          <div class="naba-hdwp-token-field">
            <input
              type="password"
              id="naba-hdwp-api-token"
              name="<?php echo esc_attr($options['option_api_key']); ?>"
              value="<?php echo esc_attr($form_data['api_key']); ?>"
              autocomplete="off"
            >
            <button type="button" class="naba-hdwp-button--default" data-naba-hdwp-toggle="naba-hdwp-api-token">
              Show
            </button>
          </div>
        </div>

        <button type="submit" id="submit" class="naba-hdwp-save-button">Save Token</button>
      </div>
    </div>
  </form>

  <div class="naba-hdwp-admin-panel naba-hdwp-panel--spaced">
    <div class="naba-hdwp-admin-panel-header">Status</div>
    <div class="naba-hdwp-admin-panel-body">

      <h3>Connection</h3>
      <?php if (!$connection['configured']) : ?>
        <p class="naba-hdwp-status-row">
          <?php $statusDot('warn', 'No token'); ?>
          No API token configured yet. Paste your token above and save.
        </p>
      <?php elseif ($connection['httpError'] !== null) : ?>
        <p class="naba-hdwp-status-row">
          <?php $statusDot('error', 'Unreachable'); ?>
          Could not reach the Nova Stats backend: <?php echo esc_html($connection['httpError']); ?>
        </p>
      <?php elseif ($connection['connected']) : ?>
        <p class="naba-hdwp-status-row">
          <?php $statusDot('ok', 'Connected'); ?>
          Token valid — <?php echo (int) $connection['leagueCount']; ?> league(s) configured.
        </p>
        <?php if ($connection['features'] !== []) : ?>
          <p>Active features: <code><?php echo esc_html(implode(', ', $connection['features'])); ?></code></p>
        <?php endif; ?>
      <?php else : ?>
        <p class="naba-hdwp-status-row">
          <?php $statusDot('error', 'Rejected'); ?>
          Backend rejected the token: <?php echo esc_html($connection['message']); ?>
        </p>
        <p class="naba-hdwp-hint">
          Tip: make sure the referrer configured in the client portal matches this site
          (<code><?php echo esc_html($diagnostics['referrer']); ?></code>).
        </p>
      <?php endif; ?>

      <p><a class="naba-hdwp-button--default" href="<?php echo esc_url($recheck_url); ?>">Re-check</a></p>

      <hr>

      <h3>Diagnostics</h3>
      <table class="naba-hdwp-diagnostics">
        <tbody>
          <tr>
            <th>Plugin version</th>
            <td>
              <?php echo esc_html($diagnostics['plugin']['version']); ?>
              <?php if ($diagnostics['plugin']['updateAvailable']) : ?>
                <?php $statusDot('warn', 'Update: ' . (string) $diagnostics['plugin']['latestVersion']); ?>
              <?php else : ?>
                <?php $statusDot('ok', 'Up to date'); ?>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th>PHP version</th>
            <td>
              <?php echo esc_html($diagnostics['php']['version']); ?>
              <?php if ($diagnostics['php']['ok']) : ?>
                <?php $statusDot('ok', 'OK'); ?>
              <?php else : ?>
                <?php $statusDot('error', 'Requires ' . $diagnostics['php']['required'] . '+'); ?>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th>WordPress version</th>
            <td><?php echo esc_html($diagnostics['wp']['version']); ?></td>
          </tr>
          <tr>
            <th>Frontend build</th>
            <td>
              <?php if ($diagnostics['build']['app'] && $diagnostics['build']['compact']) : ?>
                <?php $statusDot('ok', 'Present'); ?>
              <?php else : ?>
                <?php $statusDot('error', 'Missing — reinstall the plugin'); ?>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th>Site referrer</th>
            <td><code><?php echo esc_html($diagnostics['referrer']); ?></code></td>
          </tr>
          <tr>
            <th>Shortcodes</th>
            <td>
              <?php foreach ($diagnostics['shortcodes'] as $shortcode) : ?>
                <code>[<?php echo esc_html($shortcode); ?>]</code><br>
              <?php endforeach; ?>
            </td>
          </tr>
        </tbody>
      </table>

    </div>
  </div>

</div>

<script>
  document.querySelectorAll('[data-naba-hdwp-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.getElementById(button.getAttribute('data-naba-hdwp-toggle'));
      if (!input) {
        return;
      }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.textContent = show ? 'Hide' : 'Show';
    });
  });
</script>
