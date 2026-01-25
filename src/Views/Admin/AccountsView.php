<?php
/**
 * Accounts view.
 */

namespace VkPhotos\Views\Admin;

use VkPhotos\Services\SettingsService;
use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Api\VkApiClientInterface;
use VkPhotos\Config;

/**
 * Class AccountsView.
 * Handles accounts view rendering.
 */
class AccountsView {

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
	private VkApiClientInterface $vk_api;

	/**
	 * Settings model.
	 *
	 * @var SettingsModel
	 */
	private SettingsModel $settings;

	/**
	 * Template data.
	 *
	 * @var array<string, mixed>
	 */
	private array $template_data = array();

	/**
	 * Constructor.
	 *
	 * @param SettingsService      $settings_service Settings service.
	 * @param VkApiClientInterface $vk_api VK API client.
	 * @return void
	 */
	public function __construct( SettingsService $settings_service, VkApiClientInterface $vk_api ) {
		$this->settings_service = $settings_service;
		$this->vk_api           = $vk_api;
	}

	/**
	 * Render accounts view.
	 *
	 * @param array<string, mixed> $data Additional template data.
	 * @return void
	 */
	public function render( array $data = array() ): void {
		// Get settings model from service.
		$this->settings = $this->settings_service->get_settings();

		// Prepare template data.
		$this->prepare_template_data( $data );

		// Load template.
		$this->load_template();
	}

	/**
	 * Prepare template data.
	 *
	 * @param array<string, mixed> $data Additional data from controller.
	 * @return void
	 */
	private function prepare_template_data( array $data ): void {
		// Get accounts in v2 format.
		$accounts = $this->settings->get_accounts_v2();

		// Basic template data.
		$this->template_data = array(
			'accounts'       => $accounts,
			'accounts_count' => count( $accounts ),
			'access_token'   => $this->settings->access_token ?? '',
			'has_token'      => ! empty( $this->settings->access_token ),
		);

		// Merge with controller data.
		$this->template_data = array_merge( $this->template_data, $data );

		// Add VK API wrapper for template compatibility.
		$this->template_data['VKP'] = $this->get_vkp();
	}

	/**
	 * Get VK API wrapper.
	 *
	 * @return object VK API wrapper.
	 */
	public function get_vkp(): object {
		// Set access token.
		$this->vk_api->access_token = $this->settings->access_token ?? '';

		// Create wrapper class.
		$wrapper = new class( $this->vk_api ) {
			/**
			 * VK API client.
			 *
			 * @var VkApiClientInterface
			 */
			private VkApiClientInterface $vk_api;

			/**
			 * Constructor.
			 *
			 * @param VkApiClientInterface $vk_api VK API client.
			 */
			public function __construct( VkApiClientInterface $vk_api ) {
				$this->vk_api = $vk_api;
			}

			/**
			 * Execute API method.
			 *
			 * @param string $method API method name.
			 * @param array  $params Request parameters.
			 * @return array API response.
			 */
			public function api( string $method, array $params = array() ): array {
				return $this->vk_api->api( $method, $params );
			}
		};

		return $wrapper;
	}

	/**
	 * Magic getter for template data access.
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( string $name ) {
		return $this->template_data[ $name ] ?? null;
	}

	/**
	 * Check if template data property exists.
	 *
	 * @param string $name Property name.
	 * @return bool True if property exists.
	 */
	public function __isset( string $name ): bool {
		return isset( $this->template_data[ $name ] );
	}

	/**
	 * Load template file.
	 *
	 * @return void
	 */
	private function load_template(): void {
		// Get admin templates path from config.
		$template_path = Config::get( 'paths.templates.admin', '' ) . 'accounts-view.php';

		if ( ! file_exists( $template_path ) ) {
			$this->render_fallback();
			return;
		}

		// Include template - $this will refer to this AccountsView instance.
		// Template data properties are accessible via __get magic method.
		include $template_path;
	}

	/**
	 * Fallback rendering if template file is not found.
	 *
	 * @return void
	 */
	private function render_fallback(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'VK Accounts Management', 'vkp' ); ?></h1>
			
			<?php settings_errors( 'vkp_accounts' ); ?>

			<div class="notice notice-warning">
				<p><?php esc_html_e( 'Template file not found. Using fallback rendering.', 'vkp' ); ?></p>
			</div>

			<form method="post" action="">
				<?php echo $this->template_data['nonce_field'] ?? ''; ?>

				<h2><?php esc_html_e( 'Current Accounts', 'vkp' ); ?></h2>
				
				<?php if ( empty( $this->accounts ) ) : ?>
					<p><?php esc_html_e( 'No accounts configured.', 'vkp' ); ?></p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'ID', 'vkp' ); ?></th>
								<th><?php esc_html_e( 'Type', 'vkp' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'vkp' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $this->accounts as $index => $account ) : ?>
								<tr>
									<td><?php echo esc_html( $account['id'] ); ?></td>
									<td><?php echo esc_html( $account['type'] ); ?></td>
									<td>
										<button type="button" class="button button-small vkp-remove-account" data-index="<?php echo esc_attr( $index ); ?>">
											<?php esc_html_e( 'Remove', 'vkp' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<h2><?php esc_html_e( 'Add New Account', 'vkp' ); ?></h2>
				
				<?php if ( ! $this->has_token ) : ?>
					<div class="notice notice-error">
						<p>
							<?php esc_html_e( 'Access token is not configured. Please set it in the plugin settings first.', 'vkp' ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=vk-photos-settings' ) ); ?>">
								<?php esc_html_e( 'Go to Settings', 'vkp' ); ?>
							</a>
						</p>
					</div>
				<?php endif; ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="vkp_new_account_id"><?php esc_html_e( 'Account ID', 'vkp' ); ?></label>
						</th>
						<td>
							<input type="number" id="vkp_new_account_id" name="vkp_new_account_id" class="regular-text" min="1">
							<p class="description">
								<?php esc_html_e( 'Enter VK user ID or group ID (without minus sign for groups).', 'vkp' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'Account Type', 'vkp' ); ?></label>
						</th>
						<td>
							<label>
								<input type="radio" name="vkp_new_account_type" value="user" checked>
								<?php esc_html_e( 'User', 'vkp' ); ?>
							</label>
							<br>
							<label>
								<input type="radio" name="vkp_new_account_type" value="group">
								<?php esc_html_e( 'Group', 'vkp' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" id="vkp_add_account" class="button button-primary" <?php echo ! $this->has_token ? 'disabled' : ''; ?>>
						<?php esc_html_e( 'Add Account', 'vkp' ); ?>
					</button>
					&nbsp;
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'vkp' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}
}
