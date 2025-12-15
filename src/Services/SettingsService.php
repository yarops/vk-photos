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
		$this->load_from_wp_options();
	}

	/**
	 * Load settings from WordPress options.
	 *
	 * @return void
	 */
	private function load_from_wp_options(): void {
		$wp_options = array();
		foreach ( SettingsModel::CONFIG as $config ) {
			list( $legacy_key )        = $config;
			$wp_options[ $legacy_key ] = get_option( $legacy_key );
		}

		// Reinitialize model with WordPress options.
		$this->settings = new SettingsModel( $wp_options );
	}

	/**
	 * Register settings from model.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		// Register all settings from model.
		foreach ( $this->settings->get_keys() as $key ) {
			register_setting( 'VKPPhotosSettingsGroup', $key );
		}
	}

	/**
	 * Get settings model.
	 *
	 * @return SettingsModel Settings model instance.
	 */
	public function get_settings(): SettingsModel {
		// Reload settings from WordPress options to get latest values.
		$this->load_from_wp_options();
		return $this->settings;
	}
}
