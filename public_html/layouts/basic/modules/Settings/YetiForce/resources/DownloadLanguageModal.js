/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_YetiForce_DownloadLanguage_Js', {
	registerEvents() {
		let container = $('.js-modal-data');
		container.find('.js-download').on('click', function (e) {
			let progress = $.progressIndicator({
				'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request({
				module: 'YetiForce',
				parent: 'Settings',
				action: 'DownloadLanguage',
				prefix: $(e.target).data('prefix')
			}).done(function (data) {
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: data['result']['type']
				});
				if (data['result']['type'] === 'success') {
					location.reload();
				} else {
					progress.progressIndicator({'mode': 'hide'});
				}
			});
		});
	}
}, {});
Settings_YetiForce_DownloadLanguage_Js.registerEvents();
