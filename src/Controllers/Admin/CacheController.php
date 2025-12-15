<?php
/**
 * Cache controller for admin actions.
 */

namespace VkPhotos\Controllers\Admin;

use VkPhotos\Container;
use VkPhotos\Services\CacheServiceInterface;

/**
 * Class CacheController.
 * Handles cache clearing actions.
 */
class CacheController {

	/**
	 * Clear all cache entries.
	 *
	 * @return void
	 */
	public function clear_all(): void {
		$this->assert_permissions();
		check_admin_referer( 'vkp_clear_cache' );

		$service = Container::make( CacheServiceInterface::class );
		$service->purge_all();

		$this->redirect_back();
	}

	/**
	 * Clear specific album cache.
	 *
	 * @return void
	 */
	public function clear_album(): void {
		$this->assert_permissions();
		check_admin_referer( 'vkp_clear_album_cache' );

		$owner_id_raw = isset( $_POST['owner_id'] ) ? $_POST['owner_id'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$album_id_raw = isset( $_POST['album_id'] ) ? $_POST['album_id'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$owner_id = ( is_scalar( $owner_id_raw ) ) ? (int) $owner_id_raw : 0;
		$album_id = ( is_scalar( $album_id_raw ) ) ? (int) $album_id_raw : 0;

		if ( 0 !== $owner_id && 0 !== $album_id ) {
			$service = Container::make( CacheServiceInterface::class );
			$service->delete_album( $owner_id, $album_id );
		}

		$this->redirect_back();
	}

	/**
	 * Ensure user has permissions.
	 *
	 * @return void
	 */
	private function assert_permissions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'vkp' ) );
		}
	}

	/**
	 * Redirect back to referer or albums page.
	 *
	 * @return void
	 */
	private function redirect_back(): void {
		$target = wp_get_referer() ?: admin_url( 'admin.php?page=vk-albums' );
		wp_safe_redirect( $target );
		exit;
	}
}
