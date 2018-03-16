/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
var Settings_GlobalPermission_Js = {
	savePermissions: function (e) {
		var target = jQuery(e.currentTarget);
		var parent = target.closest('tr');
		var checked = target.attr('checked') ? false : true;
		var params = {
			'module': app.getModuleName(),
			'parent': app.getParentModuleName(),
			'action': 'Save',
			'profileID': parent.data('pid'),
			'globalactionid': target.data('globalactionid'),
			'checked': checked
		}
		AppConnector.request(params).then(
			function (data) {
				var response = data['result'];
				var params = {
					text: response['message'],
					type: 'success',
				};
				Vtiger_Helper_Js.showPnotify(params);
			},
			function (error, err) {
			}
		);
	},
	registerEvents: function () {
		jQuery('.GP_SAVE').change(Settings_GlobalPermission_Js.savePermissions);
	}
}
jQuery(document).ready(function () {
	Settings_GlobalPermission_Js.registerEvents();
})
