/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Password_Encryption_Js',
	{},
	{
		/**
		 * Container (Form)
		 */
		container: null,
		/**
		 * Set container (Form)
		 * @param {Object} element
		 */
		setContainer: function (element) {
			this.container = element;
		},
		/**
		 * Get Container (Form)
		 * @returns {Object}
		 */
		getContainer: function () {
			return this.container;
		},
		/**
		 * Register events for change method encryption
		 */
		registerChangeMethodName: function () {
			const self = this;
			let container = this.getContainer();
			this.passwordAlert(container);
			container.find('[name="methods"]').on('change', function () {
				self.passwordAlert(container);
			});
		},
		passwordAlert: function (container) {
			let methodElement = container.find('[name="methods"]');
			let mapLengthVector = JSON.parse($('[name="lengthVectors"]').val());
			let length = mapLengthVector[methodElement.val()];
			let validator = '';
			let passwordElement = container.find('#password');
			let vectorElement = container.find('#vector');
			let passwordInfoAlert = container.find('.js-password-alert');
			if (length == undefined) {
				passwordInfoAlert.addClass('d-none');
				vectorElement.attr('disabled', 'disabled');
				passwordElement.attr('disabled', 'disabled');
			} else if (length === 0) {
				passwordInfoAlert.addClass('d-none');
				vectorElement.val('');
				vectorElement.attr('disabled', 'disabled');
			} else {
				passwordInfoAlert.removeClass('d-none');
				passwordInfoAlert.find('.js-password-length').text(length);
				vectorElement.removeAttr('disabled');
				passwordElement.removeAttr('disabled');
				validator = 'validate[required,maxSize[' + length + '],minSize[' + length + ']]';
			}
			vectorElement.attr('data-validation-engine', validator);
		},
		/**
		 * Register events for form
		 */
		registerForm: function () {
			var thisInstance = this;
			var container = thisInstance.getContainer();
			container.on('submit', function (event) {
				event.preventDefault();
				container.validationEngine(app.validationEngineOptions);
				if (container.validationEngine('validate')) {
					var progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request(container.serializeFormData()).done(function (response) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.showNotify({
							text: response.result,
							type: 'info',
							hide: false
						});
					});
				}
			});
		},
		/**
		 * Register events to preview password
		 */
		registerPreviewPassword: function () {
			var container = this.getContainer();
			var button = container.find('.previewPassword');
			button.on('mousedown', function () {
				$('#' + $(this).data('id')).attr('type', 'text');
			});
			button.on('mouseup', function () {
				$('#' + $(this).data('id')).attr('type', 'password');
			});
			button.on('mouseout', function () {
				$('#' + $(this).data('id')).attr('type', 'password');
			});
		},
		/**
		 * Register all events in view
		 */
		registerEvents: function () {
			this.setContainer($('.formEncryption'));
			this.registerForm();
			this.registerChangeMethodName();
			this.registerPreviewPassword();
		}
	}
);
