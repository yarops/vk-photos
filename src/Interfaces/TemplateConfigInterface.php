<?php
/**
 * Template configuration interface.
 * Defines contract for template configuration classes.
 *
 * @package VkPhotos
 */

namespace VkPhotos\Interfaces;

/**
 * Interface TemplateConfigInterface.
 * Configuration contract for template assets and metadata.
 */
interface TemplateConfigInterface {
	/**
	 * Get template display name.
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Get template directory slug.
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Get template directory path.
	 *
	 * @return string
	 */
	public function get_path(): string;

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
	public function get_scripts(): array;

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
	public function get_styles(): array;

	/**
	 * Get inline JavaScript to add after template scripts.
	 *
	 * @return array<int, array{
	 *     handle?: string,
	 *     data: string,
	 *     position?: string
	 * }>
	 */
	public function get_inline_scripts(): array;

	/**
	 * Get inline CSS to add after template styles.
	 *
	 * @return array<int, array{
	 *     handle?: string,
	 *     css: string
	 * }>
	 */
	public function get_inline_styles(): array;
}