/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Documents_List_Js", {

	triggerAddFolder : function(url) {
		var params = url;
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(params).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				var callBackFunction = function(data){
					jQuery('#addDocumentsFolder').validationEngine({
						// to prevent the page reload after the validation has completed
						'onValidationComplete' : function(form,valid){
                             return valid;
						}
					});
					Vtiger_List_Js.getInstance().folderSubmit().then(function(data){
						app.hideModalWindow();
						if(data.success){
							var result = data.result;
							if(result.success){
								var info = result.info;
								Vtiger_List_Js.getInstance().updateCustomFilter(info);
								var  params = {
									title : app.vtranslate('JS_NEW_FOLDER'),
									text : result.message,
									delay: '2000',
									type: 'success'
								}
								Vtiger_Helper_Js.showPnotify(params);
							} else {
								var result = result.message;
								var folderNameElement = jQuery('#documentsFolderName');
								folderNameElement.validationEngine('showPrompt', result , 'error','topLeft',true);
							}
						} else {
							var  params = {
									title : app.vtranslate('JS_ERROR'),
									text : data.error.message,
									type: 'error'
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

	massMove : function(url){
		var listInstance = Vtiger_List_Js.getInstance();
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

            var searchValue = listInstance.getAlphabetSearchValue();

            if(searchValue.length > 0) {
                postData['search_key'] = listInstance.getAlphabetSearchField();
                postData['search_value'] = searchValue;
                postData['operator'] = "s";
            }

			var params = {
				"url":url,
				"data" : postData
			};
			var progressIndicatorElement = jQuery.progressIndicator();
			AppConnector.request(params).then(
				function(data) {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					var callBackFunction = function(data){

						listInstance.moveDocuments().then(function(data){
							if(data){
								var result = data.result;
								if(result.success){
									app.hideModalWindow();
									var  params = {
										title : app.vtranslate('JS_MOVE_DOCUMENTS'),
										text : result.message,
										delay: '2000',
										type: 'success'
									}
									Vtiger_Helper_Js.showPnotify(params);
									var urlParams = listInstance.getDefaultParams();
									listInstance.getListViewRecords(urlParams);
								} else {
									var  params = {
										title : app.vtranslate('JS_OPERATION_DENIED'),
										text : result.message,
										delay: '2000',
										type: 'error'
									}
									Vtiger_Helper_Js.showPnotify(params);
								}
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

} ,{


	folderSubmit : function() {
		var aDeferred = jQuery.Deferred();
		jQuery('#addDocumentsFolder').on('submit',function(e){
			var validationResult = jQuery(e.currentTarget).validationEngine('validate');
			if(validationResult == true){
				var formData = jQuery(e.currentTarget).serializeFormData();
				var foldername = jQuery.trim(formData.foldername);
				formData.foldername = foldername;
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
		var cvId = this.getCurrentCvId();
		return '<option data-deletable="1" data-folderid="'+info.folderid+'" data-foldername="'+info.folderName+'" class="filterOptionId_folder'+info.folderid+' folderOption" id="filterOptionId_folder'+info.folderid+'"  data-id="'+cvId+'" >'+info.folderName+'</option>';

	},

	moveDocuments : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#moveDocuments').on('submit',function(e){
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

	getDefaultParams : function() {
		var search_value = jQuery('#customFilter').find('option:selected').data('foldername');
		var customParams = {
					'folder_id' : 'folderid',
					'folder_value' : search_value
					}
		var params = this._super();
		jQuery.extend(params,customParams);
		return params;
	},

	registerDeleteFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if(listViewFilterBlock != false){
			//used mouseup event to stop the propagation of customfilter select change event.
			listViewFilterBlock.on('mouseup','li i.deleteFilter',function(event){
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
				var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
				if(liElement.hasClass('folderOption')){
					if(liElement.find('.deleteFilter').hasClass('dull')){
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_FOLDER_IS_NOT_EMPTY'));
						return;
					} else {
						Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
							function(e) {
								var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
								var folderId = currentOptionElement.data('folderid');
								var params = {
									module : app.getModuleName(),
									mode  : 'delete',
									action : 'Folder',
									folderid : folderId
								}
								AppConnector.request(params).then(function(data) {
									if(data.success) {
										currentOptionElement.remove();
										thisInstance.getFilterSelectElement().trigger('change');
									}
								})
							},
							function(error, err){
							}
						);
					}

				} else {
					Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
						function(e) {
							var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
							var deleteUrl = currentOptionElement.data('deleteurl');
							window.location.href = deleteUrl;
						},
						function(error, err){
						}
					);
				}
			});
		}
	},

	performFilterImageActions : function(liElement) {
		jQuery('.filterActionImages').clone(true,true).removeClass('filterActionImages').addClass('filterActionImgs').appendTo(liElement.find('.select2-result-label')).show();
		var currentOptionElement = this.getSelectOptionFromChosenOption(liElement);
		var deletable = currentOptionElement.data('deletable');
		if(deletable != '1'){
			if(liElement.hasClass('folderOption')){
				liElement.find('.deleteFilter').addClass('dull');
			}else{
				liElement.find('.deleteFilter').remove();
			}
		}
		if(liElement.hasClass('defaultFolder')) {
			liElement.find('.deleteFilter').remove();
		}
		var editable = currentOptionElement.data('editable');
		if(editable != '1'){
			liElement.find('.editFilter').remove();
		}
		var pending = currentOptionElement.data('pending');
		if(pending != '1'){
			liElement.find('.approveFilter').remove();
		}
		var approve = currentOptionElement.data('public');
		if(approve != '1'){
			liElement.find('.denyFilter').remove();
		}
	}

});

Vtiger_Base_Validator_Js("Vtiger_FolderName_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var instance = new Vtiger_FieldLabel_Validator_Js();
		instance.setElement(field);
		var response = instance.validate();
		if(response != true){
			return instance.getError();
		}
	}

},{
	/**
	 * Function to validate the field label
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();
		return this.validateValue(fieldValue);
	},

	validateValue : function(fieldValue){
		var specialChars = /[&\<\>\:\'\"\,\_]/ ;

		if (specialChars.test(fieldValue)) {
			var errorInfo = app.vtranslate('JS_SPECIAL_CHARACTERS')+" & < > ' \" : , _ "+app.vtranslate('JS_NOT_ALLOWED');
			this.setError(errorInfo);
			return false;
		}
        return true;
	}
});


