/**
 * VK Photos Accounts Admin JavaScript
 * Handles dynamic accounts management in admin area.
 */

import '../styles/accounts-admin.scss';

(function ($) {
    'use strict';

    // Accounts manager class
    var VkpAccountsManager = function (options) {
        this.options = $.extend({
            ajaxUrl: '',
            nonce: '',
            accounts: [],
            strings: {
                loading: 'Loading...',
                validationFailed: 'Validation failed',
                ajaxFailed: 'AJAX request failed',
                confirmRemove: 'Are you sure you want to remove this account?',
                invalidId: 'Account ID must be a number',
                enterId: 'Please enter account ID',
                accountAdded: 'Account added',
                saving: 'Saving...'
            }
        }, options);

        this.init();
    };

    VkpAccountsManager.prototype = {
        init: function () {
            this.cacheElements();
            this.bindEvents();
            this.initAccountsTable();
            this.updateHiddenField();
        },

        cacheElements: function () {
            this.$form = $('#vkp-accounts-form');
            this.$table = $('#vkp-accounts-table');
            this.$tbody = this.$table.find('tbody');
            this.$newId = $('#vkp_new_account_id');
            this.$newType = $('input[name="vkp_new_account_type"]');
            this.$addButton = $('#vkp_add_account');
            this.$validationResult = $('#vkp_validation_result');
            this.$hiddenField = $('#vkp_accounts_data');
            this.$submitButton = $('#submit');
            this.$savingIndicator = $('#vkp-saving');

            // Templates
            this.noAccountsTemplate = $('#vkp-no-accounts-template').html();
            this.accountRowTemplate = $('#vkp-account-row-template').html();
        },

        bindEvents: function () {
            var self = this;

            // Add account button
            this.$addButton.on('click', function () {
                self.addAccount();
            });

            // Remove account buttons (delegated)
            this.$tbody.on('click', '.vkp-remove-account', function () {
                var $row = $(this).closest('tr');
                var index = parseInt($row.data('index'));
                self.removeAccount(index);
            });

            // Form submission
            this.$form.on('submit', function () {
                self.handleFormSubmit();
            });

            // Enter key in account ID field
            this.$newId.on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.addAccount();
                }
            });
        },

        initAccountsTable: function () {
            if (this.options.accounts.length === 0) {
                this.$tbody.html(this.noAccountsTemplate);
                return;
            }

            // Validate each account
            var self = this;
            this.options.accounts.forEach(function (account, index) {
                self.validateAccount(account.id, account.type, index);
            });
        },

        addAccount: function () {
            var accountId = this.$newId.val().trim();
            var accountType = this.$newType.filter(':checked').val();

            if (!accountId) {
                alert(this.options.strings.enterId);
                return;
            }

            if (!/^\d+$/.test(accountId)) {
                alert(this.options.strings.invalidId);
                return;
            }

            var self = this;
            var $button = this.$addButton;

            // Disable button during validation
            $button.prop('disabled', true).text(this.options.strings.loading);
            this.$validationResult.hide().removeClass('success error');

            // Validate via AJAX
            $.ajax({
                url: this.options.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vkp_validate_account',
                    account_id: accountId,
                    account_type: accountType,
                    nonce: this.options.nonce
                },
                success: function (response) {
                    if (response.success && response.data.valid) {
                        // Add to accounts array
                        var newAccount = {
                            id: parseInt(accountId),
                            type: accountType
                        };

                        self.options.accounts.push(newAccount);
                        self.updateAccountsTable();

                        // Clear form
                        self.$newId.val('');
                        self.$validationResult
                            .html('<span style="color: green;">✓ ' + response.data.name + ' - ' + self.options.strings.accountAdded + '</span>')
                            .addClass('success')
                            .show();
                    } else {
                        var errorMsg = response.data ? response.data.error : self.options.strings.validationFailed;
                        self.$validationResult
                            .html('<span style="color: red;">✗ ' + errorMsg + '</span>')
                            .addClass('error')
                            .show();
                    }
                },
                error: function () {
                    self.$validationResult
                        .html('<span style="color: red;">✗ ' + self.options.strings.ajaxFailed + '</span>')
                        .addClass('error')
                        .show();
                },
                complete: function () {
                    $button.prop('disabled', false).text('Add Account');
                }
            });
        },

        removeAccount: function (index) {
            if (!confirm(this.options.strings.confirmRemove)) {
                return;
            }

            // Remove from array
            this.options.accounts.splice(index, 1);

            // Update indices
            this.options.accounts.forEach(function (account, i) {
                account.index = i;
            });

            this.updateAccountsTable();
        },

        validateAccount: function (accountId, accountType, rowIndex) {
            var self = this;

            $.ajax({
                url: this.options.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vkp_validate_account',
                    account_id: accountId,
                    account_type: accountType,
                    nonce: this.options.nonce
                },
                success: function (response) {
                    if (response.success) {
                        self.updateAccountInfo(rowIndex, response.data);
                    } else {
                        self.updateAccountInfo(rowIndex, {
                            valid: false,
                            name: '',
                            error: response.data || self.options.strings.validationFailed
                        });
                    }
                },
                error: function () {
                    self.updateAccountInfo(rowIndex, {
                        valid: false,
                        name: '',
                        error: self.options.strings.ajaxFailed
                    });
                }
            });
        },

        updateAccountInfo: function (rowIndex, data) {
            var $row = $('tr[data-index="' + rowIndex + '"]');
            var $infoCell = $row.find('.account-info .vkp-account-validation');

            if (data.valid) {
                $infoCell.html('<span style="color: green;">✓ ' + data.name + '</span>');
            } else {
                $infoCell.html('<span style="color: red;">✗ ' + (data.error || 'Invalid account') + '</span>');
            }
        },

        updateAccountsTable: function () {
            if (this.options.accounts.length === 0) {
                this.$tbody.html(this.noAccountsTemplate);
                this.updateHiddenField();
                return;
            }

            var rowsHtml = '';
            var self = this;

            this.options.accounts.forEach(function (account, index) {
                var rowHtml = self.accountRowTemplate
                    .replace(/{index}/g, index)
                    .replace(/{number}/g, index + 1)
                    .replace(/{id}/g, account.id)
                    .replace(/{type}/g, account.type);
                rowsHtml += rowHtml;
            });

            this.$tbody.html(rowsHtml);
            this.updateHiddenField();

            // Validate all accounts
            this.options.accounts.forEach(function (account, index) {
                self.validateAccount(account.id, account.type, index);
            });
        },

        updateHiddenField: function () {
            this.$hiddenField.val(JSON.stringify(this.options.accounts));
        },

        handleFormSubmit: function () {
            // Update hidden field before submission
            this.updateHiddenField();

            // Show saving indicator
            this.$savingIndicator.show();
            this.$submitButton.prop('disabled', true);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function () {
        // Check if we're on the accounts page
        if ($('#vkp-accounts-form').length) {
            window.vkpAccountsManager = new VkpAccountsManager({
                ajaxUrl: vkpAccountsData.ajaxUrl,
                nonce: vkpAccountsData.nonce,
                accounts: vkpAccountsData.accounts,
                strings: vkpAccountsData.strings || {}
            });
        }
    });

})(jQuery);