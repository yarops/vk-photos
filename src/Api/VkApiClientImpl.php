<?php
/**
 * VK API Client Implementation.
 * Implementation of VK API client interface.
 *
 * @package VkPhotos\Api
 */

namespace VkPhotos\Api;

/**
 * Class VkApiClientImpl.
 * VK API client implementation.
 */
class VkApiClientImpl implements VkApiClientInterface {

	/**
	 * Access token for API requests.
	 *
	 * @var string|null
	 */
	public ?string $access_token = null;

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private string $api_url;

	/**
	 * Constructor.
	 *
	 * @param string $api_url API URL (default: api.vk.com/method/).
	 */
	public function __construct( string $api_url = 'api.vk.com/method/' ) {
		if ( ! str_contains( $api_url, 'https://' ) ) {
			$api_url = 'https://' . $api_url;
		}
		$this->api_url = $api_url;
	}

	/**
	 * Execute API method.
	 *
	 * @param string $method API method name.
	 * @param array  $params Request parameters.
	 * @return array API response.
	 */
	public function api( string $method, array $params = array() ): array {
		// Use API version 5.199 (new format with sizes array).
		// The code will convert new format to old format for compatibility.
		if ( ! isset( $params['v'] ) ) {
			$params['v'] = '5.199';
		}

		// Add access token if set.
		if ( $this->access_token ) {
			$params['access_token'] = $this->access_token;
		}

		ksort( $params );
		$query = $this->api_url . $method . '?' . $this->build_params( $params );

		$res = $this->make_request( $query );

		return json_decode( $res, true ) ?? array();
	}

	/**
	 * Get albums for owner.
	 *
	 * @param int $owner_id Owner ID (user or group).
	 * @return array Albums data.
	 */
	public function get_albums( int $owner_id ): array {
		$response = $this->api(
			'photos.getAlbums',
			array(
				'owner_id' => $owner_id,
			)
		);

		return $response['response']['items'] ?? array();
	}

	/**
	 * Get photos from album.
	 *
	 * @param int $owner_id Owner ID (user or group).
	 * @param int $album_id Album ID.
	 * @return array Photos data.
	 */
	public function get_photos( int $owner_id, int $album_id ): array {
		$response = $this->api(
			'photos.get',
			array(
				'owner_id' => $owner_id,
				'album_id' => $album_id,
			)
		);

		return $response['response']['items'] ?? array();
	}

	/**
	 * Build query parameters string.
	 *
	 * @param array $params Parameters array.
	 * @return string Query string.
	 */
	private function build_params( array $params ): string {
		$pieces = array();
		foreach ( $params as $key => $value ) {
			$pieces[] = $key . '=' . urlencode( $value );
		}
		return implode( '&', $pieces );
	}

	/**
	 * Make HTTP request to API.
	 *
	 * @param string $query Full query URL.
	 * @return string Response body.
	 */
	private function make_request( string $query ): string {
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			@curl_setopt( $ch, CURLOPT_URL, $query );
			@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			@curl_setopt( $ch, CURLOPT_TIMEOUT, 2 );
			// Connection timeout.
			@curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 2 );
			// User agent.
			@curl_setopt( $ch, CURLOPT_USERAGENT, 'Opera 10.00' );
			@curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$res = @curl_exec( $ch );
			@curl_close( $ch );
			// Fallback to file_get_contents if curl fails.
			if ( empty( $res ) ) {
				$res = @file_get_contents( $query );
			}
		} else {
			$res = @file_get_contents( $query );
		}

		return $res ?: '';
	}
}
