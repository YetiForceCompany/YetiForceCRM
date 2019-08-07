/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
Vtiger_Edit_Js('Settings_Magento_Edit_Js', {}, {
	getForm: function () {
		if (this.formElement === false) {
			this.setForm($('.js-edit-form'));
		}
		return this.formElement;
	},
	getRecordsListParams: function (container) {
		return {module: $('input[name="popupReferenceModule"]', container).val()};
	},
	registerForm() {
		let form = this.getForm();
		form.on('submit', (event) => {
			event.preventDefault();
			form.validationEngine(app.validationEngineOptions);
			if (form.validationEngine('validate')) {
				let progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(form.serializeFormData()).done((response) => {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					if (response.result.success) {
						Vtiger_Helper_Js.showPnotify({
							text: response.result.message,
							type: 'info',
						});
					} else {
						Vtiger_Helper_Js.showPnotify({
							text: response.result.message,
						});
					}
				});
			}
		});
	},
	registerEvents: function () {
		let thisInstance = this,
			container = thisInstance.getForm();
		this.registerBasicEvents(container);
		this.registerForm();
	},

});
$(document).ready(function (e) {
	let instance = new Settings_Magento_Edit_Js();
	instance.registerEvents();
});
