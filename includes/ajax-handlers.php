<?php

// Exit if accessed directly
defined('ABSPATH') or exit;

/**
 * Process the AJAX request to share a post with ScreenCloud.
 */
function screencloud_send_post()
{
  if (!isset($_POST['post_id']) || !current_user_can('edit_posts')) {
    wp_send_json_error('Invalid permissions or missing post ID', 403);
    return;
  }

  if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(
    sanitize_text_field(wp_unslash($_POST['_wpnonce'])),
    'screencloud_nonce'
  )) {
    wp_send_json_error('Nonce verification failed', 403);
    return;
  }

  $post_id =
    isset($_POST['post_id']) ? intval(wp_unslash($_POST['post_id'])) : 0;
  $post = get_post($post_id);

  if (!$post) {
    wp_send_json_error('Post not found', 404);
    return;
  }

  $config_index =
  isset($_POST['config_index']) ? intval(wp_unslash($_POST['config_index'])) : 0; // Default to the first connection
  $options = get_option('screencloud_plugin_settings');
  $config = $options['connections'][$config_index] ?? null;

  if (!$config || empty($config['webhook_url']) || empty($config['api_key'])) {
    wp_send_json_error('Connection not found or incomplete', 400);
    return;
  }

  $webhook_url = $config['webhook_url'];
  $api_key = $config['api_key'];

  if (!$webhook_url || !$api_key) {
    wp_send_json_error('Missing connection settings', 400);
    return;
  }

  $createdTime = new DateTime($post->post_date_gmt);
  $lastEditedTime = new DateTime($post->post_modified_gmt);
  $currentTime = new DateTime('now', new DateTimeZone('UTC'));

  $item = array(
    "itemId" => strval($post->ID),
    "dateCreated" => $currentTime->format('Y-m-d\TH:i:s\Z'),
    "lastEditedTime" => $currentTime->format('Y-m-d\TH:i:s\Z'),
    "messageUrl" => esc_url(get_permalink($post)),
    "content" => array(
      "title" => array(
        "content" => sanitize_text_field($post->post_title)
      )
    )
  );

  if ($author_display_name = sanitize_text_field(get_the_author_meta('display_name', $post->post_author))) {
    $item['author'] = array(
      "displayName" => $author_display_name,
      "profileImage" => array(
        "url" => esc_url(
          get_avatar_url($post->post_author)
        )
      )
    );
  }

  if ($post_thumbnail_url = get_the_post_thumbnail_url($post, 'full')) {
    $item['attachments'] = array(array(
      "contentType" => "image",
      "url" => esc_url(
        $post_thumbnail_url
      )
    ));
  }

  $excerpt = sanitize_textarea_field($post->post_excerpt);
  if ($excerpt) {
    $item['content']['body'] = array(
      "content" => $excerpt
    );
  } else if ($post->post_content) {
    $text = strip_shortcodes($post->post_content);
    $text = wp_strip_all_tags($text);

    $words = explode(' ', $text);
    if (count($words) > 25) {
      $text = implode(' ', array_slice($words, 0, 25)) . ' &hellip;';
    }
    $item['content']['body'] = array(
      "content" => $text
    );
  }

  $payload = array("items" => array($item));

  $request_debug_info = array(
    'webhook_url' => $webhook_url,
    'request' => array(
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json',
        'X-API-Key' => $api_key
      ),
      'body' => wp_json_encode($payload, JSON_PRETTY_PRINT),
      'data_format' => 'body'
    )
  );

  $response = wp_remote_post($webhook_url, array(
    'method' => 'POST',
    'headers' => array(
      'Content-Type' => 'application/json',
      'X-API-Key' => $api_key
    ),
    'body' => wp_json_encode($payload),
    'data_format' => 'body'
  ));

  if (is_wp_error($response)) {
    $error_message = sanitize_text_field($response->get_error_message());
    wp_send_json_error(array('message' => $error_message, 'debug_info' => $request_debug_info), 500);
  } else {
    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
    $response_data = json_decode($response_body, true);
    $response_data['debug_info'] = $request_debug_info;

    if ($response_code != 200) {
      $error_message = isset($response_data['message']) ? sanitize_text_field($response_data['message']) : 'An error occurred.';
      wp_send_json_error(array('message' => $error_message, 'debug_info' => $request_debug_info), $response_code);
    } else {
      $response_data_sanitized = array_map('sanitize_text_field', $response_data);
      wp_send_json_success($response_data_sanitized);
    }
  }
}

add_action('wp_ajax_screencloud_send_post', 'screencloud_send_post');
add_action('wp_ajax_nopriv_screencloud_send_post', 'screencloud_send_post');
