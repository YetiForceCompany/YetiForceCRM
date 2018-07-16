/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_TwoFactorAuthentication_Index_Js', {}, {
	/**
	 * Container (Form)
	 */
	container: null,
	/**
	 * Get Container (Form)
	 * @returns {Object}
	 */
	getContainer(){
		if( this.container===null ){
			this.container = $('form.js-two-factor-auth__form');
		}
		return this.container;
	},
	/**
	 * Register events for form
	 */
	registerForm(){
		let container = this.getContainer();
		container.on('change', (event) => {
			event.preventDefault();
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
				});
			}
		});
	},
	/**
	 * Register all events in view
	 */
	registerEvents() {
		this.registerForm();
	}
});
