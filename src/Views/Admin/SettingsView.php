<?php
/**
 * Settings view.
 */

namespace VkPhotos\Views\Admin;

use VkPhotos\Services\SettingsService;
use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Container;
use VkPhotos\Config;

/**
 * Class SettingsView.
 * Handles settings view rendering.
 */
class SettingsView {

	/**
	 * Settings service.
	 *
	 * @var SettingsService
	 */
	private SettingsService $settings_service;

	/**
	 * VK API client.
	 *
	 * @var VkApiClientInterface
	 */
	private VkApiClientInterface $vk_api;

	/**
	 * Settings model (for template access).
	 *
	 * @var SettingsModel
	 */
	private SettingsModel $settings;

	/**
	 * Constructor.
	 *
	 * @param SettingsService      $settings_service Settings service.
	 * @param VkApiClientInterface $vk_api VK API client.
	 * @return void
	 */
	public function __construct( SettingsService $settings_service, VkApiClientInterface $vk_api ) {
		$this->settings_service = $settings_service;
		$this->vk_api           = $vk_api;
	}

	/**
	 * Render settings view.
	 *
	 * @return void
	 */
	public function render(): void {
		// Get settings model from service.
		$this->settings = $this->settings_service->get_settings();

		// Load template.
		$this->load_template();
	}


	/**
	 * Calculate directory size.
	 *
	 * @param string $dir Directory path.
	 * @return int Directory size in bytes.
	 */
	public function dir_size( string $dir ): int {
		$calculate_cache = $this->settings->calculate_cache ?? 'no';
		if ( ! file_exists( $dir ) ) {
			return 0;
		}

		if ( 'yes' !== $calculate_cache ) {
			return 0;
		}

		$total_size = 0;

		if ( $dirstream = @opendir( $dir ) ) {
			while ( false !== ( $filename = readdir( $dirstream ) ) ) {
				if ( $filename != '.' && $filename != '..' ) {
					if ( is_file( $dir . '/' . $filename ) ) {
						$total_size += filesize( $dir . '/' . $filename );
					}

					if ( is_dir( $dir . '/' . $filename ) ) {
						$total_size += $this->dir_size( $dir . '/' . $filename );
					}
				}
			}
			closedir( $dirstream );
		}

		return $total_size;
	}

	/**
	 * Get upload directory.
	 *
	 * @return array<string, mixed>|false Upload directory info.
	 */
	public function get_upload_dir() {
		return wp_upload_dir();
	}

	/**
	 * Get directory for cache.
	 *
	 * @return string Cache directory path.
	 */
	public function get_dir_for_cache(): string {
		$upload_dir = $this->get_upload_dir();
		return $upload_dir['basedir'] . '/vk-photos-cache/';
	}

	/**
	 * Get VK API wrapper.
	 *
	 * @return object VK API wrapper.
	 */
	public function get_vkp(): object {
		// Set access token.
		$this->vk_api->access_token = $this->settings->access_token ?? '';

		// Create wrapper class.
		$wrapper = new class( $this->vk_api ) {
			/**
			 * VK API client.
			 *
			 * @var VkApiClientInterface
			 */
			private VkApiClientInterface $vk_api;

			/**
			 * Constructor.
			 *
			 * @param VkApiClientInterface $vk_api VK API client.
			 */
			public function __construct( VkApiClientInterface $vk_api ) {
				$this->vk_api = $vk_api;
			}

			/**
			 * Execute API method.
			 *
			 * @param string $method API method name.
			 * @param array  $params Request parameters.
			 * @return array API response.
			 */
			public function api( string $method, array $params = array() ): array {
				return $this->vk_api->api( $method, $params );
			}
		};

		return $wrapper;
	}

	/**
	 * Magic getter to delegate to Settings model and provide additional properties.
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( string $name ) {
		// Handle additional properties not in Settings.
		switch ( $name ) {
			case 'upload_dir':
				return $this->get_upload_dir();
			case 'dir_for_cache':
				return $this->get_dir_for_cache();
			case 'VKP':
				return $this->get_vkp();
		}

		// Delegate to settings model.
		if ( isset( $this->settings ) ) {
			return $this->settings->$name ?? null;
		}
		return null;
	}

	/**
	 * Load template file.
	 *
	 * @return void
	 */
	private function load_template(): void {
		// Get plugin directory from config or constant.
		$plugin_dir    = defined( 'VKP__PLUGIN_DIR' ) ? VKP__PLUGIN_DIR : Config::get( 'plugin.dir', '' );
		$template_path = $plugin_dir . 'templates/admin/settings-view.php';

		if ( ! file_exists( $template_path ) ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Template file not found.', 'vkp' ) . '</p></div>';
			return;
		}

		// Include template - $this will refer to this SettingsView instance.
		// Settings properties are accessible via __get magic method.
		include $template_path;
	}
}
