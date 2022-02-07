/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Backup_Index_Js',
	{},
	{
		registerDataTableinBackup: function () {
			app.registerDataTables($('.js-data-table'));
		},
		registerEvents: function () {
			this.registerDataTableinBackup();
		}
	}
);
