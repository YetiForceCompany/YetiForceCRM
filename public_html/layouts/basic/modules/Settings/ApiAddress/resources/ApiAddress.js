/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_ApiAddress_Configuration_Js',
	{},
	{
		registerSave: function(container) {
			container.validationEngine(app.validationEngineOptions);
			container.find('.saveGlobal').on('click', event => {
				if (!container.validationEngine('validate')) {
					app.formAlignmentAfterValidation(container);
					return false;
				}
				const defaultProvider = $('[name="default_provider"]:checked');
				let elements = {
					global: {
						min_length: $('[name="min_length"]').val(),
						result_num: $('[name="result_num"]').val(),
						default_provider: defaultProvider.length ? defaultProvider.val() : 0
					}
				};
				$('[name="active"]').each((i, e) => {
					elements[e.dataset.type] = { active: e.checked ? 1 : 0 };
				});
				AppConnector.request({
					data: {
						module: 'ApiAddress',
						parent: 'Settings',
						action: 'SaveConfig',
						elements: elements
					},
					async: false,
					dataType: 'json'
				})
					.done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function() {
						Vtiger_Helper_Js.showPnotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerConfigModal(container) {
			container.find('.js-show-config-modal').on('click', e => {
				const providerName = e.currentTarget.dataset.provider;
				app.showModalWindow(
					null,
					`index.php?module=ApiAddress&parent=Settings&view=ApiConfigModal&provider=${providerName}`,
					modalContainer => {
						const form = modalContainer.find('.js-form-validation');
						form.validationEngine(app.validationEngineOptions);
						modalContainer.find('.js-modal__save').on('click', _ => {
							if (!form.validationEngine('validate')) {
								app.formAlignmentAfterValidation(container);
								return false;
							}
							let elements = {};
							let customField = modalContainer.find('.js-custom-field');
							customField.each((i, e) => {
								elements[$(e).attr('name')] = e.value;
							});
							elements = { [providerName]: elements };
							AppConnector.request({
								data: {
									module: 'ApiAddress',
									parent: 'Settings',
									action: 'SaveConfig',
									elements: elements
								},
								async: false,
								dataType: 'json'
							})
								.done(function(data) {
									Vtiger_Helper_Js.showPnotify({
										text: data['result']['message'],
										type: 'success'
									});
									window.location.reload();
								})
								.fail(function() {
									Vtiger_Helper_Js.showPnotify({
										text: app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								});
						});
					}
				);
			});
		},
		registerEvents: function() {
			const container = $('.js-validation-form');
			this.registerConfigModal(container);
			this.registerSave(container);
		}
	}
);

Vtiger_Base_Validator_Js(
	'Vtiger_CountryCode_Validator_Js',
	{
		/**
		 *Function which invokes field validation
		 *@param accepts field element as parameter
		 * @return error if validation fails true on success
		 */
		invokeValidation: function(field, rules, i, options) {
			var validatorInstance = new Vtiger_CountryCode_Validator_Js();
			validatorInstance.setElement(field);
			const result = validatorInstance.validate();
			if (result === true) {
				return result;
			} else {
				return validatorInstance.getError();
			}
		}
	},
	{
		/**
		 * Function to validate country codes
		 * @return true if validation is successfull
		 * @return false if validation error occurs
		 */
		validate: function() {
			let response = this._super();
			if (!response) {
				return response;
			}
			const fieldValue = this.getFieldValue();
			const regex = /^[a-z]{2}(?:,[a-z]{2})*$/i;
			if (!regex.test(fieldValue)) {
				this.setError(app.vtranslate('JS_INVALID_COUNTRY_CODES'));
				return false;
			}
			return true;
		}
	}
);
