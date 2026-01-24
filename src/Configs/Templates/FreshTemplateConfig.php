<?php
/**
 * Fresh template configuration.
 * Configuration for the "Fresh" template with macy.js masonry layout.
 *
 * @package VkPhotos
 */

namespace VkPhotos\Configs\Templates;

use VkPhotos\Configs\AbstractTemplateConfig;

/**
 * Class FreshTemplateConfig.
 * Configuration for the "Fresh" template.
 */
class FreshTemplateConfig extends AbstractTemplateConfig {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'Fresh';
		$this->slug = 'fresh';
	}

	/**
	 * Get template JavaScript dependencies.
	 *
	 * @return array<int, array{
	 *     handle: string,
	 *     src: string,
	 *     deps?: array<int, string>,
	 *     version?: string,
	 *     in_footer?: bool,
	 *     inline?: string
	 * }>
	 */
	public function get_scripts(): array {
		return array(
			array(
				'handle'    => 'vk-photos-fresh-init',
				'src'       => $this->build_local_url( 'dist/fresh.js' ),
				'deps'      => array(),
				'version'   => '1.0.0',
				'in_footer' => true,
			),
		);
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
				'handle'  => 'vk-photos-fresh-styles',
				'src'     => $this->build_local_url( 'dist/fresh.css' ),
				'deps'    => array(),
				'version' => '1.0.0',
				'media'   => 'all',
			),
		);
	}

	/**
	 * Get inline JavaScript to add after template scripts.
	 *
	 * @return array<int, array{
	 *     handle?: string,
	 *     data: string,
	 *     position?: string
	 * }>
	 */
	public function get_inline_scripts(): array {
		return array();
	}
}