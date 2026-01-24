<?php
/**
 * Light template configuration.
 * Configuration for the "Light" template.
 *
 * @package VkPhotos
 */

namespace VkPhotos\Configs\Templates;

use VkPhotos\Configs\AbstractTemplateConfig;

/**
 * Class LightTemplateConfig.
 * Configuration for the "Light" template.
 */
class LightTemplateConfig extends AbstractTemplateConfig {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'Light';
		$this->slug = 'light';
	}

	/**
	 * Get template CSS dependencies.
	 *
	 * @return array<int, array{
	 *     handle: string,
	 *     src: string,
	 *     deps?: array<int, string>,
	 *     version?: string,
	 *     media?: string,
	 *     inline?: string
	 * }>
	 */
	public function get_styles(): array {
		return array(
			array(
				'handle'  => 'vk-photos-light-styles',
				'src'     => $this->build_local_url( 'dist/light.css' ),
				'deps'    => array(),
				'version' => '1.0.0',
				'media'   => 'all',
			),
		);
	}
}