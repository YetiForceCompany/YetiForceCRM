/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_ConfReport_Index_Js',
	{},
	{
		/*
		 * Shows or hides block informing about supported currencies by presently select bank
		 */
		registerButtons: function (container) {
			container.find('.js-test-speed').on('click', function () {
				let progress = jQuery.progressIndicator({
					message: app.vtranslate('JS_SPEED_TEST_START'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					parent: 'Settings',
					module: 'ConfReport',
					view: 'Speed'
				})
					.done(function (response) {
						app.showModalWindow(response);
						progress.progressIndicator({ mode: 'hide' });
					})
					.fail(function (data, err) {
						progress.progressIndicator({ mode: 'hide' });
					});
			});
			container.find('.js-check-php').on('click', function () {
				AppConnector.request({
					parent: 'Settings',
					module: 'ConfReport',
					action: 'Check'
				}).done(function (response) {
					if (response.success) {
						app.showNotify({
							title: response.result.title,
							text: response.result.text,
							type: 'info'
						});
					}
				});
			});
			container.find('.js-db-info').on('click', function () {
				let progress = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					parent: 'Settings',
					module: 'ConfReport',
					view: 'DbInfo'
				})
					.done(function (response) {
						app.showModalWindow(response, '', (modalContainer) => {
							app.registerDataTables(modalContainer.find('.js-db-info-table'), {
								searching: true,
								lengthMenu: [
									[10, 25, 50, 100, -1],
									[10, 25, 50, 100, app.vtranslate('JS_ALL')]
								]
							});
						});
						progress.progressIndicator({ mode: 'hide' });
					})
					.fail(function (data, err) {
						progress.progressIndicator({ mode: 'hide' });
					});
			});
		},
		/**
		 * Register events
		 */
		registerEvents: function () {
			let container = $('.contentsDiv');
			this.registerButtons(container);
		}
	}
);
