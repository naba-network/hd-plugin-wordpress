<?php
defined('ABSPATH') || exit;

?>
<script>
  window.initialData = window.initialData || {};
  window.initialData.session = <?php echo wp_json_encode($sessionData); ?>;
</script>

<nova-stats-schedule-slider></nova-stats-schedule-slider>
