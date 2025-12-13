<?php
/**
 * Admin hooks.
 */

namespace VkPhotos\Hooks;

use VkPhotos\Container;
use VkPhotos\Views\Admin\SettingsView;
use VkPhotos\Services\SettingsService;
use VkPhotos\Api\VkApiClientInterface;

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
}
