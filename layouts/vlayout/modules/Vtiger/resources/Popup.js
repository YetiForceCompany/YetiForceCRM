/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Vtiger_Popup_Js",{

    getInstance: function(){
	    var module = app.getModuleName();
		var className = jQuery('#popUpClassName').val();
		if(typeof className != 'undefined'){
			var moduleClassName = className;
		}else{
			var moduleClassName = module+"_Popup_Js";
		}
		var fallbackClassName = Vtiger_Popup_Js;
	    if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		}else{
			var instance = new fallbackClassName();
		}
	    return instance;
	}

},{

	//holds the event name that child window need to trigger
	eventName : '',
	popupPageContentsContainer : false,
	sourceModule : false,
	sourceRecord : false,
	sourceField : false,
	multiSelect : false,
	relatedParentModule : false,
	relatedParentRecord : false,

	/**
	 * Function to get source module
	 */
	getSourceModule : function(){
		if(this.sourceModule == false){
			this.sourceModule = jQuery('#parentModule').val();
		}
		return this.sourceModule;
	},

	/**
	 * Function to get source record
	 */
	getSourceRecord : function(){
		if(this.sourceRecord == false){
			this.sourceRecord = jQuery('#sourceRecord').val();
		}
		return this.sourceRecord;
	},

	/**
	 * Function to get source field
	 */
	getSourceField : function(){
		if(this.sourceField == false){
			this.sourceField = jQuery('#sourceField').val();
		}
		return this.sourceField;
	},

	/**
	 * Function to get related parent module
	 */
	getRelatedParentModule : function(){
		if(this.relatedParentModule == false){
			this.relatedParentModule = jQuery('#relatedParentModule').val();
		}
		return this.relatedParentModule;
	},
	/**
	 * Function to get related parent id
	 */
	getRelatedParentRecord : function(){
		if(this.relatedParentRecord == false){
			this.relatedParentRecord = jQuery('#relatedParentId').val();
		}
		return this.relatedParentRecord;
	},

	/**
	 * Function to get Search key
	 */
	getSearchKey : function(){
		return jQuery('#searchableColumnsList').val();
	},

	/**
	 * Function to get Search value
	 */
	getSearchValue : function(){
		return jQuery('#searchvalue').val();
	},

	/**
	 * Function to get Order by
	 */
	getOrderBy : function(){
		return jQuery('#orderBy').val();
	},

	/**
	 * Function to get Sort Order
	 */
	getSortOrder : function(){
			return jQuery("#sortOrder").val();
	},

	/**
	 * Function to get Page Number
	 */
	getPageNumber : function(){
		return jQuery('#pageNumber').val();
	},

	getPopupPageContainer : function(){
		if(this.popupPageContentsContainer == false) {
			this.popupPageContentsContainer = jQuery('#popupPageContainer');
		}
		return this.popupPageContentsContainer;

	},

	show : function(urlOrParams, cb, windowName, eventName, onLoadCb){
		if(typeof urlOrParams == 'undefined'){
			urlOrParams = {};
		}
		if (typeof urlOrParams == 'object' && (typeof urlOrParams['view'] == "undefined")) {
			urlOrParams['view'] = 'Popup';
		}

		// Target eventName to be trigger post data selection.
		if(typeof eventName == 'undefined') {
			eventName = 'postSelection'+ Math.floor(Math.random() * 10000);
		}
		if(typeof windowName == 'undefined' ){
			windowName = 'test';
		}
		if (typeof urlOrParams == 'object') {
			urlOrParams['triggerEventName'] = eventName;
		} else {
			urlOrParams += '&triggerEventName=' + eventName;
		}

		var urlString = (typeof urlOrParams == 'string')? urlOrParams : jQuery.param(urlOrParams);
		var url = 'index.php?'+urlString;
		var popupWinRef =  window.open(url, windowName ,'width=900,height=650,resizable=0,scrollbars=1');
		if (typeof this.destroy == 'function') {
			// To remove form elements that have created earlier
			this.destroy();
		}
		jQuery.initWindowMsg();

		if(typeof cb != 'undefined') {
			this.retrieveSelectedRecords(cb, eventName);
		}

		 if(typeof onLoadCb == 'function') {
			jQuery.windowMsg('Vtiger.OnPopupWindowLoad.Event', function(data){
				onLoadCb(data);
            })
        }

		// http://stackoverflow.com/questions/13953321/how-can-i-call-a-window-child-function-in-javascript
		// This could be useful for the caller to invoke child window methods post load.
		return popupWinRef;
	},

	retrieveSelectedRecords : function(cb, eventName) {
		if(typeof eventName == 'undefined') {
			eventName = 'postSelection';
		}

		jQuery.windowMsg(eventName, function(data) {
			cb(data);
		});
	},

	/**
	 * Function which removes the elements that are added by the plugin
	 *
	 */
	destroy : function(){
		jQuery('form[name="windowComm"]').remove();
	},

	done : function(result, eventToTrigger, window) {

		if(typeof eventToTrigger == 'undefined' || eventToTrigger.length <=0 ) {
			eventToTrigger = 'postSelection'
		}

		if(typeof window == 'undefined'){
			window = self;
		}
		window.close();
        var data = JSON.stringify(result);
        // Because if we have two dollars like this "$$" it's not working because it'll be like escape char(Email Templates)
        data = data.replace(/\$\$/g,"$ $");

		jQuery.triggerParentEvent(eventToTrigger, data);

	},

	getView : function(){
	    var view = jQuery('#view').val();
	    if(view == '') {
		    view = 'PopupAjax';
	    } else {
		    view = view+'Ajax';
	    }
	    return view;
	},

	setEventName : function(eventName) {
		this.eventName = eventName;
	},

	getEventName : function() {
		return this.eventName;
	},

	isMultiSelectMode : function() {
		if(this.multiSelect == false){
			this.multiSelect = jQuery('#multi_select');
		}
		var value = this.multiSelect.val();
		if(value) {
			return value;
		}
		return false;
	},

	getListViewEntries: function(e){
		var thisInstance = this;
		var row  = jQuery(e.currentTarget);
		var dataUrl = row.data('url');
		if(typeof dataUrl != 'undefined'){
			dataUrl = dataUrl+'&currency_id='+jQuery('#currencyId').val();
		    AppConnector.request(dataUrl).then(
			function(data){
				for(var id in data){
				    if(typeof data[id] == "object"){
					var recordData = data[id];
				    }
				}
				thisInstance.done(recordData, thisInstance.getEventName());
				e.preventDefault();
			},
			function(error,err){

			}
		    );
		} else {
		    var id = row.data('id');
		    var recordName = row.data('name');
			var recordInfo = row.data('info');
		    var response ={};
		    response[id] = {'name' : recordName,'info' : recordInfo} ;
			thisInstance.done(response, thisInstance.getEventName());
		    e.preventDefault();
		}

	},

	registerSelectButton : function(){
		var popupPageContentsContainer = this.getPopupPageContainer();
		var thisInstance = this;
		popupPageContentsContainer.on('click','button.select', function(e){
			var tableEntriesElement = popupPageContentsContainer.find('table');
			var selectedRecordDetails = {};
			var recordIds = new Array();
			var dataUrl;
			jQuery('input.entryCheckBox', tableEntriesElement).each(function(index, checkBoxElement){
				var checkBoxJqueryObject = jQuery(checkBoxElement)
				if(! checkBoxJqueryObject.is(":checked")){
					return true;
				}
				var row = checkBoxJqueryObject.closest('tr');
				var id = row.data('id');
				recordIds.push(id);
				var name = row.data('name');
				dataUrl = row.data('url');
				selectedRecordDetails[id] = {'name' : name};
			});
			var jsonRecorIds = JSON.stringify(recordIds);
			if(Object.keys(selectedRecordDetails).length <= 0) {
				alert(app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'));
			}else{
				if(typeof dataUrl != 'undefined'){
				    dataUrl = dataUrl+'&idlist='+jsonRecorIds+'&currency_id='+jQuery('#currencyId').val();
				    AppConnector.request(dataUrl).then(
					function(data){
						for(var id in data){
						    if(typeof data[id] == "object"){
							var recordData = data[id];
						    }
						}
						var recordDataLength = Object.keys(recordData).length;
						if(recordDataLength == 1){
							recordData = recordData[0];
						}
						thisInstance.done(recordData, thisInstance.getEventName());
						e.preventDefault();
					},
					function(error,err){

					}
				);
				}else{
				    thisInstance.done(selectedRecordDetails, thisInstance.getEventName());
				}
			}
		});
	},

	selectAllHandler : function(e){
		var currentElement = jQuery(e.currentTarget);
		var isMainCheckBoxChecked = currentElement.is(':checked');
		var tableElement = currentElement.closest('table');
		if(isMainCheckBoxChecked) {
			jQuery('input.entryCheckBox', tableElement).attr('checked','checked').closest('tr').addClass('highlightBackgroundColor');
		}else {
			jQuery('input.entryCheckBox', tableElement).removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');
		}
	},

	registerEventForSelectAllInCurrentPage : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('change','input.selectAllInCurrentPage',function(e){
			thisInstance.selectAllHandler(e);
		});
	},

	checkBoxChangeHandler : function(e){
		var elem = jQuery(e.currentTarget);
		var parentElem = elem.closest('tr');
		if(elem.is(':checked')){
			parentElem.addClass('highlightBackgroundColor');

		}else{
			parentElem.removeClass('highlightBackgroundColor');
		}
	},

	/**
	 * Function to register event for entry checkbox change
	 */
	registerEventForCheckboxChange : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','input.entryCheckBox',function(e){
			e.stopPropagation();
			thisInstance.checkBoxChangeHandler(e);
		});
	},
	/**
	 * Function to get complete params
	 */
	getCompleteParams : function(){
		var params = {};
		params['view'] = this.getView();
		params['src_module'] = this.getSourceModule();
		params['src_record'] = this.getSourceRecord();
		params['src_field'] = this.getSourceField();
		params['search_key'] =  this.getSearchKey();
		params['search_value'] =  this.getSearchValue();
		params['orderby'] =  this.getOrderBy();
		params['sortorder'] =  this.getSortOrder();
		params['page'] = this.getPageNumber();
		params['related_parent_module'] = this.getRelatedParentModule();
		params['related_parent_id'] = this.getRelatedParentRecord();
		params['module'] = app.getModuleName();

		if(this.isMultiSelectMode()) {
			params['multi_select'] = true;
		}
		return params;
	},

	/**
	 * Function to get Page Records
	 */
	getPageRecords : function(params){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		Vtiger_BaseList_Js.getPageRecords(params).then(
				function(data){
					jQuery('#popupContents').html(data);
					Vtiger_Helper_Js.showHorizontalTopScrollBar();
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					})
					thisInstance.calculatePages().then(function(data){
						aDeferred.resolve(data);
					});
				},

				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			);
		return aDeferred.promise();
	},
	
		/**
	 * Function to calculate number of pages
	 */
	calculatePages : function() {
		var aDeferred = jQuery.Deferred();
		var element = jQuery('#totalPageCount');
		var totalPageNumber = element.text();
		if(totalPageNumber == ""){
			var totalRecordCount = jQuery('#totalCount').val();
			if(totalRecordCount != '') {
				var recordPerPage = jQuery('#noOfEntries').val();
				if(recordPerPage == '0') recordPerPage = 1;
				pageCount = Math.ceil(totalRecordCount/recordPerPage);
				if(pageCount == 0){
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
				return aDeferred.promise();
			}
			this.getPageCount().then(function(data){
				var pageCount = data['result']['page'];
				if(pageCount == 0){
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
			});
		} else{
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	
	/**
	 * Function to handle search event
	 */
	searchHandler : function(){
		var aDeferred = jQuery.Deferred();
		var completeParams = this.getCompleteParams();
		completeParams['page'] = 1;
		return this.getPageRecords(completeParams).then(
			function(data){
				aDeferred.resolve(data);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},

	/**
	 * Function to register event for Search
	 */
	registerEventForSearch : function(){
		var thisInstance = this;
		jQuery('#popupSearchButton').on('click',function(e){
			jQuery('#totalPageCount').text("");
			thisInstance.searchHandler().then(function(data){
				jQuery('#pageNumber').val(1);
				jQuery('#pageToJump').val(1);
				thisInstance.updatePagination();
			});
		});
	},
    /**
	 * Function to register event for Searching on click of enter
	 */
	registerEventForEnter : function(){
		var thisInstance = this;
		jQuery('#searchvalue').keyup(function (e) {
            if (e.keyCode == 13) {
            jQuery('#popupSearchButton').trigger('click');
            }
		});
	},

	/**
	 * Function to handle Sort
	 */
	sortHandler : function(headerElement){
		var aDeferred = jQuery.Deferred();
		//Listprice column should not be sorted so checking for class noSorting
		if(headerElement.hasClass('noSorting')){
			return;
		}
		var fieldName = headerElement.data('columnname');
		var sortOrderVal = headerElement.data('nextsortorderval');
		var sortingParams = {
			"orderby" : fieldName,
			"sortorder" : sortOrderVal
		}
		var completeParams = this.getCompleteParams();
		jQuery.extend(completeParams,sortingParams);
		return this.getPageRecords(completeParams).then(
			function(data){
				aDeferred.resolve(data);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to register Event for Sorting
	 */
	registerEventForSort : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','.listViewHeaderValues',function(e){
			var element = jQuery(e.currentTarget);
			thisInstance.sortHandler(element).then(function(data){
				thisInstance.updatePagination();
			});
		});
	},

	/**
	 * Function to handle next page navigation
	 */

	nextPageHandler : function(){
		var aDeferred = jQuery.Deferred();
		var pageLimit = jQuery('#pageLimit').val();
		var noOfEntries = jQuery('#noOfEntries').val();
		if(noOfEntries == pageLimit){
			var pageNumber = jQuery('#pageNumber').val();
			var nextPageNumber = parseInt(pageNumber) + 1;
			var pagingParams = {
					"page": nextPageNumber
				}
			var completeParams = this.getCompleteParams();
			jQuery.extend(completeParams,pagingParams);
			this.getPageRecords(completeParams).then(
				function(data){
					jQuery('#pageNumber').val(nextPageNumber);
					jQuery('#pageToJump').val(nextPageNumber);
					aDeferred.resolve(data);
				},

				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			);
		}
		return aDeferred.promise();
	},

	/**
	 * Function to handle Previous page navigation
	 */
	previousPageHandler : function(){
		var aDeferred = jQuery.Deferred();
		var pageNumber = jQuery('#pageNumber').val();
		var previousPageNumber = parseInt(pageNumber) - 1;
		if(pageNumber > 1){
			var pagingParams = {
				"page": previousPageNumber
			}
			var completeParams = this.getCompleteParams();
			jQuery.extend(completeParams,pagingParams);
			this.getPageRecords(completeParams).then(
				function(data){
					jQuery('#pageNumber').val(previousPageNumber);
					jQuery('#pageToJump').val(previousPageNumber);
					aDeferred.resolve(data);
				},

				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			);
		}
		return aDeferred.promise();
	},

	/**
	 * Function to register event for Paging
	 */
	registerEventForPagination : function(){
		var thisInstance = this;
		jQuery('#listViewNextPageButton').on('click',function(){
			thisInstance.nextPageHandler().then(function(data){
				thisInstance.updatePagination();
			});
		});
		jQuery('#listViewPreviousPageButton').on('click',function(){
			thisInstance.previousPageHandler().then(function(data){
				thisInstance.updatePagination();
			});
		});
		jQuery('#listViewPageJump').on('click',function(e){
			jQuery('#pageToJump').validationEngine('hideAll');
			var element = jQuery('#totalPageCount');
			var totalPageNumber = element.text();
			if(totalPageNumber == ""){
				var totalRecordElement = jQuery('#totalCount');
				var totalRecordCount = totalRecordElement.val();
				if(totalRecordCount != '') {
					var recordPerPage = jQuery('#pageLimit').val();
					if(recordPerPage == '0') recordPerPage = 1;
					pageCount = Math.ceil(totalRecordCount/recordPerPage);
					if(pageCount == 0){
						pageCount = 1;
					}
					element.text(pageCount);
					return;
				}
				element.progressIndicator({});
				thisInstance.getPageCount().then(function(data){
					var pageCount = data['result']['page'];
					element.text(pageCount);
					totalRecordElement.val(data['result']['numberOfRecords']);
					element.progressIndicator({'mode': 'hide'});
			});
		}
		})

		jQuery('#listViewPageJumpDropDown').on('click','li',function(e){
			e.stopImmediatePropagation();
		}).on('keypress','#pageToJump',function(e){
			if(e.which == 13){
				e.stopImmediatePropagation();
				var element = jQuery(e.currentTarget);
				var response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
				if(typeof response != "undefined"){
					element.validationEngine('showPrompt',response,'',"topLeft",true);
				} else {
					element.validationEngine('hideAll');
					var currentPageElement = jQuery('#pageNumber');
					var currentPageNumber = currentPageElement.val();
					var newPageNumber = parseInt(element.val());
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
					var pagingParams = {
						"page": newPageNumber
					}
					var completeParams = thisInstance.getCompleteParams();
					jQuery.extend(completeParams,pagingParams);
					thisInstance.getPageRecords(completeParams).then(
						function(data){
							currentPageElement.val(newPageNumber);
							thisInstance.updatePagination();
							element.closest('.btn-group ').removeClass('open');
						},
						function(textStatus, errorThrown){
						}
					);
				}
				return false;
		}
		});
	},

	registerEventForListViewEntries : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','.listViewEntries',function(e){
		    thisInstance.getListViewEntries(e);
		});
	},

	triggerDisplayTypeEvent : function() {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if(widthType) {
			var elements = jQuery('.listViewEntriesTable').find('td,th');
			elements.addClass(widthType);
		}
	},
		/**
	 * Function to get page count and total number of records in list
	 */
	getPageCount : function(){
		var aDeferred = jQuery.Deferred();
		var pageJumpParams = {
			'mode' : "getPageCount"
		}
		var completeParams = this.getCompleteParams();
		jQuery.extend(completeParams,pageJumpParams);
		AppConnector.request(completeParams).then(
			function(data) {
				var response;
				if(typeof data != "object"){
					response = JSON.parse(data);
				} else{
					response = data;
				}
				aDeferred.resolve(response);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to show total records count in listview on hover
	 * of pageNumber text
	 */
	registerEventForTotalRecordsCount : function(){
		var thisInstance = this;
		jQuery('.totalNumberOfRecords').on('click',function(e){
			var element = jQuery(e.currentTarget);
			element.addClass('hide');
			element.parent().find('.pageNumbersText').progressIndicator({});
			var totalRecordsElement = jQuery('#totalCount');
			var totalNumberOfRecords = totalRecordsElement.val();
			if(totalNumberOfRecords == '') {
				thisInstance.getPageCount().then(function(data){
					totalNumberOfRecords = data['result']['numberOfRecords'];
					var numberOfPages = data['result']['page'];
					totalRecordsElement.val(totalNumberOfRecords);
					jQuery('#totalPageCount').text(numberOfPages);
					thisInstance.showPagingInfo();
				});
			}else{
				thisInstance.showPagingInfo();
			}
			element.parent().find('.pageNumbersText').progressIndicator({'mode':'hide'});
		})
	},
	
	showPagingInfo : function(){
		var totalNumberOfRecords = jQuery('#totalCount').val();
		var pageNumberElement = jQuery('.pageNumbersText');
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange+" "+app.vtranslate('of')+" "+totalNumberOfRecords;
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		if(listViewEntriesCount != 0){
			jQuery('.pageNumbersText').html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText').html("");
		}
	},
	
	/**
	 * Function to update Pagining status
	 */
	updatePagination : function(){
		var previousPageExist = jQuery('#previousPageExist').val();
		var nextPageExist = jQuery('#nextPageExist').val();
		var previousPageButton = jQuery('#listViewPreviousPageButton');
		var nextPageButton = jQuery('#listViewNextPageButton');
		var listViewEntriesCount = jQuery('#noOfEntries').val();
		var pageStartRange = jQuery('#pageStartRange').val();
		var pageEndRange = jQuery('#pageEndRange').val();
		var pageJumpButton = jQuery('#listViewPageJump');
		var pages = jQuery('#totalPageCount').text();
		var totalNumberOfRecords = jQuery('.totalNumberOfRecords');
		var pageNumbersTextElem = jQuery('.pageNumbersText');

		if(pages == 1){
			pageJumpButton.attr('disabled',"disabled");
		}
		if(pages > 1){
			pageJumpButton.removeAttr('disabled');
		}

		if(previousPageExist != ""){
			previousPageButton.removeAttr('disabled');
		} else if(previousPageExist == "") {
			previousPageButton.attr("disabled","disabled");
		}

		if((nextPageExist != "") && (pages >1)){
			nextPageButton.removeAttr('disabled');
		} else if((nextPageExist == "") || (pages == 1)) {
			nextPageButton.attr("disabled","disabled");
		}
		if(listViewEntriesCount != 0){
			var pageNumberText = pageStartRange+" "+app.vtranslate('to')+" "+pageEndRange;
			pageNumbersTextElem.html(pageNumberText);
			totalNumberOfRecords.removeClass('hide');
		} else {
			pageNumbersTextElem.html("<span>&nbsp;</span>");
			if(!totalNumberOfRecords.hasClass('hide')){
				totalNumberOfRecords.addClass('hide');
			}
		}

	},

	registerEvents: function(){
		var pageNumber = jQuery('#pageNumber').val();
		if(pageNumber == 1){
			jQuery('#listViewPreviousPageButton').attr("disabled", "disabled");
		}
		this.registerEventForSelectAllInCurrentPage();
		this.registerSelectButton();
		this.registerEventForCheckboxChange();
		this.registerEventForSearch();
        this.registerEventForEnter();
		this.registerEventForSort();
		this.registerEventForListViewEntries();
		//this.triggerDisplayTypeEvent();
		var popupPageContainer = jQuery('#popupPageContainer');
		if(popupPageContainer.length > 0){
			this.registerEventForTotalRecordsCount();
			this.registerEventForPagination();
		}
	}
});
jQuery(document).ready(function() {
	var popupInstance = Vtiger_Popup_Js.getInstance();
	var triggerEventName = jQuery('.triggerEventName').val();
	var documentHeight = (jQuery(document).height())+'px';
	jQuery('#popupPageContainer').css('height',documentHeight);
	popupInstance.setEventName(triggerEventName);
	popupInstance.registerEvents();
	Vtiger_Helper_Js.showHorizontalTopScrollBar();
});