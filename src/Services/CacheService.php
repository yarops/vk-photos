<?php
/**
 * Cache service.
 */

namespace VkPhotos\Services;

use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Repositories\CacheRepository;

/**
 * Class CacheService.
 * Coordinates cache repository with settings (TTL, enable flags).
 */
class CacheService implements CacheServiceInterface {

	/**
	 * Cache repository.
	 *
	 * @var CacheRepository
	 */
	private CacheRepository $repository;

	/**
	 * Settings service.
	 *
	 * @var SettingsService
	 */
	private SettingsService $settings_service;

	/**
	 * Constructor.
	 *
	 * @param CacheRepository $repository       Cache repository.
	 * @param SettingsService $settings_service Settings service.
	 * @return void
	 */
	public function __construct( CacheRepository $repository, SettingsService $settings_service ) {
		$this->repository       = $repository;
		$this->settings_service = $settings_service;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_album( int $owner_id, int $album_id ): ?array {
		if ( ! $this->is_enabled() ) {
			return null;
		}

		$payload = $this->repository->read( $owner_id, $album_id );
		if ( null === $payload ) {
			return null;
		}

		if ( ! $this->is_payload_valid( $payload ) ) {
			return null;
		}

		return $payload['data'] ?? null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save_album( int $owner_id, int $album_id, array $payload ): bool {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$envelope = array(
			'meta' => array(
				'fetched_at'     => time(),
				'schema_version' => 1,
			),
			'data' => $payload,
		);

		return $this->repository->write( $owner_id, $album_id, $envelope );
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete_album( int $owner_id, int $album_id ): bool {
		return $this->repository->delete( $owner_id, $album_id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function purge_all(): bool {
		return $this->repository->purge_all();
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( int $owner_id, int $album_id ): bool {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$payload = $this->repository->read( $owner_id, $album_id );
		if ( null === $payload ) {
			return false;
		}

		return $this->is_payload_valid( $payload );
	}

	/**
	 * {@inheritdoc}
	 */
	public function size( ?int $owner_id = null, ?int $album_id = null ): ?int {
		if ( ! $this->should_calculate_size() ) {
			return null;
		}

		return $this->repository->size( $owner_id, $album_id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_enabled(): bool {
		$settings = $this->settings_service->get_settings();
		return isset( $settings->enable_caching ) && 'yes' === $settings->enable_caching;
	}

	/**
	 * Check payload TTL validity.
	 *
	 * @param array $payload Cached envelope.
	 * @return bool
	 */
	private function is_payload_valid( array $payload ): bool {
		if ( ! isset( $payload['meta']['fetched_at'] ) ) {
			return false;
		}

		$settings = $this->settings_service->get_settings();
		$ttl      = $this->get_ttl_seconds( $settings );

		if ( 0 === $ttl ) {
			return true;
		}

		$fetched_at = (int) $payload['meta']['fetched_at'];

		return ( time() - $fetched_at ) <= $ttl;
	}

	/**
	 * TTL in seconds from settings.
	 *
	 * @param SettingsModel $settings Settings model.
	 * @return int
	 */
	private function get_ttl_seconds( SettingsModel $settings ): int {
		$ttl_hours = isset( $settings->lifetime_caching ) ? (int) $settings->lifetime_caching : 0;
		if ( $ttl_hours <= 0 ) {
			return 0;
		}

		return $ttl_hours * 3600;
	}

	/**
	 * Whether cache size calculation is allowed.
	 *
	 * @return bool
	 */
	private function should_calculate_size(): bool {
		$settings = $this->settings_service->get_settings();
		return isset( $settings->calculate_cache ) && 'yes' === $settings->calculate_cache;
	}
}
