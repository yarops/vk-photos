<?php
/**
 * Photo model.
 */

namespace VkPhotos\Models;

/**
 * Class Photo.
 * Represents a photo entity.
 */
class Photo {
	/**
	 * Photo ID.
	 * Can be 'aid' (old format) or 'id' (new format).
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Owner ID (user or group).
	 *
	 * @var int
	 */
	public int $owner_id;

	/**
	 * Photo album ID.
	 *
	 * @var int
	 */
	public int $album_id;

	/**
	 * Photo description.
	 *
	 * @var string
	 */
	public string $text;

	/**
	 * Upload date.
	 *
	 * @var int
	 */
	public int $date;

	/**
	 * Original photo URL.
	 *
	 * @var string
	 */
	public string $orig_photo_url;

	/**
	 * Original photo width.
	 *
	 * @var int
	 */
	public int $orig_photo_width;

	/**
	 * Original photo height.
	 *
	 * @var int
	 */
	public int $orig_photo_height;

	/**
	 * Original photo type.
	 *
	 * @var string
	 */
	public string $orig_photo_type;

	/**
	 * Constructor.
	 *
	 * @param array $data Photo data.
	 *
	 * @return void
	 */
	public function __construct( array $data = array() ) {
		// Handle both 'aid' (old format) and 'id' (new format).
		$this->id       = isset( $data['aid'] ) ? (int) $data['aid'] : ( isset( $data['id'] ) ? (int) $data['id'] : 0 );
		$this->owner_id = isset( $data['owner_id'] ) ? (int) $data['owner_id'] : 0;
		$this->album_id = isset( $data['album_id'] ) ? (int) $data['album_id'] : 0;
		$this->text     = isset( $data['text'] ) ? $data['text'] : '';
		$this->date     = isset( $data['date'] ) ? (int) $data['date'] : 0;

		// Handle orig_photo data.
		if ( isset( $data['orig_photo'] ) && is_array( $data['orig_photo'] ) ) {
			$this->orig_photo_url    = $data['orig_photo']['url'] ?? '';
			$this->orig_photo_width  = isset( $data['orig_photo']['width'] ) ? (int) $data['orig_photo']['width'] : 0;
			$this->orig_photo_height = isset( $data['orig_photo']['height'] ) ? (int) $data['orig_photo']['height'] : 0;
			$this->orig_photo_type   = $data['orig_photo']['type'] ?? '';
		} else {
			$this->orig_photo_url    = '';
			$this->orig_photo_width  = 0;
			$this->orig_photo_height = 0;
			$this->orig_photo_type   = '';
		}
	}

	/**
	 * Get formatted creation date.
	 *
	 * @param string $format Date format (default: 'd.m.Y').
	 * @return string Formatted date.
	 */
	public function get_upload_date( string $format = 'd.m.Y' ): string {
		return $this->date > 0 ? gmdate( $format, $this->date ) : '';
	}

	/**
	 * Get VK photo URL.
	 *
	 * @param bool $is_group Whether owner is a group.
	 *
	 * @return string Photo URL.
	 */
	public function get_vk_url( bool $is_group = false ): string {
		$prefix = $is_group ? '-' : '';
		return "http://vk.com/photo{$prefix}{$this->owner_id}_{$this->id}";
	}

	/**
	 * Convert photo to array.
	 *
	 * @return array Photo data as array.
	 */
	public function to_array(): array {
		return array(
			'id'         => $this->id,
			'owner_id'   => $this->owner_id,
			'album_id'   => $this->album_id,
			'text'       => $this->text,
			'date'       => $this->date,
			'orig_photo' => array(
				'url'    => $this->orig_photo_url,
				'width'  => $this->orig_photo_width,
				'height' => $this->orig_photo_height,
				'type'   => $this->orig_photo_type,
			),
		);
	}
}
