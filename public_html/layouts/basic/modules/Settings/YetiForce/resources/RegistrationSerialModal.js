/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Settings_YetiForce_RegistrationSerialModal_Js.
 * @type {window.Settings_YetiForce_RegistrationSerialModal_Js}
 */
window.Settings_YetiForce_RegistrationSerialModal_Js = class Settings_YetiForce_RegistrationSerialModal_Js {
	/**
	 * Register events.
	 * @param {jQuery} modalContainer
	 */
	registerEvents(modalContainer) {
		const form = modalContainer.find('form');
		form.validationEngine(app.validationEngineOptions);
		form.on('submit', function (e) {
			e.preventDefault();
			modalContainer.find('[name="saveButton"]').click();
		});
		modalContainer.find('[name="saveButton"]').on('click', function (e) {
			if (!form.validationEngine('validate')) {
				e.preventDefault();
				app.showNotify({
					text: app.vtranslate('JS_ENTER_REGISTRATION_KEY'),
					type: 'error'
				});
				return false;
			}
			modalContainer.find('button[name=saveButton]').prop('disabled', true);
			var progress = $.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: 'YetiForce',
				parent: 'Settings',
				action: 'Register',
				mode: 'serial',
				key: modalContainer.find('.registrationKey').val()
			}).done(function (data) {
				app.showNotify({
					text: data['result']['message'],
					type: data['result']['type']
				});
				progress.progressIndicator({ mode: 'hide' });
				if (data['result']['type'] === 'success') {
					app.hideModalWindow();
				}
				modalContainer.find('button[name=saveButton]').prop('disabled', false);
				return data['result'];
			});
		});
	}
};
