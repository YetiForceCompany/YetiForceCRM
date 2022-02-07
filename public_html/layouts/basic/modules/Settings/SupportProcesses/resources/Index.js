/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_SupportProcesses_Index_Js',
	{},
	{
		/**
		 * Register change field value
		 * @param {jQuery} content
		 */
		registerChangeVal: function (content) {
			content.find('.js-config-field').on('change', function (e) {
				var target = $(e.currentTarget);
				var params = {};
				params['type'] = target.data('type');
				params['param'] = target.attr('name');
				if (target.attr('type') == 'checkbox') {
					params['val'] = this.checked;
				} else {
					params['val'] = target.val() != null ? target.val() : '';
				}
				app.saveAjax('updateConfig', params).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
				});
			});
		},
		registerEvents: function () {
			var content = $('.supportProcessesContainer');
			this.registerChangeVal(content);
		}
	}
);
