<?php
/**
 * Lightweight CacheService test (standalone).
 */

use VkPhotos\Models\Settings;
use VkPhotos\Repositories\CacheRepository;
use VkPhotos\Services\CacheService;
use VkPhotos\Services\SettingsService;

require_once __DIR__ . '/../vendor/autoload.php';

// --------- WordPress stubs for standalone run. --------- //
if ( ! function_exists( 'wp_upload_dir' ) ) {
	function wp_upload_dir() {
		return array(
			'basedir' => $GLOBALS['__vkp_test_dir'] ?? sys_get_temp_dir(),
		);
	}
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	function wp_mkdir_p( $target ) {
		return is_dir( $target ) ? true : mkdir( $target, 0777, true );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $key ) {
		return $GLOBALS['__vkp_options'][ $key ] ?? null;
	}
}

if ( ! function_exists( 'register_setting' ) ) {
	function register_setting() {
		// No-op for standalone test.
	}
}

// --------- Fixtures --------- //
$base_dir                  = sys_get_temp_dir() . '/vk-photos-cache-test-' . uniqid();
$GLOBALS['__vkp_test_dir'] = $base_dir;
$GLOBALS['__vkp_options']  = array(
	'vkpEnableCaching'   => 'yes',
	'vkpCalculateCache'  => 'no',
	'vkpLifeTimeCaching' => 1,
);

// --------- Helpers --------- //
function assert_true( $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function cleanup_dir( string $dir ): void {
	if ( ! file_exists( $dir ) ) {
		return;
	}
	$items = scandir( $dir );
	if ( ! $items ) {
		return;
	}
	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}
		$path = $dir . '/' . $item;
		if ( is_dir( $path ) ) {
			cleanup_dir( $path );
		} else {
			@unlink( $path );
		}
	}
	@rmdir( $dir );
}

register_shutdown_function(
	function () use ( $base_dir ) {
		cleanup_dir( $base_dir );
	}
);

// --------- Test flow --------- //
$settings_model   = new Settings();
$settings_service = new SettingsService( $settings_model );
$cache_repository = new CacheRepository( $base_dir );
$cache_service    = new CacheService( $cache_repository, $settings_service );

$owner_id = 42;
$album_id = 7;

$payload = array(
	'photos' => array(
		array(
			'id'         => 11,
			'owner_id'   => $owner_id,
			'album_id'   => $album_id,
			'text'       => 'test',
			'date'       => time(),
			'sizes'      => array( 'photo_130' => 'http://example.com/p130.jpg' ),
			'orig_photo' => array(
				'url'    => 'http://example.com/orig.jpg',
				'width'  => 100,
				'height' => 100,
				'type'   => 'x',
			),
		),
	),
);

assert_true( $cache_service->save_album( $owner_id, $album_id, $payload ), 'Save cache failed.' );

$cached = $cache_service->get_album( $owner_id, $album_id );
assert_true( is_array( $cached ), 'Cache not returned.' );
assert_true( isset( $cached['photos'][0]['id'] ) && 11 === $cached['photos'][0]['id'], 'Cached payload mismatch.' );

assert_true( $cache_service->delete_album( $owner_id, $album_id ), 'Delete album cache failed.' );
assert_true( null === $cache_service->get_album( $owner_id, $album_id ), 'Cache should be empty after delete.' );

assert_true( $cache_service->purge_all(), 'Purge all cache failed.' );

echo "CacheService tests completed.\n";
