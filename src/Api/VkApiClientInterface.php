<?php
/**
 * VK API Client Interface.
 * Defines contract for VK API communication.
 *
 * @package VkPhotos\Api
 */

namespace VkPhotos\Api;

/**
 * Interface VkApiClientInterface.
 * Contract for VK API client implementations.
 */
interface VkApiClientInterface {

	/**
	 * Execute API method.
	 *
	 * @param string $method API method name.
	 * @param array  $params Request parameters.
	 * @return array API response.
	 */
	public function api( string $method, array $params = array() ): array;

	/**
	 * Get albums for owner.
	 *
	 * @param int $owner_id Owner ID (user or group).
	 * @return array Albums data.
	 */
	public function get_albums( int $owner_id ): array;

	/**
	 * Get photos from album.
	 *
	 * @param int $owner_id Owner ID (user or group).
	 * @param int $album_id Album ID.
	 * @return array Photos data.
	 */
	public function get_photos( int $owner_id, int $album_id ): array;
}
