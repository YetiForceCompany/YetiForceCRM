/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_List_Js(
	'Campaigns_List_Js',
	{},
	{
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
			if (app.getViewName() != 'Detail') {
				return this._super();
			}
			var detailInstance = Vtiger_Detail_Js.getInstance();
			var aDeferred = jQuery.Deferred();
			var recordCountVal = jQuery('#recordsCount').val();
			if (recordCountVal != '') {
				aDeferred.resolve(recordCountVal);
			} else {
				var count = '';
				var cvId = jQuery('#customFilter').val();
				var module = app.getModuleName();
				var parent = app.getParentModuleName();
				var relatedModuleName = jQuery('[name="relatedModuleName"]').val();
				var recordId = app.getRecordId();
				let selectedTab = detailInstance.getSelectedTab();
				AppConnector.request({
					module: module,
					parent: parent,
					action: 'DetailAjax',
					viewname: cvId,
					mode: 'getRecordsCount',
					relatedModule: relatedModuleName,
					record: recordId,
					tab_label: selectedTab.data('labelKey'),
					relationId: selectedTab.data('relationId')
				}).done(function (data) {
					jQuery('#recordsCount').val(data['result']['count']);
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
			if (app.getViewName() != 'Detail') {
				this._super();
				return;
			}
			this.registerMainCheckBoxClickEvent();
			this.registerCheckBoxClickEvent();
			this.registerSelectAllClickEvent();
			this.registerDeselectAllClickEvent();
		}
	}
);
