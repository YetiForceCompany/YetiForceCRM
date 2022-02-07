/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Mail_Autologin_Js',
	{},
	{
		registerChangeUser: function () {
			var container = jQuery('.autologinContainer');
			container.on('change', '.users', function () {
				var row = jQuery(this).closest('tr');
				var users = jQuery(this).val();
				if (users == null) users = [];
				var progressIndicator = jQuery.progressIndicator();
				var params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['action'] = 'SaveAjax';
				params['mode'] = 'updateUsers';
				params['id'] = row.data('id');
				params['user'] = users;
				AppConnector.request(params)
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						var params = {};
						params['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(params);
					})
					.fail(function (error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		registerChangeConfig: function () {
			var container = jQuery('.autologinContainer');
			container.on('change', '.configCheckbox', function () {
				var name = jQuery(this).attr('name');
				var val = this.checked;
				var progressIndicator = jQuery.progressIndicator();
				var params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['action'] = 'SaveAjax';
				params['mode'] = 'updateConfig';
				params['type'] = 'autologin';
				params['name'] = name;
				params['val'] = val;

				AppConnector.request(params)
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						var params = {};
						params['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(params);
					})
					.fail(function (error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		registerEvents: function () {
			var thisInstance = this;
			thisInstance.registerChangeUser();
			thisInstance.registerChangeConfig();
		}
	}
);
