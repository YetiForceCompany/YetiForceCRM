/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_SocialMedia_Index_Js', {}, {
	/**
	 * Container (Form)
	 */
	container: null,
	/**
	 * Get Container (Form)
	 * @returns {Object}
	 */
	getContainer() {
		if (this.container === null) {
			this.container = $('.js-social-media-container');
		}
		return this.container;
	},
	/**
	 * Submit form
	 * @param {jQuery} form
	 */
	saveForm(form) {
		form.validationEngine(app.validationEngineOptions);
		if (form.validationEngine('validate')) {
			let progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let dataForm = form.serializeFormData();
			dataForm.module = app.getModuleName();
			dataForm.parent = app.getParentModuleName();
			dataForm.action = 'SaveAjax';
			AppConnector.request(dataForm).done((response) => {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				Vtiger_Helper_Js.showPnotify({
					text: response.result.message,
					type: 'info',
				});
			}).fail(function (textStatus, errorThrown) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				Vtiger_Helper_Js.showMessage({
					type: 'error',
					text: app.vtranslate('JS_ERROR')
				});
			});
		}
	},
	/**
	 * Register events for form
	 */
	registerForm(form) {
		const thisInstance = this;
		form.on('change', (event) => {
			event.preventDefault();
			thisInstance.saveForm(form);
		});
		//Executed when the enter key is pressed.
		form.on('submit', (event) => {
			event.preventDefault();
			thisInstance.saveForm(form);
		});
	},
	/**
	 * Register all events in view
	 */
	registerEvents() {
		this.getContainer().find('form').each((index, form) => {
			this.registerForm($(form));
		});
	}
});
