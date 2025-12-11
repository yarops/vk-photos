<?php
/**
 * Photo repository.
 */

namespace VkPhotos\Repositories;

use VkPhotos\Models\Photo;
use VkPhotos\Api\VkApiClientInterface;

/**
 * Class PhotoRepository.
 * Handles photo data retrieval from a data source.
 */
class PhotoRepository {

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
	 * Get photos by album ID.
	 *
	 * @param int $album_id Album ID.
	 * @return array<Photo> Array of Photo models.
	 */
	public function get_photos( int $album_id ): array {
		$photos = array();

		try {
			$response = $this->api_client->api(
				'photos.get',
				array(
					'album_id' => $album_id,
				)
			);

			$items = $this->extract_items_from_response( $response );
			if ( empty( $items ) ) {
				return $photos;
			}

			// Convert each item to Album model.
			foreach ( $items as $item ) {
				$photo = $this->map_to_photo( $item );
				if ( $photo ) {
					$photos[] = $photo;
				}
			}
		} catch ( \Exception $e ) {
			// Log error if needed.
			return $photos;
		}

		return $photos;
	}

	/**
	 * Get single photo by ID.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $photo_id Photo ID.
	 * @return Photo|null Photo model or null if not found.
	 */
	public function get_photo( int $owner_id, int $photo_id ): ?Photo {
		try {
			$response = $this->api_client->api(
				'photos.getById',
				array(
					'photos' => $owner_id . '_' . $photo_id,
				)
			);

			$items = $this->extract_items_from_response( $response );
			if ( empty( $items ) ) {
				return null;
			}

			// Get first album (should be only one when requesting by ID).
			$photo_data = $items[0];
			return $this->map_to_photo( $photo_data );

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
	 * Map API response data to Photo model.
	 *
	 * @param array $data Photo data from API.
	 * @return Photo|null Photo model or null if data is invalid.
	 */
	private function map_to_photo( array $data ): ?Photo {
		if ( empty( $data ) ) {
			return null;
		}

		// Normalize data format.
		// Handle both 'id' and 'aid' fields.
		$normalized = array(
			'id'         => isset( $data['aid'] ) ? (int) $data['aid'] : ( isset( $data['id'] ) ? (int) $data['id'] : 0 ),
			'album_id'   => isset( $data['album_id'] ) ? (int) $data['album_id'] : 0,
			'text'       => isset( $data['text'] ) ? (string) $data['text'] : '',
			'date'       => isset( $data['date'] ) ? (int) $data['date'] : 0,
			'orig_photo' => isset( $data['orig_photo'] ) ? (array) $data['orig_photo'] : array(),
		);

		// Validate that we have at least ID and owner_id.
		if ( 0 === $normalized['id'] || 0 === $normalized['album_id'] ) {
			return null;
		}

		return new Photo( $normalized );
	}
}
