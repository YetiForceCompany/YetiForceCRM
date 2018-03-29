/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

jQuery.Class('Settings_RealizationProcesses_Js', {}, {
	/**
	 * Saves config to database
	 */
	saveConfig: function () {
		jQuery('.js-config-field').on('change', function () {
			var status = jQuery(this).val();
			AppConnector.request({
				module: 'RealizationProcesses',
				parent: 'Settings',
				action: 'SaveGeneral',
				status: status,
				moduleId: jQuery(this).data('moduleid'),
				mode: 'save'
			}).then(function (data) {
				var response = data['result'];
				if (response['success']) {
					var params = {
						text: app.vtranslate(response.message),
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					var params = {
						text: app.vtranslate(response.message),
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			}
			);
		});
	},

});

jQuery(document).ready(function () {
	var instance = new Settings_RealizationProcesses_Js();
	instance.saveConfig();
})
