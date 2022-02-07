/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_MailSmtp_Detail_Js',
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
					detailView: true,
					record: $(e.currentTarget).data('recordId')
				}).done(function (data) {
					window.location.href = data.result;
				});
			});
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			this.registerRemove();
		}
	}
);
