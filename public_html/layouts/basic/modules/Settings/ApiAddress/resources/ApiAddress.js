/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_ApiAddress_Configuration_Js',
	{},
	{
		registerSave: function (container) {
			container.validationEngine(app.validationEngineOptions);
			container.find('.saveGlobal').on('click', (e) => {
				if (!container.validationEngine('validate')) {
					app.formAlignmentAfterValidation(container);
					return false;
				}
				let form = $(e.currentTarget).closest('form');
				let formData = form.serializeFormData();
				let active = {};
				$('[name="active"]').each((i, e) => {
					active[e.dataset.type] = e.checked ? 1 : 0;
				});
				formData['active'] = active;
				formData['module'] = 'ApiAddress';
				formData['parent'] = 'Settings';
				formData['mode'] = 'global';
				formData['action'] = 'SaveConfig';

				AppConnector.request(formData)
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

				app.showModalWindow({
					url: `index.php?module=ApiAddress&parent=Settings&view=ApiConfigModal&provider=${providerName}`,
					cb: (modalContainer) => {
						modalContainer.on('click', '.js-modal__save', () => {
							let form = modalContainer.find('form');
							if (form.validationEngine('validate')) {
								let formData = form.serializeFormData();
								let progress = $.progressIndicator({
									message: app.vtranslate('JS_SAVE_LOADER_INFO'),
									blockInfo: { enabled: true }
								});
								app
									.saveAjax('', [], formData)
									.done(() => {
										window.location.reload();
									})
									.fail(() => {
										app.showNotify({
											text: app.vtranslate('JS_ERROR'),
											type: 'error'
										});
									})
									.always(() => {
										app.hideModalWindow();
										progress.progressIndicator({ mode: 'hide' });
									});
							}
						});
					}
				});
			});
		},
		registerEvents: function () {
			const container = $('.js-validation-form');
			this.registerConfigModal(container);
			this.registerSave(container);
		}
	}
);
