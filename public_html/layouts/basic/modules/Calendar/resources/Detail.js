/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js("Calendar_Detail_Js", {
	deleteRecord: function (deleteRecordActionUrl) {
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (data) {
			app.showModalWindow($('.typeRemoveModal').clone(), function (container) {
				container.find('.typeSavingBtn').on('click', function (e) {
					var currentTarget = $(e.currentTarget);
					app.hideModalWindow();
					AppConnector.request(deleteRecordActionUrl + '&typeRemove=' + currentTarget.data('value')).done(function (data) {
						if (data.success == true) {
							window.location.href = data.result;
						} else {
							Vtiger_Helper_Js.showPnotify(data.error.message);
						}
					});
				});
			});
		});
	},
}, {});
