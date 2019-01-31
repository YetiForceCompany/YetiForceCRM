/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js('Settings_WebserviceUsers_Edit_Js', {}, {
	getForm: function () {
		if (this.formElement == false) {
			this.setForm(jQuery('#modalEdit'));
		}
		return this.formElement;
	},
	showHideFields: function (typeElement) {
		var elementContainer = this.getForm().find('[name="crmid"]').closest('.form-group');
		if (typeElement.val() === '1') {
			elementContainer.addClass('d-none').find('.clearReferenceSelection').trigger('click');
		} else {
			elementContainer.removeClass('d-none');
		}
	},
	registerEvents: function () {
		const self = this,
			container = this.getForm();
		this.registerBasicEvents(container);
		let form = container.find('form');
		form.on('submit', function (e) {
			e.preventDefault();
			if (form.validationEngine('validate')) {
				let formData = form.serializeFormData();
				AppConnector.request(formData).done(function (data) {
					if (data.result) {
						Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_SAVE_SUCCESS')});
						let listInstance = Settings_WebserviceUsers_List_Js.getInstance();
						listInstance.reloadTab();
					} else {
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_ERROR'));
					}
					app.hideModalWindow();
				});
			}
		});
		let typeElement = form.find('[name="type"]');
		self.showHideFields(typeElement);
		typeElement.on('change', function (e) {
			self.showHideFields(jQuery(e.currentTarget));
		});
	}
})
jQuery(document).ready(function (e) {
	var instance = new Settings_WebserviceUsers_Edit_Js();
	instance.registerEvents();
})
