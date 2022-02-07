/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_Companies_Edit_Js',
	{},
	{
		/**
		 * Register events for form checkbox element
		 */
		registerNewsletter() {
			const form = $('[name="EditCompanies"]');
			form.find('[id$="newsletter"]').on('click', (e) => {
				let inputsContainer = $(e.target).closest('.js-card-body');
				if ($(e.target).prop('checked')) {
					inputsContainer.find('[id$="firstname"]').attr('data-validation-engine', 'validate[required]');
					inputsContainer.find('[id$="lastname"]').attr('data-validation-engine', 'validate[required]');
					inputsContainer.find('[id$="email"]').attr('data-validation-engine', 'validate[required,custom[email]]');
					inputsContainer.find('.js-newsletter-content').removeClass('d-none');
				} else {
					inputsContainer.find('[id$="firstname"]').removeAttr('data-validation-engine').val('');
					inputsContainer.find('[id$="lastname"]').removeAttr('data-validation-engine').val('');
					inputsContainer.find('[id$="email"]').removeAttr('data-validation-engine').val('');
					inputsContainer.find('.js-newsletter-content').addClass('d-none');
				}
			});
		},
		registerSubmitForm: function () {
			var form = this.getForm();
			form.on('submit', function (e) {
				e.preventDefault();
				if (form.validationEngine('validate') === true) {
					app.removeEmptyFilesInput(form[0]);
					var formData = new FormData(form[0]);
					var params = {
						url: 'index.php',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false
					};
					var progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(params).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (true == data.result.success) {
							window.location.href = data.result.url;
						} else {
							Settings_Vtiger_Index_Js.showMessage({ text: data.result.message, type: 'error' });
						}
					});
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},
		registerEvents: function () {
			var form = this.getForm();
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
				form.find('[data-inputmask]').inputmask();
			}
			this.registerSubmitForm();
			this.registerNewsletter();
		}
	}
);
