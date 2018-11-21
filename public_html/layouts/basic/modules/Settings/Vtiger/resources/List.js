/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_List_Js("Settings_Vtiger_List_Js", {
	triggerDelete: function (event, url) {
		event.stopPropagation();
		var instance = Vtiger_List_Js.getInstance();
		instance.DeleteRecord(url);
	},
	deleteById: function (id) {
		var aDeferred = jQuery.Deferred();
		var instance = Vtiger_List_Js.getInstance();
		var params = $.extend(instance.getDeleteParams(), {
			record: id,
		});
		AppConnector.request(params).done(function (data) {
			if (data.success) {
				$('#recordsCount').val('');
				$('#totalPageCount').text('');
				instance.getListViewRecords().done(function () {
					instance.updatePagination();
				});
			}
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
}, {
	getDeleteParams: function () {
		return {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: "DeleteAjax"
		};
	},
	/*
	 * Function to register the list view container
	 */
	getListViewContainer() {
		if (this.listViewContainer == false) {
			this.listViewContainer = $('div.listViewPageDiv');
		}
		return this.listViewContainer;
	},

	/*
	 * Function to register the list view delete record click event
	 */
	DeleteRecord: function (url) {
		var thisInstance = this;

		AppConnector.request(url).done(function (data) {
			if (data) {
				app.showModalWindow(data, function (container) {
					thisInstance.postDeleteAction(container);
				});
			}
		});
	},

	/**
	 * Function to load list view after deletion of record from list view
	 */
	postDeleteAction: function (container) {
		var thisInstance = this;
		var deleteConfirmForm = jQuery(container).find('#DeleteModal');
		deleteConfirmForm.on('submit', function (e) {
			e.preventDefault();
			var deleteActionUrl = deleteConfirmForm.serializeFormData();
			AppConnector.request(deleteActionUrl).done(function () {
				app.hideModalWindow();
				var params = {
					text: app.vtranslate('JS_RECORD_DELETED_SUCCESSFULLY')
				};
				Settings_Vtiger_Index_Js.showMessage(params);
				jQuery('#recordsCount').val('');
				jQuery('#totalPageCount').text('');
				thisInstance.getListViewRecords().done(function () {
					thisInstance.updatePagination();
				});
			}).fail(function (error, err) {
				app.hideModalWindow();
			});
		});
	},

	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams: function () {
		var module = app.getModuleName();
		var cvId = this.getCurrentCvId();
		var pageCountParams = {
			'module': module,
			'parent': "Settings",
			'action': "ListAjax",
			'mode': "getPageCount",
			"viewname": cvId
		}
		var sourceModule = jQuery('#moduleFilter').val();
		if (typeof sourceModule !== "undefined") {
			pageCountParams['sourceModule'] = sourceModule;
		}
		return pageCountParams;
	},
	registerEvents: function () {
		//this.triggerDisplayTypeEvent();
		this.registerRowClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerHeadersClickEvent();
		this.registerPageNavigationEvents();
		this.registerEventForTotalRecordsCount();
	}
});
