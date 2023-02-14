/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Groups_Delete_Js',
	{},
	{
		/**
		 * Register delete
		 */
		registerDelete: function () {
			this.container.find('.js-modal__save').on('click', () => {
				let form = this.container.find('form');
				let progress = $.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					blockInfo: { enabled: true }
				});
				let formData = form.serializeFormData();
				app.saveAjax('', [], formData).done(function (data) {
					if (data.result) {
						app.showNotify({ text: app.vtranslate('JS_SAVE_SUCCESS') });
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
			});
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.registerDelete();
		}
	}
);
