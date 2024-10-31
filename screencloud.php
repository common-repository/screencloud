<?php

/**
 * Plugin Name: ScreenCloud
 * Description: Push content from WordPress to your screens seamlessly with ScreenCloud, auto-transforming data into designs for digital signage.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: ScreenCloud
 * Author URI: https://screencloud.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: screencloud
 * Domain Path: /languages
 */

defined('ABSPATH') or exit; // Exit if accessed directly for security

// Include the required files
require_once plugin_dir_path(__FILE__) . 'includes/activation.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/post-sharing.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';
