<?php
/**
 * Settings model.
 */

namespace VkPhotos\Models;

/**
 * Class Settings.
 * Represents plugin settings entity.
 */
class Settings {

	/**
	 * Number of photos to display.
	 *
	 * @var int
	 */
	public int $count_photos;

	/**
	 * VK accounts (user/group IDs).
	 *
	 * @var array<int>
	 */
	public array $accounts;

	/**
	 * Account types ('user' or 'group').
	 *
	 * @var array<string>
	 */
	public array $accounts_type;

	/**
	 * Enable caching ('yes' or 'no').
	 *
	 * @var string
	 */
	public string $enable_caching;

	/**
	 * VK API access token.
	 *
	 * @var string
	 */
	public string $access_token;

	/**
	 * Cache lifetime in hours.
	 *
	 * @var int
	 */
	public int $lifetime_caching;

	/**
	 * Preview image size.
	 *
	 * @var string
	 */
	public string $preview_size;

	/**
	 * Photo view size.
	 *
	 * @var string
	 */
	public string $photo_view_size;

	/**
	 * Preview type ('keep' or 'square').
	 *
	 * @var string
	 */
	public string $preview_type;

	/**
	 * Show album title ('yes' or 'no').
	 *
	 * @var string
	 */
	public string $show_title;

	/**
	 * Show photo signatures ('yes' or 'no').
	 *
	 * @var string
	 */
	public string $show_signatures;

	/**
	 * Gallery template name.
	 *
	 * @var string
	 */
	public string $template;

	/**
	 * Viewer type ('none', 'colorbox', 'swipebox', etc.).
	 *
	 * @var string
	 */
	public string $viewer;

	/**
	 * Calculate cache size ('yes' or 'no').
	 *
	 * @var string
	 */
	public string $calculate_cache;

	/**
	 * Show album description ('yes' or 'no').
	 *
	 * @var string
	 */
	public string $show_description;

	/**
	 * "More" button title.
	 *
	 * @var string
	 */
	public string $more_title;

	/**
	 * Constructor.
	 *
	 * @param array $data Settings data.
	 *
	 * @return void
	 */
	public function __construct( array $data = array() ) {
		$this->count_photos     = isset( $data['count_photos'] ) ? (int) $data['count_photos'] : ( isset( $data['vkpCountPhotos'] ) ? (int) $data['vkpCountPhotos'] : 12 );
		$this->accounts         = isset( $data['accounts'] ) && is_array( $data['accounts'] ) ? $data['accounts'] : ( isset( $data['vkpAccaunts'] ) && is_array( $data['vkpAccaunts'] ) ? $data['vkpAccaunts'] : array() );
		$this->accounts_type    = isset( $data['accounts_type'] ) && is_array( $data['accounts_type'] ) ? $data['accounts_type'] : ( isset( $data['vkpAccaunts_type'] ) && is_array( $data['vkpAccaunts_type'] ) ? $data['vkpAccaunts_type'] : array() );
		$this->enable_caching   = isset( $data['enable_caching'] ) ? (string) $data['enable_caching'] : ( isset( $data['vkpEnableCaching'] ) ? ( $data['vkpEnableCaching'] === 'yes' ? 'yes' : 'no' ) : 'no' );
		$this->access_token     = isset( $data['access_token'] ) ? (string) $data['access_token'] : ( isset( $data['vkpAccessToken'] ) ? (string) $data['vkpAccessToken'] : '' );
		$this->lifetime_caching = isset( $data['lifetime_caching'] ) ? (int) $data['lifetime_caching'] : ( isset( $data['vkpLifeTimeCaching'] ) ? (int) $data['vkpLifeTimeCaching'] : 0 );
		$this->preview_size     = isset( $data['preview_size'] ) ? (string) $data['preview_size'] : ( isset( $data['vkpPreviewSize'] ) ? (string) $data['vkpPreviewSize'] : 'photo_130' );
		$this->photo_view_size  = isset( $data['photo_view_size'] ) ? (string) $data['photo_view_size'] : ( isset( $data['vkpPhotoViewSize'] ) ? (string) $data['vkpPhotoViewSize'] : 'photo_807' );
		$this->preview_type     = isset( $data['preview_type'] ) ? (string) $data['preview_type'] : ( isset( $data['vkpPreviewType'] ) ? (string) $data['vkpPreviewType'] : 'keep' );
		$this->show_title       = isset( $data['show_title'] ) ? (string) $data['show_title'] : ( isset( $data['vkpShowTitle'] ) ? ( $data['vkpShowTitle'] === 'yes' ? 'yes' : 'no' ) : 'no' );
		$this->show_signatures  = isset( $data['show_signatures'] ) ? (string) $data['show_signatures'] : ( isset( $data['vkpShowSignatures'] ) ? ( $data['vkpShowSignatures'] === 'yes' ? 'yes' : 'no' ) : 'no' );
		$this->template         = isset( $data['template'] ) ? (string) $data['template'] : ( isset( $data['vkpTemplate'] ) ? (string) $data['vkpTemplate'] : 'light' );
		$this->viewer           = isset( $data['viewer'] ) ? (string) $data['viewer'] : ( isset( $data['vkpViewer'] ) ? (string) $data['vkpViewer'] : 'none' );
		$this->calculate_cache  = isset( $data['calculate_cache'] ) ? (string) $data['calculate_cache'] : ( isset( $data['vkpCalculateCache'] ) ? ( $data['vkpCalculateCache'] === 'yes' ? 'yes' : 'no' ) : 'no' );
		$this->show_description = isset( $data['show_description'] ) ? (string) $data['show_description'] : ( isset( $data['vkpShowDescription'] ) ? ( $data['vkpShowDescription'] === 'yes' ? 'yes' : 'no' ) : 'no' );
		$this->more_title       = isset( $data['more_title'] ) ? (string) $data['more_title'] : ( isset( $data['vkpMoreTitle'] ) ? (string) $data['vkpMoreTitle'] : '[далее]' );
	}

	/**
	 * Convert settings to array.
	 *
	 * @return array Settings data as array.
	 */
	public function to_array(): array {
		return array(
			'count_photos'     => $this->count_photos,
			'accounts'         => $this->accounts,
			'accounts_type'    => $this->accounts_type,
			'enable_caching'   => $this->enable_caching,
			'access_token'     => $this->access_token,
			'lifetime_caching' => $this->lifetime_caching,
			'preview_size'     => $this->preview_size,
			'photo_view_size'  => $this->photo_view_size,
			'preview_type'     => $this->preview_type,
			'show_title'       => $this->show_title,
			'show_signatures'  => $this->show_signatures,
			'template'         => $this->template,
			'viewer'           => $this->viewer,
			'calculate_cache'  => $this->calculate_cache,
			'show_description' => $this->show_description,
			'more_title'       => $this->more_title,
		);
	}

	/**
	 * Convert settings to WordPress options format (legacy names).
	 *
	 * @return array Settings data with legacy option names.
	 */
	public function to_wp_options(): array {
		return array(
			'vkpCountPhotos'     => $this->count_photos,
			'vkpAccaunts'        => $this->accounts,
			'vkpAccaunts_type'   => $this->accounts_type,
			'vkpEnableCaching'   => $this->enable_caching,
			'vkpAccessToken'     => $this->access_token,
			'vkpLifeTimeCaching' => $this->lifetime_caching,
			'vkpPreviewSize'     => $this->preview_size,
			'vkpPhotoViewSize'   => $this->photo_view_size,
			'vkpPreviewType'     => $this->preview_type,
			'vkpShowTitle'       => $this->show_title,
			'vkpShowSignatures'  => $this->show_signatures,
			'vkpTemplate'        => $this->template,
			'vkpViewer'          => $this->viewer,
			'vkpCalculateCache'  => $this->calculate_cache,
			'vkpShowDescription' => $this->show_description,
			'vkpMoreTitle'       => $this->more_title,
		);
	}
}
