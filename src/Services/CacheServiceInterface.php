<?php
/**
 * Cache service interface.
 */

namespace VkPhotos\Services;

/**
 * Interface CacheServiceInterface.
 */
interface CacheServiceInterface {

	/**
	 * Get cached album payload.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $album_id Album ID.
	 * @return array<string, mixed>|null Cached payload or null.
	 */
	public function get_album( int $owner_id, int $album_id ): ?array;

	/**
	 * Save album payload to cache.
	 *
	 * @param int   $owner_id Owner ID.
	 * @param int   $album_id Album ID.
	 * @param array $payload  Payload to store.
	 * @return bool True on success.
	 */
	public function save_album( int $owner_id, int $album_id, array $payload ): bool;

	/**
	 * Delete album cache.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $album_id Album ID.
	 * @return bool
	 */
	public function delete_album( int $owner_id, int $album_id ): bool;

	/**
	 * Purge all cache.
	 *
	 * @return bool
	 */
	public function purge_all(): bool;

	/**
	 * Check if cache entry is valid.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $album_id Album ID.
	 * @return bool
	 */
	public function is_valid( int $owner_id, int $album_id ): bool;

	/**
	 * Calculate cache size.
	 *
	 * @param int|null $owner_id Owner filter.
	 * @param int|null $album_id Album filter.
	 * @return int|null Bytes or null when disabled/unavailable.
	 */
	public function size( ?int $owner_id = null, ?int $album_id = null ): ?int;

	/**
	 * Whether caching is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool;
}
