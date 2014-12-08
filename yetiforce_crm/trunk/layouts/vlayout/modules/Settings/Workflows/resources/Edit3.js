/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Settings_Workflows_Edit_Js("Settings_Workflows_Edit3_Js",{},{
	
	step3Container : false,
	
	advanceFilterInstance : false,
	
	ckEditorInstance : false,
	
	fieldValueMap : false,
	
	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step3Container;
	},

	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step3Container = element;
		return this;
	},
	
	/**
	 * Function  to intialize the reports step1
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#workflow_step3');
		}
		if(container.is('#workflow_step3')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#workflow_step3'));
		}
	},
	
	registerEditTaskEvent : function() {
		var thisInstance = this;
		var container = this.getContainer();
		container.on('click','[data-url]',function(e) {
			var currentElement = jQuery(e.currentTarget);
			var params = currentElement.data('url');
			var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			AppConnector.request(params).then(function(data) {
				var callBackFunction = function(data) {
					app.showScrollBar(jQuery('#addTaskContainer').find('#scrollContainer'),{
						height : '450px'
					});
                    thisInstance.registerVTCreateTodoTaskEvents();
					var taskType = jQuery('#taskType').val();
					var functionName = 'register'+taskType+'Events';
					if(typeof thisInstance[functionName] != 'undefined' ) {
						thisInstance[functionName].apply(thisInstance);
					}
					thisInstance.registerSaveTaskSubmitEvent(taskType);
					jQuery('#saveTask').validationEngine(app.validationEngineOptions);
					thisInstance.registerFillTaskFieldsEvent();
					thisInstance.registerCheckSelectDateEvent();
				}
				app.showModalWindow(data,function(){
					if(typeof callBackFunction == 'function') {
						callBackFunction(data)
					}
				},{'min-width' : '900px'});
			});
		});
	},
	registerCheckSelectDateEvent : function() {
		jQuery('[name="check_select_date"]').on('change',function(e){
			if(jQuery(e.currentTarget).is(':checked')){
				jQuery('#checkSelectDateContainer').removeClass('hide').addClass('show');
			} else {
				jQuery('#checkSelectDateContainer').removeClass('show').addClass('hide');
			}
		});
	},
	
	registerSaveTaskSubmitEvent : function(taskType) {
		var thisInstance = this;
		jQuery('#saveTask').on('submit',function(e) {
			var form = jQuery(e.currentTarget);
			var validationResult = form.validationEngine('validate');
			if(validationResult == true) {
				var customValidationFunctionName = taskType+'CustomValidation';
				if(typeof thisInstance[customValidationFunctionName] != 'undefined') {
					 var result = thisInstance[customValidationFunctionName].apply(thisInstance);
					 if(result != true) {
						 var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: result,
							animation: 'show',
							type: 'error'
						}
						Vtiger_Helper_Js.showPnotify(params);
						e.preventDefault();
						return;
					 }
				}
				var preSaveActionFunctionName = 'preSave'+taskType;
				if(typeof thisInstance[preSaveActionFunctionName] != 'undefined' ) {
					thisInstance[preSaveActionFunctionName].apply(thisInstance,[taskType]);
				}
				var params  = form.serializeFormData();
				
				AppConnector.request(params).then(function(data){
					if(data.result){
						thisInstance.getTaskList();
						app.hideModalWindow();
					}
				});
			}
			e.preventDefault();
		})
	},
	
	VTUpdateFieldsTaskCustomValidation : function() {
		return this.checkDuplicateFieldsSelected();
	},
	
	VTCreateEntityTaskCustomValidation : function() {
		return this.checkDuplicateFieldsSelected();
	},
	
	checkDuplicateFieldsSelected : function() {
		var selectedFieldNames = jQuery('#save_fieldvaluemapping').find('.conditionRow').find('[name="fieldname"]');
		var result = true;
		var failureMessage = app.vtranslate('JS_SAME_FIELDS_SELECTED_MORE_THAN_ONCE');
		jQuery.each(selectedFieldNames, function(i, ele) {
			var fieldName = jQuery(ele).attr("value");
			var fields = jQuery("[name="+fieldName+"]").not(':hidden');
			if(fields.length > 1) {
				result = failureMessage;
				return false;
			}
		});
		return result;
	},
	
	preSaveVTUpdateFieldsTask : function(tasktype) {
		var values = this.getValues(tasktype);
		jQuery('[name="field_value_mapping"]').val(JSON.stringify(values));
	},
	
	preSaveVTCreateEntityTask : function(tasktype) {
		var values = this.getValues(tasktype);
		jQuery('[name="field_value_mapping"]').val(JSON.stringify(values));
	},
    
    preSaveVTEmailTask : function(tasktype) {
        var textAreaElement = jQuery('#content');
		//To keep the plain text value to the textarea which need to be
		//sent to server
        textAreaElement.val(CKEDITOR.instances['content'].getData());
    },
	
	/**
	 * Function to check if the field selected is empty field
	 * @params : select element which represents the field
	 * @return : boolean true/false
	 */
	isEmptyFieldSelected : function(fieldSelect) {
		var selectedOption = fieldSelect.find('option:selected');
		//assumption that empty field will be having value none
		if(selectedOption.val() == 'none'){
			return true;
		}
		return false;
	},
	
	getVTCreateEntityTaskFieldList : function() {
		return new Array('fieldname', 'value', 'valuetype','modulename');
	},
	
	getVTUpdateFieldsTaskFieldList : function() {
		return new Array('fieldname', 'value', 'valuetype');
	},
	
	getValues : function(tasktype) {
		var thisInstance = this;
		var conditionsContainer = jQuery('#save_fieldvaluemapping');
		var fieldListFunctionName = 'get'+tasktype+'FieldList';
		if(typeof thisInstance[fieldListFunctionName] != 'undefined' ){
			var fieldList = thisInstance[fieldListFunctionName].apply()
		}

		var values = [];
		var conditions = jQuery('.conditionRow', conditionsContainer);
		conditions.each(function(i, conditionDomElement){
			var rowElement = jQuery(conditionDomElement);
			var fieldSelectElement = jQuery('[name="fieldname"]', rowElement);
			var valueSelectElement = jQuery('[data-value="value"]',rowElement);
			//To not send empty fields to server
			if(thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
				return true;
			}
			var fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo');
			var fieldType = fieldDataInfo.type;
			var rowValues = {};
			if(fieldType == 'owner'){
				for(var key in fieldList) {
					var field = fieldList[key];
					if(field == 'value' && valueSelectElement.is('select')){
						rowValues[field] = valueSelectElement.find('option:selected').val();
					} else {
						rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
					}
				}
			} else if (fieldType == 'picklist' || fieldType == 'multipicklist') {
				for(var key in fieldList) {
					var field = fieldList[key];
					if(field == 'value' && valueSelectElement.is('input')) {
						var commaSeperatedValues = valueSelectElement.val();
						var pickListValues = valueSelectElement.data('picklistvalues');
						var valuesArr = commaSeperatedValues.split(',');
						var newvaluesArr = [];
						for(i=0;i<valuesArr.length;i++){
							if(typeof pickListValues[valuesArr[i]] != 'undefined'){
								newvaluesArr.push(pickListValues[valuesArr[i]]);
							} else {
								newvaluesArr.push(valuesArr[i]);
							}
						}
						var reconstructedCommaSeperatedValues = newvaluesArr.join(',');
						rowValues[field] = reconstructedCommaSeperatedValues;
					} else if(field == 'value' && valueSelectElement.is('select') && fieldType == 'picklist'){
						rowValues[field] = valueSelectElement.val();
					} else if(field == 'value' && valueSelectElement.is('select') && fieldType == 'multipicklist'){
						var value = valueSelectElement.val();
						if(value == null){
							rowValues[field] = value;
						} else {
							rowValues[field] = value.join(',');
						}
					} else {
						rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
					}
				}

			} else {
				for(var key in fieldList) {
					var field = fieldList[key];
					if(field == 'value'){
						rowValues[field] = valueSelectElement.val();
					} else {
						rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
					}
				}
			}
			if(jQuery('[name="valuetype"]', rowElement).val() == 'false' || (jQuery('[name="valuetype"]', rowElement).length == 0)) {
				rowValues['valuetype'] = 'rawtext';
			}
			
			values.push(rowValues); 
		});
		return values;
	},
	
	getTaskList : function() {
		var container = this.getContainer();
		var params = {
			module : app.getModuleName(),
			parent : app.getParentModuleName(),
			view : 'TasksList',
			record : jQuery('[name="record"]',container).val()
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(params).then(function(data){
			jQuery('#taskListContainer').html(data);
			progressIndicatorElement.progressIndicator({mode : 'hide'});
		});
	},
	
	/**
	 * Function to get ckEditorInstance
	 */
	getckEditorInstance : function(){
		if(this.ckEditorInstance == false){
			this.ckEditorInstance = new Vtiger_CkEditor_Js();
		}
		return this.ckEditorInstance;
	},
	
	registerTaskStatusChangeEvent : function() {
		var container = this.getContainer();
		container.on('change','.taskStatus',function(e) {
			var currentStatusElement = jQuery(e.currentTarget);
			var url = currentStatusElement.data('statusurl');
			if(currentStatusElement.is(':checked')){
				url = url+'&status=true';
			} else {
				url = url+'&status=false';
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			AppConnector.request(url).then(function(data) {
				if(data.result == "ok") {
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_STATUS_CHANGED_SUCCESSFULLY'),
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
				progressIndicatorElement.progressIndicator({mode : 'hide'});
			});
			e.stopImmediatePropagation();
		});
	},
	
	registerTaskDeleteEvent : function() {
		var thisInstance = this;
		var container = this.getContainer();
		container.on('click','.deleteTask',function(e) {
			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({
				'message' : message
			}).then(
				function() {
					var currentElement = jQuery(e.currentTarget);
					var deleteUrl = currentElement.data('deleteurl');
					AppConnector.request(deleteUrl).then(function(data){
						if(data.result == 'ok'){
							thisInstance.getTaskList();
							var params = {
								title : app.vtranslate('JS_MESSAGE'),
								text: app.vtranslate('JS_TASK_DELETED_SUCCESSFULLY'),
								animation: 'show',
								type: 'success'
							};
							Vtiger_Helper_Js.showPnotify(params);
						}
					});
				});
		});
	},

	registerFillTaskFromEmailFieldEvent: function() {
		jQuery('#saveTask').on('change','#fromEmailOption',function(e) {
			var currentElement = jQuery(e.currentTarget);
			var inputElement = currentElement.closest('.row-fluid').find('.fields');
			inputElement.val(currentElement.val());
		})
	},
	
	registerFillTaskFieldsEvent: function() {
		jQuery('#saveTask').on('change','.task-fields',function(e) {
			var currentElement = jQuery(e.currentTarget);
			var inputElement = currentElement.closest('.row-fluid').find('.fields');
			var oldValue = inputElement.val();
			var newValue = oldValue+currentElement.val();
			inputElement.val(newValue);
		})
	},
	
	registerFillMailContentEvent : function() {
		jQuery('#task-fieldnames,#task_timefields,#task-templates').change(function(e){
			var textarea = CKEDITOR.instances.content;
			var value = jQuery(e.currentTarget).val();
			if(textarea != undefined) {
				textarea.insertHtml(value);
			} else if(jQuery('textarea[name="content"]')) {
				var textArea = jQuery('textarea[name="content"]');
				textArea.insertAtCaret(value);
			}
		});
	},
	
	registerVTEmailTaskEvents : function() {
		var textAreaElement = jQuery('#content');
		var ckEditorInstance = this.getckEditorInstance();
		ckEditorInstance.loadCkEditor(textAreaElement);
		this.registerFillMailContentEvent();
		this.registerFillTaskFromEmailFieldEvent();
		this.registerCcAndBccEvents();
	},
	
	registerVTCreateTodoTaskEvents : function() {
		app.registerEventForTimeFields(jQuery('#saveTask'));
	},
	
	registerVTUpdateFieldsTaskEvents : function() {
		var thisInstance = this;
		this.registerAddFieldEvent();
		this.registerDeleteConditionEvent();
		this.registerFieldChange();
		this.fieldValueMap = false;
		if(jQuery('#fieldValueMapping').val() != ''){
			this.fieldValueReMapping();
		}
		var fields = jQuery('#save_fieldvaluemapping').find('select[name="fieldname"]');
		jQuery.each(fields,function(i,field){
			thisInstance.loadFieldSpecificUi(jQuery(field));
		});
		this.getPopUp(jQuery('#saveTask'));
	},
        
	registerAddFieldEvent : function() {
		jQuery('#addFieldBtn').on('click',function(e) {
			var newAddFieldContainer = jQuery('.basicAddFieldContainer').clone(true,true).removeClass('basicAddFieldContainer hide').addClass('conditionRow');
			jQuery('select',newAddFieldContainer).addClass('select2');
			jQuery('#save_fieldvaluemapping').append(newAddFieldContainer);
			//change in to chosen elements
			app.changeSelectElementView(newAddFieldContainer);
			app.showSelect2ElementView(newAddFieldContainer.find('.select2'));
		});
	},
	
	registerDeleteConditionEvent : function() {
		jQuery('#saveTask').on('click','.deleteCondition',function(e) {
			jQuery(e.currentTarget).closest('.conditionRow').remove();
		})
	},
	
	/**
	 * Function which will register field change event
	 */
	registerFieldChange : function() {
		var thisInstance = this;
		jQuery('#saveTask').on('change','select[name="fieldname"]',function(e){
			var selectedElement = jQuery(e.currentTarget);
			if(selectedElement.val() != 'none'){
				var conditionRow = selectedElement.closest('.conditionRow');
				var moduleNameElement = conditionRow.find('[name="modulename"]');
				if(moduleNameElement.length > 0){
					var selectedOptionFieldInfo = selectedElement.find('option:selected').data('fieldinfo');
					var type = selectedOptionFieldInfo.type;
					if(type == 'picklist' || type == 'multipicklist'){
						var moduleName = jQuery('#createEntityModule').val();
						moduleNameElement.find('option[value="'+moduleName+'"]').attr('selected', true);
						moduleNameElement.trigger('change');
						moduleNameElement.select2("disable");
					}
				}
				thisInstance.loadFieldSpecificUi(selectedElement);
			}
		});
	},
	
	getModuleName : function() {
		return app.getModuleName();
	},
	
	getFieldValueMapping : function() {
		var fieldValueMap = this.fieldValueMap;
		if(fieldValueMap != false){
			return fieldValueMap;
		} else {
			return '';
		}
	},
	
	fieldValueReMapping : function() {
		var object = JSON.parse(jQuery('#fieldValueMapping').val());
		var fieldValueReMap = {};

		jQuery.each(object,function (i,array) {
			fieldValueReMap[array.fieldname] = {};
			var values = {}
			jQuery.each(array, function (key , value) {
				values[key] = value;
			});
			fieldValueReMap[array.fieldname] = values
		});
		this.fieldValueMap = fieldValueReMap;
	},
	
	loadFieldSpecificUi : function(fieldSelect) {
		var selectedOption = fieldSelect.find('option:selected');
		var row = fieldSelect.closest('div.conditionRow');
		var fieldUiHolder = row.find('.fieldUiHolder');
		var fieldInfo = selectedOption.data('fieldinfo');
		var fieldValueMapping = this.getFieldValueMapping();
		if(fieldValueMapping != '' && typeof fieldValueMapping[fieldInfo.name] != 'undefined'){
			fieldInfo.value = fieldValueMapping[fieldInfo.name]['value'];
			fieldInfo.workflow_valuetype = fieldValueMapping[fieldInfo.name]['valuetype'];
		} else {
			fieldInfo.workflow_valuetype = 'rawtext';
		}
		
		var moduleName = this.getModuleName();
		
		var fieldModel = Vtiger_Field_Js.getInstance(fieldInfo,moduleName);
		this.fieldModelInstance = fieldModel;
		var fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);

		//remove validation since we dont need validations for all eleements
		// Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
		var fieldName = fieldModel.getName();
		if(fieldModel.getType() == 'multipicklist'){
			fieldName = fieldName+"[]";
		}
		fieldSpecificUi.filter('[name="'+ fieldName +'"]').attr('data-value', 'value').addClass('row-fluid');
		fieldSpecificUi.find('[name="'+ fieldName +'"]').attr('data-value','value').addClass('row-fluid');
		fieldSpecificUi.filter('[name="valuetype"]').removeAttr('data-validation-engine');
		fieldSpecificUi.find('[name="valuetype"]').removeAttr('data-validation-engine');
		
		//If the workflowValueType is rawtext then only validation should happen
		var workflowValueType = fieldSpecificUi.filter('[name="valuetype"]').val();
		if(workflowValueType != 'rawtext' && typeof workflowValueType != 'undefined') {
			fieldSpecificUi.filter('[name="'+ fieldName +'"]').removeAttr('data-validation-engine');
			fieldSpecificUi.find('[name="'+ fieldName +'"]').removeAttr('data-validation-engine');
		}
		
		fieldUiHolder.html(fieldSpecificUi);

		if(fieldSpecificUi.is('input.select2')){
			var tagElements = fieldSpecificUi.data('tags');
			var params = {tags : tagElements,tokenSeparators: [","]}
			app.showSelect2ElementView(fieldSpecificUi,params)
		} else if(fieldSpecificUi.is('select')){
			if(fieldSpecificUi.hasClass('chzn-select')) {
				app.changeSelectElementView(fieldSpecificUi)
			}else{
				app.showSelect2ElementView(fieldSpecificUi);
			}
		} else if (fieldSpecificUi.is('input.dateField')){
			var calendarType = fieldSpecificUi.data('calendarType');
			if(calendarType == 'range'){
				var customParams = {
					calendars: 3,
					mode: 'range',
					className : 'rangeCalendar',
					onChange: function(formated) {
						fieldSpecificUi.val(formated.join(','));
					}
				}
				app.registerEventForDatePickerFields(fieldSpecificUi,false,customParams);
			}else{
				app.registerEventForDatePickerFields(fieldSpecificUi);
			}
		}
		return this;
	},
	
	/**
	 * Functiont to get the field specific ui for the selected field
	 * @prarms : fieldSelectElement - select element which will represents field list
	 * @return : jquery object which represents the ui for the field
	 */
	getFieldSpecificUi : function(fieldSelectElement) {
		var fieldModel = this.fieldModelInstance;
		return  jQuery(fieldModel.getUiTypeSpecificHtml())
	},

	
	registerVTCreateEventTaskEvents : function() {
		app.registerEventForTimeFields(jQuery('#saveTask'));
		this.registerRecurrenceFieldCheckBox();
		this.repeatMonthOptionsChangeHandling();
		this.registerRecurringTypeChangeEvent();
		this.registerRepeatMonthActions();
	},
	
	registerVTCreateEntityTaskEvents : function() {
		this.registerChangeCreateEntityEvent();
		this.registerVTUpdateFieldsTaskEvents();
	},
	
	registerChangeCreateEntityEvent : function() {
		var thisInstance = this;
		jQuery('#createEntityModule').on('change',function(e) {
			var params = {
				module : app.getModuleName(),
				parent : app.getParentModuleName(),
				view : 'CreateEntity',
				relatedModule : jQuery(e.currentTarget).val(),
				for_workflow : jQuery('[name="for_workflow"]').val()
			}
			AppConnector.request(params).then(function(data) {
				var createEntityContainer = jQuery('#addCreateEntityContainer');
				createEntityContainer.html(data);
				app.changeSelectElementView(createEntityContainer);
				app.showSelect2ElementView(createEntityContainer.find('.select2'));
				thisInstance.registerAddFieldEvent();
				thisInstance.fieldValueMap = false;
				if(jQuery('#fieldValueMapping').val() != ''){
					this.fieldValueReMapping();
				}
				var fields = jQuery('#save_fieldvaluemapping').find('select[name="fieldname"]');
				jQuery.each(fields,function(i,field){
					thisInstance.loadFieldSpecificUi(jQuery(field));
				});
			});
		});
	},
	
	/**
	 * Function which will register change event on recurrence field checkbox
	 */
	registerRecurrenceFieldCheckBox : function() {
		var thisInstance = this;
		jQuery('#saveTask').find('input[name="recurringcheck"]').on('change', function(e) {
			var element = jQuery(e.currentTarget);
			var repeatUI = jQuery('#repeatUI');
			if(element.is(':checked')) {
				repeatUI.show();
			} else {
				repeatUI.hide();
			}
		});
	},
	
	/**
	 * Function which will register the change event for recurring type
	 */
	registerRecurringTypeChangeEvent : function() {
		var thisInstance = this;
		jQuery('#recurringType').on('change', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var recurringType = currentTarget.val();
			thisInstance.changeRecurringTypesUIStyles(recurringType);
			
		});
	},
	
	/**
	 * Function which will register the change event for repeatMonth radio buttons
	 */
	registerRepeatMonthActions : function() {
		var thisInstance = this;
		jQuery('#saveTask').find('input[name="repeatMonth"]').on('change', function(e) {
			//If repeatDay radio button is checked then only select2 elements will be enable
			thisInstance.repeatMonthOptionsChangeHandling();
		});
	},
	
	
	/**
	 * Function which will change the UI styles based on recurring type
	 * @params - recurringType - which recurringtype is selected
	 */
	changeRecurringTypesUIStyles : function(recurringType) {
		var thisInstance = this;
		if(recurringType == 'Daily' || recurringType == 'Yearly') {
			jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
			jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
		} else if(recurringType == 'Weekly') {
			jQuery('#repeatWeekUI').removeClass('hide').addClass('show');
			jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
		} else if(recurringType == 'Monthly') {
			jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
			jQuery('#repeatMonthUI').removeClass('hide').addClass('show');
		}
	},
	
	/**
	 * This function will handle the change event for RepeatMonthOptions
	 */
	repeatMonthOptionsChangeHandling : function() {
		//If repeatDay radio button is checked then only select2 elements will be enable
		if(jQuery('#repeatDay').is(':checked')) {
			jQuery('#repeatMonthDate').attr('disabled', true);
			jQuery('#repeatMonthDayType').select2("enable");
			jQuery('#repeatMonthDay').select2("enable");
		} else {
			jQuery('#repeatMonthDate').removeAttr('disabled');
			jQuery('#repeatMonthDayType').select2("disable");
			jQuery('#repeatMonthDay').select2("disable");
		}
	},
	
	checkHiddenStatusofCcandBcc : function(){
		var ccLink = jQuery('#ccLink');
		var bccLink = jQuery('#bccLink');
		if(ccLink.is(':hidden') && bccLink.is(':hidden')){
			ccLink.closest('div.row-fluid').addClass('hide');
		}
	},

	/*
	 * Function to register the events for bcc and cc links
	 */
	registerCcAndBccEvents : function(){
		var thisInstance = this;
		jQuery('#ccLink').on('click',function(e){
			var ccContainer = jQuery('#ccContainer');
			ccContainer.show();
			var taskFieldElement = ccContainer.find('select.task-fields');
			taskFieldElement.addClass('chzn-select');
			app.changeSelectElementView(taskFieldElement);
			jQuery(e.currentTarget).hide();
			thisInstance.checkHiddenStatusofCcandBcc();
		});
		jQuery('#bccLink').on('click',function(e){
			var bccContainer = jQuery('#bccContainer');
			bccContainer.show();
			var taskFieldElement = bccContainer.find('select.task-fields');
			taskFieldElement.addClass('chzn-select');
			app.changeSelectElementView(taskFieldElement);
			jQuery(e.currentTarget).hide();
			thisInstance.checkHiddenStatusofCcandBcc();
		});
	},
	
	registerEvents : function(){
		var container = this.getContainer();
		app.changeSelectElementView(container);
		this.registerEditTaskEvent();
		this.registerTaskStatusChangeEvent();
		this.registerTaskDeleteEvent();
	}
});

//http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery
jQuery.fn.extend({
	insertAtCaret: function(myValue) {
		return this.each(function(i) {
			if (document.selection) {
				//For browsers like Internet Explorer
				this.focus();
				var sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			} else if (this.selectionStart || this.selectionStart == '0') {
				//For browsers like Firefox and Webkit based
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
				this.focus();
				this.selectionStart = startPos + myValue.length;
				this.selectionEnd = startPos + myValue.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += myValue;
				this.focus();
			}
		});
	}
});