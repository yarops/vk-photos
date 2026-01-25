<?php
/**
 * Bootstrap class for vk-photos plugin.
 * Handles plugin initialization and setup.
 *
 * @package VkPhotos
 */

namespace VkPhotos;

use VkPhotos\Container;
use VkPhotos\Config;
use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Api\VkApiClientImpl;
use VkPhotos\Services\SettingsService;
use VkPhotos\Services\AlbumsService;
use VkPhotos\Services\PhotoService;
use VkPhotos\Services\CacheService;
use VkPhotos\Services\CacheServiceInterface;
use VkPhotos\Services\TemplateService;
use VkPhotos\Services\MigrationService;
use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Repositories\AlbumRepository;
use VkPhotos\Repositories\PhotoRepository;
use VkPhotos\Repositories\CacheRepository;
use VkPhotos\Interfaces\AlbumsViewData;
use VkPhotos\Hooks\AdminHooks;
use VkPhotos\Hooks\AlbumShortcode;

/**
 * Class Bootstrap.
 * Main plugin bootstrap class.
 */
class Bootstrap {

	/**
	 * Plugin instance.
	 *
	 * @var Bootstrap|null
	 */
	private static ?Bootstrap $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Bootstrap
	 */
	public static function get_instance(): Bootstrap {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin.
	 */
	private function init(): void {
		// Initialize configuration.
		Config::init();

		// Initialize service container.
		$this->init_container();

		// Load text domain.
		load_plugin_textdomain(
			Config::get( 'plugin.text_domain', 'vkp' ),
			false,
			Config::get( 'paths.languages' )
		);

		// Check and run migrations.
		$this->run_migrations();

		// Register hooks.
		$this->register_hooks();

		// Initialize admin hooks.
		if ( is_admin() ) {
			new AdminHooks();
		}
	}

	/**
	 * Initialize service container and bind services.
	 */
	private function init_container(): void {
		// Bind VK API client.
		Container::singleton(
			VkApiClientInterface::class,
			function (): VkApiClientImpl {
				return new VkApiClientImpl();
			}
		);

		// Bind settings model.
		Container::singleton(
			SettingsModel::class,
			function (): SettingsModel {
				return new SettingsModel();
			}
		);

		// Bind settings service.
		Container::singleton(
			SettingsService::class,
			function (): SettingsService {
				return new SettingsService( Container::make( SettingsModel::class ) );
			}
		);

		// Bind album repository.
		Container::singleton(
			AlbumRepository::class,
			function (): AlbumRepository {
				return new AlbumRepository( Container::make( VkApiClientInterface::class ) );
			}
		);

		// Bind photo repository.
		Container::singleton(
			PhotoRepository::class,
			function (): PhotoRepository {
				return new PhotoRepository( Container::make( VkApiClientInterface::class ) );
			}
		);

		// Bind cache repository.
		Container::singleton(
			CacheRepository::class,
			function (): CacheRepository {
				return new CacheRepository();
			}
		);

		// Bind cache service.
		Container::singleton(
			CacheServiceInterface::class,
			function (): CacheServiceInterface {
				return new CacheService(
					Container::make( CacheRepository::class ),
					Container::make( SettingsService::class )
				);
			}
		);

		// Bind photo service.
		Container::singleton(
			PhotoService::class,
			function (): PhotoService {
				return new PhotoService(
					Container::make( PhotoRepository::class ),
					Container::make( CacheServiceInterface::class )
				);
			}
		);

		// Bind albums service.
		Container::singleton(
			AlbumsService::class,
			function (): AlbumsService {
				return new AlbumsService(
					Container::make( SettingsService::class ),
					Container::make( VkApiClientInterface::class ),
					Container::make( AlbumRepository::class ),
					Container::make( CacheServiceInterface::class )
				);
			}
		);

		// Bind AlbumsViewData interface to AlbumsService implementation.
		Container::singleton(
			AlbumsViewData::class,
			function (): AlbumsViewData {
				return Container::make( AlbumsService::class );
			}
		);

		// Bind template service.
		Container::singleton(
			TemplateService::class,
			function (): TemplateService {
				return new TemplateService();
			}
		);
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		// Register settings.
		add_action(
			'admin_init',
			function (): void {
				$settings_service = Container::make( SettingsService::class );
				$settings_service->register_settings();
			}
		);

		// Register album shortcode.
		add_action(
			'init',
			function (): void {
				Container::make( AlbumShortcode::class );
			}
		);
	}

	/**
	 * Run plugin migrations.
	 *
	 * @return void
	 */
	private function run_migrations(): void {
		MigrationService::check_migrations();
	}
}
