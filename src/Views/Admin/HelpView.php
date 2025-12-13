<?php
/**
 * Help view.
 */

namespace VkPhotos\Views\Admin;

use VkPhotos\Config;

/**
 * Class HelpView.
 * Handles help view rendering.
 */
class HelpView {

	/**
	 * Render help view.
	 *
	 * @return void
	 */
	public function render(): void {
		// Load template.
		$this->load_template();
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
	 * Load template file.
	 *
	 * @return void
	 */
	private function load_template(): void {
		// Get plugin directory from config or constant.
		$plugin_dir    = defined( 'VKP__PLUGIN_DIR' ) ? VKP__PLUGIN_DIR : Config::get( 'plugin.dir', '' );
		$template_path = $plugin_dir . 'templates/admin/help-view.php';

		if ( ! file_exists( $template_path ) ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Template file not found.', 'vkp' ) . '</p></div>';
			return;
		}

		// Include template - $this will refer to this HelpView instance.
		include $template_path;
	}
}
