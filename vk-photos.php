<?php
/**
 * Plugin Name: vk-photos
 * Plugin URI: https://codesweet.ru/vk-photos
 * Description: Photo gallery from vk.com
 * Author: Yaroslv Popov <yarops.one@gmail.com>
 * Author URI: https://codesweet.ru
 * Version: 2.0
 * Text Domain: vkp
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Composer autoloader.
$plugin_dir = plugin_dir_path( __FILE__ );
if ( file_exists( $plugin_dir . 'vendor/autoload.php' ) ) {
	require_once $plugin_dir . 'vendor/autoload.php';
}

// Load helper functions.
require_once $plugin_dir . 'functions.php';

// Initialize plugin.
use VkPhotos\Bootstrap;

Bootstrap::get_instance();
