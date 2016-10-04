/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_Edit_Js('Settings_Webforms_Edit_Js', {}, {
	
	duplicateWebformNames : {},
	
	targetFieldsTable : false,
	listElementsLenght : '',
	/**
	 * Function to get source module fields table
	 */
	getSourceModuleFieldTable : function() {
		var editViewForm = this.getForm();
		if(this.targetFieldsTable == false){
			this.targetFieldsTable = editViewForm.find('[name="targetModuleFields"]');
		}
		return this.targetFieldsTable;
	},
	
	targetModule : false,
	
	/**
	 * Function to set target module
	 */
	setTargetModule : function(targetModuleName){
		this.targetModule = targetModuleName;
	},
	getInstanceListElements: function(){
		var target = jQuery('#fieldsList');
		if(target.length){
			return target[0].selectize.options;
		}
	},
	/**
	 * Function to render selected field UI
	 */
	displaySelectedField : function(selectedField){
		var instance = this.getInstanceListElements();
		var dataSelectedElement = instance[selectedField];

		var editViewForm = this.getForm();
		var targetFieldsTable = this.getSourceModuleFieldTable();
		var selectedFieldInfo = dataSelectedElement.fieldInfo;
		var selectedOptionLabel = selectedFieldInfo.label;
		var selectedOptionName = selectedFieldInfo.name;
		var selectedOptionType = selectedFieldInfo.type;
		var isCustomField = selectedFieldInfo.customField;
		var moduleName = app.getModuleName();
		var fieldInstance = Vtiger_Field_Js.getInstance(selectedFieldInfo,moduleName);
		var fieldMandatoryStatus = dataSelectedElement.mandatory;
		var UI = fieldInstance.getUiTypeSpecificHtml();
		var UI = jQuery(UI);
		var addOnElementExist = UI.find('.input-group-addon');
		var parentInputPrepend = addOnElementExist.closest('.input-group');
		
		if((parentInputPrepend.length > 0) && (selectedOptionType != "reference")){
			parentInputPrepend.find('.input-group-addon').addClass('overWriteAddOnStyles');
		}

		var webFormTargetFieldStructure = '<tr data-name="'+selectedOptionName+'" data-type="'+selectedOptionType+'" data-mandatory-field="'+fieldMandatoryStatus+'" class="listViewEntries">'+
											'<td class="textAlignCenter">'+
								'<input type="hidden" class="sequenceNumber" name="selectedFieldsData['+selectedField+'][sequence]" value=""/>'+
								'<input type="hidden" value="0" name="selectedFieldsData['+selectedField+'][required]"/>'+
								'<input type="checkbox" value="1" class="markRequired mandatoryField" name="selectedFieldsData['+selectedField+'][required]"/></td>';
		
		webFormTargetFieldStructure+= '<td class="textAlignCenter">'+
										'<input type="hidden" value="0" name="selectedFieldsData['+selectedField+'][hidden]"/>'+
										'<input type="checkbox" value="1" class="markRequired hiddenField" name="selectedFieldsData['+selectedField+'][hidden]"/>'+
										'</td>'+
										'<td class="fieldLabel" data-label="'+selectedOptionLabel+'">'+selectedOptionLabel+'</td>'+
										'<td class="textAlignCenter fieldValue" data-name="fieldUI_'+selectedOptionName+'"></td>';
				
		if(isCustomField){
			webFormTargetFieldStructure+=	'<td>'+app.vtranslate('JS_LABEL')+":"+selectedOptionLabel;
		} else {
			webFormTargetFieldStructure+=	'<td>'+selectedField;
		}
		
		webFormTargetFieldStructure+=	'<div class="pull-right actions">'+
										'<span class="actionImages"><a class="removeTargetModuleField"><span class="glyphicon glyphicon-remove-sign"></span></a></span></div></td></tr>';
				
		targetFieldsTable.append(webFormTargetFieldStructure);
		targetFieldsTable.find('[data-name="fieldUI_'+selectedOptionName+'"]').html(UI);
		
		if (UI.has('input.dateField').length > 0){ 
				app.registerEventForDatePickerFields(UI); 
		} else if(UI.has('input.timepicker-default').length > 0){ 
				app.registerEventForTimeFields(UI); 
		}
		if(UI.attr('multiple')){
			app.showSelect2ElementView(UI);
		} else if((UI.hasClass('chzn-select')) || (UI.find('.chzn-select').length > 0)){
			app.changeSelectElementView(UI);
		}
		if(selectedOptionType == 'reference'){
			this.registerAutoCompleteFields(editViewForm);
		}
	},
	
	/**
	 * Function to register event for onchange event for 
	 * selectize element fro adding and changing sequence fields
	 */
	registerChangeListElements : function(){
		var thisInstance = this;
		var fieldsTable = this.getSourceModuleFieldTable();
		var listElements = fieldsTable.find('#fieldsList');
		listElements.on('change',function(e){
			var target = jQuery(e.currentTarget);
			var selectedElement = target.find(':selected');
			thisInstance.triggerLockMandatoryFieldOptions();
			jQuery('#saveFieldsOrder').attr('disabled',false);
			selectedElement.each(function(){
				if(!jQuery('tr[data-name="selectedFieldsData['+jQuery(this).val()+'][defaultvalue]"]').length){
					thisInstance.addValueToListElement(jQuery(this).val());
				}
			});
		});
	},
	
	removeValueToListElement : function(removedFieldName){
		var thisInstance = this;
		var editViewForm = this.getForm();
		var fieldsTable = this.getSourceModuleFieldTable();
		
		var instance = this.getInstanceListElements();
		var removedFieldObject = instance[removedFieldName];
		var element = fieldsTable.find('#fieldsList');
		var removedFieldLabel = removedFieldObject.text; 
		var selectedFieldOption = editViewForm.find('option[value="'+removedFieldName+'"]');
		var fieldMandatoryStatus = removedFieldObject.mandatory;
		//To handle the mandatory option that are removed using backspace from select2
		if(!fieldMandatoryStatus){
			//Remove the row with respect to option that are removed from select2
			var selectedFieldInfo = removedFieldObject.fieldInfo;
			var removeRowName = selectedFieldInfo.name;
			fieldsTable.find('tr[data-name="'+removeRowName+'"]').find('.removeTargetModuleField').trigger('click');
			if(element.val().length == 1){
				jQuery('#saveFieldsOrder').attr('disabled',true);
			}
		}
	},
	
	addValueToListElement : function(e){
		var thisInstance = this;
		thisInstance.displaySelectedField(e);
		thisInstance.registerEventToHandleOnChangeOfOverrideValue();
	},
	
	/**
	 * Function to register event for making field as required
	 */
	registerEventForMarkRequiredField : function(){
		var thisInstance = this;
		this.getSourceModuleFieldTable().on('change','.markRequired',function(e){
			var message = app.vtranslate('JS_MANDATORY_FIELDS_WITHOUT_OVERRIDE_VALUE_CANT_BE_HIDDEN');
			var element = jQuery(e.currentTarget);
			var elementRow = element.closest('tr');
			var fieldName= elementRow.data('name');
			var fieldType = elementRow.data('type');
			if(fieldType == "multipicklist"){
				fieldName = fieldName+'[]';
			}
			var fieldValue = jQuery.trim(elementRow.find('[name="'+fieldName+'"]').val());
			var isMandatory = element.closest('tr').data('mandatoryField');
			if(isMandatory){
				if(element.hasClass('mandatoryField')){
					element.attr('checked',true);
				}else if(element.hasClass('hiddenField')){
					if(fieldValue == ''){
						element.attr('checked',false);
						thisInstance.showErrorMessage(message);
					}
				}
				e.preventDefault();
				return;
			}else{
				if(element.hasClass('mandatoryField')){
					if(element.is(':checked')){
						var hiddenOption = elementRow.find('.hiddenField');
						if(hiddenOption.is(':checked')){
							if(fieldValue == ''){
								element.attr('checked',false);
								thisInstance.showErrorMessage(message);
								e.preventDefault();
								return;
							}
						}
					}
				}else if(element.hasClass('hiddenField')){
					var mandatoryOption = elementRow.find('.mandatoryField');
					if(mandatoryOption.is(':checked') && fieldValue == ''){
						element.attr('checked',false);
						thisInstance.showErrorMessage(message);
						e.preventDefault();
						return;
					}
				}
			}
		})
	},
	
	/**
	 * Function to show error messages
	 */
	showErrorMessage : function(message){
		var isAlertAlreadyShown = jQuery('.ui-pnotify').length;
		var params = {
			text: message,
			type: 'error'
		};
		if(isAlertAlreadyShown <= 0) {
			Settings_Vtiger_Index_Js.showMessage(params);
		}
	},
	
	/**
	 * Function to handle target module remove field action
	 */
	registerEventForRemoveTargetModuleField : function(){
		var thisInstance = this;
		var sourceModuleContainer = this.getSourceModuleFieldTable();
		sourceModuleContainer.on('click','.removeTargetModuleField',function(e){
			var element = jQuery(e.currentTarget);
			var containerRow = element.closest('tr');
			var removedFieldLabel = containerRow.find('td.fieldLabel').text();
			var selectElement = sourceModuleContainer.find('#fieldsList');
			var select2Element = app.getSelect2ElementFromSelect(selectElement);
			select2Element.find('li.select2-search-choice').find('div:contains('+removedFieldLabel+')').closest('li').remove();
			selectElement.find('option:contains('+removedFieldLabel+')').removeAttr('selected');
			if(selectElement.val().length == 1){
				jQuery('#saveFieldsOrder').attr('disabled',true);
			}
			thisInstance.triggerLockMandatoryFieldOptions();
			containerRow.remove();
		})
	},
	
	/**
	 * Function to lock mandatory option in select2
	 */
	lockMandatoryOptionInSelect2 : function(mandatoryFieldValue){
		var sourceModuleContainer = this.getSourceModuleFieldTable();
		var fieldsListSelect2Element = sourceModuleContainer.find('#fieldsList');
		fieldsListSelect2Element.parent().find('.items [data-value="'+mandatoryFieldValue+'"] a.remove').remove();
	},
	
	/**
	 * Function to trigger lock mandatory field options in edit mode
	 */
	triggerLockMandatoryFieldOptions : function(){
		var thisInstance =this;
		var editViewForm = this.getForm();
		var instance = this.getInstanceListElements();
		var selectedOptions = editViewForm.find('#fieldsList option:selected');
		selectedOptions.each(function(){
			var value = jQuery(this).val();
			var selectedOption = instance[value];;
			var mandatoryStatus = selectedOption.mandatory;
			if(mandatoryStatus){
				thisInstance.lockMandatoryOptionInSelect2(value);
			}
		});
	},
	
	/**
	 * Function to handle on change of target module
	 */
	registerEventToHandleChangeofTargetModule : function(){
		var thisInstance =this;
		var editViewForm = this.getForm();
		editViewForm.find('[name="targetmodule"]').on('change',function(e){
			var element = jQuery(e.currentTarget);
			var targetModule = element.val();
			var existingTargetModule = thisInstance.targetModule;
			
			if(existingTargetModule == targetModule){
				return;
			}
			
			var params = {
			"module" : app.getModuleName(),
			"parent" : app.getParentModuleName(),
			"view" : "GetSourceModuleFields",
			"sourceModule" : targetModule
			}
			var message = app.vtranslate('JS_LOADING_TARGET_MODULE_FIELDS');
			var progressIndicatorElement = jQuery.progressIndicator({
				'message' : message,
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			AppConnector.request(params).then(
				function(data){
					if(data){
						progressIndicatorElement.progressIndicator({
							'mode' : 'hide'
						})
						editViewForm.find('.targetFieldsTableContainer').html(data);
						thisInstance.targetFieldsTable = editViewForm.find('[name="targetModuleFields"]');
						thisInstance.setTargetModule(targetModule);
						thisInstance.registerBasicEvents();
						thisInstance.eventToHandleChangesForReferenceFields();
					}
				},
				function(error){

				})
		})
	},
	
	/**
	 * Function to add floatNone and displayInlineBlock class for
	 * input-group-addon element in a form
	 */
	addExternalStylesForElement : function(){
		var editViewForm = this.getForm();
		var targetModuleFieldsTable = this.getSourceModuleFieldTable();
		var addOnElementExist = editViewForm.find('.input-group-addon');
		var parentInputPrepend = addOnElementExist.closest('.input-group');
		if(parentInputPrepend.length > 0 && (!parentInputPrepend.hasClass('input-group'))){
			parentInputPrepend.find('.input-group-addon').addClass('overWriteAddOnStyles');
		}
		targetModuleFieldsTable.find('input.timepicker-default').removeClass('input-sm');
		targetModuleFieldsTable.find('textarea').removeClass('input-xxlarge').css('width',"80%");
		targetModuleFieldsTable.find('input.currencyField').css('width',"210px")
	},
	
	/**
	 * Function to register Basic Events
	 */
	registerBasicEvents : function(){
		var thisInstance = this;
		var editViewForm = this.getForm();
		app.changeSelectElementView();
		//app.showSelect2ElementView(editViewForm.find('select.select2'));
		app.showSelectizeElementView(editViewForm.find('select.selectizeElement'),{plugins: ['drag_drop','remove_button'],
			onInitialize: function () {
				var s = this, children = this.revertSettings.$children;
				if (children.first().is('optgroup')) {
					children = children.find('option');
				}
				children.each(function () {
					var data = $(this).data();
					$.extend(s.options[this.value], data);
				});
			},
			/*,
			onItemAdd: function(e,v){
				thisInstance.addValueToListElement(e);
			},*/
			onItemRemove: function(e,v){
				thisInstance.removeValueToListElement(e);
			}
		});
		//this.registerOnChangeEventForSelect2();
		this.registerChangeListElements();
		this.registerEventForRemoveTargetModuleField();
		this.registerEventForMarkRequiredField();
		this.triggerLockMandatoryFieldOptions();
		this.addExternalStylesForElement();
		
		/** Register for fields only if field exist in a form**/
		if (editViewForm.has('input.dateField').length > 0){
			app.registerEventForDatePickerFields(editViewForm);
		}
		if(editViewForm.has('input.timepicker-default').length > 0){
			app.registerEventForTimeFields(editViewForm);
		}
		//api to support target module fields sortable
		//this.makeMenuItemsListSortable();
		this.registerEventForFieldsSaveOrder();
		//this.arrangeSelectedChoicesInOrder();
		this.registerEventToHandleOnChangeOfOverrideValue();
		this.registerAutoCompleteFields(editViewForm);
	},
	
	/**
	 * Function to handle onchange event of override values
	 */
	registerEventToHandleOnChangeOfOverrideValue : function() {
		var thisInstance = this;
		var container  = this.getSourceModuleFieldTable();
		var fieldRows = container.find('tr.listViewEntries');
		jQuery(fieldRows).each(function(key,value){
			var fieldRow = jQuery(value);
			var fieldName = fieldRow.data('name');
			var fieldType = fieldRow.data('type');
			if(fieldType == "multipicklist"){
				fieldName = fieldName+'[]';
			}
			fieldRow.find('[name="'+fieldName+'"]').on('change',function(e){
				var element = jQuery(e.currentTarget);
				var value = jQuery.trim(element.val());
				var mandatoryField = fieldRow.find('.mandatoryField');
				var hiddenField = fieldRow.find('.hiddenField');
				if((value == "") && (mandatoryField.is(':checked')) && (hiddenField.is(':checked'))){
					hiddenField.attr('checked',false);
					thisInstance.showErrorMessage(app.vtranslate('JS_MANDATORY_FIELDS_WITHOUT_OVERRIDE_VALUE_CANT_BE_HIDDEN'));
					return;
				}
			})
		})
	},
	
	/**
	 * Function to save fields order in a webform
	 */
	registerEventForFieldsSaveOrder : function(){
		var thisInstance = this;
		jQuery('#saveFieldsOrder').on('click',function(e, updateRows){
			if(typeof updateRows == "undefined"){
				updateRows = true;
			}
			var element = jQuery(e.currentTarget);
			var selectElement = jQuery('#fieldsList');
			var i = 1;
			selectElement.find(':selected').each(function(){
				jQuery('tr[data-name="selectedFieldsData['+jQuery(this).val()+'][defaultvalue]"]').find('.sequenceNumber').val(i++);
			})
			if(updateRows){
				thisInstance.arrangeFieldRowsInSequence();
				element.attr("disabled",true);
			}
		})
	},
	
	/**
	 * Function to arrange field rows according to selected sequence
	 */
	arrangeFieldRowsInSequence : function() {
		var selectElement = jQuery('#fieldsList');
		var orderedSelect2Options = selectElement.find(':selected');
			
		//Arrange field rows according to selected sequence
		var totalFieldsSelected = orderedSelect2Options.length;
		var selectedFieldRows = jQuery('tr.listViewEntries');
		for(var index=totalFieldsSelected;index>0;index--){
			var rowInSequence = jQuery('[class="sequenceNumber"][value="'+index+'"]',selectedFieldRows).closest('tr');
			rowInSequence.insertAfter(jQuery('[name="targetModuleFields"]').find('[name="fieldHeaders"]'));
		}
	},
	
	/**
	 * Function which will register reference field clear event
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerClearReferenceSelectionEvent : function(container) {
		container.on('click','.clearReferenceSelection', function(e){
			var element = jQuery(e.currentTarget);
			var parentTdElement = element.closest('td');
			var fieldNameElement = parentTdElement.find('.sourceField');
			fieldNameElement.val('');
			parentTdElement.find('.referenceFieldDisplay').removeAttr('readonly').removeAttr('value');
			element.trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
			e.preventDefault();
		})
	},
	
	/**
	 * Function to register form for validation
	 */
	registerSubmitEvent : function(){
		var editViewForm = this.getForm();
		editViewForm.submit(function(e){
			//Form should submit only once for multiple clicks also
			if(typeof editViewForm.data('submit') != "undefined") {
				return false;
			} else {
				if(editViewForm.validationEngine('validate')) {
					editViewForm.data('submit', 'true');
					var displayElementsInForm = jQuery( "input.referenceFieldDisplay" );
					if(typeof displayElementsInForm != "undefined"){
						var noData;
						if(displayElementsInForm.length > 1){
							jQuery(displayElementsInForm).each(function(key,value){
								var element = jQuery(value);
								var parentRow = element.closest('tr');
								var fieldValue = parentRow.find('.sourceField').val()
								var mandatoryField = parentRow.find('.mandatoryField');
								if(((fieldValue == '') || (fieldValue == 0)) && (mandatoryField.is(':checked'))){
									noData = true;
									return false;
								}
							})
						}else if(displayElementsInForm.length == 1){
							var parentRow = displayElementsInForm.closest('tr');
							var fieldValue = parentRow.find('.sourceField').val()
							var mandatoryField = parentRow.find('.mandatoryField');
							if(((fieldValue == '')  || (fieldValue == 0)) && (mandatoryField.is(':checked'))){
								noData = true;
							}
						}
					}
					if(noData){
						var isAlertAlreadyShown = jQuery('.ui-pnotify').length;
						var params = {
							text: app.vtranslate('JS_REFERENCE_FIELDS_CANT_BE_MANDATORY_WITHOUT_OVERRIDE_VALUE'),
							type: 'error'
						};
						if(isAlertAlreadyShown <= 0) {
							Settings_Vtiger_Index_Js.showMessage(params);
						}
						editViewForm.removeData('submit');
						return false;
					}
					//on submit form trigger the recordPreSave event
					var recordPreSaveEvent = jQuery.Event(Vtiger_Edit_Js.recordPreSave);
					editViewForm.trigger(recordPreSaveEvent);
					if(recordPreSaveEvent.isDefaultPrevented()) {
						//If duplicate record validation fails, form should submit again
						editViewForm.removeData('submit');
						return false;
					}
				} else {
					//If validation fails, form should submit again
					editViewForm.removeData('submit');
					app.formAlignmentAfterValidation(editViewForm);
				}
			}
		})
	},
	
	/**
	 * This function will register before saving any record
	 */
	registerRecordPreSaveEvent : function(form) {
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}
		
		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
			var webformName = jQuery('[name="name"]').val();
			 if(!(webformName in thisInstance.duplicateWebformNames)) {
				thisInstance.checkDuplicate().then(
					function(data){
						 thisInstance.duplicateWebformNames[webformName] = true;
						form.submit();
					},
					function(data, err){
						thisInstance.duplicateWebformNames[webformName] = false;
						thisInstance.showErrorMessage(app.vtranslate('JS_WEBFORM_WITH_THIS_NAME_ALREADY_EXISTS'));
					})
			 }
			 else {
				if(thisInstance.duplicateWebformNames[webformName] == true){
					form.submit();
					return true;
				} else {
					thisInstance.showErrorMessage(app.vtranslate('JS_WEBFORM_WITH_THIS_NAME_ALREADY_EXISTS'));
				}
			}
			e.preventDefault();
		})
	},
	
	checkDuplicate : function(){
		var aDeferred = jQuery.Deferred();
		var webformName = jQuery('[name="name"]').val();
		var recordId = jQuery('[name="record"]').val();
		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'CheckDuplicate',
			'name'	 : webformName,
			'record' : recordId
		}
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var result = response['success'];
				if(result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
		var editViewForm = this.getForm();
		editViewForm.submit(function(e){
			var displayElementsInForm = jQuery( "input.referenceFieldDisplay" );
			if(typeof displayElementsInForm != "undefined"){
				var noData;
				if(displayElementsInForm.length > 1){
					jQuery(displayElementsInForm).each(function(key,value){
						var element = jQuery(value);
						var parentRow = element.closest('tr');
						var fieldValue = parentRow.find('.sourceField').val()
						var mandatoryField = parentRow.find('.mandatoryField');
						if(((fieldValue == '') || (fieldValue == 0)) && (mandatoryField.is(':checked'))){
							noData = true;
							return false;
						}
					})
				}else if(displayElementsInForm.length == 1){
					var parentRow = displayElementsInForm.closest('tr');
					var fieldValue = parentRow.find('.sourceField').val()
					var mandatoryField = parentRow.find('.mandatoryField');
					if(((fieldValue == '')  || (fieldValue == 0)) && (mandatoryField.is(':checked'))){
						noData = true;
					}
				}
			}
			if(noData){
				var isAlertAlreadyShown = jQuery('.ui-pnotify').length;
				var params = {
					text: app.vtranslate('JS_REFERENCE_FIELDS_CANT_BE_MANDATORY_WITHOUT_OVERRIDE_VALUE'),
					type: 'error'
				};
				if(isAlertAlreadyShown <= 0) {
					Settings_Vtiger_Index_Js.showMessage(params);
				}
				editViewForm.removeData('submit');
				return false;
			}
		})
		var params = app.validationEngineOptions;
		params.onValidationComplete = function(editViewForm, valid){
			if(valid) {
				jQuery('#saveFieldsOrder').trigger('click',[false]);
				return valid;
			}
			return false;
		}
		editViewForm.validationEngine(params);
	},
    
    /**
     * Function makes the user list select element mandatory if the roundrobin is checked 
     */
    registerUsersListMandatoryOnRoundrobinChecked : function() {
        var roundrobinCheckboxElement = jQuery('[name="roundrobin"]');
        var userListSelectElement = jQuery('[data-name="roundrobin_userid"]');
        var userListLabelElement = userListSelectElement.closest('td').prev().find('label');
        if(!roundrobinCheckboxElement.is(':checked')){
            userListLabelElement.find('span.redColor').addClass('hide');
            userListSelectElement.attr('data-validation-engine','');
        }
        roundrobinCheckboxElement.change(function(){
            if(jQuery(this).is(':checked')){
                userListLabelElement.find('span.redColor').removeClass('hide');
                userListSelectElement.attr('data-validation-engine','validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
            }
            else{
                userListLabelElement.find('span.redColor').addClass('hide');
                userListSelectElement.attr('data-validation-engine','');
            }
        });
    },
	
	/**
	 * Function to append popup reference module names if exist
	 */
	eventToHandleChangesForReferenceFields : function(){
		var thisInstance = this;
		var editViewForm = this.getForm();
		var referenceModule = editViewForm.find('[name="popupReferenceModule"]');
		if(referenceModule.length > 1){
			jQuery(referenceModule).each(function(key,value){
				var element = jQuery(value);
				thisInstance.appendPopupReferenceModuleName(element);
			})
		}else if(referenceModule.length == 1){
			thisInstance.appendPopupReferenceModuleName(referenceModule);
		}
	},
	
	appendPopupReferenceModuleName : function(element){
		var referredModule = element.val();
		var fieldName = element.closest('tr').data('name');
		var referenceName = fieldName.split('[defaultvalue]');
		referenceName = referenceName[0]+'[referenceModule]';
		var html = '<input type="hidden" name="'+referenceName+'" value="'+referredModule+'" class="referenceModuleName"/>'
		element.closest('td').append(html);
		element.closest('td').find('[name="'+fieldName+'_display"]').addClass('referenceFieldDisplay').removeAttr('name');
	},
	
	setReferenceFieldValue : function(container, params) {
		var sourceField = container.find('input[class="sourceField"]').attr('name');
		var fieldElement = container.find('input[name="'+sourceField+'"]');
		var fieldDisplayElement = container.find('.referenceFieldDisplay');
		var popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();

		var selectedName = params.name;
		var id = params.id;

		fieldElement.val(id)
		fieldDisplayElement.val(selectedName).attr('readonly',true);
		fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});

		fieldDisplayElement.validationEngine('closePrompt',fieldDisplayElement);
	},
	
	referenceCreateHandler : function(container) {
		var thisInstance = this;
		var referenceFieldName = container.attr('data-name');
		var postQuickCreateSave  = function(data) {
			container = jQuery('td[data-name="'+referenceFieldName+'"]');
			var params = {};
			params.name = data.result._recordLabel;
			params.id = data.result._recordId;
            thisInstance.setReferenceFieldValue(container, params);
		}

		var referenceModuleName = this.getReferencedModuleName(container);
		Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName,{callbackFunction: postQuickCreateSave});
	},
	
	/**
	 * Function which will handle the registrations for the elements 
	 */
	registerEvents : function() {
		var form = this.getForm();
		this._super();
		this.registerEventToHandleChangeofTargetModule();
		this.registerBasicEvents(form);
		var targetModule = form.find('[name="targetModule"]').val();
		this.setTargetModule(targetModule);
		this.registerUsersListMandatoryOnRoundrobinChecked();
		//api to support reference field related actions
		this.referenceModulePopupRegisterEvent(form);
		this.registerClearReferenceSelectionEvent(form);
		this.registerReferenceCreate(form);
		this.eventToHandleChangesForReferenceFields();
        this.registerRecordPreSaveEvent(form); 
 	    this.registerSubmitEvent(); 
	}
})
