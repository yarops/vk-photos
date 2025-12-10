<?php
/**
 * Plugin Name: vk-photos
 * Plugin URI: http://photo-family.ru/vk-photos
 * Description: Photo gallery from vk.com
 * Author: volod1n <ivan.volodin@gmail.com>
 * Author URI: http://photo-family.ru
 * Version: 1.5
 * Text Domain: vkp
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'VKP__PLUGIN_FILE', __FILE__ );
define( 'VKP__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VKP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Load Composer autoloader.
if ( file_exists( VKP__PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once VKP__PLUGIN_DIR . 'vendor/autoload.php';
}

// Load helper functions.
require_once VKP__PLUGIN_DIR . 'functions.php';

// Initialize plugin.
use VkPhotos\Bootstrap;

Bootstrap::get_instance();


