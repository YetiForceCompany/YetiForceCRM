/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Settings_YetiForce_DownloadLanguageModal_Js.
 * @type {window.Settings_YetiForce_DownloadLanguageModal_Js}
 */
window.Settings_YetiForce_DownloadLanguageModal_Js = class Settings_YetiForce_DownloadLanguageModal_Js {
	/**
	 * Register events.
	 * @param {jQuery} modalContainer
	 */
	registerEvents(modalContainer) {
		modalContainer.find('.js-download').on('click', function (e) {
			let progress = $.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: 'YetiForce',
				parent: 'Settings',
				action: 'DownloadLanguage',
				prefix: $(e.target).data('prefix')
			}).done(function (data) {
				app.showNotify({
					text: data['result']['message'],
					type: data['result']['type']
				});
				if (data['result']['type'] === 'success') {
					location.reload();
				} else {
					progress.progressIndicator({ mode: 'hide' });
				}
			});
		});
	}
};
