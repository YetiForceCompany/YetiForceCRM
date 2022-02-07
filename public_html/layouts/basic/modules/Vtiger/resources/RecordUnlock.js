/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_RecordUnlock_JS',
	{},
	{
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents(modalContainer) {
			modalContainer.find('form').on('submit', function (e) {
				e.preventDefault();
				const progressIndicator = $.progressIndicator({
					position: 'html',
					blockInfo: { enabled: true }
				});
				AppConnector.request($(this).serializeFormData()).done((response) => {
					let result = response.result;
					if (result.success && result.url) {
						if (CONFIG.view === 'ListPreview') {
							app.hideModalWindow();
							$('.listPreviewframe')[0].src = result.url.replace('view=Detail', 'view=DetailPreview');
							progressIndicator.progressIndicator({ mode: 'hide' });
						} else {
							window.location.href = result.url;
						}
					} else {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
						progressIndicator.progressIndicator({ mode: 'hide' });
					}
				});
			});
		}
	}
);
