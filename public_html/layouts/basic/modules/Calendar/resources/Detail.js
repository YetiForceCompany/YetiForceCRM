/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'Calendar_Detail_Js',
	{
		deleteRecord: function (deleteRecordActionUrl) {
			app.showConfirmModal({
				text: app.vtranslate('LBL_DELETE_CONFIRMATION'),
				confirmedCallback: () => {
					app.showModalWindow($('.typeRemoveModal').clone(), function (container) {
						container.find('.typeSavingBtn').on('click', function (e) {
							var currentTarget = $(e.currentTarget);
							app.hideModalWindow();
							AppConnector.request(deleteRecordActionUrl + '&typeRemove=' + currentTarget.data('value')).done(function (
								data
							) {
								if (data.success == true) {
									window.location.href = data.result;
								} else {
									app.showNotify({
										text: data.error.message,
										type: 'error'
									});
								}
							});
						});
					});
				}
			});
		}
	},
	{}
);
