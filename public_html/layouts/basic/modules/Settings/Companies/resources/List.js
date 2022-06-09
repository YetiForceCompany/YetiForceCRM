/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_Companies_List_Js',
	{},
	{
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
			$('.js-register-check').on('click', (e) => {
				AppConnector.request({
					module: 'YetiForce',
					parent: app.getParentModuleName(),
					action: 'Register',
					mode: 'check'
				}).done(function (data) {
					if (data.result.success === false) {
						app.showNotify({
							text: data.result.message,
							type: 'info'
						});
					} else {
						window.location.reload();
					}
				});
			});
			if (app.getUrlVar('displayModal') === 'online') {
				$('.js-register-online').click();
			}
		},
		/**
		 * Register view events
		 */
		registerEvents: function () {
			this._super();
		}
	}
);
