/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_List_Js("Portal_List_Js",{
    
    getDefaultParams : function() {
		var params = {
			'module': app.getModuleName(),
			'view' : 'List',
            'page' : jQuery('#pageNumber').val(),
			'orderby' : jQuery('#orderBy').val(),
			'sortorder' : jQuery('#sortOrder').val(),
            'search_value' : jQuery('#alphabetValue').val()
		}
        return params;
    },

    editBookmark : function(params) {
        AppConnector.request(params).then(function(data) {
            var callBackFunction = function(data) {
                Portal_List_Js.saveBookmark();
            };
            app.showModalWindow(data,function(data){
                if(typeof callBackFunction == 'function'){
                    callBackFunction(data);
                }
            });
        });
    },
    
    saveBookmark : function() {
        jQuery('#saveBookmark').on('submit',function(e){
            e.preventDefault();
            var form = jQuery(e.currentTarget);
            var params = form.serializeFormData();
            if(params.bookmarkName == '' || params.bookmarkUrl == '') {
                var data = {
                    title : app.vtranslate('JS_MESSAGE'),
                    text: 'Please enter all mandatory field',
                    animation: 'show',
                    type: 'error'
                };
                Vtiger_Helper_Js.showPnotify(data);
                return false;
            }
            AppConnector.request(params).then(function(data) {
                if(data.success) {
                    var params = {
                        title : app.vtranslate('JS_MESSAGE'),
                        text: data.result.message,
                        animation: 'show',
                        type: 'success'
                    };
                    Vtiger_Helper_Js.showPnotify(params);
                    var url = Portal_List_Js.getDefaultParams();
                    Portal_List_Js.loadListViewContent(url);
                }
            });
        });
    },
    
    massDeleteRecords : function() {
        var	listInstance = Vtiger_List_Js.getInstance();
        var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true) {
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
                    var deleteURL = 'index.php?module='+app.getModuleName()+'&action=MassDelete';
                    deleteURL += '&selected_ids='+selectedIds+'&excluded_ids='+excludedIds;
                    var searchValue = listInstance.getAlphabetSearchValue();

                   	if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
                        deleteURL += '&search_value='+searchValue;
                    }
                    AppConnector.request(deleteURL).then(function(data) {
                        if(data.success) {
                            var params = {
                                title : app.vtranslate('JS_MESSAGE'),
                                text: data.result.message,
                                animation: 'show',
                                type: 'error'
                            };
                            Vtiger_Helper_Js.showPnotify(params);
                            var url = Portal_List_Js.getDefaultParams();
                            Portal_List_Js.loadListViewContent(url);
                        }
                    });
                });
        } else {
			listInstance.noRecordSelectedAlert();
		}
    },
    
    loadListViewContent : function(url) {
        var progressIndicatorElement = jQuery.progressIndicator({
            'position' : 'html',
            'blockInfo' : {
                'enabled' : true
            }
        });
        AppConnector.requestPjax(url).then(function(data) {
            progressIndicatorElement.progressIndicator({
                'mode' : 'hide'
            });
            jQuery('#listViewContents').html(data);
            Portal_List_Js.updatePagination();
        });
    },
    
	updatePagination : function(){
		var previousPageExist = jQuery('#previousPageExist').val();
		var nextPageExist = jQuery('#nextPageExist').val();
		var previousPageButton = jQuery('#previousPageButton');
		var nextPageButton = jQuery('#nextPageButton');
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		var pageStartRange = parseInt(jQuery('#pageStartRange').val());
		var pageEndRange = parseInt(jQuery('#pageEndRange').val());
		var pages = jQuery('#totalPageCount').text();
		var totalNumberOfRecords = jQuery('.totalNumberOfRecords');
		var pageNumbersTextElem = jQuery('.pageNumbersText');
        var currentPage = parseInt(jQuery('#pageNumber').val());
        
        jQuery('#pageToJump').val(currentPage);
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
	}
},{
    
    registerAddBookmark : function() {
        jQuery('.addBookmark').on('click', function() {
            var params = {
                'module' : app.getModuleName(),
                'parent' : app.getParentModuleName(),
                'view' : 'EditAjax'
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    
    registerEditBookmark : function() {
        jQuery('.editRecord').live('click', function(e) {
            e.stopPropagation();
            var currentTarget = jQuery(e.currentTarget);
            var id = currentTarget.closest('.listViewEntries').data('id');
            var params = {
                'module' : app.getModuleName(),
                'parent' : app.getParentModuleName(),
                'view' : 'EditAjax',
                'record' : id
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    
    registerDeleteBookmark : function() {
        jQuery('.deleteRecord').live('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            var currentTarget = jQuery(e.currentTarget);
            var id = currentTarget.closest('.listViewEntries').data('id');
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            Vtiger_Helper_Js.showConfirmationBox({
                'message' : message
            }).then(function(e) {
                var params = {
                    'module' : app.getModuleName(),
                    'parent' : app.getParentModuleName(),
                    'action' : 'DeleteAjax',
                    'record' : id
                };
                AppConnector.request(params).then(function(data) {
                    if(data.success) {
                        var params = {
                            title : app.vtranslate('JS_MESSAGE'),
                            text: data.result.message,
                            animation: 'show',
                            type: 'error'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                        var url = Portal_List_Js.getDefaultParams();
                        Portal_List_Js.loadListViewContent(url);
                    }
                });
            });
        });
    },
    
    registerAlphabetSearch : function() {
        jQuery('.portalAlphabetSearch').live('click', function(e) {
            var currentTarget = jQuery(e.currentTarget);
            var searchValue = currentTarget.find('a').attr('id');
            var url = Portal_List_Js.getDefaultParams();
            url['search_value'] = searchValue;
            Portal_List_Js.loadListViewContent(url);
        })
    },
    
    registerSortingEvent : function() {
        jQuery('.portalListViewHeader').live('click', function(e) {
            var currentTarget = jQuery(e.currentTarget);
            var orderBy = currentTarget.attr('id');
            var sortOrder = currentTarget.data('nextsortorderval');
            var url = Portal_List_Js.getDefaultParams();
            url['orderby'] = orderBy;
            url['sortorder'] = sortOrder;
            Portal_List_Js.loadListViewContent(url);
        });
    },
    
    registerPreviousPageEvent : function() {
        jQuery('#previousPageButton').on('click', function(e) {
            var currentPage = jQuery('#pageNumber').val();
            var previousPage = parseInt(currentPage) - 1;
            if(previousPage < 1)
                return false;
            var url = Portal_List_Js.getDefaultParams();
            url['page'] = previousPage;
            Portal_List_Js.loadListViewContent(url);
        });
    },
    
    registerNextPageEvent : function() {
        jQuery('#nextPageButton').on('click', function(e) {
            var currentPage = jQuery('#pageNumber').val();
            var nextPage = parseInt(currentPage) + 1;
            var totalPages = parseInt(jQuery('#totalPageCount').text());
            if(nextPage > totalPages)
                return false;
            var url = Portal_List_Js.getDefaultParams();
            url['page'] = nextPage;
            Portal_List_Js.loadListViewContent(url);
        });
    },
    
    registerRowClickEvent: function(){
		jQuery('.listViewEntries').live('click', function(e){
			if(jQuery(e.target, jQuery(e.currentTarget)).is('td:first-child')) return;
			if(jQuery(e.target).is('input[type="checkbox"]')) return;
			var elem = jQuery(e.currentTarget);
			var recordUrl = elem.data('recordurl');
            if(typeof recordUrl == 'undefined') {
                return;
            }
			window.location.href = recordUrl;
		});
	},
    
    registerEvents : function(){
		this._super();
        this.registerAddBookmark();
        this.registerEditBookmark();
        this.registerDeleteBookmark();
        this.registerAlphabetSearch();
        this.registerSortingEvent();
        this.registerPreviousPageEvent();
        this.registerNextPageEvent();
        this.registerRowClickEvent();
	}
});