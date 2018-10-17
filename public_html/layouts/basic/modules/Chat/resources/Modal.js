/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class("Chat_Modal_JS", {}, {
	/**
	 * Register base events
	 * @param {jQuery} modalContainer
	 */
	registerEvents(modalContainer) {
		Chat_JS.getInstance(modalContainer).registerModalEvents();
	}
});
