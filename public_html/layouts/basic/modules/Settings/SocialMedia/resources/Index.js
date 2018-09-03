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
			this.container = $('tpl-Settings-SocialMedia-Index');
		}
		return this.container;
	},
	/**
	 * Submit form
	 * @param {jQuery} container
	 */
	saveForm(container) {
		container.validationEngine(app.validationEngineOptions);
		if (container.validationEngine('validate')) {
			let progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(container.serializeFormData()).done((response) => {
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
	registerForm() {
		let container = this.getContainer();
		let thisInstance = this;
		container.on('change', (event) => {
			event.preventDefault();
			thisInstance.saveForm(container);
		});
		//Executed when the enter key is pressed.
		container.on('submit', (event) => {
			event.preventDefault();
			thisInstance.saveForm(container);
		});
	},
	/**
	 * Register all events in view
	 */
	registerEvents() {
		this.registerForm();
	}
});
