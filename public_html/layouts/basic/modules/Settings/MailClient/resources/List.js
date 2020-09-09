/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_MailClient_List_Js',
	{},
	{
		/**
		 * Register events for removing record
		 */
		registerRemove: function () {
			$('.js-remove').on('click', function (e) {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'DeleteAjax',
					record: $(e.currentTarget).data('record-id')
				}).done(function (data) {
					window.location.href = data.result;
				});
			});
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			this._super();
			this.registerRemove();
		}
	}
);
