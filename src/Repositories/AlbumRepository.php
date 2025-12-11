<?php
/**
 * Album repository.
 */

namespace VkPhotos\Repositories;

use VkPhotos\Models\Album;
use VkPhotos\Api\VkApiClientInterface;

/**
 * Class AlbumRepository.
 * Handles album data retrieval from a data source.
 */
class AlbumRepository {

	/**
	 * VK API client.
	 *
	 * @var VkApiClientInterface
	 */
	private VkApiClientInterface $api_client;

	/**
	 * Constructor.
	 *
	 * @param VkApiClientInterface $api_client VK API client.
	 * @return void
	 */
	public function __construct( VkApiClientInterface $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Get albums for owner.
	 *
	 * @param int $owner_id Owner ID (user or group).
	 * @return array<Album> Array of Album models.
	 */
	public function get_albums( int $owner_id ): array {
		$albums = array();

		try {
			// Use direct API call for consistency with get_album().
			$response = $this->api_client->api(
				'photos.getAlbums',
				array(
					'owner_id' => $owner_id,
				)
			);

			$items = $this->extract_items_from_response( $response );
			if ( empty( $items ) ) {
				return $albums;
			}

			// Convert each item to Album model.
			foreach ( $items as $item ) {
				$album = $this->map_to_album( $item );
				if ( $album ) {
					$albums[] = $album;
				}
			}
		} catch ( \Exception $e ) {
			// Log error if needed.
			return $albums;
		}

		return $albums;
	}

	/**
	 * Get single album by ID.
	 *
	 * @param int $owner_id Owner ID (user or group).
	 * @param int $album_id Album ID.
	 * @return Album|null Album model or null if not found.
	 */
	public function get_album( int $owner_id, int $album_id ): ?Album {
		try {
			// Use direct API call for consistency with get_albums().
			$response = $this->api_client->api(
				'photos.getAlbums',
				array(
					'owner_id'  => $owner_id,
					'album_ids' => $album_id,
				)
			);

			$items = $this->extract_items_from_response( $response );
			if ( empty( $items ) ) {
				return null;
			}

			// Get first album (should be only one when requesting by ID).
			$album_data = $items[0];
			return $this->map_to_album( $album_data );

		} catch ( \Exception $e ) {
			// Log error if needed.
			return null;
		}
	}

	/**
	 * Extract items array from API response.
	 *
	 * @param array $response API response.
	 * @return array Items array or empty array on error.
	 */
	private function extract_items_from_response( array $response ): array {
		// Check for API errors.
		if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
			return array();
		}

		// Check if we have valid response.
		if ( ! isset( $response['response'] ) || ! is_array( $response['response'] ) ) {
			return array();
		}

		// Handle both 'items' (new format) and direct array (old format).
		if ( isset( $response['response']['items'] ) && is_array( $response['response']['items'] ) ) {
			return $response['response']['items'];
		} elseif ( ! empty( $response['response'] ) ) {
			// Old format - direct array.
			return $response['response'];
		}

		return array();
	}

	/**
	 * Map API response data to Album model.
	 *
	 * @param array $data Album data from API.
	 * @return Album|null Album model or null if data is invalid.
	 */
	private function map_to_album( array $data ): ?Album {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return null;
		}

		// Normalize data format.
		// Handle both 'id' and 'aid' fields.
		$normalized = array(
			'id'          => isset( $data['aid'] ) ? (int) $data['aid'] : ( isset( $data['id'] ) ? (int) $data['id'] : 0 ),
			'owner_id'    => isset( $data['owner_id'] ) ? (int) $data['owner_id'] : 0,
			'title'       => isset( $data['title'] ) ? (string) $data['title'] : '',
			'description' => isset( $data['description'] ) ? (string) $data['description'] : '',
			'created_at'  => isset( $data['created'] ) ? (int) $data['created'] : ( isset( $data['created_at'] ) ? (int) $data['created_at'] : 0 ),
			'updated_at'  => isset( $data['updated'] ) ? (int) $data['updated'] : ( isset( $data['updated_at'] ) ? (int) $data['updated_at'] : 0 ),
			'size'        => isset( $data['size'] ) ? (int) $data['size'] : 0,
		);

		// Validate that we have at least ID and owner_id.
		if ( 0 === $normalized['id'] || 0 === $normalized['owner_id'] ) {
			return null;
		}

		return new Album( $normalized );
	}
}
