/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_Detail_Js("Settings_Mail_Detail_Js", {}, {
	registerAcceptanceEvent: function () {
		var thisInstance = this;
		var container = jQuery('.contentsDiv');
		container.on('click', '.acceptanceRecord', function (e) {
			var elem = this
			var progressIndicator = jQuery.progressIndicator();
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SaveAjax';
			params['mode'] = 'acceptanceRecord';
			params['id'] = $('#recordId').val();
			AppConnector.request(params).then(
					function (data) {
						progressIndicator.progressIndicator({'mode': 'hide'});
						var params = {};
						params['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(params);
						$(elem).remove()
					},
					function (error) {
						progressIndicator.progressIndicator({'mode': 'hide'});
					}
			);
		});
	},
	sendMailManually: function (id) {
		var thisInstance = this;
		var container = jQuery('.contentsDiv');
		container.on('click', '.sendManually', function (e) {
			var progressIndicator = jQuery.progressIndicator();
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SendManuallyAjax';
			params['id'] = $('#recordId').val();
			AppConnector.request(params).then(
					function (data) {
						progressIndicator.progressIndicator({'mode': 'hide'});
						var params = {};
						params['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(params);
						$('.sendManually').remove()
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
