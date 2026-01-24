<?php
/**
 * Abstract template configuration class.
 * Provides base implementation for template configuration classes.
 *
 * @package VkPhotos
 */

namespace VkPhotos\Configs;

use VkPhotos\Interfaces\TemplateConfigInterface;
use VkPhotos\Config;

/**
 * Class AbstractTemplateConfig.
 * Base class for template configuration implementations.
 */
abstract class AbstractTemplateConfig implements TemplateConfigInterface {
	/**
	 * Template display name.
	 *
	 * @var string
	 */
	protected string $name = '';

	/**
	 * Template directory slug.
	 *
	 * @var string
	 */
	protected string $slug = '';

	/**
	 * Get template display name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get template directory slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Get template directory path.
	 *
	 * @return string
	 */
	public function get_path(): string {
		return Config::get( 'paths.templates.frontend', '' ) . $this->slug . '/';
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
		return array();
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
		return array();
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

	/**
	 * Get inline CSS to add after template styles.
	 *
	 * @return array<int, array{
	 *     handle?: string,
	 *     css: string
	 * }>
	 */
	public function get_inline_styles(): array {
		return array();
	}

	/**
	 * Build local URL for plugin asset.
	 *
	 * @param string $path Relative path from plugin root.
	 * @return string Full URL to asset.
	 */
	protected function build_local_url( string $path ): string {
		return Config::get( 'plugin.url', '' ) . $path;
	}
}