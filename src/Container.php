<?php
/**
 * Service container for vk-photos plugin.
 * Wrapper around Illuminate Container.
 *
 * @package VkPhotos
 */

namespace VkPhotos;

use Illuminate\Container\Container as IlluminateContainer;

/**
 * Class Container.
 * Service container singleton.
 *
 * @example
 * // Получить сервис
 * $vkPhotos = Container::make('VkPhotos');
 *
 * // Привязать singleton
 * Container::singleton('MyService', function() {
 *     return new MyService();
 * });
 *
 * // Привязать обычный класс (новый экземпляр каждый раз)
 * Container::bind('MyService', MyService::class);
 *
 * // Проверить наличие привязки
 * if (Container::bound('MyService')) {
 *     $service = Container::make('MyService');
 * }
 */
class Container {

	/**
	 * Container instance.
	 *
	 * @var IlluminateContainer|null
	 */
	private static ?IlluminateContainer $instance = null;

	/**
	 * Get container instance.
	 *
	 * @return IlluminateContainer
	 */
	public static function get_instance(): IlluminateContainer {
		if ( null === self::$instance ) {
			self::$instance = new IlluminateContainer();
		}
		return self::$instance;
	}

	/**
	 * Bind service to container.
	 *
	 * @param string $abstract Abstract class or interface.
	 * @param mixed  $concrete Concrete implementation or closure.
	 * @param bool   $shared Whether to share the instance.
	 * @return void
	 */
	public static function bind( string $abstract, $concrete, bool $shared = false ): void {
		$container = self::get_instance();
		$container->bind( $abstract, $concrete, $shared );
	}

	/**
	 * Bind singleton to container.
	 *
	 * @param string $abstract Abstract class or interface.
	 * @param mixed  $concrete Concrete implementation or closure.
	 * @return void
	 */
	public static function singleton( string $abstract, $concrete ): void {
		$container = self::get_instance();
		$container->singleton( $abstract, $concrete );
	}

	/**
	 * Resolve service from container.
	 *
	 * @param string $abstract Abstract class or interface.
	 * @param array  $parameters Optional parameters.
	 * @return mixed
	 */
	public static function make( string $abstract, array $parameters = [] ) {
		$container = self::get_instance();
		return $container->make( $abstract, $parameters );
	}

	/**
	 * Check if service is bound.
	 *
	 * @param string $abstract Abstract class or interface.
	 * @return bool
	 */
	public static function bound( string $abstract ): bool {
		$container = self::get_instance();
		return $container->bound( $abstract );
	}

	/**
	 * Get container instance directly.
	 *
	 * @return IlluminateContainer
	 */
	public static function container(): IlluminateContainer {
		return self::get_instance();
	}
}
