<?php
/**
 * Helper functions for vk-photos plugin.
 *
 * @package VkPhotos
 */

/**
 * Register scripts and styles.
 */
function vkp_scripts_register() {
	wp_enqueue_script( 'jquery' );

	wp_register_script( 'vkp_colorbox', VKP__PLUGIN_URL . 'js/jquery.colorbox-min.js' );
	wp_register_style( 'vkp_colorbox', VKP__PLUGIN_URL . 'css/colorbox.css' );

	wp_register_script( 'vkp_swipebox', VKP__PLUGIN_URL . 'js/jquery.swipebox.min.js' );
	wp_register_style( 'vkp_swipebox', VKP__PLUGIN_URL . 'css/swipebox.css' );
}
