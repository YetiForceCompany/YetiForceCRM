/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'Settings_Vtiger_EditModal_Js',
	{},
	{
		getForm: function () {
			if (this.formElement == false) {
				this.setForm(jQuery('#modalEditModal'));
			}
			return this.formElement;
		},
		registerEvents: function () {
			var container = this.getForm();
			this.registerBasicEvents(container);
			this.registerSubmit();
		},
		registerSubmit: function () {
			var container = this.getForm();
			var form = container.find('form');
			form.on('submit', function (e) {
				e.preventDefault();
				if (form.validationEngine('validate')) {
					var formData = form.serializeFormData();
					app.saveAjax('', formData, { record: container.find('[name="record"]').val() }).done(function (data) {
						if (data.result) {
							Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_SAVE_SUCCESS') });
							var moduleClassName = 'Settings_' + app.getModuleName() + '_List_Js';
							if (typeof window[moduleClassName] === 'undefined') {
								moduleClassName = 'Settings_Vtiger_List_Js';
							}
							var instance = new window[moduleClassName]();
							instance.getListViewRecords().done(function () {
								instance.updatePagination();
							});
						} else {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						}
						app.hideModalWindow();
					});
				}
			});
		}
	}
);
jQuery(document).ready(function (e) {
	setTimeout(function () {
		var moduleClassName = 'Settings_' + app.getModuleName() + '_EditModal_Js';
		if (typeof window[moduleClassName] === 'undefined') {
			moduleClassName = 'Settings_Vtiger_EditModal_Js';
		}
		var instance = new window[moduleClassName]();
		instance.registerEvents();
	}, 200);
});
