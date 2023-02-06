/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_WooCommerce_List_Js',
	{
		/**
		 * Restart synchronization
		 *
		 * @param {int}  id
		 */
		reload(id) {
			AppConnector.request({
				module: 'WooCommerce',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'reload',
				record: id
			}).done((data) => {
				app.showNotify({
					type: 'success',
					text: data.result.message
				});
			});
		}
	},
	{}
);
