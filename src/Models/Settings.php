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
		'accounts_v2'      => array( 'vkpAccaunts_v2', '', 'string', null ),
		'enable_caching'   => array( 'vkpEnableCaching', 'no', 'yesno', null ),
		'access_token'     => array( 'vkpAccessToken', '', 'string', null ),
		'lifetime_caching' => array( 'vkpLifeTimeCaching', 0, 'int', null ),
		'preview_size'     => array( 'vkpPreviewSize', 'photo_130', 'string', null ),
		'photo_view_size'  => array( 'vkpPhotoViewSize', 'photo_807', 'string', null ),
		'preview_type'     => array( 'vkpPreviewType', 'keep', 'string', null ),
		'show_title'       => array( 'vkpShowTitle', 'no', 'yesno', null ),
		'show_signatures'  => array( 'vkpShowSignatures', 'no', 'yesno', null ),
		'template'         => array( 'vkpTemplate', 'light', 'string', null ),
		'viewer'           => array( 'vkpViewer', 'fancybox', 'string', null ),
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

		// Ensure accounts data is in correct format.
		$this->normalize_accounts_data();
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

	/**
	 * Normalize accounts data to ensure correct format.
	 *
	 * @return void
	 */
	private function normalize_accounts_data(): void {
		// Try to get accounts from v2 format first.
		$accounts_v2 = $this->data['accounts_v2'] ?? '';

		if ( ! empty( $accounts_v2 ) && is_string( $accounts_v2 ) ) {
			$accounts = unserialize( $accounts_v2 );
			if ( is_array( $accounts ) ) {
				// Convert v2 format to legacy format for backward compatibility.
				$this->convert_v2_to_legacy( $accounts );
				return;
			}
		}

		// If no v2 data, ensure legacy format is correct.
		$accounts = $this->data['accounts'] ?? array();
		$types    = $this->data['accounts_type'] ?? array();

		if ( ! is_array( $accounts ) ) {
			$accounts = array();
		}
		if ( ! is_array( $types ) ) {
			$types = array();
		}

		$this->data['accounts']      = $accounts;
		$this->data['accounts_type'] = $types;
	}

	/**
	 * Convert v2 accounts format to legacy format.
	 *
	 * @param array<array{id: int, type: string}> $accounts Accounts in v2 format.
	 * @return void
	 */
	private function convert_v2_to_legacy( array $accounts ): void {
		$legacy_accounts = array();
		$legacy_types    = array();

		foreach ( $accounts as $index => $account ) {
			if ( isset( $account['id'], $account['type'] ) ) {
				// Use 1-based indexing for legacy format.
				$legacy_index                     = $index + 1;
				$legacy_accounts[ $legacy_index ] = (string) $account['id'];
				$legacy_types[ $legacy_index ]    = $account['type'];
			}
		}

		$this->data['accounts']      = $legacy_accounts;
		$this->data['accounts_type'] = $legacy_types;
	}

	/**
	 * Get accounts in v2 format (structured array).
	 *
	 * @return array<array{id: int, type: string}> Accounts data.
	 */
	public function get_accounts_v2(): array {
		$accounts_v2 = $this->data['accounts_v2'] ?? '';

		if ( ! empty( $accounts_v2 ) && is_string( $accounts_v2 ) ) {
			$accounts = unserialize( $accounts_v2 );
			if ( is_array( $accounts ) ) {
				return $accounts;
			}
		}

		// Fallback to legacy format.
		return $this->convert_legacy_to_v2();
	}

	/**
	 * Convert legacy accounts format to v2 format.
	 *
	 * @return array<array{id: int, type: string}> Accounts in v2 format.
	 */
	private function convert_legacy_to_v2(): array {
		$accounts = $this->data['accounts'] ?? array();
		$types    = $this->data['accounts_type'] ?? array();
		$result   = array();

		foreach ( $accounts as $index => $id ) {
			if ( ! empty( $id ) ) {
				$type     = $types[ $index ] ?? 'user';
				$result[] = array(
					'id'   => (int) $id,
					'type' => $type,
				);
			}
		}

		return $result;
	}

	/**
	 * Set accounts in v2 format.
	 *
	 * @param array<array{id: int, type: string}> $accounts Accounts data.
	 * @return void
	 */
	public function set_accounts_v2( array $accounts ): void {
		// Validate and filter accounts.
		$valid_accounts = array();
		foreach ( $accounts as $account ) {
			if ( isset( $account['id'], $account['type'] ) &&
				is_numeric( $account['id'] ) &&
				in_array( $account['type'], array( 'user', 'group' ), true ) ) {
				$valid_accounts[] = array(
					'id'   => (int) $account['id'],
					'type' => $account['type'],
				);
			}
		}

		// Store in v2 format.
		$this->data['accounts_v2'] = serialize( $valid_accounts );

		// Also update legacy format for backward compatibility.
		$this->convert_v2_to_legacy( $valid_accounts );
	}

	/**
	 * Get accounts count.
	 *
	 * @return int Number of accounts.
	 */
	public function get_accounts_count(): int {
		$accounts = $this->get_accounts_v2();
		return count( $accounts );
	}
}
