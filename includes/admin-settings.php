<?php

// Exit if accessed directly
defined('ABSPATH') or exit;

/**
 * Add admin menu for plugin settings.
 */
function screencloud_add_admin_menu()
{
  add_options_page('ScreenCloud Integration Settings', 'ScreenCloud', 'manage_options', 'screencloud', 'screencloud_options_page');
}
add_action('admin_menu', 'screencloud_add_admin_menu');

/**
 * Display plugin settings page.
 */
function screencloud_options_page()
{
?>
  <div class="wrap">
    <h1>ScreenCloud Integration Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('screencloud_plugin_options');
      do_settings_sections('screencloud_plugin_options');
      submit_button();
      ?>
    </form>
  </div>
<?php
}

/**
 * Initialize plugin settings.
 */
function screencloud_admin_init()
{
  register_setting('screencloud_plugin_options', 'screencloud_plugin_settings', 'screencloud_validate_settings');
  add_settings_section('screencloud_plugin_main', 'Connections', 'screencloud_section_text', 'screencloud_plugin_options');
  add_settings_field('screencloud_settings', 'Your Connections', 'screencloud_settings_field', 'screencloud_plugin_options', 'screencloud_plugin_main');
}
add_action('admin_init', 'screencloud_admin_init');

/**
 * Settings section description.
 */
function screencloud_section_text()
{
  echo '<p>Add, remove or edit connections for the ScreenCloud integration:</p>';
}

/**
 * JavaScript and HTML for dynamically adding and managing connections.
 */
function screencloud_settings_field()
{
  $options = get_option('screencloud_plugin_settings');
  // Ensure there's always one empty config if none exist
  if (empty($options['connections'])) {
    $options['connections'] = [['name' => '', 'webhook_url' => '', 'api_key' => '']];
  }
?>
  <div id="screencloud-connections">
    <?php foreach ($options['connections'] as $index => $config) :
      $index = intval($index);
      $config['name'] = isset($config['name']) ? sanitize_text_field($config['name']) : '';
      $config['webhook_url'] = isset($config['webhook_url']) ? esc_url($config['webhook_url']) : '';
      $config['api_key'] = isset($config['api_key']) ? sanitize_text_field($config['api_key']) : '';
      ?>
      <fieldset>
        <legend>Connection <?php echo esc_html($index + 1); ?></legend>
        <label>
          <span>Name:</span>
          <input type="text" name="screencloud_plugin_settings[connections][<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($config['name'] ?? ''); ?>" required>
        </label>
        <label>
          <span>Webhook URL:</span>
          <input type="text" name="screencloud_plugin_settings[connections][<?php echo esc_attr($index); ?>][webhook_url]" value="<?php echo esc_attr($config['webhook_url'] ?? ''); ?>" required>
        </label>
        <label>
          <span>API Key:</span>
          <input type="text" name="screencloud_plugin_settings[connections][<?php echo esc_attr($index); ?>][api_key]" value="<?php echo esc_attr($config['api_key'] ?? ''); ?>" required>
        </label>
        <button type="button" class="button danger delete" onclick="removeConnection(this, <?php echo intval($index); ?>);">Delete</button>
      </fieldset>
    <?php endforeach; ?>
    <button type="button" class="button button-primary" id="addConnection">Add Connection</button>
  </div>
<?php
}

/**
 * Validate and sanitize plugin settings.
 */
function screencloud_validate_settings($input)
{
  $new_input = ['connections' => []];

  if (isset($input['connections']) && is_array($input['connections'])) {
    foreach ($input['connections'] as $index => $config) {
      $new_config = [];
      // Check for empty fields
      if (empty($config['name']) || empty($config['webhook_url']) || empty($config['api_key'])) {
        add_settings_error(
          'screencloud_plugin_settings',
          'screencloud_config_error',
          'All connection fields must be filled out.',
          'error'
        );
        continue;
      }

      // Check for excessive length
      if (strlen($config['name']) > 200 || strlen($config['webhook_url']) > 200 || strlen($config['api_key']) > 200) {
        add_settings_error(
          'screencloud_plugin_settings',
          'screencloud_config_length_error',
          'None of the fields can exceed 200 characters.',
          'error'
        );
        continue;
      }
      // Check that url is valid
      $sanitized_url = esc_url_raw($config['webhook_url']);
      if (! filter_var($sanitized_url, FILTER_VALIDATE_URL)) {
        add_settings_error(
          'screencloud_plugin_settings',
          'screencloud_config_url_error',
          'Invalid Webhook URL.',
          'error'
        );
        continue;
      }

      // Sanitize and add the connection
      $new_config['name'] = sanitize_text_field($config['name']);
      $new_config['webhook_url'] = $sanitized_url;
      $new_config['api_key'] = sanitize_text_field($config['api_key']);
      $new_input['connections'][] = $new_config;
    }
  }

  // Ensure at least one empty connection if all are deleted or none initially present
  if (empty($new_input['connections'])) {
    $new_input['connections'][] = ['name' => '', 'webhook_url' => '', 'api_key' => ''];
  }

  return $new_input;
}
