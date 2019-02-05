/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_Mail_Detail_Js", {}, {

	registerRemoveEvents: function () {
		var container = jQuery('.contentsDiv');
		container.on('click', '.js-delete', function () {
			var progressIndicator = jQuery.progressIndicator();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'DeleteAjax',
				record: $('#recordId').val()
			}).done(function (data) {
				progressIndicator.progressIndicator({mode: 'hide'});
				window.location.href = data.result;
			}).fail(function (error) {
				progressIndicator.progressIndicator({mode: 'hide'});
			});
		});
	},
	registerAcceptanceEvent: function () {
		var container = jQuery('.contentsDiv');
		container.on('click', '.acceptanceRecord', function (e) {
			var elem = this
			var progressIndicator = jQuery.progressIndicator();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'acceptanceRecord',
				id: $('#recordId').val()
			}).done(function (data) {
				progressIndicator.progressIndicator({'mode': 'hide'});
				Settings_Vtiger_Index_Js.showMessage({text: data.result.message});
				$(elem).remove()
			}).fail(function (error) {
				progressIndicator.progressIndicator({'mode': 'hide'});
			});
		});
	},
	sendMailManually: function () {
		var container = jQuery('.contentsDiv');
		container.on('click', '.sendManually', function (e) {
			var progressIndicator = jQuery.progressIndicator();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SendManuallyAjax',
				id: container.find('#recordId').val()
			}).done(function (data) {
				progressIndicator.progressIndicator({'mode': 'hide'});
				Settings_Vtiger_Index_Js.showMessage({text: data.result.message});
				container.find('.sendManually').remove()
				container.find('.deleteButton').remove()
			}).fail(function (error) {
				progressIndicator.progressIndicator({'mode': 'hide'});
			});
		});
	},
	registerEvents: function () {
		this.registerAcceptanceEvent();
		this.sendMailManually();
		this.registerRemoveEvents();
	}
});
