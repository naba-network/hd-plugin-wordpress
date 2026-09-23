<?php
defined('ABSPATH') || exit;

use NovaStats\Gamecenter\Constant\PluginConstants;

?>
<script>
  window.initialData = window.initialData || {};
  window.initialData.session = <?php echo wp_json_encode($sessionData); ?>;
  window.initialData['<?php echo esc_js(PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL); ?>'] = <?php echo wp_json_encode($gamecenterBaseUrl); ?>;
</script>

<nova-stats-team-page
  division-id="<?php echo esc_html($divisionId); ?>"
  team-id="<?php echo esc_html($teamId); ?>"
  player-image-path="<?php echo esc_html($playerImagePath); ?>"
></nova-stats-team-page>
