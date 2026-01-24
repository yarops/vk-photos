<?php
/**
 * Blocks template configuration.
 * Configuration for the "Blocks" template.
 *
 * @package VkPhotos
 */

namespace VkPhotos\Configs\Templates;

use VkPhotos\Configs\AbstractTemplateConfig;

/**
 * Class BlocksTemplateConfig.
 * Configuration for the "Blocks" template.
 */
class BlocksTemplateConfig extends AbstractTemplateConfig {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'Blocks';
		$this->slug = 'blocks';
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
				'handle'  => 'vk-photos-blocks-styles',
				'src'     => $this->build_local_url( 'dist/blocks.css' ),
				'deps'    => array(),
				'version' => '1.0.0',
				'media'   => 'all',
			),
		);
	}
}