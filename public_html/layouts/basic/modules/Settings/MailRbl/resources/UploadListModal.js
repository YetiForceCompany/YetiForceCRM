/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_UploadListModal_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,

		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			let form = modalContainer.find('.js-import-list');
			this.registerUploadButton(form);
		},

		/**
		 * Register button send form
		 * @param {jQuery} form
		 */
		registerUploadButton: function (form) {
			form.on('submit', function (e) {
				e.preventDefault();
				if (form.validationEngine('validate') === true) {
					let formData = new FormData(form[0]),
						progressIndicatorElement = jQuery.progressIndicator({
							blockInfo: { enabled: true }
						}),
						notifyType = '';
					AppConnector.request({
						url: 'index.php',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false
					}).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (true === data.result.success) {
							notifyType = 'success';
						} else {
							notifyType = 'error';
						}
						app.showNotify({
							text: app.vtranslate(data.result.message),
							type: notifyType
						});
						app.hideModalWindow();
					});
				}
			});
		}
	}
);
