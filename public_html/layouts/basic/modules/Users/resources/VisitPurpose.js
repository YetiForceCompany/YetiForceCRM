/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
					app.saveAjax('', [], formData).done(function (data) {
						if (data.result) {
							app.hideModalWindow();
						} else {
							app.showNotify(app.vtranslate('JS_ERROR'));
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
