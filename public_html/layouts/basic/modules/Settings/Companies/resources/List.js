/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js('Settings_Companies_List_Js', {}, {
	/**
	 * Init registration buttons
	 */
	registerButtons() {
		$('.js-register-online').on('click', (e) => {
			app.showModalWindow(null, 'index.php?module=YetiForce&parent=Settings&view=RegistrationOnlineModal');
		});
		$('.js-register-serial').on('click', (e) => {
			app.showModalWindow(null, 'index.php?module=YetiForce&parent=Settings&view=RegistrationSerialModal');
		});
	},
	/**
	 * Register view events
	 */
	registerEvents: function () {
		this._super();
		this.registerButtons();
	}
});
