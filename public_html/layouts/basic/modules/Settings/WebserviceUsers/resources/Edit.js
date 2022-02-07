/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'Settings_WebserviceUsers_Edit_Js',
	{},
	{
		getForm: function () {
			if (this.formElement == false) {
				this.setForm(jQuery('#modalEdit'));
			}
			return this.formElement;
		},
		/**
		 * Show hide fields
		 * @param {Object} typeElement
		 * @param {Object} params
		 */
		showHideFields: function (typeElement, params) {
			let elementContainer = this.getForm()
				.find('[name="' + params.fieldHide + '"]')
				.closest('.form-group');
			if (typeElement.val() === params.value) {
				let clearReference = elementContainer.find('.clearReferenceSelection');
				if (clearReference.length > 0) {
					clearReference.trigger('click');
				}
				elementContainer.addClass('d-none');
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
						if (data.result.success) {
							Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_SAVE_SUCCESS') });
							let listInstance = Settings_WebserviceUsers_List_Js.getInstance();
							listInstance.reloadTab();
							app.hideModalWindow();
						} else {
							if (data.result.message) {
								app.showNotify({
									text: data.result.message,
									type: 'error'
								});
							} else {
								app.showNotify({
									text: app.vtranslate('JS_ERROR'),
									type: 'error'
								});
							}
						}
					});
				}
			});
			let typeElement = form.find('[name="type"]');
			let typeParams = { fieldHide: 'crmid', value: '1' };
			self.showHideFields(typeElement, typeParams);
			typeElement.on('change', function (e) {
				self.showHideFields(jQuery(e.currentTarget), typeParams);
			});

			let loginMethod = form.find('[name="login_method"]');
			let loginMethodParams = { fieldHide: 'authy_methods', value: 'PLL_PASSWORD' };
			self.showHideFields(loginMethod, loginMethodParams);
			loginMethod.on('change', function (e) {
				self.showHideFields(jQuery(e.currentTarget), loginMethodParams);
			});
		}
	}
);
jQuery(document).ready(function (e) {
	var instance = new Settings_WebserviceUsers_Edit_Js();
	instance.registerEvents();
});
