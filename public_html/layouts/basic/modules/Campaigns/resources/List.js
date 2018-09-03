/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_List_Js("Campaigns_List_Js", {}, {

	readSelectedIds: function (decode) {
		if (app.getViewName() != "Detail") {
			return this._super(decode);
		}
		var selectedIdsElement = jQuery('#selectedIds');
		var selectedIdsDataAttr = 'selectedIds';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		var selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		if (selectedIds == "") {
			selectedIds = [];
			this.writeSelectedIds(selectedIds);
		} else {
			selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		}
		if (decode == true) {
			if (typeof selectedIds == 'object') {
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},

	readExcludedIds: function (decode) {
		if (app.getViewName() != "Detail") {
			return this._super(decode);
		}
		var exlcudedIdsElement = jQuery('#excludedIds');
		var excludedIdsDataAttr = 'excludedIds';
		var excludedIdsElementDataAttributes = exlcudedIdsElement.data();
		var excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		if (excludedIds == "") {
			excludedIds = [];
			this.writeExcludedIds(excludedIds);
		} else {
			excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		}
		if (decode == true) {
			if (typeof excludedIds == 'object') {
				return JSON.stringify(excludedIds);
			}
		}
		return excludedIds;
	},

	writeSelectedIds: function (selectedIds) {
		if (app.getViewName() != "Detail") {
			this._super(selectedIds);
			return;
		}
		jQuery('#selectedIds').data('selectedIds', selectedIds);
	},

	writeExcludedIds: function (excludedIds) {
		if (app.getViewName() != "Detail") {
			this._super(excludedIds);
			return;
		}
		jQuery('#excludedIds').data('excludedIds', excludedIds);
	},

	/**
	 * Function to mark selected records
	 */
	markSelectedRecords: function () {
		var thisInstance = this;
		var selectedIds = this.readSelectedIds();
		if (selectedIds != '') {
			if (selectedIds == 'all') {
				jQuery('.listViewEntriesCheckBox').each(function (index, element) {
					jQuery(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
				});
				jQuery('#deSelectAllMsgDiv').show();
				var excludedIds = jQuery('[name="excludedIds"]').data('excludedIds');
				if (excludedIds != '') {
					jQuery('#listViewEntriesMainCheckBox').prop('checked', false);
					jQuery('.listViewEntriesCheckBox').each(function (index, element) {
						if (jQuery.inArray(jQuery(element).val(), excludedIds) != -1) {
							jQuery(element).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
						}
					});
				}
			} else {
				jQuery('.listViewEntriesCheckBox').each(function (index, element) {
					if (jQuery.inArray(jQuery(element).val(), selectedIds) != -1) {
						jQuery(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
					}
				});
			}
			thisInstance.checkSelectAll();
		}
	},

	getRecordsCount: function () {
		if (app.getViewName() != "Detail") {
			return this._super();
		}
		var detailInstance = Vtiger_Detail_Js.getInstance();
		var aDeferred = jQuery.Deferred();
		var recordCountVal = jQuery("#recordsCount").val();
		if (recordCountVal != '') {
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var cvId = jQuery('#recordsFilter').val();
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var relatedModuleName = jQuery('[name="relatedModuleName"]').val();
			var recordId = app.getRecordId();
			var tab_label = detailInstance.getSelectedTab().data('labelKey');
			AppConnector.request({
				"module": module,
				"parent": parent,
				"action": "DetailAjax",
				"viewname": cvId,
				"mode": "getRecordsCount",
				"relatedModule": relatedModuleName,
				'record': recordId,
				'tab_label': tab_label
			}).done(function (data) {
				jQuery("#recordsCount").val(data['result']['count']);
				count = data['result']['count'];
				aDeferred.resolve(count);
			});
		}

		return aDeferred.promise();
	},

	/**
	 * Function to register events
	 */
	registerEvents: function () {
		if (app.getViewName() != "Detail") {
			this._super();
			return;
		}
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
	}
});
