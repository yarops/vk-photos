<?php
/**
 * Albums service.
 */

namespace VkPhotos\Services;

use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Interfaces\AlbumsViewData;
use VkPhotos\Models\Album;
use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Repositories\AlbumRepository;
use VkPhotos\Services\CacheServiceInterface;

/**
 * Class AlbumsService.
 * Prepares albums data for admin view.
 */
class AlbumsService implements AlbumsViewData {

	/**
	 * Settings service.
	 *
	 * @var SettingsService
	 */
	private SettingsService $settings_service;

	/**
	 * VK API client.
	 *
	 * @var VkApiClientInterface
	 */
	private VkApiClientInterface $api_client;

	/**
	 * Album repository.
	 *
	 * @var AlbumRepository
	 */
	private AlbumRepository $album_repository;

	/**
	 * Cache directory absolute path.
	 *
	 * @var string
	 */
	private string $cache_dir;

	/**
	 * Cache service.
	 *
	 * @var CacheServiceInterface
	 */
	private CacheServiceInterface $cache_service;

	/**
	 * Whether to calculate cache size.
	 *
	 * @var bool
	 */
	private bool $should_calculate_cache = false;

	/**
	 * Constructor.
	 *
	 * @param SettingsService       $settings_service Settings service.
	 * @param VkApiClientInterface  $api_client VK API client.
	 * @param AlbumRepository       $album_repository Album repository.
	 * @param CacheServiceInterface $cache_service   Cache service.
	 * @return void
	 */
	public function __construct(
		SettingsService $settings_service,
		VkApiClientInterface $api_client,
		AlbumRepository $album_repository,
		CacheServiceInterface $cache_service
	) {
		$this->settings_service = $settings_service;
		$this->api_client       = $api_client;
		$this->album_repository = $album_repository;
		$this->cache_service    = $cache_service;
		$this->cache_dir        = $this->resolve_cache_dir();
	}

	/**
	 * Get data for albums view.
	 *
	 * @return array<string, mixed> View data.
	 */
	public function get_view_data(): array {
		$settings = $this->settings_service->get_settings();

		$this->should_calculate_cache = ( 'yes' === ( $settings->calculate_cache ?? 'no' ) );

		$this->set_access_token( $settings->access_token ?? '' );

		return array(
			'pageTitle' => __( 'Albums', 'vkp' ),
			'accounts'  => $this->build_accounts_data( $settings ),
			'actions'   => $this->build_cache_actions(),
			'showCache' => $this->should_calculate_cache,
		);
	}

	/**
	 * Build accounts view data.
	 *
	 * @param SettingsModel $settings Settings model.
	 * @return array<int, array<string, mixed>> Accounts data.
	 */
	private function build_accounts_data( SettingsModel $settings ): array {
		// Get accounts in v2 format (structured array).
		$accounts = $settings->get_accounts_v2();
		$result   = array();

		foreach ( $accounts as $account ) {
			$account_id = (int) $account['id'];
			$type       = $account['type'] ?? 'user';
			$is_group   = ( 'group' === $type );

			if ( $account_id <= 0 ) {
				continue;
			}

			$account_data = array(
				'id'          => $account_id,
				'type'        => $type,
				'link'        => '',
				'displayName' => '',
				'error'       => null,
				'albums'      => array(),
				'warnings'    => array(),
			);

			$meta = $this->fetch_account_meta( $account_id, $is_group );

			$account_data['displayName'] = $meta['displayName'];
			$account_data['link']        = $meta['link'];

			if ( $meta['error'] ) {
				$account_data['error'] = $meta['error'];
				$result[]              = $account_data;
				continue;
			}

			$albums_data            = $this->fetch_albums_for_owner( $account_id, $is_group );
			$account_data['albums'] = $albums_data['albums'];

			if ( $albums_data['error'] ) {
				$account_data['warnings'][] = $albums_data['error'];
			}

			$result[] = $account_data;
		}

		return $result;
	}

	/**
	 * Fetch albums for owner and map to view format.
	 *
	 * @param int  $account_id Account id (positive).
	 * @param bool $is_group   Whether account is group.
	 * @return array{albums: array<int, array<string, mixed>>, error: string|null} Albums and error.
	 */
	private function fetch_albums_for_owner( int $account_id, bool $is_group ): array {
		$owner_id = $is_group ? -abs( $account_id ) : $account_id;

		$result = $this->album_repository->get_albums_with_error( $owner_id );

		$albums_view = array();
		foreach ( $result['albums'] as $album ) {
			$albums_view[] = $this->map_album_to_view( $album, $account_id, $is_group, $owner_id );
		}

		return array(
			'albums' => $albums_view,
			'error'  => $result['error'],
		);
	}

	/**
	 * Map album model to view data.
	 *
	 * @param Album $album      Album model.
	 * @param int   $account_id Account id without sign.
	 * @param bool  $is_group   Whether account is group.
	 * @param int   $owner_id   Owner id with sign (group negative).
	 * @return array<string, mixed> Album view data.
	 */
	private function map_album_to_view( Album $album, int $account_id, bool $is_group, int $owner_id ): array {
		$owner_prefix = $is_group ? '-' : '';

		return array(
			'id'          => $album->id,
			'ownerId'     => $album->owner_id,
			'ownerLink'   => "http://vk.com/album{$owner_prefix}{$account_id}_{$album->id}",
			'title'       => $album->title,
			'description' => $album->description,
			'createdAt'   => $album->get_created_date(),
			'updatedAt'   => $album->get_updated_date(),
			'size'        => $album->size,
			'cacheSizeMb' => $this->get_album_cache_size_mb( $owner_id, $album->id ),
			'shortcode'   => $album->get_shortcode(),
		);
	}

	/**
	 * Fetch account meta (name and link).
	 *
	 * @param int  $account_id Account id without sign.
	 * @param bool $is_group   Whether account is group.
	 * @return array{displayName: string, link: string, error: string|null} Account data or error.
	 */
	private function fetch_account_meta( int $account_id, bool $is_group ): array {
		$result = array(
			'displayName' => '',
			'link'        => '',
			'error'       => null,
		);

		try {
			if ( $is_group ) {
				$response = $this->api_client->api(
					'groups.getById',
					array(
						'group_id' => $account_id,
					)
				);

				if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
					$result['error'] = $response['error']['error_msg'] ?? __( 'Error getting group info', 'vkp' );
					return $result;
				}

				$group = $response['response']['groups'][0] ?? null;
				if ( ! $group ) {
					$result['error'] = __( 'Error getting group info', 'vkp' );
					return $result;
				}

				$group_link = '';
				if ( isset( $group['screen_name'] ) && ! empty( $group['screen_name'] ) ) {
					$group_link = 'http://vk.com/' . $group['screen_name'];
				} else {
					$group_id   = $group['id'] ?? $account_id;
					$group_link = 'http://vk.com/club' . $group_id;
				}

				$result['displayName'] = $group['name'] ?? '';
				$result['link']        = $group_link;
				return $result;
			}

			$response = $this->api_client->api(
				'users.get',
				array(
					'user_id' => $account_id,
				)
			);

			if ( isset( $response['error'] ) && is_array( $response['error'] ) ) {
				$result['error'] = $response['error']['error_msg'] ?? __( 'Error getting user info', 'vkp' );
				return $result;
			}

			$user = $response['response'][0] ?? null;
			if ( ! $user ) {
				$result['error'] = __( 'Error getting user info', 'vkp' );
				return $result;
			}

			$result['displayName'] = trim( ( $user['first_name'] ?? '' ) . ' ' . ( $user['last_name'] ?? '' ) );
			$result['link']        = 'http://vk.com/id' . $account_id;
			return $result;

		} catch ( \Exception $e ) {
			$result['error'] = $e->getMessage();
			return $result;
		}
	}

	/**
	 * Build cache-related actions for view.
	 *
	 * @return array<string, mixed>
	 */
	private function build_cache_actions(): array {
		return array(
			'postUrl'   => admin_url( 'admin-post.php' ),
			'clearAll'  => array(
				'action' => 'vkp_clear_cache',
				'nonce'  => wp_create_nonce( 'vkp_clear_cache' ),
			),
			'clearItem' => array(
				'action' => 'vkp_clear_album_cache',
				'nonce'  => wp_create_nonce( 'vkp_clear_album_cache' ),
			),
		);
	}

	/**
	 * Resolve cache directory path.
	 *
	 * @return string Cache directory.
	 */
	private function resolve_cache_dir(): string {
		$upload_dir = wp_upload_dir();

		$base_dir = $upload_dir['basedir'] ?? '';
		$base_dir = rtrim( (string) $base_dir, '/\\' );

		return $base_dir . '/vk-photos-cache/';
	}

	/**
	 * Set API access token if provided.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private function set_access_token( string $token ): void {
		if ( empty( $token ) ) {
			return;
		}

		if ( property_exists( $this->api_client, 'access_token' ) ) {
			$this->api_client->access_token = $token;
		}
	}

	/**
	 * Calculate album cache size in megabytes.
	 *
	 * @param int $owner_id Owner id with sign.
	 * @param int $album_id Album id.
	 * @return float|null Cache size or null if not calculated.
	 */
	private function get_album_cache_size_mb( int $owner_id, int $album_id ): ?float {
		$size = $this->cache_service->size( $owner_id, $album_id );
		if ( null === $size ) {
			return null;
		}

		return round( $size / 1024 / 1024, 2 );
	}
}
