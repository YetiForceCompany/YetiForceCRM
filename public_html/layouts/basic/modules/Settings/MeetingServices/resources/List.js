/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_MeetingServices_List_Js',
	{},
	{
		/**
		 * Register button to create record
		 */
		registerButtons: function () {
			this._super();
			App.Fields.Text.registerCopyClipboard(this.getListViewContainer(), '.js-clipboard');
		},
		postLoadListViewRecordsEvents: function (container) {
			App.Fields.Text.registerCopyClipboard(container, '.js-clipboard');
		},
		/**
		 * Function to register events
		 */
		registerEvents: function () {
			this._super();
		}
	}
);
