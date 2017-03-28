/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Settings_SalesProcesses_Index_Js", {}, {
	registerChangeVal: function (content) {
		var thisInstance = this;
		content.on('change', '.configField', function (e) {
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if (target.attr('type') == 'checkbox') {
				params['val'] = this.checked;
			} else {
				params['val'] = target.val() != null ? target.val() : '';
			}
			app.saveAjax('updateConfig', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
				if (target.attr('type') == 'checkbox') {
					if (params['val']) {
						target.parent().removeClass('btn-default').addClass('btn-success').find('.glyphicon').removeClass('glyphicon-unchecked').addClass('glyphicon-check');
					} else {
						target.parent().removeClass('btn-success').addClass('btn-default').find('.glyphicon').removeClass('glyphicon-check').addClass('glyphicon-unchecked');
					}
				}

			});
		});
	},
	registerEvents: function () {
		var content = $('#salesProcessesContainer');
		this.registerChangeVal(content);
	}
});
