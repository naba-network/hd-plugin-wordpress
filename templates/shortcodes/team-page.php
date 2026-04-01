<?php
defined('ABSPATH') || exit;
?>
<script>
  window.initialData = window.initialData || {};
  window.initialData.session = <?php echo wp_json_encode($sessionData); ?>;
</script>

<div id="appWpc">
  <ssr-team-page
    division-id="<?php echo esc_html($divisionId); ?>"
    team-id="<?php echo esc_html($teamId); ?>"
    player-image-path="<?php echo esc_html($playerImagePath); ?>"
  ></ssr-team-page>
</div>
