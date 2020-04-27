/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_Inventory_Config_Js", {}, {
	registerChangeVal: function(content) {
		content.find('select').on('change', function (e) {
			let target = $(e.currentTarget);
			let isMultiple = target.attr('multiple');
			let isRequired = target.attr('required');
			let prevValue = target.data('prevvalue') != null ? target.data('prevvalue').toString() : '';
			let value = target.val();
			let params = {};

			if (isRequired && value == '') {
				prevValue = isMultiple ? prevValue.split() : prevValue;
				target.val(prevValue);
				target.change();
				Settings_Vtiger_Index_Js.showMessage({type: 'error', text: app.vtranslate('JS_IS_MANDATORY')});
			} else {
				value = isMultiple ? value.join() : value;
				params['param'] = {
					'value': value,
					'param': target.attr('name')
				};
				params['view'] = app.getViewName();
				app.saveAjax('saveConfig', params).done(function (data) {
					target.data('prevvalue', value);
					Settings_Vtiger_Index_Js.showMessage({type: 'success', text: app.vtranslate('JS_SAVE_CHANGES')});
				});
			}
		});
	},

	registerEvents: function () {
		var content = jQuery('#inventoryConfig');
		this.registerChangeVal(content);
	}

});

jQuery(document).ready(function (e) {
	var instance = new Settings_Inventory_Config_Js();
	instance.registerEvents();
})
