/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Watchdog_Index_Js',
	{},
	{
		registerEvents() {
			const container = $('.js-watchdog-container');
			container.find('.js-vars').on('change', function (e) {
				let field = $(this);
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					flagName: field.data('flag'),
					newParam: field.val()
				})
					.done(function (data) {
						let response = data['result'];
						if (response['success']) {
							Vtiger_Helper_Js.showPnotify({
								text: response['message'],
								type: 'info'
							});
						} else {
							Vtiger_Helper_Js.showPnotify({
								text: response['message']
							});
						}
					})
					.fail(function (data) {
						Vtiger_Helper_Js.showPnotify({
							text: response['message']
						});
					});
			});
		}
	}
);
