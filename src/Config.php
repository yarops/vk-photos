<?php
/**
 * Configuration class for vk-photos plugin.
 * Centralized configuration management to replace constants.
 *
 * @package VkPhotos
 */

namespace VkPhotos;

/**
 * Class Config.
 * Configuration singleton with dot-notation support.
 *
 * @example
 * // Get config value
 * $url = Config::get('plugin.url');
 *
 * // Get with default value
 * $timeout = Config::get('api.timeout', 30);
 *
 * // Set config value
 * Config::set('plugin.version', '1.5');
 *
 * // Check if key exists
 * if (Config::has('plugin.url')) {
 *     $url = Config::get('plugin.url');
 * }
 */
class Config {

	/**
	 * Configuration storage.
	 *
	 * @var array<string, mixed>
	 */
	private static array $config = array();

	/**
	 * Whether config is initialized.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;

	/**
	 * Initialize configuration with default values.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}

		// Calculate plugin main file path.
		// Config.php is in src/, plugin root is one level up.
		$plugin_root = dirname( __DIR__ );
		$plugin_file = $plugin_root . '/vk-photos.php';

		$default_config = require __DIR__ . '/config/default.php';
		self::$config   = $default_config( $plugin_file );

		self::$initialized = true;
	}

	/**
	 * Get configuration value by key.
	 * Supports dot-notation for nested arrays.
	 *
	 * @param string $key Configuration key (e.g., 'plugin.url' or 'plugin').
	 * @param mixed  $default Default value if key doesn't exist.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		if ( ! self::$initialized ) {
			return $default;
		}

		$keys  = explode( '.', $key );
		$value = self::$config;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Set configuration value by key.
	 * Supports dot-notation for nested arrays.
	 *
	 * @param string $key Configuration key (e.g., 'plugin.url').
	 * @param mixed  $value Value to set.
	 * @return void
	 */
	public static function set( string $key, $value ): void {
		$keys   = explode( '.', $key );
		$config = &self::$config;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $config ) ) {
				$config = array();
			}
			if ( ! array_key_exists( $segment, $config ) ) {
				$config[ $segment ] = array();
			}
			$config = &$config[ $segment ];
		}

		$config = $value;
	}

	/**
	 * Check if configuration key exists.
	 * Supports dot-notation for nested arrays.
	 *
	 * @param string $key Configuration key.
	 * @return bool
	 */
	public static function has( string $key ): bool {
		if ( ! self::$initialized ) {
			return false;
		}

		$keys  = explode( '.', $key );
		$value = self::$config;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return false;
			}
			$value = $value[ $segment ];
		}

		return true;
	}

	/**
	 * Get all configuration.
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array {
		return self::$config;
	}

	/**
	 * Merge configuration array.
	 * Useful for loading additional config from files.
	 *
	 * @param array<string, mixed> $config Configuration to merge.
	 * @return void
	 */
	public static function merge( array $config ): void {
		self::$config = array_merge_recursive( self::$config, $config );
	}
}
