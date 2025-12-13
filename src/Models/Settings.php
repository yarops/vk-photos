<?php
/**
 * Settings model.
 */

namespace VkPhotos\Models;

/**
 * Class Settings.
 * Represents plugin settings entity.
 */
class Settings {

	/**
	 * Settings data storage.
	 *
	 * @var array<string, mixed>
	 */
	private array $data = array();

	/**
	 * Keys for settings.
	 *
	 * @var array<string, array{string, mixed, string, callable|null}>
	 */
	public const CONFIG = array(
		'count_photos'     => array( 'vkpCountPhotos', 12, 'int', null ),
		'accounts'         => array( 'vkpAccaunts', array(), 'array', null ),
		'accounts_type'    => array( 'vkpAccaunts_type', array(), 'array', null ),
		'enable_caching'   => array( 'vkpEnableCaching', 'no', 'yesno', null ),
		'access_token'     => array( 'vkpAccessToken', '', 'string', null ),
		'lifetime_caching' => array( 'vkpLifeTimeCaching', 0, 'int', null ),
		'preview_size'     => array( 'vkpPreviewSize', 'photo_130', 'string', null ),
		'photo_view_size'  => array( 'vkpPhotoViewSize', 'photo_807', 'string', null ),
		'preview_type'     => array( 'vkpPreviewType', 'keep', 'string', null ),
		'show_title'       => array( 'vkpShowTitle', 'no', 'yesno', null ),
		'show_signatures'  => array( 'vkpShowSignatures', 'no', 'yesno', null ),
		'template'         => array( 'vkpTemplate', 'light', 'string', null ),
		'viewer'           => array( 'vkpViewer', 'none', 'string', null ),
		'calculate_cache'  => array( 'vkpCalculateCache', 'no', 'yesno', null ),
		'show_description' => array( 'vkpShowDescription', 'no', 'yesno', null ),
		'more_title'       => array( 'vkpMoreTitle', '[далее]', 'string', null ),
	);

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $data Settings data.
	 * @return void
	 */
	public function __construct( array $data = array() ) {
		foreach ( self::CONFIG as $key => $value ) {
			list($legacy_key, $default, $type, $callback) = $value;

			// Try new name first, then legacy name.
			$value = $data[ $key ] ?? $data[ $legacy_key ] ?? $default;

			// Apply type casting and transformation.
			$this->data[ $key ] = $this->normalize_value( $value, $type );
		}
	}

	/**
	 * Normalize value by type.
	 *
	 * @param mixed  $value Value to normalize.
	 * @param string $type  Value type.
	 * @return mixed Normalized value.
	 */
	private function normalize_value( $value, string $type ) {
		switch ( $type ) {
			case 'int':
				return (int) $value;
			case 'array':
				return is_array( $value ) ? $value : array();
			case 'yesno':
				return ( 'yes' === $value
				|| true === $value
				|| '1' === $value ) ? 'yes' : 'no';
			case 'string':
			default:
				return (string) $value;
		}
	}

	/**
	 * Magic getter for property access (backward compatibility).
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( string $name ) {
		return $this->data[ $name ] ?? null;
	}

	/**
	 * Magic setter for property access (backward compatibility).
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 * @return void
	 */
	public function __set( string $name, $value ): void {
		if ( isset( self::CONFIG[ $name ] ) ) {
			list( , , $type )    = self::CONFIG[ $name ];
			$this->data[ $name ] = $this->normalize_value( $value, $type );
		}
	}

	/**
	 * Check if property exists.
	 *
	 * @param string $name Property name.
	 * @return bool True if property exists.
	 */
	public function __isset( string $name ): bool {
		return isset( $this->data[ $name ] );
	}

	/**
	 * Convert settings to array.
	 *
	 * @return array Settings data as array.
	 */
	public function to_array(): array {
		return $this->data;
	}

	/**
	 * Convert settings to WordPress options format (legacy names).
	 *
	 * @return array Settings data with legacy option names.
	 */
	public function to_wp_options(): array {
		$result = array();
		foreach ( self::CONFIG as $key => $value ) {
			list( $legacy_key )    = $value;
			$result[ $legacy_key ] = $this->data[ $key ];
		}

		return $result;
	}

	/**
	 * Get legacy option keys (for backward compatibility).
	 *
	 * @return array<string> Array of legacy option keys.
	 */
	public function get_keys(): array {
		$keys = array();
		foreach ( self::CONFIG as $value ) {
			list( $legacy_key ) = $value;
			$keys[]             = $legacy_key;
		}
		return $keys;
	}
}
