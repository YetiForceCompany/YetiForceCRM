/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_ModTracker_Js', {}, {
	registerActiveEvent: function () {
		var modTrackerContainer = jQuery('#modTrackerContainer');
		modTrackerContainer.on('change', '.activeModTracker', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var tr = currentTarget.closest('tr');
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'Save';
			params['mode'] = 'changeActiveStatus';
			params['id'] = tr.data('id');
			params['status'] = currentTarget.attr('checked') == 'checked';

			AppConnector.request(params).then(
				function (data) {
					var params = {};
					params['text'] = data.result.message;
					Settings_Vtiger_Index_Js.showMessage(params);
				},
				function (error) {
					var params = {};
					params['text'] = error;
					Settings_Vtiger_Index_Js.showMessage(params);
				}
			);
		})
	},

	/**
	 * Function to register events
	 */
	registerEvents: function () {
		this.registerActiveEvent();
	}
})
jQuery(document).ready(function () {
	var settingModTrackerInstance = new Settings_ModTracker_Js();
	settingModTrackerInstance.registerEvents();
})
