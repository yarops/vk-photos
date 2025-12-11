<?php
/**
 * Default configuration for vk-photos plugin.
 *
 * @package VkPhotos
 */

/**
 * Get default configuration array.
 *
 * @param string $plugin_file Main plugin file path.
 * @return array<string, mixed> Default configuration.
 */
return function ( string $plugin_file ): array {
	$plugin_dir = plugin_dir_path( $plugin_file );
	$plugin_url = plugin_dir_url( $plugin_file );

	return array(
		'plugin' => array(
			'file'        => $plugin_file,
			'dir'         => $plugin_dir,
			'url'         => $plugin_url,
			'version'     => '1.5',
			'text_domain' => 'vkp',
			'basename'    => plugin_basename( $plugin_file ),
		),
		'paths'  => array(
			'inc'       => $plugin_dir . 'inc/',
			'templates' => $plugin_dir . 'templates/',
			'languages' => $plugin_dir . 'languages/',
			'api'       => $plugin_dir . 'api/',
		),
	);
};
