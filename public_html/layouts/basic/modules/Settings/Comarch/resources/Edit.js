/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_Comarch_Edit_Js',
	{},
	{
		registerSubmitForm: function () {
			const form = this.getForm();
			form.on('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				if (form.validationEngine('validate') === true) {
					const progress = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(form.serializeFormData())
						.done(function (data) {
							progress.progressIndicator({ mode: 'hide' });
							if (true == data.result.success) {
								window.location.href = data.result.url;
							} else {
								app.showNotify({ text: data.result.message, type: 'error' });
							}
						})
						.fail(function (textStatus) {
							progress.progressIndicator({ mode: 'hide' });
							app.showNotify({ text: textStatus, type: 'error' });
						});
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},
		getRecordsListParams: function (container) {
			return { module: $('input[name="popupReferenceModule"]', container).val() };
		},
		registerEvents: function () {
			const form = this.getForm();
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
			}
			this.registerSubmitForm();
			this.registerBasicEvents(form);
		}
	}
);
