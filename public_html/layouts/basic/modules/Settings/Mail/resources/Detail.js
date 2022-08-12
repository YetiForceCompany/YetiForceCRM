/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Mail_Detail_Js',
	{},
	{
		registerRemoveEvents: function () {
			let container = jQuery('.contentsDiv');
			container.on('click', '.js-delete', function () {
				let progressIndicator = jQuery.progressIndicator();
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'DeleteAjax',
					detailView: true,
					record: $('#recordId').val()
				})
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						window.location.href = data.result;
					})
					.fail(function (_error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		registerAcceptanceEvent: function () {
			let container = jQuery('.contentsDiv');
			container.on('click', '.acceptanceRecord', function () {
				let elem = this;
				let progressIndicator = jQuery.progressIndicator();
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SaveAjax',
					mode: 'acceptanceRecord',
					id: $('#recordId').val()
				})
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						Settings_Vtiger_Index_Js.showMessage({ text: data.result.message });
						$(elem).remove();
					})
					.fail(function (_error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		sendMailManually: function () {
			const container = $('.contentsDiv');
			container.on('click', '.sendManually', function () {
				const progressIndicator = $.progressIndicator();
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'SendManuallyAjax',
					id: container.find('#recordId').val()
				})
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						Settings_Vtiger_Index_Js.showMessage({
							text: data.result.message,
							type: data.result.success ? 'success' : 'error'
						});
						if (data.result.success) {
							window.history.back();
						}
					})
					.fail(function (_error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		registerEvents: function () {
			this.registerAcceptanceEvent();
			this.sendMailManually();
			this.registerRemoveEvents();
		}
	}
);
