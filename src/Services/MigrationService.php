<?php
/**
 * Migration service.
 */

namespace VkPhotos\Services;

/**
 * Class MigrationService.
 * Handles data migration between plugin versions.
 */
class MigrationService {

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private const CURRENT_VERSION = '2.1.0';

	/**
	 * Option name for storing plugin version.
	 *
	 * @var string
	 */
	private const VERSION_OPTION = 'vk_photos_version';

	/**
	 * Check and run migrations if needed.
	 *
	 * @return void
	 */
	public static function check_migrations(): void {
		$stored_version = get_option( self::VERSION_OPTION, '1.0.0' );

		if ( version_compare( $stored_version, self::CURRENT_VERSION, '<' ) ) {
			self::run_migrations( $stored_version );
			update_option( self::VERSION_OPTION, self::CURRENT_VERSION );
		}
	}

	/**
	 * Run migrations from stored version to current.
	 *
	 * @param string $from_version Version to migrate from.
	 * @return void
	 */
	private static function run_migrations( string $from_version ): void {
		// Migration from versions before 2.1.0 (accounts v2 format).
		if ( version_compare( $from_version, '2.1.0', '<' ) ) {
			self::migrate_to_accounts_v2();
		}
	}

	/**
	 * Migrate accounts data from legacy format to v2 format.
	 *
	 * @return void
	 */
	private static function migrate_to_accounts_v2(): void {
		$old_accounts = get_option( 'vkpAccaunts', array() );
		$old_types    = get_option( 'vkpAccaunts_type', array() );

		if ( empty( $old_accounts ) || ! empty( get_option( 'vkpAccaunts_v2', '' ) ) ) {
			return;
		}

		$new_accounts = array();

		foreach ( $old_accounts as $index => $id ) {
			if ( ! empty( $id ) ) {
				$type           = $old_types[ $index ] ?? 'user';
				$new_accounts[] = array(
					'id'   => (int) $id,
					'type' => $type,
				);
			}
		}

		// Save in new format.
		if ( ! empty( $new_accounts ) ) {
			update_option( 'vkpAccaunts_v2', serialize( $new_accounts ) );
		}

		// Optionally, clean up old data after confirming migration works.
		// delete_option( 'vkpAccaunts' );
		// delete_option( 'vkpAccaunts_type' ).
	}

	/**
	 * Get current plugin version.
	 *
	 * @return string Plugin version.
	 */
	public static function get_current_version(): string {
		return self::CURRENT_VERSION;
	}

	/**
	 * Get stored plugin version.
	 *
	 * @return string Stored version.
	 */
	public static function get_stored_version(): string {
		return get_option( self::VERSION_OPTION, '1.0.0' );
	}
}
