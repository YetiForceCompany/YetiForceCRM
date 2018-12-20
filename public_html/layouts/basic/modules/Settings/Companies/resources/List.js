/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js('Settings_Companies_List_Js', {}, {
	registerButtons: function () {
		$('.contentsDiv').on('click', '.js-send', function (e) {

		});
	},
	registerEvents: function () {
		this._super();
		this.registerButtons();
	}
});
