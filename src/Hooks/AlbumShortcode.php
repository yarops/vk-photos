<?php
/**
 * Album shortcode hook.
 */

namespace VkPhotos\Hooks;

use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Config;
use VkPhotos\Container;
use VkPhotos\Models\Photo as PhotoModel;
use VkPhotos\Repositories\AlbumRepository;
use VkPhotos\Services\SettingsService;
use VkPhotos\Services\PhotoService;
use VkPhotos\Services\TemplateService;

/**
 * Registers and handles the album shortcode.
 */
class AlbumShortcode {

	/**
	 * Settings service.
	 *
	 * @var SettingsService
	 */
	private SettingsService $settings_service;

	/**
	 * VK API client.
	 *
	 * @var VkApiClientInterface
	 */
	private VkApiClientInterface $api_client;

	/**
	 * Album repository.
	 *
	 * @var AlbumRepository
	 */
	private AlbumRepository $album_repository;

	/**
	 * Photo service.
	 *
	 * @var PhotoService
	 */
	private PhotoService $photo_service;

	/**
	 * Template service.
	 *
	 * @var TemplateService
	 */
	private TemplateService $template_service;

	/**
	 * Allowed size keys (legacy names).
	 *
	 * @var array<int, string>
	 */
	private array $available_sizes = array(
		'photo_75',
		'photo_130',
		'photo_604',
		'photo_807',
		'photo_1280',
		'photo_2560',
	);

	/**
	 * Register shortcode on construction.
	 */
	public function __construct(
		?SettingsService $settings_service = null,
		?VkApiClientInterface $api_client = null,
		?AlbumRepository $album_repository = null,
		?PhotoService $photo_service = null,
		?TemplateService $template_service = null
	) {
		$this->settings_service  = $settings_service ?? Container::make( SettingsService::class );
		$this->api_client        = $api_client ?? Container::make( VkApiClientInterface::class );
		$this->album_repository  = $album_repository ?? Container::make( AlbumRepository::class );
		$this->photo_service     = $photo_service ?? Container::make( PhotoService::class );
		$this->template_service  = $template_service ?? Container::make( TemplateService::class );

		add_shortcode( 'vkalbum', array( $this, 'render' ) );
	}

	/**
	 * Render album shortcode output.
	 *
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = is_array( $atts ) ? $atts : array();

		$settings = $this->settings_service->get_settings();

		$defaults = array(
			'id'               => 'no',
			'owner'            => 'no',
			'template'         => $settings->template ?? 'light',
			'viewer'           => $settings->viewer ?? 'fancybox',
			'sign'             => $settings->show_signatures ?? 'no',
			'count'            => $settings->count_photos ?? 12,
			'preview'          => $settings->preview_size ?? 'photo_130',
			'photo'            => $settings->photo_view_size ?? 'photo_807',
			'show_title'       => $settings->show_title ?? 'no',
			'show_description' => $settings->show_description ?? 'no',
		);

		$atts = shortcode_atts(
			$defaults,
			$atts
		);

		$this->set_api_token( $settings->access_token ?? '' );

		return (string) $this->render_album( $atts );
	}

	/**
	 * Render album content.
	 *
	 * @param array<string, mixed> $atts Attributes with defaults applied.
	 * @return string
	 */
	private function render_album( array $atts ): string {

		$owner    = (int) $atts['owner'];
		$album_id = (int) $atts['id'];

		$preview = $this->normalize_size( $atts['preview'], $atts['preview'] );
		$photo   = $this->normalize_size( $atts['photo'], $atts['photo'] );

		$output          = '';
		$template_viewer = '';

		// Configure viewer based on settings.
		if ( 'fancybox' === $atts['viewer'] ) {
			// Enqueue Fancybox scripts and styles.
			wp_enqueue_script(
				'fancybox',
				'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js',
				array( 'jquery' ),
				'5.0',
				true
			);
			wp_enqueue_style(
				'fancybox',
				'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css',
				array(),
				'5.0'
			);
			$template_viewer = ' data-fancybox="gallery-' . esc_attr( $album_id ) . '"';
		} elseif ( 'new tab' === $atts['viewer'] ) {
			$template_viewer = ' target="_blank"';
		}

		$album = $this->album_repository->get_album( $owner, $album_id );
		if ( ! $album ) {
			return '<div class="vkp-error">' . esc_html__( 'Album not found.', 'vkp' ) . '</div>';
		}

		$photos = $this->photo_service->get_photos( $album_id, $owner );
		if ( empty( $photos ) ) {
			return '<div class="vkp-error">' . esc_html__( 'Photos not available.', 'vkp' ) . '</div>';
		}

		if ( $atts['show_title'] === 'yes' ) {
			$output .= '<h2>' . esc_html( $album->title ) . '</h2>';
		}
		if ( $atts['show_description'] === 'yes' && ! empty( $album->description ) ) {
			$output .= wp_kses_post( $album->description ) . '<br>';
		}

		$plugin_url = Config::get( 'plugin.url', '' );

		// Render templates using TemplateService
		$template_style = $this->template_service->render_style( $atts['template'], [
			'ID'                => $album_id,
			'DIRECTORY_PLUGIN'  => $plugin_url,
		] );

		$template_head = $this->template_service->render_header( $atts['template'], [
			'ID' => $album_id,
		] );

		$template_item = $this->template_service->render_item( $atts['template'], [
			'VIEWER' => $template_viewer,
			'ID'     => $album_id,
		] );

		$template_foot = $this->template_service->render_footer( $atts['template'], [
			'ID' => $album_id,
		] );

		$output .= $template_style;
		$output .= $template_head;

		$limited_photos = array_slice( $photos, 0, (int) $atts['count'] );

		foreach ( $limited_photos as $photo_model ) {
			if ( ! $photo_model instanceof PhotoModel ) {
				continue;
			}

			$preview_url = $this->get_photo_url( $photo_model, $preview );
			$photo_url   = $this->get_photo_url( $photo_model, $photo );

			if ( empty( $preview_url ) || empty( $photo_url ) ) {
				continue;
			}

			$item = str_replace( '[[PHOTO]]', esc_url( $photo_url ), $template_item );

			if ( $atts['sign'] === 'yes' ) {
				$item = str_replace( '[[SIGNATURES]]', esc_html( $photo_model->text ), $item );
				if ( 'fancybox' === $atts['viewer'] ) {
					$item = str_replace( '[[VIEWERSIGN]]', " data-caption='" . esc_attr( $photo_model->text ) . "'", $item );
				} else {
					$item = str_replace( '[[VIEWERSIGN]]', '', $item );
				}
			}

			$item    = str_replace( '[[PREVIEW]]', esc_url( $preview_url ), $item );
			$output .= $item;
		}

		$output  = str_replace( '[[VIEWER]]', '', str_replace( '[[SIGNATURES]]', '', str_replace( '[[VIEWERSIGN]]', '', $output ) ) );
		$output .= $template_foot;

		// Initialize Fancybox if viewer is fancybox.
		if ( 'fancybox' === $atts['viewer'] ) {
			$output .= '<script>
				jQuery(document).ready(function($) {
					Fancybox.bind("[data-fancybox=\'gallery-' . esc_js( $album_id ) . '\']", {
						Toolbar: {
							display: {
								left: ["infobar"],
								middle: [],
								right: ["slideshow", "download", "thumbs", "close"]
							}
						}
					});
				});
			</script>';
		}

		return $output;
	}

	/**
	 * Normalize size with fallback to defaults and allowed list.
	 *
	 * @param string $requested Requested size.
	 * @param string $fallback  Fallback size.
	 * @return string
	 */
	private function normalize_size( string $requested, string $fallback ): string {
		$normalized = $requested;
		if ( in_array( $normalized, $this->available_sizes, true ) ) {
			return $normalized;
		}

		return in_array( $fallback, $this->available_sizes, true ) ? $fallback : 'photo_130';
	}

	/**
	 * Get photo URL by size key.
	 *
	 * @param PhotoModel $photo_model Photo model.
	 * @param string     $size_key    Size key.
	 * @return string
	 */
	private function get_photo_url( PhotoModel $photo_model, string $size_key ): string {
		return $photo_model->get_size_url( $size_key );
	}

	/**
	 * Set VK API token if supported by client.
	 *
	 * @param string $token Access token.
	 * @return void
	 */
	private function set_api_token( string $token ): void {
		if ( empty( $token ) ) {
			return;
		}

		if ( property_exists( $this->api_client, 'access_token' ) ) {
			$this->api_client->access_token = $token;
		}
	}
}
