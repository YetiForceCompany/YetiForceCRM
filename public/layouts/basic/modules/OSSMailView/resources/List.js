/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
Vtiger_List_Js("OSSMailView_List_Js", {
	bindMails: function (url) {
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			if (!confirm(app.vtranslate('JS_BIND_CONFIRM'))) {
				return false;
			}
			var params = {};
			params.data = {module: 'OSSMailView', action: 'BindMails'};
			$.extend(params.data, Vtiger_List_Js.getSelectedRecordsParams());
			params.async = false;
			AppConnector.request(params).then(function (data) {
				Vtiger_Helper_Js.showPnotify({
					text: data.result,
					delay: '4000',
					type: 'success'
				});
			});
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerChangeTypeForm: function () {
		var listInstance = Vtiger_List_Js.getInstance();
		var selectedIds = listInstance.readSelectedIds(true);
		$("#ChangeType").submit(function (event) {
			var mail_type = jQuery('#mail_type').val();
			var save_params = {};
			save_params.data = {module: 'OSSMailView', action: 'ChangeType', data: selectedIds, mail_type: mail_type};
			save_params.async = false;
			AppConnector.request(save_params).then(
					function (data) {
						var params = {
							title: app.vtranslate('JS_MESSAGE'),
							text: data.result,
							animation: 'show',
							type: 'info'
						};
						Vtiger_Helper_Js.showPnotify(params);
						Vtiger_List_Js.clearList();
						listInstance.getListViewRecords();
						app.hideModalWindow();
					}
			);
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
				"type": "POST",
				"url": url,
				"dataType": "html",
				"data": {}
			};
			AppConnector.request(actionParams).then(
					function (data) {
						if (data) {
							app.showModalWindow(data, function (data) {
								thisInstance.triggerChangeTypeForm();
							});
						}
					}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
}, {});
