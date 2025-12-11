<?php
/**
 * Settings service.
 */

namespace VkPhotos\Services;

use VkPhotos\Models\Settings as SettingsModel;

/**
 * Class SettingsService.
 * Handles settings data management.
 */
class SettingsService {

	/**
	 * Settings model.
	 *
	 * @var SettingsModel
	 */
	private SettingsModel $settings;

	/**
	 * Constructor.
	 *
	 * @param SettingsModel $settings Settings model.
	 * @return void
	 */
	public function __construct( SettingsModel $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register settings from model.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		// Handle cache deletion request.
		if ( is_admin() && isset( $_GET['clearcache'] ) ) {
			$clearcache       = explode( '|', sanitize_text_field( wp_unslash( $_GET['clearcache'] ) ) );
			$clearcache_owner = isset( $clearcache[0] ) ? (int) $clearcache[0] : 0;
			$clearcache_id    = isset( $clearcache[1] ) ? (int) $clearcache[1] : 0;
			if ( function_exists( 'vkp_delete_cache' ) ) {
				vkp_delete_cache( $clearcache_owner, $clearcache_id );
			}
		}

		// Register all settings from model.
		foreach ( $this->settings->keys as $key ) {
			register_setting( 'VKPPhotosSettingsGroup', $key );
		}
	}
}
