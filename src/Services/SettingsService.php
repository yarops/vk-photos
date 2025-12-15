<?php
/**
 * Settings service.
 */

namespace VkPhotos\Services;

use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Container;
use VkPhotos\Services\CacheServiceInterface;

/**
 * Class SettingsService.
 * Handles settings data management.
 */
class SettingsService {

	/**
	 * Whether hooks are registered.
	 *
	 * @var bool
	 */
	private bool $hooks_registered = false;

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

		$this->register_option_update_hook();
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

	/**
	 * Register option update hook to purge cache on critical changes.
	 *
	 * @return void
	 */
	private function register_option_update_hook(): void {
		if ( $this->hooks_registered ) {
			return;
		}

		add_action(
			'updated_option',
			array( $this, 'handle_option_update' ),
			10,
			3
		);

		$this->hooks_registered = true;
	}

	/**
	 * Handle option update to invalidate cache.
	 *
	 * @param string $option    Option name.
	 * @param mixed  $old_value Old value.
	 * @param mixed  $value     New value.
	 * @return void
	 */
	public function handle_option_update( string $option, $old_value, $value ): void {
		if ( ! in_array( $option, $this->get_cache_invalidation_options(), true ) ) {
			return;
		}

		$cache_service = null;

		if ( Container::bound( CacheServiceInterface::class ) ) {
			$cache_service = Container::make( CacheServiceInterface::class );
		}

		if ( $cache_service instanceof CacheServiceInterface ) {
			$cache_service->purge_all();
		}
	}

	/**
	 * List of options that require cache purge.
	 *
	 * @return array<int, string>
	 */
	private function get_cache_invalidation_options(): array {
		return array(
			'vkpAccessToken',
			'vkpAccaunts',
			'vkpAccaunts_type',
			'vkpPreviewSize',
			'vkpPhotoViewSize',
			'vkpLifeTimeCaching',
			'vkpEnableCaching',
			'vkpCalculateCache',
		);
	}
}
