<?php
/**
 * Photo service with caching.
 */

namespace VkPhotos\Services;

use VkPhotos\Models\Photo;
use VkPhotos\Repositories\PhotoRepository;

/**
 * Class PhotoService.
 * Provides cached access to photos.
 */
class PhotoService {

	/**
	 * Photo repository.
	 *
	 * @var PhotoRepository
	 */
	private PhotoRepository $photo_repository;

	/**
	 * Cache service.
	 *
	 * @var CacheServiceInterface
	 */
	private CacheServiceInterface $cache_service;

	/**
	 * Constructor.
	 *
	 * @param PhotoRepository       $photo_repository Photo repository.
	 * @param CacheServiceInterface $cache_service   Cache service.
	 * @return void
	 */
	public function __construct( PhotoRepository $photo_repository, CacheServiceInterface $cache_service ) {
		$this->photo_repository = $photo_repository;
		$this->cache_service    = $cache_service;
	}

	/**
	 * Get photos with cache lookup.
	 *
	 * @param int $album_id Album ID.
	 * @param int $owner_id Owner ID.
	 * @return array<Photo>
	 */
	public function get_photos( int $album_id, int $owner_id ): array {
		// Try cache first.
		$cached = $this->cache_service->get_album( $owner_id, $album_id );
		if ( null !== $cached && isset( $cached['photos'] ) && is_array( $cached['photos'] ) ) {
			return $this->map_cached_photos( $cached['photos'] );
		}

		$photos = $this->photo_repository->get_photos( $album_id, $owner_id );

		if ( $this->cache_service->is_enabled() && ! empty( $photos ) ) {
			$this->cache_service->save_album(
				$owner_id,
				$album_id,
				array(
					'photos' => $this->map_photos_to_array( $photos ),
				)
			);
		}

		return $photos;
	}

	/**
	 * Map cached arrays to Photo models.
	 *
	 * @param array<int, array<string, mixed>> $cached Cached data.
	 * @return array<Photo>
	 */
	private function map_cached_photos( array $cached ): array {
		$result = array();

		foreach ( $cached as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$result[] = new Photo( $item );
		}

		return $result;
	}

	/**
	 * Map Photo models to arrays for storage.
	 *
	 * @param array<int, Photo> $photos Photo models.
	 * @return array<int, array<string, mixed>>
	 */
	private function map_photos_to_array( array $photos ): array {
		$result = array();

		foreach ( $photos as $photo ) {
			if ( ! $photo instanceof Photo ) {
				continue;
			}
			$result[] = $photo->to_array();
		}

		return $result;
	}
}
