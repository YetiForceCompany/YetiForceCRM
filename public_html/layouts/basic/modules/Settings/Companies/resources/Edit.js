/* {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_Companies_Edit_Js',
	{},
	{
		/** Submit form */
		registerSubmitForm: function () {
			let form = this.getForm();
			form.on('submit', function (e) {
				e.preventDefault();
				if (form.validationEngine('validate') === true) {
					let formData = new FormData(form[0]);
					let params = {
						url: 'index.php',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false
					};
					let progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(params).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (true === data.result.success) {
							window.location.href = data.result.url;
						} else {
							Settings_Vtiger_Index_Js.showMessage({ text: data.result.message, type: 'error' });
						}
					});
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},

		/** Check registration status */
		registerRefreshStatus: function () {
			let form = this.getForm();
			form.find('.js-refresh-status').on('click', function () {
				const progressIndicator = $.progressIndicator({
					blockInfo: { enabled: true }
				});
				AppConnector.request({
					parent: 'Settings',
					module: 'Companies',
					action: 'CheckStatus'
				}).done((data) => {
					progressIndicator.progressIndicator({ mode: 'hide' });
					if (data.success && data.result) {
						if (data.result.message) {
							app.showNotify({
								text: data.result.message,
								type: data.result.type,
								hide: true,
								delay: 8000,
								textTrusted: false
							});
						}
						if (data.result.success) {
							window.location.reload();
						}
					}
				});
			});
		},

		/** @inheritdoc */
		registerEvents: function () {
			let form = this.getForm();
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
				form.find('[data-inputmask]').inputmask();
			}
			this.registerSubmitForm();
			this.registerRefreshStatus();
		}
	}
);
