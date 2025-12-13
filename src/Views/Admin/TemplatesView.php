<?php
/**
 * Templates view.
 */

namespace VkPhotos\Views\Admin;

use VkPhotos\Config;

/**
 * Class TemplatesView.
 * Handles templates view rendering.
 */
class TemplatesView {

	/**
	 * Render templates view.
	 *
	 * @return void
	 */
	public function render(): void {
		// Load template.
		$this->load_template();
	}

	/**
	 * Get plugin directory.
	 *
	 * @return string Plugin directory path.
	 */
	public function get_plugin_dir(): string {
		return defined( 'VKP__PLUGIN_DIR' ) ? VKP__PLUGIN_DIR : Config::get( 'plugin.dir', '' );
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string Plugin URL.
	 */
	public function get_plugin_url(): string {
		return defined( 'VKP__PLUGIN_URL' ) ? VKP__PLUGIN_URL : Config::get( 'plugin.url', '' );
	}

	/**
	 * Get templates directory.
	 *
	 * @return string Templates directory path.
	 */
	public function get_templates_dir(): string {
		return $this->get_plugin_dir() . 'templates';
	}

	/**
	 * Get available templates.
	 *
	 * @return array<string> List of template names.
	 */
	public function get_templates(): array {
		$templates = array();
		$dir       = $this->get_templates_dir();

		if ( $dirstream = @opendir( $dir ) ) {
			while ( false !== ( $filename = readdir( $dirstream ) ) ) {
				if ( $filename != '.' && $filename != '..' ) {
					if ( is_dir( $dir . '/' . $filename ) && file_exists( $dir . '/' . $filename . '/item.html' ) ) {
						$templates[] = $filename;
					}
				}
			}
			closedir( $dirstream );
		}

		return $templates;
	}

	/**
	 * Load template file.
	 *
	 * @return void
	 */
	private function load_template(): void {
		// Get plugin directory from config or constant.
		$plugin_dir    = defined( 'VKP__PLUGIN_DIR' ) ? VKP__PLUGIN_DIR : Config::get( 'plugin.dir', '' );
		$template_path = $plugin_dir . 'templates/admin/templates-view.php';

		if ( ! file_exists( $template_path ) ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Template file not found.', 'vkp' ) . '</p></div>';
			return;
		}

		// Include template - $this will refer to this TemplatesView instance.
		include $template_path;
	}
}
