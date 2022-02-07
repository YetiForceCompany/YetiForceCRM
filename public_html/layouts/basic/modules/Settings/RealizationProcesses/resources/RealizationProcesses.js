/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_RealizationProcesses_Js',
	{},
	{
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
				}).done(function (data) {
					var response = data['result'],
						params;
					if (response['success']) {
						params = {
							text: app.vtranslate(response.message),
							type: 'success'
						};
						app.showNotify(params);
					} else {
						params = {
							text: app.vtranslate(response.message),
							type: 'error'
						};
						app.showNotify(params);
					}
				});
			});
		}
	}
);

jQuery(document).ready(function () {
	var instance = new Settings_RealizationProcesses_Js();
	instance.saveConfig();
});
