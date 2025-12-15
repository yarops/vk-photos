<?php
/**
 * Albums view.
 */

namespace VkPhotos\Views\Admin;

use VkPhotos\Config;
use VkPhotos\Services\SettingsService;
use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Container;
use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Controllers\AlbumsController;

/**
 * Class AlbumsView.
 * Handles albums view rendering.
 */
class AlbumsView {

	/**
	 * Settings service.
	 *
	 * @var SettingsService|null
	 */
	private ?SettingsService $settings_service = null;

	/**
	 * VK API client.
	 *
	 * @var VkApiClientInterface|null
	 */
	private ?VkApiClientInterface $vk_api = null;

	/**
	 * Settings model.
	 *
	 * @var SettingsModel|null
	 */
	private ?SettingsModel $settings = null;

	/**
	 * Albums controller.
	 *
	 * @var AlbumsController|null
	 */
	private ?AlbumsController $albums_controller = null;

	/**
	 * Constructor.
	 *
	 * @param ?SettingsService|null      $settings_service Settings service.
	 * @param ?VkApiClientInterface|null $vk_api VK API client.
	 * @param ?AlbumsController|null     $albums_controller Albums controller.
	 * @return void
	 */
	public function __construct(
		?SettingsService $settings_service = null,
		?VkApiClientInterface $vk_api = null,
		?AlbumsController $albums_controller = null
	) {
		$this->settings_service  = $settings_service ?? Container::make( SettingsService::class );
		$this->vk_api            = $vk_api ?? Container::make( VkApiClientInterface::class );
		$this->albums_controller = $albums_controller ?? Container::make( AlbumsController::class );

		if ( null === $this->settings ) {
			$this->settings = $this->settings_service->get_settings();
		}
	}

	/**
	 * Render albums view.
	 *
	 * @return void
	 */
	public function render(): void {
		$view_data = $this->albums_controller->get_view_data();

		// Load template with prepared data.
		$this->load_template( $view_data );
	}

	/**
	 * Get plugin directory.
	 *
	 * @return string Plugin directory path.
	 */
	public function get_plugin_dir(): string {
		return Config::get( 'plugin.dir', '' );
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
	 * Get templates directory.
	 *
	 * @return string Templates directory path.
	 */
	public function get_templates_dir(): string {
		return Config::get( 'paths.templates.root', '' );
	}

	/**
	 * Load albums template.
	 *
	 * @param array $view_data View data.
	 * @return void
	 */
	private function load_template( array $view_data = array() ): void {
		// Get admin templates path from config.
		$albums_path = Config::get( 'paths.templates.admin', '' ) . 'albums-view.php';

		if ( ! file_exists( $albums_path ) ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Albums file not found.', 'vkp' ) . '</p></div>';
			return;
		}

		// Provide data to template.
		$albums_view_data = $view_data;

		// Include albums - $this will refer to this AlbumsView instance.
		include $albums_path;
	}
}
