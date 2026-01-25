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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_post_vkp_clear_cache', array( $this, 'handle_clear_cache' ) );
		add_action( 'admin_post_vkp_clear_album_cache', array( $this, 'handle_clear_album_cache' ) );

		// Register AJAX handlers for accounts.
		$this->register_ajax_handlers();
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

		add_submenu_page(
			'vk-photos.php',
			__( 'Accounts', 'vkp' ),
			__( 'Accounts', 'vkp' ),
			'manage_options',
			'vk-accounts',
			array( $this, 'vk_accounts' )
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

	/**
	 * Render accounts page.
	 *
	 * @return void
	 */
	public function vk_accounts(): void {
		// Get dependencies from container.
		$settings_service = Container::make( SettingsService::class );
		$vk_api           = Container::make( VkApiClientInterface::class );

		// Create and render accounts controller.
		$accounts_controller = new \VkPhotos\Controllers\Admin\AccountsController( $settings_service, $vk_api );
		$accounts_controller->handle_page();
	}

	/**
	 * Register AJAX handlers for accounts.
	 *
	 * @return void
	 */
	private function register_ajax_handlers(): void {
		$accounts_controller = new \VkPhotos\Controllers\Admin\AccountsController(
			Container::make( SettingsService::class ),
			Container::make( VkApiClientInterface::class )
		);

		$accounts_controller->register_ajax_handlers();
	}

	/**
	 * Enqueue admin assets for accounts page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only load on our accounts page.
		if ( 'toplevel_page_vk-photos' !== $hook && 'vk-photos_page_vk-accounts' !== $hook ) {
			return;
		}

		// Enqueue accounts admin styles.
		wp_enqueue_style(
			'vkp-accounts-admin',
			plugins_url( 'vk-photos/dist/accounts-admin.css' ),
			array(),
			'2.1.0'
		);

		// Enqueue accounts admin script.
		wp_enqueue_script(
			'vkp-accounts-admin',
			plugins_url( 'vk-photos/dist/accounts-admin.js' ),
			array( 'jquery' ),
			'2.1.0',
			true
		);

		// Localize script with data.
		$settings_service = Container::make( SettingsService::class );
		$settings         = $settings_service->get_settings();

		wp_localize_script(
			'vkp-accounts-admin',
			'vkpAccountsData',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'vkp_accounts_ajax' ),
				'accounts' => $settings->get_accounts_v2(),
				'strings'  => array(
					'loading'          => __( 'Loading...', 'vkp' ),
					'validationFailed' => __( 'Validation failed', 'vkp' ),
					'ajaxFailed'       => __( 'AJAX request failed', 'vkp' ),
					'confirmRemove'    => __( 'Are you sure you want to remove this account?', 'vkp' ),
					'invalidId'        => __( 'Account ID must be a number', 'vkp' ),
					'enterId'          => __( 'Please enter account ID', 'vkp' ),
					'accountAdded'     => __( 'Account added', 'vkp' ),
					'saving'           => __( 'Saving...', 'vkp' ),
				),
			)
		);
	}
}
