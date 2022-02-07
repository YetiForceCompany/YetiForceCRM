/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_Inventory_Config_Js',
	{},
	{
		registerChangeVal: function ($container) {
			$container.on('change', 'select', (e) => {
				let $target = $(e.currentTarget);
				let isMultiple = $target.attr('multiple');
				if ($target.validationEngine('validate')) {
					let prevValue = $target.data('prevvalue').toString();
					prevValue = isMultiple ? prevValue.split() : prevValue;
					$target.val(prevValue);
					$target.change();
				} else {
					let value = $target.val();
					value = isMultiple ? value.join() : value.toString();
					let params = {
						param: {
							value: value,
							param: $target.attr('name')
						},
						view: app.getViewName()
					};
					app.saveAjax('saveConfig', params).done((data) => {
						$target.data('prevvalue', value);
						Settings_Vtiger_Index_Js.showMessage({
							type: 'success',
							text: app.vtranslate('JS_SAVE_CHANGES')
						});
					});
				}
			});
		},

		registerValidationsFields: function ($container) {
			let params = app.validationEngineOptionsForRecord;
			$container.validationEngine(params);
		},

		registerEvents: function () {
			let $container = $('#configForm');
			this.registerValidationsFields($container);
			this.registerChangeVal($container);
		}
	}
);

$(document).ready((e) => {
	let instance = new Settings_Inventory_Config_Js();
	instance.registerEvents();
});
