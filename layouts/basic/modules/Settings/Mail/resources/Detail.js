/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_Detail_Js("Settings_Mail_Detail_Js", {}, {
	registerAcceptanceEvent: function () {
		var thisInstance = this;
		var container = jQuery('.contentsDiv');
		container.on('click', '.acceptanceRecord', function (e) {
			var elem = this
			var progressIndicator = jQuery.progressIndicator();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'acceptanceRecord',
				id:  $('#recordId').val()
			}).then(
					function (data) {
						progressIndicator.progressIndicator({'mode': 'hide'});
						Settings_Vtiger_Index_Js.showMessage({text: data.result.message});
						$(elem).remove()
					},
					function (error) {
						progressIndicator.progressIndicator({'mode': 'hide'});
					}
			);
		});
	},
	sendMailManually: function () {
		var thisInstance = this;
		var container = jQuery('.contentsDiv');
		container.on('click', '.sendManually', function (e) {
			var progressIndicator = jQuery.progressIndicator();
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SendManuallyAjax',
				id: container.find('#recordId').val()
			}).then(
					function (data) {
						progressIndicator.progressIndicator({'mode': 'hide'});
						Settings_Vtiger_Index_Js.showMessage({text: data.result.message});
						container.find('.sendManually').remove()
						container.find('.deleteButton').remove()
					},
					function (error) {
						progressIndicator.progressIndicator({'mode': 'hide'});
					}
			);
		});
	},
	registerEvents: function () {
		this.registerAcceptanceEvent();
		this.sendMailManually();
	}
});
