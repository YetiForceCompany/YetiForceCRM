/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_TwoFactorAuthenticationModal_JS',
	{},
	{
		/**
		 * Function to handle sending the AJAX form
		 * @param data
		 */
		registerSubmitFrom(data) {
			data.find('button[name=saveButton]').prop('disabled', true);
			data.find('input[name=user_code]').on('keyup', (e) => {
				if (e.keyCode !== 13) {
					data.find('button[name=saveButton]').prop('disabled', $(e.currentTarget).val().length === 0);
				}
			});
			data.find('input[name=user_code]').on('change', (e) => {
				data.find('button[name=saveButton]').prop('disabled', $(e.currentTarget).val().length === 0);
			});
			data.find('input[name=turn_off_2fa]').on('change', (e) => {
				if ($(e.currentTarget).prop('checked')) {
					data.find('.js-qr-code,.js-user-code').addClass('hide');
					data.find('input[name=mode]').val('off');
					data.find('button[name=saveButton]').prop('disabled', false);
				} else {
					data.find('.js-qr-code,.js-user-code').removeClass('hide');
					data.find('input[name=mode]').val('secret');
					data.find('button[name=saveButton]').prop('disabled', true);
					data.find('input[name=user_code]').val('');
				}
			});
			let form = data.find('form');
			form.on('submit', (e) => {
				let progress = $.progressIndicator({ blockInfo: { enabled: true } });
				AppConnector.request(form.serializeFormData()).done((response) => {
					if (response.result.success) {
						app.hideModalWindow();
						app.showNotify({
							text: response.result.message,
							type: 'success',
							animation: 'show'
						});
						if (app.getModuleName() === 'Users') {
							location.reload();
						}
					} else {
						let wrongCode = form.find('.js-wrong-code');
						if (wrongCode.hasClass('hide')) {
							wrongCode.removeClass('hide');
						}
					}
					progress.progressIndicator({ mode: 'hide' });
				});
				e.preventDefault();
			});
		},
		/**
		 * Register base events
		 * @param {jQuery} modalContainer
		 */
		registerEvents(modalContainer) {
			this.registerSubmitFrom(modalContainer);
			App.Fields.Text.registerCopyClipboard(modalContainer, '.js-clipboard');
		}
	}
);
