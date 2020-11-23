/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_ApiAddress_Configuration_Js',
	{},
	{
		registerSave: function (container) {
			container.validationEngine(app.validationEngineOptions);
			container.find('.saveGlobal').on('click', (event) => {
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
					.done(function (data) {
						app.showNotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function () {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerConfigModal(container) {
			container.find('.js-show-config-modal').on('click', (e) => {
				const providerName = e.currentTarget.dataset.provider;
				app.showModalWindow(
					null,
					`index.php?module=ApiAddress&parent=Settings&view=ApiConfigModal&provider=${providerName}`,
					(modalContainer) => {
						const form = modalContainer.find('.js-form-validation');
						form.validationEngine(app.validationEngineOptions);
						modalContainer.find('.js-modal__save').on('click', (_) => {
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
								.done(function (data) {
									app.showNotify({
										text: data['result']['message'],
										type: 'success'
									});
									window.location.reload();
								})
								.fail(function () {
									app.showNotify({
										text: app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								});
						});
					}
				);
			});
		},
		registerValidateBtn(container) {
			container.find('.js-validate').on('click', (e) => {
				const currentTarget = $(e.currentTarget);
				let icon = currentTarget.find('.js-validate__icon');
				icon.addClass('fa-spin');
				AppConnector.request({
					module: 'ApiAddress',
					parent: 'Settings',
					action: 'ValidateConfiguration',
					provider: currentTarget.data('provider')
				}).done((data) => {
					icon.removeClass('fa-spin');
					app.showNotify({
						text: data['result']['message'],
						type: data['result']['type']
					});
				});
			});
		},
		registerEvents: function () {
			const container = $('.js-validation-form');
			this.registerConfigModal(container);
			this.registerSave(container);
			this.registerValidateBtn(container);
		}
	}
);
