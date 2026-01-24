<?php
/**
 * Template service for managing frontend templates.
 *
 * Handles loading, caching, and rendering of HTML templates with placeholder replacement.
 * Provides a centralized way to work with template files for the VK Photos plugin.
 */

namespace VkPhotos\Services;

use VkPhotos\Config;

/**
 * Class TemplateService.
 * Manages template loading and rendering with caching capabilities.
 */
class TemplateService {

	/**
	 * Cache for loaded templates.
	 *
	 * @var array<string, string>
	 */
	private array $cache = array();

	/**
	 * Path to frontend templates directory.
	 *
	 * @var string
	 */
	private string $templates_path;

	/**
	 * Constructor.
	 * Initializes the template service with configuration.
	 */
	public function __construct() {
		$this->templates_path = Config::get( 'paths.templates.frontend', '' );
	}

	/**
	 * Render a template with provided data.
	 *
	 * Loads template file, replaces placeholders with data, and returns rendered content.
	 * Templates are cached after first load for better performance.
	 *
	 * @param string $template     Template directory name (e.g., 'light', 'blocks').
	 * @param string $template_name Template file name (e.g., 'header.html', 'item.html').
	 * @param array  $data         Associative array of placeholder => value pairs.
	 * @return string Rendered template content or empty string if template not found.
	 */
	public function render( string $template, string $template_name, array $data = array() ): string {
		$template_path = $this->get_template_path( $template, $template_name );

		if ( ! $this->template_exists( $template_path ) ) {
			return '';
		}

		$content = $this->load_template( $template_path );
		return $this->replace_placeholders( $content, $data );
	}

	/**
	 * Render template header.
	 *
	 * Convenience method for rendering header.html template.
	 *
	 * @param string $template Template directory name.
	 * @param array  $data     Placeholder data.
	 * @return string Rendered header template.
	 */
	public function render_header( string $template, array $data = array() ): string {
		return $this->render( $template, 'header.html', $data );
	}

	/**
	 * Render template item.
	 *
	 * Convenience method for rendering item.html template.
	 *
	 * @param string $template Template directory name.
	 * @param array  $data     Placeholder data.
	 * @return string Rendered item template.
	 */
	public function render_item( string $template, array $data = array() ): string {
		return $this->render( $template, 'item.html', $data );
	}

	/**
	 * Render template footer.
	 *
	 * Convenience method for rendering footer.html template.
	 *
	 * @param string $template Template directory name.
	 * @param array  $data     Placeholder data.
	 * @return string Rendered footer template.
	 */
	public function render_footer( string $template, array $data = array() ): string {
		return $this->render( $template, 'footer.html', $data );
	}

	/**
	 * Render template styles.
	 *
	 * Convenience method for rendering style.html template.
	 *
	 * @param string $template Template directory name.
	 * @param array  $data     Placeholder data.
	 * @return string Rendered style template.
	 */
	public function render_style( string $template, array $data = array() ): string {
		return $this->render( $template, 'style.html', $data );
	}

	/**
	 * Load template content from file.
	 *
	 * Uses caching to avoid repeated file reads.
	 * Suppresses errors with @ to handle missing files gracefully.
	 *
	 * @param string $path Full path to template file.
	 * @return string Template content or empty string on failure.
	 */
	private function load_template( string $path ): string {
		if ( ! isset( $this->cache[ $path ] ) ) {
			$this->cache[ $path ] = @file_get_contents( $path ) ?: '';
		}
		return $this->cache[ $path ];
	}

	/**
	 * Check if template file exists and is readable.
	 *
	 * @param string $path Full path to template file.
	 * @return bool True if template exists and is readable.
	 */
	private function template_exists( string $path ): bool {
		return file_exists( $path ) && is_readable( $path );
	}

	/**
	 * Build full path to template file.
	 *
	 * @param string $template     Template directory name.
	 * @param string $template_name Template file name.
	 * @return string Full path to template file.
	 */
	private function get_template_path( string $template, string $template_name ): string {
		return $this->templates_path . $template . '/' . $template_name;
	}

	/**
	 * Replace placeholders in template content.
	 *
	 * Replaces [[KEY]] placeholders with corresponding values from data array.
	 *
	 * @param string $content Template content with placeholders.
	 * @param array  $data    Associative array of replacements.
	 * @return string Content with replaced placeholders.
	 */
	private function replace_placeholders( string $content, array $data ): string {
		foreach ( $data as $key => $value ) {
			$placeholder = "[[$key]]";
			$content     = str_replace( $placeholder, $value, $content );
		}
		return $content;
	}

	/**
	 * Clear template cache.
	 *
	 * Useful for development or when templates are updated.
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cache = array();
	}

	/**
	 * Get list of available templates.
	 *
	 * Returns array of template directory names that have required files.
	 *
	 * @return array<int, string> Array of available template names.
	 */
	public function get_available_templates(): array {
		$templates = array();
		if ( ! is_dir( $this->templates_path ) ) {
			return $templates;
		}

		$dirs = scandir( $this->templates_path );
		foreach ( $dirs as $dir ) {
			if ( '.' === $dir || '..' === $dir || ! is_dir( $this->templates_path . $dir ) ) {
				continue;
			}

			// Check if template has required files.
			if ( $this->template_exists( $this->templates_path . $dir . '/header.html' ) &&
				 $this->template_exists( $this->templates_path . $dir . '/item.html' ) &&
				 $this->template_exists( $this->templates_path . $dir . '/footer.html' ) ) {
				$templates[] = $dir;
			}
		}

		return $templates;
	}
}
