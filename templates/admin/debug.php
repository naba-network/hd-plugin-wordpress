<?php
defined('ABSPATH') || exit;

/**
 * @var array{url: string, httpError: ?string, status: ?int, body: mixed} $raw
 */
?>
<div class="wrap naba-hdwp-admin">
  <h1>Naba HDWP Debug</h1>
  <p>Raw response of the token-validation request. Visible only while <code>WP_DEBUG</code> is enabled.</p>

  <h3>Request</h3>
  <code><pre><?php echo esc_html($raw['url']); ?></pre></code>

  <h3>Response</h3>
  <?php if ($raw['httpError'] !== null) : ?>
    <p><strong>Error:</strong> <?php echo esc_html($raw['httpError']); ?></p>
  <?php else : ?>
    <p><strong>HTTP status:</strong> <?php echo esc_html((string) $raw['status']); ?></p>
    <code><pre><?php echo esc_html((string) wp_json_encode($raw['body'], JSON_PRETTY_PRINT)); ?></pre></code>
  <?php endif; ?>
</div>

<style>
  .naba-hdwp-admin {
    padding: 10px 30px 0 20px;
  }

  .naba-hdwp-admin pre {
    white-space: pre-wrap;
    word-break: break-all;
  }
</style>
