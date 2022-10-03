/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
/**
 * @classdesc Base Mail integrations class.
 * @class
 */
window.Integrations_Mail_Base = class Integrations_Mail_Base {
	/** @type {string} */
	static driver = 'Base';

	/** @type {Integrations_Mail_Base} */
	static instance;

	/**
	 * Get current class instance
	 * @param {jQuery} container
	 * @returns {window.Integrations_Mail_Base}
	 */
	static getInstance(container) {
		const moduleClassName = 'Integrations_Mail_' + Integrations_Mail_Base.driver;
		return (Integrations_Mail_Base.instance = new window[moduleClassName](container));
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
		this.container.on('click', '.js-email-compose', (e) => {
			this.sendMail($(e.currentTarget).data(), e);
		});
	}
	/**
	 * Send mail message
	 * @param {Object} data
	 */
	sendMail(data) {
		window.open('mailto:' + data['email']);
	}
	/**
	 * Show console logs
	 * @param {string} message
	 * @param {string} body
	 */
	log(message, body) {
		if (CONFIG.debug) {
			if (body) {
				console.groupCollapsed('[Mail] ' + message);
				console.dirxml(body);
				console.groupEnd();
			} else {
				console.log('PBX ' + message, 'color: red;font-size: 1.2em;font-weight: bolder;');
			}
		}
	}
};
