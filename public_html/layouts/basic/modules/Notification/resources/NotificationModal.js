/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';

jQuery.Class(
	'YetiForce_NotificationModal_Js',
	{
		/**
		 * Register events
		 */
		registerEvents() {
			NotificationModal.mount({
				el: '#NotificationModal',
				state: {
					moduleName: 'Notification'
				}
			});
		}
	},
	{}
);
YetiForce_NotificationModal_Js.registerEvents();
