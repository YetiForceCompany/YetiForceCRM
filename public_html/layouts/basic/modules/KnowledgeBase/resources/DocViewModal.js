/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';

jQuery.Class(
	'YetiForce_DocViewModal_Js',
	{
		/**
		 * Register events
		 */
		registerEvents() {
			DocView.mount({
				el: '#DocViewModal',
				moduleName: 'KnowledgeBase'
			});
		},
		showModalContent(container) {
			container.find('#quasar-css').on('load', function() {
				container.removeClass('d-none');
			});
		}
	},
	{}
);
YetiForce_DocViewModal_Js.registerEvents();
