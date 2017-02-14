/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

Vtiger_Detail_Js("Calendar_Detail_Js", {
	deleteRecord: function (deleteRecordActionUrl) {
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function (data) {
			app.showModalWindow($('.typeRemoveModal').clone(), function (container) {
				container.find('.typeSavingBtn').click(function (e) {
					var currentTarget = $(e.currentTarget);
					app.hideModalWindow();
					AppConnector.request(deleteRecordActionUrl + '&ajaxDelete=true&typeRemove=' + currentTarget.data('value')).then(
							function (data) {
								if (data.success == true) {
									window.location.href = data.result;
								} else {
									Vtiger_Helper_Js.showPnotify(data.error.message);
								}
							}
					);
				});
			});
		});
	},
}, {});
