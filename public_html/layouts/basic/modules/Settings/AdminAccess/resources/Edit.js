/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_AdminAccess_Edit_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,

		/**
		 * Register buttons
		 */
		registerButtons: function () {
			this.container.find('.js-modules-select-all, .js-modules-deselect-all').on('click', (e) => {
				let name = e.currentTarget.dataset.name;
				let element = this.container.find(`[name="${name}"],[name="${name}[]"]`);
				element
					.find('option')
					.prop('selected', $(e.currentTarget).hasClass('js-modules-select-all'))
					.parent()
					.trigger('change');
			});
		},

		/**
		 * Register save
		 */
		registerSave: function () {
			this.container.find('.js-modal__save').on('click', (e) => {
				let form = this.container.find('form');
				if (form.validationEngine('validate')) {
					let progress = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						blockInfo: { enabled: true }
					});
					let formData = form.serializeFormData();
					app.saveAjax('', [], formData).done(function (data) {
						if (data.result) {
							Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_SAVE_SUCCESS') });
							$('.js-data-table').DataTable().ajax.reload();
						} else {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						}
						app.hideModalWindow();
						progress.progressIndicator({ mode: 'hide' });
					});
				}
			});
		},

		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.registerButtons();
			this.registerSave();
		}
	}
);
