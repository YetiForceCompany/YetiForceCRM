/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_LayoutEditor_EditField_JS',
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
				app.showConfirmModal({
					text: app.vtranslate('JS_COLUMN_LENGTH_CHANGE_WARNING'),
					confirmedCallback: () => {
						let progressIndicatorElement = $.progressIndicator({
							blockInfo: { enabled: true }
						});
						let form = this.container.find('form');
						AppConnector.request(form.serializeFormData())
							.done(function (_data) {
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
								app.showNotify({ text: app.vtranslate('JS_SAVE_NOTIFY_SUCCESS'), type: 'success' });
								app.hideModalWindow();
							})
							.fail(function (error, err) {
								app.hideModalWindow();
								app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
								app.errorLog(error, err);
							});
					},
					rejectedCallback: () => {
						e.preventDefault();
					}
				});
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
