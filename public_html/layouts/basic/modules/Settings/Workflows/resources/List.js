/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Settings_Vtiger_List_Js("Settings_Workflows_List_Js", {

	triggerCreate: function (url) {
		var selectedModule = jQuery('#moduleFilter').val();
		if (selectedModule.length > 0) {
			url += '&source_module=' + selectedModule
		}
		window.location.href = url;
	},

	setChangeStatusTasks: function (e, recordId, status) {
		var changeButtonType = jQuery(e);
		var container = jQuery(e).closest('tr');
		var message = app.vtranslate('LBL_STATUS_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
			var module = app.getModuleName();
			var postData = {
				"module": module,
				"action": "TaskAjax",
				"mode": "changeStatusAllTasks",
				"record": recordId,
				"status": status,
				"parent": app.getParentModuleName()
			}
			var deleteMessage = app.vtranslate('JS_TASKS_STATUS_GETTING_CHANGED');
			var progressIndicatorElement = jQuery.progressIndicator({
				'message': deleteMessage,
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request(postData).done(function (data) {
				progressIndicatorElement.progressIndicator({
					'mode': 'hide'
				})
				if (data.success) {
					var count = data.result.count;
					var element = container.find('[data-name="active_tasks"]');
					changeButtonType.hide();
					if (status) {
						element.html('&nbsp;' + count);
						changeButtonType.closest('td').find('.deactiveTasks').show();
					} else {
						element.html('&nbsp;0');
						changeButtonType.closest('td').find('.activeTasks').show();
					}

				} else {
					var params = {
						text: app.vtranslate(data.error.message),
						title: app.vtranslate('JS_LBL_PERMISSION')
					}
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
		});
	},

}, {

	registerFilterChangeEvent: function () {
		var thisInstance = this;
		jQuery('#moduleFilter').on('change', function (e) {
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: jQuery(e.currentTarget).val()
			}
			//Make the select all count as empty
			jQuery('#recordsCount').val('');
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(params).done(function (data) {
				thisInstance.updatePagination();
			});
		});
	},

	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.listViewEntries', function (e) {
			var editUrl = jQuery(e.currentTarget).find('.fa-edit').closest('a').attr('href');
			window.location.href = editUrl;
		});
	},

	getDefaultParams: function () {
		var pageNumber = jQuery('#pageNumber').val();
		var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var params = {
			'module': module,
			'parent': parent,
			'page': pageNumber,
			'view': "List",
			sourceModule: jQuery('#moduleFilter').val()
		};
		return params;
	},
	registerImportTemplate: function () {
		jQuery('#importButton').on('click', function () {
			window.location.href = jQuery(this).data('url');
		});
	},
	registerEvents: function () {
		this._super();
		this.registerFilterChangeEvent();
		this.registerImportTemplate();
	}
});
