<?php
defined('ABSPATH') || exit;

/**
 * @var string $form_action
 * @var string $portal_url
 * @var array{api_key: string} $form_data
 * @var array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string} $connection
 * @var array{group_name: string, option_api_key: string} $options
 */

// Material Design Icons "check-decagram" / "lock" (pictogrammers.com, Apache 2.0).
$iconCheckDecagram = 'M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.78L23,12M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z';
$iconLock = 'M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z';

if ($connection['connected']) {
    $status = ['state' => 'valid', 'icon' => $iconCheckDecagram, 'title' => 'API token gültig – verbunden.', 'detail' => ''];
} elseif (!$connection['configured']) {
    $status = ['state' => 'missing', 'icon' => $iconLock, 'title' => 'Noch kein API token gespeichert – nicht verbunden.', 'detail' => 'Füge deinen API token vom client portal ein und speichere.'];
} elseif ($connection['httpError'] !== null) {
    $status = ['state' => 'unreachable', 'icon' => $iconLock, 'title' => 'Connection could not be checked – nova·stats is unreachable.', 'detail' => $connection['httpError']];
} else {
    $status = ['state' => 'invalid', 'icon' => $iconLock, 'title' => 'API token ungültig – nicht verbunden.', 'detail' => $connection['message']];
}
?>
<div class="nova-stats-admin container px-0 py-3">
    <form action="<?php echo esc_attr($form_action); ?>" method="post">
    <div class="card p-0">
      <div class="card-header">nova·stats</div>
      <div class="card-body">
        <?php settings_fields($options['group_name']); ?>

        <div class="nova-stats-connection nova-stats-connection--<?php echo esc_attr($status['state']); ?> d-flex align-items-center gap-3 mb-4" role="status">
          <span class="nova-stats-connection__avatar" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><path fill="currentColor" d="<?php echo esc_attr($status['icon']); ?>"/></svg>
          </span>
          <div>
            <p class="fw-bold mb-0"><?php echo esc_html($status['title']); ?></p>
            <?php if ($status['detail'] !== '') : ?>
              <p class="text-muted small mb-0"><?php echo esc_html($status['detail']); ?></p>
            <?php endif; ?>
          </div>
        </div>

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
            <button type="button" class="btn btn-outline-dark" data-nova-stats-toggle="nova-stats-api-token">
              anzeigen
            </button>
          </div>
        </div>

        <button type="submit" id="submit" class="btn btn-sm btn-dark mb-3">speichern & verbinden</button>

        <hr class="pt-3" />

        <p class="card-text">
          Deine Zugangsdaten, Ligen anlegen und für dein Gamecenter konfigurieren, kannst du im nova·stats client portal.
        </p>
        <a class="btn btn-sm btn-light" href="<?php echo esc_url($portal_url); ?>" target="_blank" rel="noopener">
          zum client portal
        </a>

      </div>
    </div>
  </form>

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
      button.textContent = show ? 'verbergen' : 'anzeigen';
    });
  });
</script>
