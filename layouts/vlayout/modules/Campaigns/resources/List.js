/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Campaigns_List_Js",{},{
	
	readSelectedIds : function(decode){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			return this._super(decode);
		}
		var selectedIdsElement = jQuery('#selectedIds');
		var selectedIdsDataAttr = 'selectedIds';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		var selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		if (selectedIds == "") {
			selectedIds = new Array();
			this.writeSelectedIds(selectedIds);
		} else {
			selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		}
		if(decode == true){
			if(typeof selectedIds == 'object'){
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},
	
	readExcludedIds : function(decode){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			return this._super(decode);
		}
		var exlcudedIdsElement = jQuery('#excludedIds');
		var excludedIdsDataAttr = 'excludedIds';
		var excludedIdsElementDataAttributes = exlcudedIdsElement.data();
		var excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		if (excludedIds == "") {
			excludedIds = new Array();
			this.writeExcludedIds(excludedIds);
		}else{
			excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		}
		if(decode == true){
			if(typeof excludedIds == 'object') {
				return JSON.stringify(excludedIds);
			}
		}
		return excludedIds;
	},

	writeSelectedIds : function(selectedIds){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			this._super(selectedIds);
			return;
		}
		jQuery('#selectedIds').data('selectedIds',selectedIds);
	},

	writeExcludedIds : function(excludedIds){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			this._super(excludedIds);
			return;
		}
		jQuery('#excludedIds').data('excludedIds',excludedIds);
	},
	
	/**
	 * Function to mark selected records
	 */
	markSelectedRecords : function(){
		var thisInstance = this;
		var selectedIds = this.readSelectedIds();
		if(selectedIds != ''){
			if(selectedIds == 'all'){
				jQuery('.listViewEntriesCheckBox').each( function(index,element) {
					jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
				});
				jQuery('#deSelectAllMsgDiv').show();
				var excludedIds = jQuery('[name="excludedIds"]').data('excludedIds');
				if(excludedIds != ''){
					jQuery('#listViewEntriesMainCheckBox').attr('checked',false);
					jQuery('.listViewEntriesCheckBox').each( function(index,element) {
						if(jQuery.inArray(jQuery(element).val(),excludedIds) != -1){
							jQuery(element).attr('checked', false).closest('tr').removeClass('highlightBackgroundColor');
						}
					});
				}
			} else {
				jQuery('.listViewEntriesCheckBox').each( function(index,element) {
					if(jQuery.inArray(jQuery(element).val(),selectedIds) != -1){
						jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
					}
				});
			}
			thisInstance.checkSelectAll();
		}
	},
	
	getRecordsCount : function(){
		var aDeferred = jQuery.Deferred();
		var view = jQuery('#view').val();
		if(view != "Detail"){
			return this._super();
		}
		var recordCountVal = jQuery("#recordsCount").val();
		if(recordCountVal != ''){
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var cvId = this.getCurrentCvId();
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var relatedModuleName = jQuery('[name="relatedModuleName"]').val();
			var recordId = jQuery('#recordId').val();
			var tab_label = jQuery('div.related').find('li.active').data('labelKey');
			var postData = {
				"module": module,
				"parent": parent,
				"action": "DetailAjax",
				"viewname": cvId,
				"mode": "getRecordsCount",
				"relatedModule" : relatedModuleName,
				'record' : recordId,
				'tab_label' : tab_label
			}
			AppConnector.request(postData).then(
				function(data) {
					jQuery("#recordsCount").val(data['result']['count']);
					count =  data['result']['count'];
					aDeferred.resolve(count);
				},
				function(error,err){

				}
			);
		}

		return aDeferred.promise();
	},
	
	/** 
	 * Function to register events
	 */
	registerEvents : function(){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			this._super();
			return;
		}
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
	}
})