/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_SMSNotifier_Create_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,

		/**
		 * Register save
		 */
		registerSave: function () {
			this.container.find('.js-modal__save').on('click', (e) => {
				e.preventDefault();
				let form = this.container.find('form');
				if (form.validationEngine('validate')) {
					let progress = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						blockInfo: { enabled: true }
					});
					let url = form.find('[name="provider"]').val();
					app.hideModalWindow();
					app.showModalWindow({
						url: url,
						cb: () => {
							progress.progressIndicator({ mode: 'hide' });
						}
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
			this.registerSave();
		}
	}
);
