<?php
/**
 * Interface AlbumsViewData.
 */

namespace VkPhotos\Interfaces;

interface AlbumsViewData {

	/**
	 * Get prepared view data for albums template.
	 *
	 * Expected shape:
	 * - pageTitle: string.
	 * - accounts: array<int, array{
	 *     id: int,
	 *     type: 'user'|'group',
	 *     link: string,
	 *     displayName: string,
	 *     error: string|null,
	 *     warnings: array<int, string>,
	 *     albums: array<int, array{
	 *       id: int,
	 *       ownerId: int,
	 *       ownerLink: string,
	 *       title: string,
	 *       description: string,
	 *       createdAt: string,
	 *       updatedAt: string,
	 *       size: int,
	 *       cacheSizeMb: float|null,
	 *       shortcode: string
	 *     }>
	 *   }>.
	 *
	 * @return array<string, mixed> View data.
	 */
	public function get_view_data(): array;
}
