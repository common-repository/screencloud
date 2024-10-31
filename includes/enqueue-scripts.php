<?php

// Exit if accessed directly
defined('ABSPATH') or exit;

/**
 * Enqueue necessary scripts and styles.
 */
function screencloud_enqueue_scripts($hook)
{
  wp_enqueue_script('jquery-ui-dialog');
  wp_enqueue_style('wp-jquery-ui-dialog');

  // Load settings page scripts only on the settings page
  if ($hook == 'settings_page_screencloud') {
    wp_enqueue_script('screencloud-settings-js', plugin_dir_url(__FILE__) . '../js/screencloud-settings.js', array('jquery'), '1.0.0', true);

    // Localize the script with the nonce
    $screencloud_ajax = array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('screencloud_nonce')
    );

    wp_localize_script(
      'screencloud-settings-js',
      'screencloudAjax',
      $screencloud_ajax
    );
  }

  // Load modal and post actions only on the post edit or list page
  if ($hook == 'edit.php' || $hook == 'post.php' || $hook == 'post-new.php') {
    wp_enqueue_script('screencloud-modal-js', plugin_dir_url(__FILE__) . '../js/screencloud-modal.js', array('jquery', 'jquery-ui-dialog'), '1.0.0', true);

    $screencloud_ajax = array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('screencloud_nonce')
    );
    wp_localize_script('screencloud-modal-js', 'screencloudAjax', $screencloud_ajax);
  }

  wp_enqueue_style('screencloud-css', plugin_dir_url(__FILE__) . '../css/screencloud.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'screencloud_enqueue_scripts');
