<?php
/**
 * Cache repository (filesystem).
 */

namespace VkPhotos\Repositories;

/**
 * Class CacheRepository.
 * Handles filesystem read/write for cached album data.
 */
class CacheRepository {

	/**
	 * Base cache directory.
	 *
	 * @var string
	 */
	private string $base_dir;

	/**
	 * Cache payload file name.
	 *
	 * @var string
	 */
	private const CACHE_FILE = 'cache.json';

	/**
	 * Constructor.
	 *
	 * @param string|null $base_dir Base cache directory.
	 * @return void
	 */
	public function __construct( ?string $base_dir = null ) {
		$this->base_dir = $base_dir ?? $this->resolve_base_dir();
	}

	/**
	 * Read cached payload for album.
	 *
	 * @param int $owner_id Owner ID (can be negative for groups).
	 * @param int $album_id Album ID.
	 * @return array<string, mixed>|null Cached payload or null if missing.
	 */
	public function read( int $owner_id, int $album_id ): ?array {
		$file = $this->get_cache_file_path( $owner_id, $album_id );
		if ( ! file_exists( $file ) ) {
			return null;
		}

		$raw = @file_get_contents( $file );
		if ( false === $raw ) {
			return null;
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			return null;
		}

		return $data;
	}

	/**
	 * Write cached payload for album.
	 *
	 * @param int   $owner_id Owner ID.
	 * @param int   $album_id Album ID.
	 * @param array $payload  Payload to store.
	 * @return bool True on success.
	 */
	public function write( int $owner_id, int $album_id, array $payload ): bool {
		$dir = $this->get_album_dir( $owner_id, $album_id );

		if ( ! $this->ensure_dir( $dir ) ) {
			return false;
		}

		$file = $dir . '/' . self::CACHE_FILE;

		$result = @file_put_contents(
			$file,
			wp_json_encode( $payload ),
			LOCK_EX
		);

		return false !== $result;
	}

	/**
	 * Delete cached album.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $album_id Album ID.
	 * @return bool True when deleted or not present.
	 */
	public function delete( int $owner_id, int $album_id ): bool {
		$dir = $this->get_album_dir( $owner_id, $album_id );
		if ( ! file_exists( $dir ) ) {
			return true;
		}

		return $this->remove_dir( $dir );
	}

	/**
	 * Purge entire cache.
	 *
	 * @return bool True on success.
	 */
	public function purge_all(): bool {
		if ( ! file_exists( $this->base_dir ) ) {
			return true;
		}

		return $this->remove_dir( $this->base_dir );
	}

	/**
	 * Calculate size in bytes.
	 *
	 * @param int|null $owner_id Owner ID filter.
	 * @param int|null $album_id Album ID filter.
	 * @return int|null Size in bytes or null if unavailable.
	 */
	public function size( ?int $owner_id = null, ?int $album_id = null ): ?int {
		$dir = $this->base_dir;

		if ( null !== $owner_id ) {
			$dir = $this->get_owner_dir( $owner_id );
		}

		if ( null !== $album_id ) {
			$dir = $this->get_album_dir( $owner_id ?? 0, $album_id );
		}

		return $this->dir_size( $dir );
	}

	/**
	 * Resolve base cache directory path.
	 *
	 * @return string
	 */
	private function resolve_base_dir(): string {
		$upload_dir = wp_upload_dir();

		$base_dir = $upload_dir['basedir'] ?? '';
		$base_dir = rtrim( (string) $base_dir, '/\\' );

		return $base_dir . '/vk-photos-cache';
	}

	/**
	 * Get cache file path.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $album_id Album ID.
	 * @return string
	 */
	private function get_cache_file_path( int $owner_id, int $album_id ): string {
		return $this->get_album_dir( $owner_id, $album_id ) . '/' . self::CACHE_FILE;
	}

	/**
	 * Get album directory path.
	 *
	 * @param int $owner_id Owner ID.
	 * @param int $album_id Album ID.
	 * @return string
	 */
	private function get_album_dir( int $owner_id, int $album_id ): string {
		return $this->get_owner_dir( $owner_id ) . '/' . $album_id;
	}

	/**
	 * Get owner directory path.
	 *
	 * @param int $owner_id Owner ID.
	 * @return string
	 */
	private function get_owner_dir( int $owner_id ): string {
		$owner_dir = (string) $owner_id;
		return rtrim( $this->base_dir, '/\\' ) . '/' . $owner_dir;
	}

	/**
	 * Ensure directory exists.
	 *
	 * @param string $dir Directory path.
	 * @return bool
	 */
	private function ensure_dir( string $dir ): bool {
		if ( file_exists( $dir ) ) {
			return is_dir( $dir ) && is_writable( $dir );
		}

		return wp_mkdir_p( $dir );
	}

	/**
	 * Recursively remove directory.
	 *
	 * @param string $dir Directory path.
	 * @return bool
	 */
	private function remove_dir( string $dir ): bool {
		if ( ! file_exists( $dir ) ) {
			return true;
		}

		if ( is_file( $dir ) ) {
			return @unlink( $dir );
		}

		$items = scandir( $dir );
		if ( false === $items ) {
			return false;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->remove_dir( $path );
			} else {
				@unlink( $path );
			}
		}

		return @rmdir( $dir );
	}

	/**
	 * Directory size in bytes.
	 *
	 * @param string $dir Directory path.
	 * @return int|null
	 */
	private function dir_size( string $dir ): ?int {
		if ( ! file_exists( $dir ) ) {
			return null;
		}

		if ( is_file( $dir ) ) {
			$size = @filesize( $dir );
			return false === $size ? null : (int) $size;
		}

		$total  = 0;
		$handle = @opendir( $dir );
		if ( ! $handle ) {
			return null;
		}

		while ( false !== ( $entry = readdir( $handle ) ) ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$path = $dir . '/' . $entry;

			if ( is_dir( $path ) ) {
				$child_size = $this->dir_size( $path );
				if ( null !== $child_size ) {
					$total += $child_size;
				}
			} elseif ( is_file( $path ) ) {
				$file_size = @filesize( $path );
				if ( false !== $file_size ) {
					$total += $file_size;
				}
			}
		}

		closedir( $handle );

		return $total;
	}
}
