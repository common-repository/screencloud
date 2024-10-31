<?php

// Exit if accessed directly
defined('ABSPATH') or exit;

/**
 * Activate the plugin.
 */
function screencloud_activate_plugin()
{
  // Set a transient to trigger a one-time admin notice
  add_option('screencloud_plugin_settings', ['connections' => []]);
  set_transient('screencloud_activation_notice', true, 5);
}

/**
 * Deactivate the plugin.
 */
function screencloud_deactivate_plugin()
{
  // Deactivation tasks (if any)
}

register_activation_hook(__FILE__, 'screencloud_activate_plugin');
register_deactivation_hook(__FILE__, 'screencloud_deactivate_plugin');

/**
 * Show an admin notice to visit the settings page
 */
function screencloud_admin_notice()
{
  if (get_transient('screencloud_activation_notice')) {
?>
    <div class="updated notice is-dismissible">
      <p>Please go to <a href="<?php echo esc_url(admin_url('options-general.php?page=screencloud')); ?>">Settings -> ScreenCloud</a> and enter your Webhook URL and API Key.</p>
    </div>
<?php
    delete_transient('screencloud_activation_notice');
  }
}
add_action('admin_notices', 'screencloud_admin_notice');
