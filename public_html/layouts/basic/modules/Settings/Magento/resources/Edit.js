/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_Magento_Edit_Js',
	{},
	{
		registerSubmitForm: function () {
			var form = this.getForm();
			form.on('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				if (form.validationEngine('validate') === true) {
					var paramsForm = form.serializeFormData();
					var progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(paramsForm)
						.done(function (data) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							if (true == data.result.success) {
								window.location.href = data.result.url;
							} else {
								app.showNotify({
									text: data.result.message,
									type: 'error'
								});
							}
						})
						.fail(function (textStatus) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							app.showNotify({
								text: textStatus,
								type: 'error'
							});
						});
					return false;
				} else {
					app.formAlignmentAfterValidation(form);
				}
				return false;
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
