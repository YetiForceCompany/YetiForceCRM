/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_YetiForce_Status_Js', {}, {
	registerEvents: function (container) {
		var thisInstance = this;
		if (typeof container === "undefined") {
			container = jQuery('.YetiForceStatusContainer');
		}
		container.find(".YetiForceStatusUrlInput").on('change', function (e) {
			AppConnector.request({
				'module': 'YetiForce',
				'parent': 'Settings',
				'action': 'Status',
				'type': 'url',
				'newUrl': e.currentTarget.value
			}).done(function (data) {
				var response = data['result'], params;
				if (response['success']) {
					params = {
						text: response['message'],
						type: 'info',
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					params = {
						text: response['message'],
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
		});

		container.find(".YetiForceStatusFlagBool").on('change', function (e) {
			AppConnector.request({
				'module': 'YetiForce',
				'parent': 'Settings',
				'action': 'Status',
				'type': 'flag',
				'flagName': e.currentTarget.dataset.flag,
				'newParam': e.currentTarget.value
			}).done(function (data) {
				var response = data['result'], params;
				if (response['success']) {
					params = {
						text: response['message'],
						type: 'info',
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					params = {
						text: response['message'],
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
		});
	}
});
