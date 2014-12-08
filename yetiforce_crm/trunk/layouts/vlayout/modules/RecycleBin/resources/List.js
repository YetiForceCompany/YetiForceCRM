/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("RecycleBin_List_Js", {
    emptyRecycleBin: function(url) {
        var message = app.vtranslate('JS_MSG_EMPTY_RB_CONFIRMATION');
        Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                function(e) {
                    var deleteURL = url + '&mode=emptyRecycleBin';
                    var instance = new RecycleBin_List_Js();
                    AppConnector.request(deleteURL).then(
                            function(data) {
                                if (data) {
                                    //fix for EmptyRecycle bin 
                                    jQuery(".clearRecycleBin").attr('disabled','disabled'); 
                                    instance.recycleBinActionPostOperations(data);
                                }
                            }
                    );
                },
                function(error, err) {
                })
    },
    deleteRecords: function(url) {
        var listInstance = Vtiger_List_Js.getInstance();
        var validationResult = listInstance.checkListRecordSelected();
        if (validationResult != true) {
            var selectedIds = listInstance.readSelectedIds(true);
            var cvId = listInstance.getCurrentCvId();
            var message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');
            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                    function(e) {
                        var sourceModule = jQuery('#customFilter').val();
                        var deleteURL = url + '&viewname=' + cvId + '&selected_ids=' + selectedIds + '&mode=deleteRecords&sourceModule=' + sourceModule;
                        var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
                        var progressIndicatorElement = jQuery.progressIndicator({
                            'message': deleteMessage,
                            'position': 'html',
                            'blockInfo': {
                                'enabled': true
                            }
                        });
                        AppConnector.request(deleteURL).then(
                                function(data) {
                                    if (data) {
                                        progressIndicatorElement.progressIndicator({
                                            'mode': 'hide'
                                        })
                                        var instance = new RecycleBin_List_Js();
                                        instance.recycleBinActionPostOperations(data);
                                    }
                                }
                        );
                    },
                    function(error, err) {
                    })
        } else {
            listInstance.noRecordSelectedAlert();
        }

    },
    restoreRecords: function(url) {
        var listInstance = Vtiger_List_Js.getInstance();
        var validationResult = listInstance.checkListRecordSelected();
        if (validationResult != true) {
            var selectedIds = listInstance.readSelectedIds(true);
            var excludedIds = listInstance.readExcludedIds(true);
            var cvId = listInstance.getCurrentCvId();
            var message = app.vtranslate('JS_LBL_RESTORE_RECORDS_CONFIRMATION');
            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                    function(e) {
                        var sourceModule = jQuery('#customFilter').val();
                        var deleteURL = url + '&viewname=' + cvId + '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds + '&mode=restoreRecords&sourceModule=' + sourceModule;
                        var restoreMessage = app.vtranslate('JS_RESTORING_RECORDS');
                        var progressIndicatorElement = jQuery.progressIndicator({
                            'message': restoreMessage,
                            'position': 'html',
                            'blockInfo': {
                                'enabled': true
                            }
                        });
                        AppConnector.request(deleteURL).then(
                                function(data) {
                                    if (data) {
                                        progressIndicatorElement.progressIndicator({
                                            'mode': 'hide'
                                        })
                                        var instance = new RecycleBin_List_Js();
                                        instance.recycleBinActionPostOperations(data);
                                    }
                                }
                        );
                    },
                    function(error, err) {
                    })
        } else {
            listInstance.noRecordSelectedAlert();
        }
    },
    
    /**
     * Function to convert id into json string
     * @param <integer> id
     * @return <string> json string
     */
    convertToJsonString : function(id) {
        var jsonObject = [];
        jsonObject.push(id);
        return JSON.stringify(jsonObject);
    },

       
    /**
     * Function to delete a record
     */
    deleteRecord : function(recordId) {
        var recordId = RecycleBin_List_Js.convertToJsonString(recordId);
		var listInstance = Vtiger_List_Js.getInstance();
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
        var sourceModule = jQuery('#customFilter').val();
        var cvId = listInstance.getCurrentCvId();
		Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
			function(e) {
				var module = app.getModuleName();
				var postData = {
					"module": module,
                    "viewname": cvId,
                    "selected_ids": recordId,
					"action": "RecycleBinAjax",
					"sourceModule": sourceModule,
                    "mode": "deleteRecords"
				}
				var deleteMessage = app.vtranslate('JS_RECORD_GETTING_DELETED');
				var progressIndicatorElement = jQuery.progressIndicator({
					'message' : deleteMessage,
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});
				AppConnector.request(postData).then(
                    function(data) {
                        if(data){
                            progressIndicatorElement.progressIndicator({
                                'mode' : 'hide'
                            })
                            var instance = new RecycleBin_List_Js();
                            instance.recycleBinActionPostOperations(data);
                        }
                    }
                );
            },
            function(error, err){
            });
	},
    
    /**
    * Function to restore a record
    */
    restoreRecord : function(recordId){
        var recordId = RecycleBin_List_Js.convertToJsonString(recordId);
		var listInstance = Vtiger_List_Js.getInstance();
        var sourceModule = jQuery('#customFilter').val();
        var cvId = listInstance.getCurrentCvId();
        var message = app.vtranslate('JS_LBL_RESTORE_RECORD_CONFIRMATION');
        Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
            function(e) {
                var module = app.getModuleName();
                var postData = {
                    "module": module,
                    "action": "RecycleBinAjax",
                    "viewname": cvId,
                    "selected_ids": recordId,
                    "mode": "restoreRecords",
                    "sourceModule": sourceModule
                }
                var restoreMessage = app.vtranslate('JS_RESTORING_RECORD');
                var progressIndicatorElement = jQuery.progressIndicator({
                    'message' : restoreMessage,
                    'position' : 'html',
                    'blockInfo' : {
                        'enabled' : true
                    }
                });
                AppConnector.request(postData).then(
                    function(data) {
                        if(data){
                            progressIndicatorElement.progressIndicator({
                                'mode' : 'hide'
                            })
                            var instance = new RecycleBin_List_Js();
                            instance.recycleBinActionPostOperations(data);
                        }
                    }
                );
            },
            function(error, err){
            });
	}

}, { 
    
    
        //Fix for empty Recycle bin
        //Change Button State ("Enable or Dissable") 
       ListViewPostOperation: function() {
             if(parseInt(jQuery('#deletedRecordsTotalCount').val()) == 0){ 
                jQuery(".clearRecycleBin").attr('disabled','disabled'); 
            }else{ 
                    jQuery(".clearRecycleBin").removeAttr('disabled'); 
            }
       },
       
	getDefaultParams : function() {
		var pageNumber = jQuery('#pageNumber').val();
		var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var cvId = this.getCurrentCvId();
		var orderBy = jQuery('#orderBy').val();
		var sortOrder = jQuery("#sortOrder").val();
		var params = {
			'module': module,
			'parent' : parent,
			'page' : pageNumber,
			'view' : "List",
			'orderby' : orderBy,
			'sortorder' : sortOrder,
			'sourceModule' : jQuery('#customFilter').val()
		}
		return params;
	},
	/*
	 * Function to perform the operations after the Empty RecycleBin
	 */
	recycleBinActionPostOperations : function(data){
		jQuery('#recordsCount').val('');
		jQuery('#totalPageCount').text('');
		var thisInstance = this;
		var cvId = this.getCurrentCvId();
		if(data.success){
			var module = app.getModuleName();
			var params = thisInstance.getDefaultParams();
			AppConnector.request(params).then(
				function(data) {
					app.hideModalWindow();
					var listViewContainer = thisInstance.getListViewContentContainer();
					listViewContainer.html(data);
					jQuery('#deSelectAllMsg').trigger('click');
                                        thisInstance.ListViewPostOperation();
					thisInstance.calculatePages().then(function(){
						thisInstance.updatePagination();					
					});
				});
		} else {
			app.hideModalWindow();
			var params = {
				title : app.vtranslate('JS_LBL_PERMISSION'),
				text : data.error.message
			}
			Vtiger_Helper_Js.showPnotify(params);
		}
	},
	
	getRecordsCount : function(){
		var aDeferred = jQuery.Deferred();
		var recordCountVal = jQuery("#recordsCount").val();
		if(recordCountVal != ''){
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var module = app.getModuleName();
			var sourceModule = jQuery('#customFilter').val();
			var postData = {
				"module": module,
				"sourceModule": sourceModule,
				"view": "ListAjax",
				"mode": "getRecordsCount"
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
	},
	
	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var module = app.getModuleName();
		var cvId = this.getCurrentCvId();
		var pageCountParams = {
			'module' : module,
			'view' : "ListAjax",
			'mode' : "getPageCount",
			'sourceModule': jQuery('#sourceModule').val()
		}
		return pageCountParams;
	},
    
    /*
	 * Function to register the list view delete record click event
	 */
	registerDeleteRecordClickEvent: function(){
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.deleteRecordButton',function(e){
			var elem = jQuery(e.currentTarget);
			var recordId = elem.closest('tr').data('id');
			RecycleBin_List_Js.deleteRecord(recordId);
			e.stopPropagation();
		});
	}, 
    
     /*
	 * Function to register the list view restore record click event
	 */
	registerRestoreRecordClickEvent: function(){
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.restoreRecordButton',function(e){
			var elem = jQuery(e.currentTarget);
			var recordId = elem.closest('tr').data('id');
			RecycleBin_List_Js.restoreRecord(recordId);
			e.stopPropagation();
		});
	},
    
    registerEvents : function() {
        this._super();
        this.registerRestoreRecordClickEvent();
    }
});