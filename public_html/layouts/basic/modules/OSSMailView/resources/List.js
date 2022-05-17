/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_List_Js(
	'OSSMailView_List_Js',
	{
		bindMails: function (_url) {
			let listInstance = Vtiger_List_Js.getInstance();
			let validationResult = listInstance.checkListRecordSelected();
			if (validationResult != true) {
				app.showConfirmModal({
					text: app.vtranslate('JS_BIND_CONFIRM'),
					confirmedCallback: () => {
						let params = {};
						params.data = { module: 'OSSMailView', action: 'BindMails' };
						$.extend(params.data, Vtiger_List_Js.getSelectedRecordsParams());
						params.async = false;
						AppConnector.request(params).done(function (data) {
							let message = typeof data === 'string' ? JSON.parse(data).result : data.result;
							app.showNotify({
								text: message,
								delay: '4000',
								type: 'success'
							});
						});
					}
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		triggerChangeTypeForm: function () {
			var listInstance = Vtiger_List_Js.getInstance();
			var selectedIds = listInstance.readSelectedIds(true);
			$('#ChangeType').on('submit', function (event) {
				var mail_type = jQuery('#mail_type').val();
				var save_params = {};
				save_params.data = {
					module: 'OSSMailView',
					action: 'ChangeType',
					data: selectedIds,
					mail_type: mail_type
				};
				save_params.async = false;
				AppConnector.request(save_params).done(function (data) {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: data.result,
						type: 'info'
					};
					app.showNotify(params);
					Vtiger_List_Js.clearList();
					listInstance.getListViewRecords();
					app.hideModalWindow();
				});
				event.preventDefault();
			});
		},
		triggerChangeType: function (url) {
			var thisInstance = this;
			var listInstance = Vtiger_List_Js.getInstance();
			var validationResult = listInstance.checkListRecordSelected();
			if (validationResult != true) {
				thisInstance.getRelatedModulesContainer = false;
				var actionParams = {
					type: 'POST',
					url: url,
					dataType: 'html',
					data: {}
				};
				AppConnector.request(actionParams).done(function (data) {
					if (data) {
						app.showModalWindow(data, function (data) {
							thisInstance.triggerChangeTypeForm();
						});
					}
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		}
	},
	{}
);
