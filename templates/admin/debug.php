<?php
defined('ABSPATH') || exit;

/**
 * @var array{url: string, httpError: ?string, status: ?int, body: mixed} $raw
 * @var string $recheck_url
 * @var array{configured: bool, connected: bool, message: string, leagueCount: int, features: list<string>, httpError: ?string} $connection
 * @var array{plugin: array{version: string, updateAvailable: bool, latestVersion: ?string}, php: array{version: string, ok: bool, required: string}, wp: array{version: string}, embed: array{host: string, version: string}, referrer: string, shortcodes: list<string>} $diagnostics
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
<div class="wrap container-fluid px-0 py-3">

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
              No API token configured yet. Paste your token on the Configuration page and save.
            </p>
          <?php elseif ($connection['httpError'] !== null) : ?>
            <p class="d-flex align-items-center gap-2">
              <?php $statusDot('error', 'Unreachable'); ?>
              Could not reach the Nova Stats backend: <?php echo esc_html($connection['httpError']); ?>
            </p>
          <?php elseif ($connection['connected']) : ?>
            <p class="d-flex align-items-center gap-2">
              <?php $statusDot('ok', 'Connected'); ?>
              Token valid — <?php echo (int) $connection['leagueCount']; ?> league(s) configured.
            </p>
            <?php if ($connection['features'] !== []) : ?>
              <p>Active features: <code><?php echo esc_html(implode(', ', $connection['features'])); ?></code></p>
            <?php endif; ?>
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

          <p><a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url($recheck_url); ?>">Re-check</a></p>

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
                <th scope="row">Frontend assets</th>
                <td>
                  <code><?php echo esc_html($diagnostics['embed']['host']); ?></code>
                  (<code><?php echo esc_html($diagnostics['embed']['version']); ?></code>)
                </td>
              </tr>
              <tr>
                <th scope="row">Site referrer</th>
                <td><code><?php echo esc_html($diagnostics['referrer']); ?></code></td>
              </tr>
              <tr>
                <th scope="row">Shortcodes</th>
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

    <div class="col-12 col-lg-6">
      <div class="card p-0 h-100">
        <div class="card-header">Raw Response</div>
        <div class="card-body">
          <p>Raw response of the token-validation request.</p>

          <h3 class="h5">Request</h3>
          <pre class="bg-light border rounded p-2"><code><?php echo esc_html($raw['url']); ?></code></pre>

          <h3 class="h5">Response</h3>
          <?php if ($raw['httpError'] !== null) : ?>
            <p><strong>Error:</strong> <?php echo esc_html($raw['httpError']); ?></p>
          <?php else : ?>
            <p><strong>HTTP status:</strong> <?php echo esc_html((string) $raw['status']); ?></p>
            <pre class="bg-light border rounded p-2"><code><?php echo esc_html((string) wp_json_encode($raw['body'], JSON_PRETTY_PRINT)); ?></code></pre>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>

</div>
