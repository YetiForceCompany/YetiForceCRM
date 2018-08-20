/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_YetiForce_Status_Js', {}, {
	registerEvents() {
		const container = jQuery('.js-Settings-YetiForce-Status-table');
		container.find(".js-vars").on('change', function (e) {
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'Status',
				flagName: e.currentTarget.dataset.flag,
				newParam: e.currentTarget.value
			}).done(function (data) {
				let response = data['result'], params;
				if (response['success']) {
					Vtiger_Helper_Js.showPnotify({
						text: response['message'],
						type: 'info',
					});
				} else {
					Vtiger_Helper_Js.showPnotify({
						text: response['message'],
					});
				}
			}).fail(function (data) {
				Vtiger_Helper_Js.showPnotify({
					text: response['message'],
				});
			});
		});
	}
});