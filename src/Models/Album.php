<?php
/**
 * Album model.
 */

namespace VkPhotos\Models;

/**
 * Class Album.
 * Represents an album entity.
 */
class Album {
	/**
	 * Album ID.
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
	 * Album title.
	 *
	 * @var string
	 */
	public string $title;

	/**
	 * Album description.
	 *
	 * @var string
	 */
	public string $description;

	/**
	 * Creation timestamp.
	 *
	 * @var int
	 */
	public int $created_at;

	/**
	 * Last update timestamp.
	 *
	 * @var int
	 */
	public int $updated_at;

	/**
	 * Number of photos in album.
	 *
	 * @var int
	 */
	public int $size;

	/**
	 * Constructor.
	 *
	 * @param array $data Album data.
	 *
	 * @return void
	 */
	public function __construct( array $data = array() ) {
		$this->id          = isset( $data['aid'] ) ? (int) $data['aid'] : 0;
		$this->title       = isset( $data['title'] ) ? $data['title'] : '';
		$this->description = isset( $data['description'] ) ? $data['description'] : '';
		$this->owner_id    = isset( $data['owner_id'] ) ? (int) $data['owner_id'] : 0;
		$this->created_at  = isset( $data['created_at'] ) ? (int) $data['created_at'] : 0;
		$this->updated_at  = isset( $data['updated_at'] ) ? (int) $data['updated_at'] : 0;
		$this->size        = isset( $data['size'] ) ? (int) $data['size'] : 0;
	}

	/**
	 * Get formatted creation date.
	 *
	 * @param string $format Date format (default: 'd.m.Y').
	 * @return string Formatted date.
	 */
	public function get_created_date( string $format = 'd.m.Y' ): string {
		return $this->created_at > 0 ? gmdate( $format, $this->created_at ) : '';
	}

	/**
	 * Get formatted update date.
	 *
	 * @param string $format Date format (default: 'd.m.Y').
	 * @return string Formatted date.
	 */
	public function get_updated_date( string $format = 'd.m.Y' ): string {
		return $this->updated_at > 0 ? gmdate( $format, $this->updated_at ) : '';
	}

	/**
	 * Get shortcode for album.
	 *
	 * @return string Shortcode for album.
	 */
	public function get_shortcode(): string {
		return sprintf( '[vkalbum owner="%d" id="%d"]', $this->owner_id, $this->id );
	}

	/**
	 * Get VK album URL.
	 *
	 * @param bool $is_group Whether owner is a group.
	 *
	 * @return string Album URL.
	 */
	public function get_vk_url( bool $is_group = false ): string {
		$prefix = $is_group ? '-' : '';
		return "http://vk.com/album{$prefix}{$this->owner_id}_{$this->id}";
	}

	/**
	 * Convert album to array.
	 *
	 * @return array Album data as array.
	 */
	public function to_array(): array {
		return array(
			'id'          => $this->id,
			'owner_id'    => $this->owner_id,
			'title'       => $this->title,
			'description' => $this->description,
			'created_at'  => $this->created_at,
			'updated_at'  => $this->updated_at,
			'size'        => $this->size,
		);
	}
}
