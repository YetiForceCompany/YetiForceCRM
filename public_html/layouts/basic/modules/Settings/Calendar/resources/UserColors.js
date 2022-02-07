/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

var Settings_UserColors_Js = {
	initEvants: function () {
		$('.UserColors #update_event').on('click', Settings_UserColors_Js.updateEvent);
	},
	updateEvent: function (e) {
		var progress = $.progressIndicator({
			message: app.vtranslate('Update labels'),
			blockInfo: {
				enabled: true
			}
		});
		var target = $(e.currentTarget);
		var metod = target.data('metod');
		var value = 0;
		if (target.prop('checked')) {
			value = 1;
		}
		var params = {};
		params.color = value;
		params.id = target.attr('id');
		params = jQuery.extend({}, params);
		Settings_UserColors_Js.registerSaveEvent(metod, params);
		progress.progressIndicator({ mode: 'hide' });
	},
	registerSaveEvent: function (mode, data) {
		var params = {};
		params.data = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: mode,
			params: data
		};
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			var params = {
				text: response['message'],
				type: 'success'
			};
			app.hideModalWindow();
			app.showNotify(params);
			return response;
		});
	},
	registerSaveWorkingDays: function (content) {
		content.find('.workignDaysField').on('change', function (e) {
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if (target.attr('type') == 'checkbox') {
				params['val'] = this.checked;
			} else {
				params['val'] = target.val();
			}
			app.saveAjax('updateNotWorkingDays', params).done(function (data) {
				Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
			});
		});
	},
	registerEvents: function () {
		Settings_UserColors_Js.initEvants();
		var content = $('.workingDaysTable');
		this.registerSaveWorkingDays(content);
	}
};
jQuery(function () {
	Settings_UserColors_Js.registerEvents();
});
