/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'Settings_SMSNotifier_Edit_Js',
	{},
	{
		/**
		 * Container
		 */
		container: false,
		/**
		 * Save Event
		 * @param {Event} e
		 */
		saveEvent: function (e) {
			e.preventDefault();
			let form = this.getForm();
			if (form.validationEngine('validate')) {
				var formData = form.serializeFormData();
				app.saveAjax('', [], formData).done(function (data) {
					if (data.result) {
						Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_SAVE_SUCCESS') });
						var listInstance = Settings_Vtiger_List_Js.getInstance();
						listInstance.getListViewRecords();
					} else {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					}
					app.hideModalWindow();
				});
			}
		},
		/**
		 * Register Events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			App.Fields.Text.registerCopyClipboard(this.container);
			let form = this.container.find('form');
			this.setForm(form);
			this.registerBasicEvents(this.container);
			this.container.on('click', '.js-modal__save', (e) => {
				this.saveEvent(e);
			});
		}
	}
);
