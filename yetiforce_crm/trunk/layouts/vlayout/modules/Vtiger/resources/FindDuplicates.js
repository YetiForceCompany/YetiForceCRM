/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
Vtiger_List_Js('Vtiger_FindDuplicates_Js',{

	massDeleteRecords : function(url) {
		var listInstance = new Vtiger_FindDuplicates_Js();
		Vtiger_List_Js.massDeleteRecords(url,listInstance);
	}
},{

	popupWindowInstance : false,

	/**
	 * Function that is triggered after deleting records
	 */
	postMassDeleteRecords : function() {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var fields = jQuery('#duplicateSearchFields').val();
		var moduleName = app.getModuleName();
		var pageNumber = jQuery('#pageNumber').val();
		var ignoreEmpty = jQuery('#ignoreEmpty').val();
		var url = 'module='+moduleName+'&view=FindDuplicates&fields='+fields+'&ignoreEmpty='+ignoreEmpty;
		AppConnector.requestPjax(url+'&page='+pageNumber).then(
			function(data){
				jQuery('#listViewContents').html(data);
				jQuery('#recordsCount').val('');
				jQuery('#totalPageCount').text('');
                var selectedIds = new Array();
                thisInstance.writeSelectedIds(selectedIds);
				thisInstance.calculatePages().then(function(){
					thisInstance.updatePagination();
				});
				thisInstance.registerMergeRecordEvent(thisInstance.mergeRecordPopupCallback);
				aDeferred.resolve();
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function registers events for navigation in duplicate search view
	 */
	registerPageNavigationEvents : function() {
		var thisInstance = this;
		var fields = jQuery('#duplicateSearchFields').val();
		var moduleName = app.getModuleName();
		var ignoreEmpty = jQuery('#ignoreEmpty').val();
		var url = 'module='+moduleName+'&view=FindDuplicates&fields='+fields+'&ignoreEmpty='+ignoreEmpty;

		jQuery('#listViewNextPageButton').on('click',function() {
			var pageLimit = jQuery('#pageLimit').val();
			var noOfEntries = jQuery('#noOfEntries').val();
			if(noOfEntries >= pageLimit) {
				var pageNumber = jQuery('#pageNumber').val();
				var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
				AppConnector.requestPjax(url+'&page='+nextPageNumber).then(function(data) {
					jQuery('#listViewContents').html(data);
					jQuery('#pageNumber').val(nextPageNumber);
					jQuery('#pageToJump').val(nextPageNumber);
					thisInstance.calculatePages().then(function(){
						thisInstance.updatePagination();
					});
					thisInstance.registerMergeRecordEvent(thisInstance.mergeRecordPopupCallback);
				});
			}
		});

		jQuery('#listViewPreviousPageButton').on('click',function() {
			var pageNumber = jQuery('#pageNumber').val();
			if(pageNumber > 1) {
				var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
				jQuery('#pageNumber').val(previousPageNumber);
				jQuery('#pageToJump').val(previousPageNumber);
				AppConnector.requestPjax(url+'&page='+previousPageNumber).then(
					function(data){
						jQuery('#listViewContents').html(data);
						thisInstance.calculatePages().then(function(){
							thisInstance.updatePagination();
						});
						thisInstance.registerMergeRecordEvent(thisInstance.mergeRecordPopupCallback);
					}
				);
			}
		});

		jQuery('#listViewPageJump').on('click', function(e) {
			jQuery('#pageToJump').validationEngine('hideAll');
			var element = jQuery('#totalPageCount');
			var totalPageNumber = element.text();
			if(totalPageNumber == "") {
				var totalRecordCount = jQuery('#totalCount').val();
				if(totalRecordCount != 'undefined') {
					var recordPerPage = jQuery('#noOfEntries').val();
					if(recordPerPage == '0') recordPerPage = 1;
					var totalPages = Math.ceil(totalRecordCount/recordPerPage);
					if(totalPages == 0){
						totalPages = 1;
					}
					element.text(totalPages);
					return;
				}
				element.progressIndicator({});
				thisInstance.getPageCount().then(function(data){
					var pageCount = data['result']['page'];
					if(pageCount == 0){
						pageCount = 1;
					}
					element.text(pageCount);
					element.progressIndicator({'mode': 'hide'});
				});
			}
		});

		jQuery('#listViewPageJumpDropDown').on('click','li',function(e) {
				e.stopImmediatePropagation();
			}).on('keypress','#pageToJump',function(e) {
				if(e.which == 13) {
					e.stopImmediatePropagation();
					var element = jQuery(e.currentTarget);
					var response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
					if(typeof response != "undefined"){
						element.validationEngine('showPrompt',response,'',"topLeft",true);
					} else {
						element.validationEngine('hideAll');
						var currentPageElement = jQuery('#pageNumber');
						var currentPageNumber = currentPageElement.val();
						var newPageNumber = parseInt(jQuery(e.currentTarget).val());
						var totalPages = parseInt(jQuery('#totalPageCount').text());
						if(newPageNumber > totalPages){
							var error = app.vtranslate('JS_PAGE_NOT_EXIST');
							element.validationEngine('showPrompt',error,'',"topLeft",true);
							return;
						}
						if(newPageNumber == currentPageNumber){
							var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER')+" "+newPageNumber;
							var params = {
								text: message,
								type: 'info'
							};
							Vtiger_Helper_Js.showMessage(params);
							return;
						}
						currentPageElement.val(newPageNumber);

						AppConnector.requestPjax(url+'&page='+newPageNumber).then(
							function(data){
								jQuery('#listViewContents').html(data);
								thisInstance.updatePagination();
								element.closest('.btn-group').removeClass('open');
								thisInstance.registerMergeRecordEvent(thisInstance.mergeRecordPopupCallback);
							}
						);
					}
				return false;
			}
		});
	},

	/**
	 * Function registers event for merge button
	 */
	registerMergeRecordEvent : function(cb) {
		var thisInstance = this;
		jQuery('input[name="merge"]').on('click', function(e) {
			var element = jQuery(e.currentTarget);
			var groupName = element.data('group');
			var mergeRecordsCheckBoxes = jQuery('input[name="mergeRecord"]:checked');
			if(mergeRecordsCheckBoxes.length < 2) {
				Vtiger_Helper_Js.showMessage({text: app.vtranslate('JS_SELECT_ATLEAST_TWO_RECORD_FOR_MERGING')});
				return false;
			} else {
				var count = 0;
				var records = [];
				var stop = false;
				mergeRecordsCheckBoxes.each(function(key, obj) {
					var ele = jQuery(obj);
					if(ele.data('group') != groupName) {
						Vtiger_Helper_Js.showMessage({text: app.vtranslate('JS_SELECT_RECORDS_TO_MERGE_FROM_SAME_GROUP')});
						stop = true;
						return false;
					}
					records.push(ele.data('id'));
					count++;
				});
				if(stop) return false;
				if(count > 3) {
					Vtiger_Helper_Js.showMessage({text: app.vtranslate('JS_ALLOWED_TO_SELECT_MAX_OF_THREE_RECORDS')});
					return false;
				}
				var popupInstance = Vtiger_Popup_Js.getInstance();
				var url = 'module='+app.getModuleName()+'&view=MergeRecord&records='+records;
				thisInstance.popupWindowInstance = popupInstance.show(url, '', '', '', function(params){
					thisInstance.mergeRecordPopupCallback();
				});
			}
		});
	},

	/**
	 * Callback function after the merge popup appears
	 */
	mergeRecordPopupCallback : function() {
		var thisInstance = this;
		var win = thisInstance.popupWindowInstance;
		var form = win.document.forms['massMerge'];
		jQuery(form.primaryRecord).on('change', function(event) {
			var id = jQuery(event.currentTarget).val();
			jQuery(form).find('[data-id='+id+']').attr('checked', true);
		});

		jQuery(form).on('submit', function(e){
			e.preventDefault();
			var params = jQuery(form).serialize();
			AppConnector.request(params).then(function(data){
				win.close();
				thisInstance.postMassDeleteRecords();
			});
		});
	},

	/**
	 * Function registers various events for duplicate search
	 */
	registerEvents : function() {
		var thisInstance = this;
		thisInstance.registerMergeRecordEvent(thisInstance.mergeRecordPopupCallback);
		thisInstance.registerMainCheckBoxClickEvent();
		thisInstance.registerPageNavigationEvents();
		thisInstance.registerCheckBoxClickEvent();
		thisInstance.registerSelectAllClickEvent();
		thisInstance.registerDeselectAllClickEvent();
		thisInstance.registerEventForTotalRecordsCount();
	},

	/**
	 * Function returns current view name for the module
	 */
	getCurrentCvId : function(){
		return jQuery('#viewName').val();
	},

	/**
	 * Function gets the record count
	 */
	getRecordsCount : function(){
		var aDeferred = jQuery.Deferred();
		var recordCountVal = jQuery("#recordsCount").val();
		if(recordCountVal != ''){
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var fields = jQuery('#duplicateSearchFields').val();
			var ignoreEmpty = jQuery('#ignoreEmpty').val();
			var postData = {
				"module": module, "parent": parent,
				"view": "FindDuplicatesAjax", "mode": "getRecordsCount",
				"fields": fields, "ignoreEmpty":ignoreEmpty
			}
			AppConnector.request(postData).then(
				function(data) {
					var response = JSON.parse(data);
					jQuery("#recordsCount").val(response['result']['count']);
					count =  response['result']['count'];
					aDeferred.resolve(count);
				},
				function(error,err){
				}
			);
		}
		return aDeferred.promise();
	}
});