<?php
/**
 * Accounts controller.
 */

namespace VkPhotos\Controllers\Admin;

use VkPhotos\Services\SettingsService;
use VkPhotos\Models\Settings as SettingsModel;
use VkPhotos\Api\VkApiClientInterface;

/**
 * Class AccountsController.
 * Handles accounts management in admin area.
 */
class AccountsController {

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
	 * Handle accounts page request.
	 *
	 * @return void
	 */
	public function handle_page(): void {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'vkp' ) );
		}

		// Handle form submission.
		if ( isset( $_POST['vkp_accounts_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vkp_accounts_nonce'] ) ), 'vkp_save_accounts' ) ) {
			$this->handle_save();
		}

		// Render page.
		$this->render_page();
	}

	/**
	 * Register AJAX handlers.
	 *
	 * @return void
	 */
	public function register_ajax_handlers(): void {
		add_action( 'wp_ajax_vkp_validate_account', array( $this, 'handle_ajax_validate_account' ) );
	}

	/**
	 * Handle save accounts data.
	 *
	 * @return void
	 */
	private function handle_save(): void {
		$accounts_data = array();

		// Get submitted accounts.
		if ( isset( $_POST['vkp_accounts'] ) && is_array( $_POST['vkp_accounts'] ) ) {
			$submitted_accounts = wp_unslash( $_POST['vkp_accounts'] ); // Unslash the array.
			foreach ( $submitted_accounts as $account ) {
				if ( isset( $account['id'], $account['type'] ) ) {
					$id   = sanitize_text_field( $account['id'] );
					$type = sanitize_text_field( $account['type'] );

					// Validate ID is numeric.
					if ( ! is_numeric( $id ) ) {
						continue;
					}

					// Validate type.
					if ( ! in_array( $type, array( 'user', 'group' ), true ) ) {
						$type = 'user';
					}

					$accounts_data[] = array(
						'id'   => (int) $id,
						'type' => $type,
					);
				}
			}
		}

		// Get settings model.
		$settings = $this->settings_service->get_settings();

		// Update accounts data.
		$settings->set_accounts_v2( $accounts_data );

		// Save to WordPress options.
		$wp_options = $settings->to_wp_options();
		foreach ( $wp_options as $key => $value ) {
			update_option( $key, $value );
		}

		// Add success message.
		add_settings_error(
			'vkp_accounts',
			'vkp_accounts_updated',
			__( 'Accounts saved successfully.', 'vkp' ),
			'success'
		);
	}

	/**
	 * Render accounts page.
	 *
	 * @return void
	 */
	private function render_page(): void {
		// Get current accounts.
		$settings = $this->settings_service->get_settings();
		$accounts = $settings->get_accounts_v2();

		// Prepare data for template.
		$data = array(
			'accounts'    => $accounts,
			'nonce_field' => wp_nonce_field( 'vkp_save_accounts', 'vkp_accounts_nonce', true, false ),
		);

		// Load view.
		$view_class = 'VkPhotos\\Views\\Admin\\AccountsView';
		if ( class_exists( $view_class ) ) {
			$view = new $view_class( $this->settings_service, $this->vk_api );
			$view->render( $data );
		} else {
			// Fallback to simple rendering.
			$this->render_fallback( $data );
		}
	}

	/**
	 * Fallback rendering if view class is not available.
	 *
	 * @param array<string, mixed> $data Template data.
	 * @return void
	 */
	private function render_fallback( array $data ): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'VK Accounts Management', 'vkp' ); ?></h1>
			<p><?php esc_html_e( 'View class not found. Please check plugin installation.', 'vkp' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Validate VK account via API.
	 *
	 * @param int    $account_id Account ID.
	 * @param string $type Account type (user/group).
	 * @return array{valid: bool, name: string, error: string|null} Validation result.
	 */
	public function validate_account( int $account_id, string $type ): array {
		if ( $account_id <= 0 ) {
			return array(
				'valid' => false,
				'name'  => '',
				'error' => __( 'Invalid account ID.', 'vkp' ),
			);
		}

		try {
			$settings = $this->settings_service->get_settings();
			$token    = $settings->access_token ?? '';

			if ( empty( $token ) ) {
				return array(
					'valid' => false,
					'name'  => '',
					'error' => __( 'Access token is not set. Please configure it in plugin settings.', 'vkp' ),
				);
			}

			// Set access token.
			$this->vk_api->access_token = $token;

			if ( 'group' === $type ) {
				$response = $this->vk_api->api(
					'groups.getById',
					array(
						'group_id' => $account_id,
					)
				);

				if ( isset( $response['error'] ) ) {
					return array(
						'valid' => false,
						'name'  => '',
						'error' => $response['error']['error_msg'] ?? __( 'Error validating group.', 'vkp' ),
					);
				}

				$group = $response['response']['groups'][0] ?? null;
				if ( $group ) {
					return array(
						'valid' => true,
						'name'  => $group['name'] ?? '',
						'error' => null,
					);
				}
			} else {
				$response = $this->vk_api->api(
					'users.get',
					array(
						'user_id' => $account_id,
					)
				);

				if ( isset( $response['error'] ) ) {
					return array(
						'valid' => false,
						'name'  => '',
						'error' => $response['error']['error_msg'] ?? __( 'Error validating user.', 'vkp' ),
					);
				}

				$user = $response['response'][0] ?? null;
				if ( $user ) {
					$name = trim( ( $user['first_name'] ?? '' ) . ' ' . ( $user['last_name'] ?? '' ) );
					return array(
						'valid' => true,
						'name'  => $name,
						'error' => null,
					);
				}
			}
		} catch ( \Exception $e ) {
			return array(
				'valid' => false,
				'name'  => '',
				'error' => $e->getMessage(),
			);
		}

		return array(
			'valid' => false,
			'name'  => '',
			'error' => __( 'Account not found.', 'vkp' ),
		);
	}

	/**
	 * Handle AJAX account validation request.
	 *
	 * @return void
	 */
	public function handle_ajax_validate_account(): void {
		// Check nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'vkp_accounts_ajax' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'vkp' ) );
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'vkp' ) );
			return;
		}

		// Get parameters.
		$account_id   = isset( $_POST['account_id'] ) ? (int) $_POST['account_id'] : 0;
		$account_type = isset( $_POST['account_type'] ) ? sanitize_text_field( wp_unslash( $_POST['account_type'] ) ) : 'user';

		// Validate parameters.
		if ( $account_id <= 0 ) {
			wp_send_json_error( __( 'Invalid account ID.', 'vkp' ) );
			return;
		}

		if ( ! in_array( $account_type, array( 'user', 'group' ), true ) ) {
			wp_send_json_error( __( 'Invalid account type.', 'vkp' ) );
			return;
		}

		// Validate account.
		$result = $this->validate_account( $account_id, $account_type );

		// Send response.
		if ( $result['valid'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result['error'] );
		}
	}
}
