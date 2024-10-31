<?php

// Exit if accessed directly
defined('ABSPATH') or exit;

/**
 * Add a 'Share with ScreenCloud' link to the post row actions.
 */
function screencloud_add_link($actions, $post)
{
  if ('post' === $post->post_type) {
    $actions['screencloud_share'] = '<a href="#" onclick="openScreenCloudModal(' . intval($post->ID) . ')">Share with ScreenCloud</a>';
  }
  return $actions;
}
add_filter('post_row_actions', 'screencloud_add_link', 10, 2);

/**
 * Include JavaScript for AJAX post submission.
 */
function screencloud_add_script()
{
  $screen = get_current_screen();
  if ($screen->base == 'edit') {
    $options = get_option('screencloud_plugin_settings');
?>
    <div id="screencloud-modal" title="Share with ScreenCloud">
      <div id="config-selection">
        <p style="margin-top: 0; width: 100%;">Select the connection you want to use:</p>
        <div>
          <select id="screencloud-connections-dropdown" name="screencloud_config">
            <?php foreach ($options['connections'] as $index => $config) {
              $index = intval($index);
              $name = isset($config['name']) ? sanitize_text_field($config['name']) : '';
              echo '<option value="' . esc_attr($index) . '">' . esc_html($name) . '</option>';
            } ?>
          </select>
          <button class="button" id="shareWithScreenCloud">Share</button>
        </div>
      </div>
      <div id="loading-indicator">
        Loading...
      </div>
    </div>
<?php
  }
}
add_action('admin_footer', 'screencloud_add_script');
