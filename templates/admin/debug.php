<?php
defined('ABSPATH') || exit;

/**
 * @var string $recheck_url
 * @var array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string} $connection
 * @var array{plugin: array{version: string, updateAvailable: bool, latestVersion: ?string}, php: array{version: string, ok: bool, required: string}, wp: array{version: string}, referrer: string} $diagnostics
 */

/** Render an OK/warn/error status badge with a label. */
$statusDot = static function (string $state, string $label): void {
    $variant = match ($state) {
        'ok' => 'success',
        'warn' => 'warning',
        'error' => 'danger',
        default => 'secondary',
    };
    printf(
        '<span class="badge text-bg-%s">%s</span>',
        esc_attr($variant),
        esc_html($label)
    );
};
?>
<div class="nova-stats-admin wrap container px-0 py-3">

  <h1>Gamecenter Debug</h1>
  <p>Visible only while <code>WP_DEBUG</code> is enabled.</p>

  <div class="row g-3">

    <div class="col-12 col-lg-6">
      <div class="card p-0 h-100">
        <div class="card-header">Status</div>
        <div class="card-body">

          <h3 class="h5">Connection</h3>
          <?php if (!$connection['configured']) : ?>
            <p class="d-flex align-items-center gap-2">
              <?php $statusDot('warn', 'No token'); ?>
              No API token configured yet. Paste your token on the Setup page and save.
            </p>
          <?php elseif ($connection['httpError'] !== null) : ?>
            <p class="d-flex align-items-center gap-2">
              <?php $statusDot('error', 'Unreachable'); ?>
              Could not reach the nova·stats backend: <?php echo esc_html($connection['httpError']); ?>
            </p>
          <?php elseif ($connection['connected']) : ?>
            <p class="d-flex align-items-center gap-2">
              <?php $statusDot('ok', 'Connected'); ?>
              Token valid — <?php echo (int) $connection['leagueCount']; ?> league(s) configured.
            </p>
          <?php else : ?>
            <p class="d-flex align-items-center gap-2">
              <?php $statusDot('error', 'Rejected'); ?>
              Backend rejected the token: <?php echo esc_html($connection['message']); ?>
            </p>
            <p class="text-muted small">
              Tip: make sure the referrer configured in the client portal matches this site
              (<code><?php echo esc_html($diagnostics['referrer']); ?></code>).
            </p>
          <?php endif; ?>

          <p><a class="btn btn-sm btn-outline-dark" href="<?php echo esc_url($recheck_url); ?>">Re-check</a></p>

          <hr>

          <h3 class="h5">Diagnostics</h3>
          <table class="table table-sm table-borderless align-middle mb-0">
            <tbody>
              <tr>
                <th scope="row" style="width: 40%;">Plugin version</th>
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
                <th scope="row">PHP version</th>
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
                <th scope="row">WordPress version</th>
                <td><?php echo esc_html($diagnostics['wp']['version']); ?></td>
              </tr>
              <tr>
                <th scope="row">Site referrer</th>
                <td><code><?php echo esc_html($diagnostics['referrer']); ?></code></td>
              </tr>
            </tbody>
          </table>

        </div>
      </div>
    </div>

  </div>

</div>
