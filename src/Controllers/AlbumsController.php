<?php
/**
 * Albums controller.
 */

namespace VkPhotos\Controllers;

use VkPhotos\Services\AlbumsService;
use VkPhotos\Interfaces\AlbumsViewData;

/**
 * Class AlbumsController.
 * Handles albums data preparation and view rendering.
 */
class AlbumsController {

	/**
	 * Constructor.
	 *
	 * @param AlbumsViewData $provider Albums service.
	 * @return void
	 */
	public function __construct( private AlbumsViewData $provider ) {}

	/**
	 * Get prepared view data for albums page.
	 *
	 * @return array<string, mixed> View data.
	 */
	public function get_view_data(): array {
		return $this->provider->get_view_data();
	}
}
