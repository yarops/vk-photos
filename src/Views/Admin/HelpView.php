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
		return Config::get( 'plugin.url', '' );
	}

	/**
	 * Load template file.
	 *
	 * @return void
	 */
	private function load_template(): void {
		// Get admin templates path from config.
		$template_path = Config::get( 'paths.templates.admin', '' ) . 'help-view.php';

		if ( ! file_exists( $template_path ) ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Template file not found.', 'vkp' ) . '</p></div>';
			return;
		}

		// Include template - $this will refer to this HelpView instance.
		include $template_path;
	}
}
