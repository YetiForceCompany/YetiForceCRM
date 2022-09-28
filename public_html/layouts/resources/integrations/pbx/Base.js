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
	 * Constructor
	 * @param {jQuery} container
	 */
	constructor(container) {
		this.container = container;
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
			Vtiger_Helper_Js.showMessage({
				text: response.result
			});
		});
	}

	/**
	 * Register events.
	 */
	registerEvents() {
		this.container.on('click', '.js-phone-perform-call', (e) => {
			this.performCall($(e.currentTarget).data(), e);
		});
	}

	/**
	 * Get current class instance
	 * @param {jQuery} container
	 * @returns {window.Integrations_Pbx_Base}
	 */
	static getInstance(container) {
		const moduleClassName = 'Integrations_Pbx_' + Integrations_Pbx_Base.driver;
		return (Integrations_Pbx_Base.instance = new window[moduleClassName](container));
	}
};
