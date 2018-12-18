/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js('Settings_Companies_List_Js', {}, {
	registerButtons: function () {
		$('.contentsDiv').on('click', '.js-register-online', function (e) {
			app.showModalWindow(null, 'index.php?module=YetiForce&parent=Settings&view=RegistrationOnlineModal');
		});
		$('.contentsDiv').on('click', '.js-register-offline', function (e) {
			app.showModalWindow(null, 'index.php?module=YetiForce&parent=Settings&view=RegistrationOfflineModal');
		});
	},
	registerEvents: function () {
		this._super();
		this.registerButtons();
	}
});
