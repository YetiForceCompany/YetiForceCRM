/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
/**
 * @classdesc Base Pbx integrations class.
 * @class
 */
window.Integrations_Pbx_Base = class Integrations_Pbx_Base {
	/** @type {string} */
	static driver = 'Base';

	/** @type {Integrations_Pbx_Base} */
	static instance;

	/**
	 * Get current class instance
	 * @param {jQuery} container
	 * @returns {window.Integrations_Pbx_Base}
	 */
	static getInstance(container) {
		const moduleClassName = 'Integrations_Pbx_' + Integrations_Pbx_Base.driver;
		return (Integrations_Pbx_Base.instance = new window[moduleClassName](container));
	}
	/**
	 * Constructor
	 * @param {jQuery} container
	 */
	constructor(container) {
		this.container = container;
	}
	/**
	 * Register events.
	 */
	registerEvents() {
		this.container.on('click', '.js-phone-perform-call', (e) => {
			app.showConfirmModal({
				text: app.vtranslate('JS_DIAL_NUMBER_CONFIRMATION'),
				confirmedCallback: () => {
					this.performCall($(e.currentTarget).data(), e);
				}
			});
		});
	}
	/**
	 * Perform call
	 * @param {Object} data
	 */
	performCall(data) {
		AppConnector.request({
			module: 'AppComponents',
			action: 'Pbx',
			mode: 'performCall',
			...data
		}).done(function (response) {
			if (response.result.status) {
				app.showNotify({
					title: response.result.text,
					type: 'info'
				});
			} else {
				app.showError({
					title: app.vtranslate('JS_UNEXPECTED_ERROR'),
					text: response.result.text
				});
			}
		});
	}
	/**
	 * Show console logs
	 * @param {string} message
	 * @param {string} body
	 */
	log(message, body) {
		if (CONFIG.debug) {
			if (body) {
				console.groupCollapsed('[PBX] ' + message);
				console.dirxml(body);
				console.groupEnd();
			} else {
				console.log('PBX ' + message, 'color: red;font-size: 1.2em;font-weight: bolder;');
			}
		}
	}
};
