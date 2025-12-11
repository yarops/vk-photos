<?php
/**
 * Helper functions for vk-photos plugin.
 *
 * @package VkPhotos
 */

/**
 * Get photo size based on requested size.
 *
 * @param string $size Requested size.
 * @param array  $value Photo data array.
 * @return string|false Photo size key or false if not found.
 */
function get_photo_size( $size, $value ) {
	$array_picture_size_desc = array(
		'5' => 'photo_2560',
		'4' => 'photo_1280',
		'3' => 'photo_807',
		'2' => 'photo_604',
		'1' => 'photo_130',
		'0' => 'photo_75',
	);

	$key_photo = array_search( $size, $array_picture_size_desc );
	if ( false === $key_photo ) {
		return false;
	}

	foreach ( $array_picture_size_desc as $mkey => $mvalue ) {
		if ( $mkey <= $key_photo ) {
			if ( isset( $value[ $mvalue ] ) ) {
				return $mvalue;
			}
		}
	}

	return false;
}

/**
 * Translate old API size names to new format.
 *
 * @param string $oldsize Old size name.
 * @return string New size name.
 */
function tr_picture_size( $oldsize ) {
	$ar_sizes = array(
		'src_small'  => 'photo_75',
		'src'        => 'photo_130',
		'src_big'    => 'photo_604',
		'src_xbig'   => 'photo_807',
		'src_xxbig'  => 'photo_1280',
		'src_xxxbig' => 'photo_2560',
		'photo_75'   => 'photo_75',
		'photo_130'  => 'photo_130',
		'photo_604'  => 'photo_604',
		'photo_807'  => 'photo_807',
		'photo_1280' => 'photo_1280',
		'photo_2560' => 'photo_2560',
	);

	return $ar_sizes[ $oldsize ] ?? $oldsize;
}

/**
 * Alias for tr_picture_size for backward compatibility.
 *
 * @param string $oldsize Old size name.
 * @return string New size name.
 */
function trPictureSize( $oldsize ) {
	return tr_picture_size( $oldsize );
}

/**
 * Normalize photos response from new VK API format to old format.
 *
 * @param array $photos Photos response from VK API.
 * @return array Normalized photos array.
 */
function vkp_normalize_photos_response( $photos ) {
	if ( ! is_array( $photos ) || ! isset( $photos['response'] ) || ! is_array( $photos['response'] ) || ! isset( $photos['response']['items'] ) ) {
		return array();
	}

	$items = $photos['response']['items'];
	if ( ! is_array( $items ) ) {
		$items = (array) $items;
	}
	$items = array_values( $items );

	foreach ( $items as $key => $photo ) {
		if ( isset( $photo['sizes'] ) && is_array( $photo['sizes'] ) ) {
			$size_map = array(
				's'    => 'photo_75',
				'm'    => 'photo_130',
				'x'    => 'photo_604',
				'y'    => 'photo_807',
				'z'    => 'photo_1280',
				'w'    => 'photo_2560',
				'o'    => 'photo_75',
				'p'    => 'photo_130',
				'q'    => 'photo_604',
				'r'    => 'photo_807',
				'base' => 'photo_2560',
			);

			foreach ( $photo['sizes'] as $size_item ) {
				if ( isset( $size_item['type'] ) && isset( $size_item['url'] ) ) {
					$old_key = isset( $size_map[ $size_item['type'] ] ) ? $size_map[ $size_item['type'] ] : null;
					if ( $old_key && ! isset( $items[ $key ][ $old_key ] ) ) {
						$items[ $key ][ $old_key ] = $size_item['url'];
					}
				}
			}

			if ( isset( $photo['orig_photo']['url'] ) ) {
				$items[ $key ]['photo_2560'] = $photo['orig_photo']['url'];
			}
		}
	}

	return $items;
}

/**
 * Add trigger query var.
 *
 * @param array $vars Query vars array.
 * @return array Modified query vars array.
 */
function vkp_add_trigger( $vars ) {
	$vars[] = 'vkp';
	return $vars;
}

/**
 * Handle next page request.
 */
function vkp_next_page() {
	if ( isset( $_POST['vkp'] ) && 'next-page' === $_POST['vkp'] ) {
		require_once VKP__PLUGIN_DIR . 'inc/next-page.php';
		exit();
	}
}

/**
 * Delete cache for specific album.
 *
 * @param int $owner Album owner ID.
 * @param int $id    Album ID.
 */
function vkp_delete_cache( $owner, $id ) {
	$upload_dir    = wp_upload_dir();
	$dir_for_cache = $upload_dir['basedir'] . '/vk-photos-cache/';

	if ( file_exists( $dir_for_cache . $owner . '/' . $id ) ) {
		if ( isset( $id ) ) {
			@unlink( $dir_for_cache . $owner . '/album_' . $id . '.cache' );
		}
		@vkp_remove_dir( $dir_for_cache . $owner . '/' . $id );
	}

	wp_redirect( $_SERVER['SCRIPT_NAME'] . '?page=vk-cache' );
}

/**
 * Remove directory recursively.
 *
 * @param string $path Directory path.
 */
function vkp_remove_dir( $path ) {
	if ( is_file( $path ) ) {
		@unlink( $path );
	} else {
		array_map( 'vkp_remove_dir', glob( $path . '/*' ) );
		@rmdir( $path );
	}
}

/**
 * Register scripts and styles.
 */
function vkp_scripts_register() {
	wp_enqueue_script( 'jquery' );

	wp_register_script( 'vkp_colorbox', VKP__PLUGIN_URL . 'js/jquery.colorbox-min.js' );
	wp_register_style( 'vkp_colorbox', VKP__PLUGIN_URL . 'css/colorbox.css' );

	wp_register_script( 'vkp_swipebox', VKP__PLUGIN_URL . 'js/jquery.swipebox.min.js' );
	wp_register_style( 'vkp_swipebox', VKP__PLUGIN_URL . 'css/swipebox.css' );
}
