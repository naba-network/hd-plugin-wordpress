<?php
defined('ABSPATH') || exit;

use NabaHdwp\Constant\PluginConstants;

?>
<script>
  window.initialData = window.initialData || {};
  window.initialData.session = <?php echo wp_json_encode($sessionData); ?>;
  window.initialData['<?php echo esc_js(PluginConstants::INITIAL_DATA_KEY_GAMECENTER_BASE_URL); ?>'] = <?php echo wp_json_encode($gamecenterBaseUrl); ?>;
</script>

<nova-stats-schedule-slider></nova-stats-schedule-slider>
