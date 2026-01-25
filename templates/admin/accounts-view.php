<div class="wrap">
	<h1><?php esc_html_e( 'VK Accounts Management', 'vkp' ); ?></h1>
	
	<?php settings_errors( 'vkp_accounts' ); ?>

	<form method="post" action="" id="vkp-accounts-form">
		<?php echo $this->nonce_field ?? ''; ?>

		<input type="hidden" name="vkp_accounts_data" id="vkp_accounts_data" value="">

		<h2><?php esc_html_e( 'Current Accounts', 'vkp' ); ?></h2>
		
		<?php if ( empty( $this->accounts ) ) : ?>
			<p><?php esc_html_e( 'No accounts configured.', 'vkp' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped" id="vkp-accounts-table">
				<thead>
					<tr>
						<th width="10%"><?php esc_html_e( '#', 'vkp' ); ?></th>
						<th width="20%"><?php esc_html_e( 'ID', 'vkp' ); ?></th>
						<th width="20%"><?php esc_html_e( 'Type', 'vkp' ); ?></th>
						<th width="40%"><?php esc_html_e( 'Info', 'vkp' ); ?></th>
						<th width="10%"><?php esc_html_e( 'Actions', 'vkp' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $this->accounts as $index => $account ) : ?>
						<tr data-index="<?php echo esc_attr( $index ); ?>" data-id="<?php echo esc_attr( $account['id'] ); ?>" data-type="<?php echo esc_attr( $account['type'] ); ?>">
							<td><?php echo esc_html( $index + 1 ); ?></td>
							<td class="account-id"><?php echo esc_html( $account['id'] ); ?></td>
							<td class="account-type"><?php echo esc_html( $account['type'] ); ?></td>
							<td class="account-info">
								<span class="vkp-account-validation">
									<em><?php esc_html_e( 'Loading...', 'vkp' ); ?></em>
								</span>
							</td>
							<td>
								<button type="button" class="button button-small vkp-remove-account">
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
					<input type="number" id="vkp_new_account_id" name="vkp_new_account_id" class="regular-text" min="1" <?php echo ! $this->has_token ? 'disabled' : ''; ?>>
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
						<input type="radio" name="vkp_new_account_type" value="user" checked <?php echo ! $this->has_token ? 'disabled' : ''; ?>>
						<?php esc_html_e( 'User', 'vkp' ); ?>
					</label>
					<br>
					<label>
						<input type="radio" name="vkp_new_account_type" value="group" <?php echo ! $this->has_token ? 'disabled' : ''; ?>>
						<?php esc_html_e( 'Group', 'vkp' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<button type="button" id="vkp_add_account" class="button button-primary" <?php echo ! $this->has_token ? 'disabled' : ''; ?>>
						<?php esc_html_e( 'Add Account', 'vkp' ); ?>
					</button>
					<span id="vkp_validation_result" class="vkp-validation-result" style="margin-left: 10px; display: none;"></span>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'vkp' ); ?>">
			<span id="vkp-saving" style="display: none; margin-left: 10px;">
				<span class="spinner is-active" style="float: none; margin: 0;"></span>
				<?php esc_html_e( 'Saving...', 'vkp' ); ?>
			</span>
		</p>
	</form>

	<div id="vkp-no-accounts-template" style="display: none;">
		<tr>
			<td colspan="5" style="text-align: center; padding: 20px;">
				<?php esc_html_e( 'No accounts configured. Add your first account above.', 'vkp' ); ?>
			</td>
		</tr>
	</div>

	<div id="vkp-account-row-template" style="display: none;">
		<tr data-index="{index}" data-id="{id}" data-type="{type}">
			<td>{number}</td>
			<td class="account-id">{id}</td>
			<td class="account-type">{type}</td>
			<td class="account-info">
				<span class="vkp-account-validation">
					<em><?php esc_html_e( 'Loading...', 'vkp' ); ?></em>
				</span>
			</td>
			<td>
				<button type="button" class="button button-small vkp-remove-account">
					<?php esc_html_e( 'Remove', 'vkp' ); ?>
				</button>
			</td>
		</tr>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	'use strict';

	// Accounts data store
	var vkpAccounts = <?php echo wp_json_encode( $this->accounts ); ?>;
	var vkpAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
	var vkpNonce = '<?php echo esc_attr( wp_create_nonce( 'vkp_accounts_ajax' ) ); ?>';

	// Initialize accounts table
	function initAccountsTable() {
		var $table = $('#vkp-accounts-table tbody');
		
		if (vkpAccounts.length === 0) {
			$table.html($('#vkp-no-accounts-template').html());
			return;
		}

		// Validate each account
		vkpAccounts.forEach(function(account, index) {
			validateAccount(account.id, account.type, index);
		});
	}

	// Validate account via AJAX
	function validateAccount(accountId, accountType, rowIndex) {
		$.ajax({
			url: vkpAjaxUrl,
			type: 'POST',
			data: {
				action: 'vkp_validate_account',
				account_id: accountId,
				account_type: accountType,
				nonce: vkpNonce
			},
			success: function(response) {
				if (response.success) {
					updateAccountInfo(rowIndex, response.data);
				} else {
					updateAccountInfo(rowIndex, {
						valid: false,
						name: '',
						error: response.data || '<?php esc_html_e( 'Validation failed', 'vkp' ); ?>'
					});
				}
			},
			error: function() {
				updateAccountInfo(rowIndex, {
					valid: false,
					name: '',
					error: '<?php esc_html_e( 'AJAX request failed', 'vkp' ); ?>'
				});
			}
		});
	}

	// Update account info in table
	function updateAccountInfo(rowIndex, data) {
		var $row = $('tr[data-index="' + rowIndex + '"]');
		var $infoCell = $row.find('.account-info .vkp-account-validation');
		
		if (data.valid) {
			$infoCell.html('<span style="color: green;">✓ ' + data.name + '</span>');
		} else {
			$infoCell.html('<span style="color: red;">✗ ' + (data.error || '<?php esc_html_e( 'Invalid account', 'vkp' ); ?>') + '</span>');
		}
	}

	// Add new account
	$('#vkp_add_account').on('click', function() {
		var $button = $(this);
		var accountId = $('#vkp_new_account_id').val().trim();
		var accountType = $('input[name="vkp_new_account_type"]:checked').val();
		
		if (!accountId) {
			alert('<?php esc_html_e( 'Please enter account ID', 'vkp' ); ?>');
			return;
		}

		if (!/^\d+$/.test(accountId)) {
			alert('<?php esc_html_e( 'Account ID must be a number', 'vkp' ); ?>');
			return;
		}

		// Disable button during validation
		$button.prop('disabled', true).text('<?php esc_html_e( 'Validating...', 'vkp' ); ?>');
		$('#vkp_validation_result').hide().removeClass('success error');

		// Validate via AJAX
		$.ajax({
			url: vkpAjaxUrl,
			type: 'POST',
			data: {
				action: 'vkp_validate_account',
				account_id: accountId,
				account_type: accountType,
				nonce: vkpNonce
			},
			success: function(response) {
				if (response.success && response.data.valid) {
					// Add to accounts array
					var newAccount = {
						id: parseInt(accountId),
						type: accountType
					};
					
					vkpAccounts.push(newAccount);
					updateAccountsTable();
					
					// Clear form
					$('#vkp_new_account_id').val('');
					$('#vkp_validation_result')
						.html('<span style="color: green;">✓ ' + response.data.name + ' - <?php esc_html_e( 'Account added', 'vkp' ); ?></span>')
						.addClass('success')
						.show();
				} else {
					var errorMsg = response.data ? response.data.error : '<?php esc_html_e( 'Validation failed', 'vkp' ); ?>';
					$('#vkp_validation_result')
						.html('<span style="color: red;">✗ ' + errorMsg + '</span>')
						.addClass('error')
						.show();
				}
			},
			error: function() {
				$('#vkp_validation_result')
					.html('<span style="color: red;">✗ <?php esc_html_e( 'AJAX request failed', 'vkp' ); ?></span>')
					.addClass('error')
					.show();
			},
			complete: function() {
				$button.prop('disabled', false).text('<?php esc_html_e( 'Add Account', 'vkp' ); ?>');
			}
		});
	});

	// Remove account
	$(document).on('click', '.vkp-remove-account', function() {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to remove this account?', 'vkp' ); ?>')) {
			return;
		}

		var $row = $(this).closest('tr');
		var index = parseInt($row.data('index'));
		
		// Remove from array
		vkpAccounts.splice(index, 1);
		
		// Update indices
		vkpAccounts.forEach(function(account, i) {
			account.index = i;
		});
		
		updateAccountsTable();
	});

	// Update accounts table
	function updateAccountsTable() {
		var $table = $('#vkp-accounts-table tbody');
		var $template = $('#vkp-account-row-template').html();
		
		if (vkpAccounts.length === 0) {
			$table.html($('#vkp-no-accounts-template').html());
			updateHiddenField();
			return;
		}

		var rowsHtml = '';
		vkpAccounts.forEach(function(account, index) {
			var rowHtml = $template
				.replace(/{index}/g, index)
				.replace(/{number}/g, index + 1)
				.replace(/{id}/g, account.id)
				.replace(/{type}/g, account.type);
			rowsHtml += rowHtml;
		});
		
		$table.html(rowsHtml);
		updateHiddenField();
		
		// Validate all accounts
		vkpAccounts.forEach(function(account, index) {
			validateAccount(account.id, account.type, index);
		});
	}

	// Update hidden field with accounts data
	function updateHiddenField() {
		$('#vkp_accounts_data').val(JSON.stringify(vkpAccounts));
	}

	// Form submission
	$('#vkp-accounts-form').on('submit', function() {
		// Update hidden field before submission
		updateHiddenField();
		
		// Show saving indicator
		$('#vkp-saving').show();
		$('#submit').prop('disabled', true);
	});

	// Initialize
	initAccountsTable();
	updateHiddenField();
});
</script>

<style type="text/css">
#vkp-accounts-table {
	margin-bottom: 20px;
}

.vkp-validation-result {
	padding: 5px 10px;
	border-radius: 3px;
}

.vkp-validation-result.success {
	background-color: #d4edda;
	color: #155724;
}

.vkp-validation-result.error {
	background-color: #f8d7da;
	color: #721c24;
}

.account-info .vkp-account-validation {
	font-style: italic;
}

#vkp-saving .spinner {
	margin: 0 5px 0 0;
	vertical-align: middle;
}
</style>