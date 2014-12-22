/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_List_Js("Reports_List_Js",{

	listInstance : false,

	addReport : function(url){
		var listInstance = Reports_List_Js.listInstance;
		window.location.href=url+'&folder='+listInstance.getCurrentCvId();
	},

	triggerAddFolder : function(url) {
		var params = url;
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(params).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				var callBackFunction = function(data){
					jQuery('#addFolder').validationEngine({
						// to prevent the page reload after the validation has completed
						'onValidationComplete' : function(form,valid){
                            return valid;
						}
					});
					Reports_List_Js.listInstance.folderSubmit().then(function(data){
						if(data.success){
							var result = data.result;
							if(result.success){
								//TODO use pines alert for showing folder has saved
								app.hideModalWindow();
								var info = result.info;
								Reports_List_Js.listInstance.updateCustomFilter(info);
							} else {
								var result = result.message;
								var folderNameElement = jQuery('#foldername');
								folderNameElement.validationEngine('showPrompt', result , 'error','topLeft',true);
							}
						} else {
							app.hideModalWindow();
							var params = {
								title : app.vtranslate('JS_ERROR'),
								text : data.error.message
							}
							Vtiger_Helper_Js.showPnotify(params);
						}
					});
				};
				app.showModalWindow(data,function(data){
					if(typeof callBackFunction == 'function'){
						callBackFunction(data);
					}
				});
			}
		)
	},

	massDelete : function(url) {
		var listInstance = Reports_List_Js.listInstance;
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();

			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					var deleteURL = url+'&viewname='+cvId+'&selected_ids='+selectedIds+'&excluded_ids='+excludedIds;
					var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					var progressIndicatorElement = jQuery.progressIndicator({
						'message' : deleteMessage,
						'position' : 'html',
						'blockInfo' : {
							'enabled' : true
						}
					});
					AppConnector.request(deleteURL).then(
						function(data) {
							progressIndicatorElement.progressIndicator({
								'mode' : 'hide'
							})
							if(data){
								listInstance.massActionPostOperations(data);
							}
						});
				},
				function(error, err){
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},

	massMove : function(url){
		var listInstance = Reports_List_Js.listInstance;
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds,
				"viewname" : cvId
			};

			var params = {
				"url":url,
				"data" : postData
			};
			var progressIndicatorElement = jQuery.progressIndicator();
			AppConnector.request(params).then(
				function(data) {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					var callBackFunction = function(data){
						var reportsListInstance = new Reports_List_Js();

						reportsListInstance.moveReports().then(function(data){
							if(data){
								listInstance.massActionPostOperations(data);
							}
						});
					}
					app.showModalWindow(data,callBackFunction);
				}
			)
		} else{
			listInstance.noRecordSelectedAlert();
		}

	}

},{

	init : function(){
		Reports_List_Js.listInstance = this;
	},

	folderSubmit : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#addFolder').on('submit',function(e){
			var validationResult = jQuery(e.currentTarget).validationEngine('validate');
			if(validationResult == true){
				var formData = jQuery(e.currentTarget).serializeFormData();
				AppConnector.request(formData).then(
					function(data){
						aDeferred.resolve(data);
					}
				);
			}
			e.preventDefault();
		});
		return aDeferred.promise();
	},

	moveReports : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#moveReports').on('submit',function(e){
			var formData = jQuery(e.currentTarget).serializeFormData();
			AppConnector.request(formData).then(
				function(data){
					aDeferred.resolve(data);
				}
			);
			e.preventDefault();
		});
		return aDeferred.promise();
	},

	updateCustomFilter : function (info){
		var folderId = info.folderId;
		var customFilter =  jQuery("#customFilter");
		var constructedOption = this.constructOptionElement(info);
		var optionId = 'filterOptionId_'+folderId;
		var optionElement = jQuery('#'+optionId);
		if(optionElement.length > 0){
			optionElement.replaceWith(constructedOption);
			customFilter.trigger("liszt:updated");
		} else {
			customFilter.find('#foldersBlock').append(constructedOption).trigger("liszt:updated");
		}
	},

	constructOptionElement : function(info){
		return '<option data-editable="'+info.isEditable+'" data-deletable="'+info.isDeletable+'" data-editurl="'+info.editURL+'" data-deleteurl="'+info.deleteURL+'" class="filterOptionId_'+info.folderId+'" id="filterOptionId_'+info.folderId+'" value="'+info.folderId+'" data-id="'+info.folderId+'">'+info.folderName+'</option>';

	},

	/*
	 * Function to perform the operations after the mass action
	 */
	massActionPostOperations : function(data){
		var thisInstance = this;
		var cvId = this.getCurrentCvId();
		if(data.success){
			var module = app.getModuleName();
			AppConnector.request('index.php?module='+module+'&view=List&viewname='+cvId).then(
				function(data) {
					jQuery('#recordsCount').val('');
					jQuery('#totalPageCount').text('');
					app.hideModalWindow();
					var listViewContainer = thisInstance.getListViewContentContainer();
					listViewContainer.html(data);
					jQuery('#deSelectAllMsg').trigger('click');
					thisInstance.calculatePages().then(function(){
						thisInstance.updatePagination();
					});
				});
		} else {
			app.hideModalWindow();
			var params = {
				title : app.vtranslate('JS_LBL_PERMISSION'),
				text : data.error.message+ ' : ' + data.error.code
			}
			Vtiger_Helper_Js.showPnotify(params);
		}
	},
	/*
	 * function to delete the folder
	 */
	deleteFolder : function(event,url){
		var thisInstance =this;
		AppConnector.request(url).then(
			function(data){
				if(data.success) {
					var chosenOption = jQuery(event.currentTarget).closest('.select2-result-selectable');
					var selectOption = thisInstance.getSelectOptionFromChosenOption(chosenOption);
					selectOption.remove();
					var customFilterElement = thisInstance.getFilterSelectElement();
					customFilterElement.trigger("liszt:updated");
					var defaultCvid = customFilterElement.find('option:first').val();
					customFilterElement.select2("val", defaultCvid);
					customFilterElement.trigger('change');
				} else {
					app.hideModalWindow();
					var params = {
						title : app.vtranslate('JS_INFORMATION'),
						text : data.error.message
					}
					Vtiger_Helper_Js.showPnotify(params);
				}
			}
		)
	},
		/*
	 * Function to register the click event for edit filter
	 */
	registerEditFilterClickEvent : function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		listViewFilterBlock.on('mouseup','li i.editFilter',function(event){
			var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
			var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
			var editUrl = currentOptionElement.data('editurl');
			Reports_List_Js.triggerAddFolder(editUrl);
			event.stopPropagation();
		});
	},

	/*
	 * Function to register the click event for delete filter
	 */
	registerDeleteFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		//used mouseup event to stop the propagation of customfilter select change event.
		listViewFilterBlock.on('mouseup','li i.deleteFilter',function(event){
			// To close the custom filter Select Element drop down
			thisInstance.getFilterSelectElement().data('select2').close();
			var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					var deleteUrl = currentOptionElement.data('deleteurl');
					thisInstance.deleteFolder(event,deleteUrl);
				},
				function(error, err){
				}
			);
			event.stopPropagation();
		});
	},

	registerEvents : function(){
		this._super();
	}
});
