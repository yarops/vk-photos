<?php
/**
 * Admin hooks.
 */

namespace VkPhotos\Hooks;

use VkPhotos\Container;
use VkPhotos\Views\Admin\SettingsView;
use VkPhotos\Services\SettingsService;
use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Controllers\Admin\CacheController;

/**
 * Class AdminHooks.
 * Handles admin hooks.
 */
class AdminHooks {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
		add_action( 'admin_post_vkp_clear_cache', array( $this, 'handle_clear_cache' ) );
		add_action( 'admin_post_vkp_clear_album_cache', array( $this, 'handle_clear_album_cache' ) );
	}

	/**
	 * Register admin pages.
	 *
	 * @return void
	 */
	public function register_admin_pages(): void {
		add_menu_page(
			__( 'VK photos', 'vkp' ),
			__( 'VK photos', 'vkp' ),
			'manage_options',
			'vk-photos.php',
			array( $this, 'vkphotos_options_page' ),
			plugins_url( 'vk-photos/images/icon.png' ),
			86
		);

		add_submenu_page(
			'vk-photos.php',
			__( 'Help', 'vkp' ),
			__( 'Help', 'vkp' ),
			'manage_options',
			'vk-help',
			array( $this, 'vk_help' )
		);

		add_submenu_page(
			'vk-photos.php',
			__( 'Templates', 'vkp' ),
			__( 'Templates', 'vkp' ),
			'manage_options',
			'vk-templates',
			array( $this, 'vk_templates' )
		);

		add_submenu_page(
			'vk-photos.php',
			__( 'Albums', 'vkp' ),
			__( 'Albums', 'vkp' ),
			'manage_options',
			'vk-albums',
			array( $this, 'vk_albums' )
		);
	}

	/**
	 * Render settings options page.
	 *
	 * @return void
	 */
	public function vkphotos_options_page(): void {
		// Get dependencies from container.
		$settings_service = Container::make( SettingsService::class );
		$vk_api           = Container::make( VkApiClientInterface::class );

		// Create and render settings view.
		$settings_view = new SettingsView( $settings_service, $vk_api );
		$settings_view->render();
	}

	/**
	 * Render help page.
	 *
	 * @return void
	 */
	public function vk_help() {
		// Use HelpView to render help page.
		$help_view = new \VkPhotos\Views\Admin\HelpView();
		$help_view->render();
	}

	/**
	 * Render templates page.
	 *
	 * @return void
	 */
	public function vk_templates() {
		// Use TemplatesView to render templates page.
		$templates_view = new \VkPhotos\Views\Admin\TemplatesView();
		$templates_view->render();
	}

	/**
	 * Render albums page.
	 *
	 * @return void
	 */
	public function vk_albums() {
		// Use AlbumsView to render albums page.
		$albums_view = new \VkPhotos\Views\Admin\AlbumsView();
		$albums_view->render();
	}

	/**
	 * Handle cache purge action.
	 *
	 * @return void
	 */
	public function handle_clear_cache(): void {
		$controller = Container::make( CacheController::class );
		$controller->clear_all();
	}

	/**
	 * Handle album cache purge action.
	 *
	 * @return void
	 */
	public function handle_clear_album_cache(): void {
		$controller = Container::make( CacheController::class );
		$controller->clear_album();
	}
}
