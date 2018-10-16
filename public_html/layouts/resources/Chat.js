/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Chat_Js.
 * @type {Window.Chat_Js}
 */
window.Chat_Js = class Chat_Js {
	/**
	 * Get instance of Chat_Js.
	 * @returns {Chat_Js|Window.Chat_Js}
	 */
	static getInstance() {
		if (typeof Chat_Js.instance === 'undefined') {
			Chat_Js.instance = new Chat_Js();
		}
		return Chat_Js.instance;
	}

	/**
	 * Register chat events
	 * @param {jQuery} container
	 */
	registerEvents(container = $('.js-chat-modal')) {
		if (container.length) {
			const self = this;
		}
	}
}
/**
 * Create chat instance and register events.
 */
$(document).ready((e) => {
	const instance = Chat_Js.getInstance();
	instance.registerEvents();
});