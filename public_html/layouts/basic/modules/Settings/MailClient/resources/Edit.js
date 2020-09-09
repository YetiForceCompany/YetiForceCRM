/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_MailClient_Edit_Js',
	{},
	{
		/**
		 * Register submit form.
		 */
		registerSubmitForm: function (form) {
			form.on('submit', function (e) {
				if (form.validationEngine('validate') === true) {
					let paramsForm = form.serializeFormData();
					let progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(paramsForm).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (true == data.result.success) {
							window.location.href = data.result.url;
						} else {
							form.find('.alert').removeClass('d-none');
							form.find('.alert p').text(data.result.message);
						}
					});
					return false;
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},

		registerEvents: function () {
			const form = this.getForm();
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
			}
			this.registerSubmitForm(form);
		}
	}
);
