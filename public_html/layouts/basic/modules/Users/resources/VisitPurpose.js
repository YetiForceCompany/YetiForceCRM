/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Users_VisitPurpose_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,

		/**
		 * Function to handle sending the AJAX form
		 */
		registerSave() {
			this.container.find('.js-modal__save').on('click', (e) => {
				let form = this.container.find('form');
				e.preventDefault();
				if (form.validationEngine('validate')) {
					let progress = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						blockInfo: { enabled: true }
					});
					let formData = form.serializeFormData();
					app.saveAjax('', [], formData).done((data) => {
						if (data.result) {
							let id = this.container.closest('.modalContainer').attr('id');
							app.hideModalWindow(null, id);
						} else {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						}
						progress.progressIndicator({ mode: 'hide' });
					});
				}
			});
		},
		/**
		 * Register base events
		 * @param {jQuery} modalContainer
		 */
		registerEvents(modalContainer) {
			this.container = modalContainer;
			this.registerSave();
			setTimeout((_) => {
				this.container.find('textarea').focus();
			}, 400);
		}
	}
);
