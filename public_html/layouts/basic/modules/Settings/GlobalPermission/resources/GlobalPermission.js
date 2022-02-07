/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

var Settings_GlobalPermission_Js = {
	savePermissions: function (e) {
		var target = jQuery(e.currentTarget);
		var parent = target.closest('.js-row');
		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'Save',
			profileID: parent.data('pid'),
			globalactionid: target.data('globalactionid'),
			checked: target.prop('checked')
		};
		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			var params = {
				text: response['message'],
				type: 'success'
			};
			app.showNotify(params);
		});
	},
	registerEvents: function () {
		jQuery('.js-save').on('change', Settings_GlobalPermission_Js.savePermissions);
	}
};
jQuery(document).ready(function () {
	Settings_GlobalPermission_Js.registerEvents();
});
