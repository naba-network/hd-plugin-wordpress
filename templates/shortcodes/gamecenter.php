<?php
defined('ABSPATH') || exit;

use NovaStats\Gamecenter\Constant\PluginConstants;

?>
<script>
  window.initialData = window.initialData || {};
  window.initialData.session = <?php echo wp_json_encode($sessionData); ?>;
  window.initialData['<?php echo esc_js(PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL); ?>'] = <?php echo wp_json_encode($gamecenterBaseUrl); ?>;
</script>

<nova-stats-gamecenter></nova-stats-gamecenter>
