/* {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_EditModal_Js(
	'Settings_Companies_EditModal_Js',
	{},
	{
		registerSubmitForm: function (form) {
			form.on('submit', function (e) {
				e.preventDefault();
				if (form.validationEngine('validate') === true) {
					let formData = new FormData(form[0]);
					let params = {
						url: 'index.php',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false
					};
					let progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(params).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (true === data.result.success) {
							window.location.href = data.result.url;
						} else {
							app.showNotify({
								text: data.result.message,
								type: 'error'
							});
						}
					});
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},

		/**
		 * Register events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			if (!modalContainer) {
				return false;
			}
			let form = modalContainer.find('form');
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
				form.find('[data-inputmask]').inputmask();
			}
			this.registerSubmitForm(form);
		}
	}
);
