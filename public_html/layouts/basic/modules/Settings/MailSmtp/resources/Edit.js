/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_MailSmtp_Edit_Js',
	{},
	{
		registerSubmitForm: function () {
			var form = this.getForm();
			form.on('submit', function (e) {
				if (form.validationEngine('validate') === true) {
					var paramsForm = form.serializeFormData();
					var progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					AppConnector.request(paramsForm).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (true == data.result.success) {
							window.location.href = data.result.url;
						} else {
							form.find('.alert').removeClass('d-none');
							form.find('.alert p').text(data.result.message);
						}
					});
					return false;
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},
		/**
		 * Register events to preview password
		 */
		registerPreviewPassword: function () {
			const container = this.getForm();
			const button = container.find('.previewPassword');
			button.on('mousedown', function (e) {
				container.find('[name="' + $(e.currentTarget).data('targetName') + '"]').attr('type', 'text');
			});
			button.on('mouseup', function (e) {
				container.find('[name="' + $(e.currentTarget).data('targetName') + '"]').attr('type', 'password');
			});
			button.on('mouseout', function (e) {
				container.find('[name="' + $(e.currentTarget).data('targetName') + '"]').attr('type', 'password');
			});
		},
		registerSaveSendMail() {
			const form = this.getForm();
			form.find('.js-save-send-mail').on('click', () => {
				if (form.find('.saveMailContent').hasClass('d-none')) {
					form.find('.js-smtp-host').attr('data-validation-engine', 'validate[required]');
					form.find('.js-smtp-port').attr('data-validation-engine', 'validate[required,custom[integer]]');
					form.find('.js-smtp-password').attr('data-validation-engine', 'validate[required]');
					form.find('.js-smtp-username').attr('data-validation-engine', 'validate[required]');
					form.find('.js-smtp-folder').attr('data-validation-engine', 'validate[required]');
					form.find('.saveMailContent').removeClass('d-none');
				} else {
					form.find('.js-smtp-host').removeAttr('data-validation-engine');
					form.find('.js-smtp-port').removeAttr('data-validation-engine');
					form.find('.js-smtp-password').removeAttr('data-validation-engine');
					form.find('.js-smtp-username').removeAttr('data-validation-engine');
					form.find('.js-smtp-folder').removeAttr('data-validation-engine');
					form.find('.saveMailContent').addClass('d-none');
				}
			});
		},
		registerEvents: function () {
			const form = this.getForm();
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
				form.find('[data-inputmask]').inputmask();
			}
			this.registerSubmitForm();
			this.registerPreviewPassword();
			this.registerSaveSendMail();
		}
	}
);
