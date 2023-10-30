/* {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_Companies_EmailVerificationModal_Js',
	{},
	{
		/** Modal container */
		container: false,
		/** Modal ID */
		modalId: false,
		/** Source field */
		sourceField: false,

		/** Toggle elements */
		toggleForms: function () {
			this.container.find('.js-send-verification-email,.js-check-verification-code').toggle(250);
		},

		/** Request sending an email with a verification code */
		registerSendVerificationEmail: function () {
			this.container.on('click', '.js-email-verification-request-modal__save', (e) => {
				e.preventDefault();
				let form = this.container.find('form');
				if (!form.validationEngine('validate')) {
					return;
				}
				let formData = form.serializeFormData();
				const progressIndicator = $.progressIndicator({
					blockInfo: { enabled: true }
				});
				app.saveAjax('request', [], formData).done((data) => {
					progressIndicator.progressIndicator({ mode: 'hide' });
					if (data.result && data.result.success) {
						this.toggleForms();
					} else {
						app.showNotify({
							text: data?.result?.message,
							type: data?.result?.type,
							hide: true,
							delay: 8000,
							textTrusted: false
						});
					}
				});
			});
		},

		/** Register an email address */
		registerCheckVerificationCode: function () {
			this.container.find('button.js-email-verification-modal__back').on('click', () => {
				this.container.find('input:visible').val('');
				this.toggleForms();
			});

			this.container.on('click', '.js-email-verification-confirm-modal__save', (e) => {
				e.preventDefault();
				let form = this.container.find('form');
				if (!form.validationEngine('validate')) {
					return false;
				}

				let formData = form.serializeFormData();
				const progressIndicator = $.progressIndicator({
					blockInfo: { enabled: true }
				});
				app.saveAjax('confirm', [], formData).done((data) => {
					progressIndicator.progressIndicator({ mode: 'hide' });
					if (data.success && data.result) {
						app.showNotify({
							text: data.result.message,
							type: data.result.type,
							hide: true,
							delay: 8000,
							textTrusted: false
						});

						if (data.result.success) {
							this.getSourceField().val(formData['email']);
							app.hideModalWindow(null, this.container.parent().attr('id'));
						}
					}
				});
			});
		},

		/**
		 * Gets basic field
		 */
		getSourceField: function () {
			if (this.sourceField === false) {
				this.sourceField = $('[data-modalid=' + this.modalId + ']')
					.closest('.js-field-block-column')
					.find('input[name="email"]');
			}

			return this.sourceField;
		},

		/** @inheritdoc */
		registerEvents: function (modal) {
			this.container = modal;
			this.modalId = modal.closest('.js-modal-container').attr('id');
			this.registerSendVerificationEmail();
			this.registerCheckVerificationCode();
		}
	}
);
