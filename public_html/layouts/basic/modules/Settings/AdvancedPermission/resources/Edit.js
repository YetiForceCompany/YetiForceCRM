/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_AdvancedPermission_Edit_Js',
	{},
	{
		advanceFilterInstance: false,
		registerAdvanceFilter: function () {
			this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance($('#advanceFilterContainer'));
		},
		registerSubmitEvent: function () {
			var thisInstance = this;
			var form = jQuery('#EditView');
			form.on('submit', function (e) {
				var advfilterlist = thisInstance.advanceFilterInstance.getValues();
				form.find('#advanced_filter').val(JSON.stringify(advfilterlist));
			});
		},
		registerEvents: function () {
			this.registerAdvanceFilter();
			this.registerSubmitEvent();
		}
	}
);
