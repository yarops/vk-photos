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
use VkPhotos\Models\Settings as SettingsModel;

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

		// Load main class.
		$main_class_path = Config::get( 'paths.inc' ) . 'class.main.php';
		if ( file_exists( $main_class_path ) ) {
			require_once $main_class_path;
		}

		// Register hooks.
		$this->register_hooks();

		// Initialize main plugin class.
		if ( class_exists( 'VkPhotos' ) ) {
			// Try to resolve from container, fallback to direct instantiation.
			if ( Container::bound( 'VkPhotos' ) ) {
				Container::make( 'VkPhotos' );
			} else {
				new \VkPhotos();
			}
		}
	}

	/**
	 * Initialize service container and bind services.
	 */
	private function init_container(): void {
		// Bind main plugin class as singleton.
		Container::singleton(
			'VkPhotos',
			function (): \VkPhotos {
				return new \VkPhotos();
			}
		);

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

		// Register query vars and template redirect.
		add_filter( 'query_vars', 'vkp_add_trigger' );
		add_action( 'template_redirect', 'vkp_next_page' );

		// Register scripts and styles.
		add_action( 'wp_enqueue_scripts', 'vkp_scripts_register' );
	}
}
