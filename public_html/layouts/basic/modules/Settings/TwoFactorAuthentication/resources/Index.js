/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_TwoFactorAuthentication_Index_Js',
	{
		checkIP: function (field, rules, i, options) {
			let fieldValue = field.val(),
				negativeRegex =
					/((^\s*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$))/,
				result = negativeRegex.test(fieldValue);
			if (!result) {
				return app.vtranslate('INVALID_NUMBER');
			}
		}
	},
	{
		/**
		 * Container (Form)
		 */
		container: null,
		/**
		 * Get Container (Form)
		 * @returns {Object}
		 */
		getContainer() {
			if (this.container === null) {
				this.container = $('form.js-two-factor-auth__form');
			}
			return this.container;
		},
		/**
		 * Register events for form
		 */
		registerForm() {
			let thisInstance = this;
			this.container.on('change', (event) => {
				event.preventDefault();
				thisInstance.sendForm();
			});
		},
		/**
		 * Send form data
		 */
		sendForm: function () {
			this.container.validationEngine(app.validationEngineOptions);
			if (this.container.validationEngine('validate')) {
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let params = this.container.serializeFormData();
				let ipAddresses = [];
				let ipAddressContainer = this.container.find('.js-ip-container_element').not('.js-base-element');
				ipAddressContainer.find('.js-ip-address').each(function () {
					ipAddresses.push($(this).val());
				});
				params['ip'] = ipAddresses;
				AppConnector.request(params).done((response) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: response.result.message,
						type: 'info'
					});
				});
			}
		},
		/**
		 * Add new row
		 */
		addRow: function () {
			let sortContainer = this.container.find('.js-base-element').clone(true, true).removeClass('js-base-element');
			this.container.find('.js-ip-container').append(sortContainer);
			return sortContainer.removeClass('d-none');
		},
		/**
		 * Register list events
		 */
		registerListEvents: function () {
			this.container.find('.js-add').on('click', (e) => {
				this.addRow();
			});
			this.container.find('.js-clear').on('click', (e) => {
				$(e.currentTarget).closest('.js-ip-container_element').remove();
				this.sendForm();
			});
		},
		/**
		 * Register all events in view
		 */
		registerEvents() {
			this.container = this.getContainer();
			this.registerListEvents();
			this.registerForm();
		}
	}
);
