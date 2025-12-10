<?php
/**
 * Bootstrap class for vk-photos plugin.
 * Handles plugin initialization and setup.
 *
 * @package VkPhotos
 */

namespace VkPhotos;

use VkPhotos\Container;

/**
 * Class Bootstrap.
 * Main plugin bootstrap class.
 */
class Bootstrap {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.5';

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
		// Initialize service container.
		$this->init_container();

		// Load text domain.
		load_plugin_textdomain(
			'vkp',
			false,
			dirname( plugin_basename( VKP__PLUGIN_FILE ) ) . '/languages/'
		);

		// Load main class.
		if ( file_exists( VKP__PLUGIN_DIR . 'inc/class.main.php' ) ) {
			require_once VKP__PLUGIN_DIR . 'inc/class.main.php';
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
		Container::singleton( 'VkPhotos', function () {
			return new \VkPhotos();
		} );

		// Bind plugin constants.
		Container::singleton( 'plugin.url', fn() => VKP__PLUGIN_URL );
		Container::singleton( 'plugin.dir', fn() => VKP__PLUGIN_DIR );
		Container::singleton( 'plugin.file', fn() => VKP__PLUGIN_FILE );
	}

	/**
	 * Register WordPress hooks.
	 */
	private function register_hooks(): void {
		// Register settings.
		add_action( 'admin_init', 'VKPPhotosRegisterSettings' );

		// Register query vars and template redirect.
		add_filter( 'query_vars', 'vkp_add_trigger' );
		add_action( 'template_redirect', 'vkp_next_page' );

		// Register scripts and styles.
		add_action( 'wp_enqueue_scripts', 'vkp_scripts_register' );
	}
}
