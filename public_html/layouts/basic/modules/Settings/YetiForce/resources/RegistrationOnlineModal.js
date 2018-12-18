/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_YetiForce_RegistrationOnlineModal_Js', {
	registerEvents() {
		const container = $("[data-view='RegistrationOnlineModal']");
		const form = container.find('form');
		form.validationEngine(app.validationEngineOptions);
		form.on('submit', function (e) {
			e.preventDefault();
			container.find('[name="saveButton"]').click();
		});
		container.find('[name="saveButton"]').on('click', function (e) {
			if (!form.validationEngine('validate')) {
				e.preventDefault();
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_ENTER_ALL_REGISTRATION_DATA'),
					type: 'error'
				});
				return false;
			}
			container.find('button[name=saveButton]').prop("disabled", true);
			var progress = $.progressIndicator({
				'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request(form.serializeFormData()).done(function (data) {
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: data['result']['type']
				});
				progress.progressIndicator({'mode': 'hide'});
				if (data['result']['type'] === 'success') {
					app.hideModalWindow();
				}
				container.find('button[name=saveButton]').prop("disabled", false);
				return data['result'];
			});
		});
	}
}, {});
Settings_YetiForce_RegistrationOnlineModal_Js.registerEvents();