/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Vtiger_GenerateModal_Js", {}, {
	registerGenetateButton: function (container) {
		var thisInstance = this;
		container.find('button.genetateButton').on('click', function (e) {
			document.progressLoader = jQuery.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var currentTarget = jQuery(e.currentTarget);
			var actionUrl = currentTarget.data('url');
			var method = jQuery('[name="method"]:checked');
			if (method.length <= 0) {
				window.location.href = actionUrl;
			} else {
				var params = {};
				params.data = {
					module: app.getModuleName(),
					action: 'GenerateRecords',
					records: jQuery('[name="all_records"]').val(),
					template: currentTarget.data('id'),
					target: currentTarget.data('name'),
					method: method.val()
				};
				params.dataType = 'json';
				AppConnector.request(params).then(
						function (data) {
							var response = data['result'];
							if (data['success']) {
								var records = response.ok;
								thisInstance.summary(container, response);
								document.progressLoader.progressIndicator({'mode': 'hide'});
								if (method.val() == 1) {
									for (var i in records) {
										var win = window.open(actionUrl + records[i], '_blank');
									}
								}
							}
						},
						function (data, err) {
							app.errorLog(data, err);
						}
				);
			}
		});
	},
	summary: function (container, data) {
		container.find('.modal-title').text(app.vtranslate('JS_SUMMARY'));
		container.find('.modal-body').html('<div>' + app.vtranslate('JS_SELECTED_RECORDS') + ': <strong>' + data.all + '</strong></div>\n\
									<div>' + app.vtranslate('JS_SUCCESSFULLY_PERFORMED_ACTION_FOR') + ': <strong>' + data.ok.length + '</strong></div>\n\
									<div>' + app.vtranslate('JS_ACTION_FAILED_FOR') + ': <strong>' + data.fail.length + '</strong></div>');
	},
	registerEvents: function () {
		var container = jQuery('.generateMappingModal');
		app.showPopoverElementView(container.find('.popoverTooltip'));
		this.registerGenetateButton(container);
	}

});

jQuery(document).ready(function (e) {
	var instance = new Vtiger_GenerateModal_Js();
	instance.registerEvents();
})
