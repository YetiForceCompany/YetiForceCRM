/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit_Js("Reports_Edit2_Js",{},{

	step2Container : false,

	//This will contain the reports multi select element
	reportsColumnsList : false,

	//This will contain the selected fields element
	selectedFields : false,

	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the report elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step2Container;
	},

	/**
	 * Function to set the report step2 container
	 * @params : element - which represents the report step2 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step2Container = element;
		return this;
	},

	/**
	 * Function to get the multi select element
	 * @return : jQuery object of reports multi select element
	 */
	getReportsColumnsList : function() {
		if(this.reportsColumnsList == false) {
			this.reportsColumnsList = jQuery('#reportsColumnsList');
		}
		return this.reportsColumnsList;
	},

	/**
	 * Function to get the selected fields
	 * @return : jQuery object of selected fields
	 */
	getSelectedFields : function() {
		if(this.selectedFields == false) {
			this.selectedFields = jQuery('#seleted_fields');
		}
		return this.selectedFields;
	},

	/**
	 * Function  to intialize the reports step2
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#report_step2');
		}

		if(container.is('#report_step2')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#report_step2'));
		}
	},
	/*
	 * Function to validate special cases in the form
	 * returns result
	 */
	isFormValidate : function(){
		var thisInstance = this;
		var selectElement = this.getReportsColumnsList();
		var select2Element = app.getSelect2ElementFromSelect(selectElement);
		var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(selectElement);
		if(result != true){
			select2Element.validationEngine('showPrompt', result , 'error','bottomLeft',true);
			var form = thisInstance.getContainer();
			app.formAlignmentAfterValidation(form);
			return false;
		} else {
			select2Element.validationEngine('hide');
			return true;
		}
	},
	/*
	 * Fucntion to perform all the requires calculation before submit
	 */
	calculateValues : function(){
		var container = this.getContainer();
		//Handled select fields values
		var selectedFields = this.getSelectedColumns();
		this.getSelectedFields().val(JSON.stringify(selectedFields));

		//handled selected sort fields
		var selectedSortOrderFields = new Array();
		var selectedSortFieldsRows = jQuery('.sortFieldRow',container);
		jQuery.each(selectedSortFieldsRows,function(index,element){
			var currentElement = jQuery(element);
			var field = currentElement.find('.selectedSortFields').val();
			var order = currentElement.find('.sortOrder').filter(':checked').val();
			//TODO: need to handle sort type for Reports
			var type = currentElement.find('.sortType').val();
			selectedSortOrderFields.push([field,order,type]);
		});
		jQuery('#selected_sort_fields').val(JSON.stringify(selectedSortOrderFields));

		//handled Selected Calculation fields

		var selectedCalculationFields = {};
		var calculationFieldsTable = jQuery('.CalculationFields',container);
		var calculationFieldRows = calculationFieldsTable.find('.calculationFieldRow');
		var indexValue = 0;
		jQuery.each(calculationFieldRows,function(index,element){
			var calculationTypes = jQuery(element).find('.calculationType:checked');
			jQuery.each(calculationTypes,function(index,element){
				selectedCalculationFields[indexValue] = jQuery(element).val();
				indexValue++;
			});
		});
		jQuery('#calculation_fields').val(JSON.stringify(selectedCalculationFields));
	},
	submit : function(){
		var aDeferred = jQuery.Deferred();
		this.calculateValues();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(formData).then(
			function(data) {
				form.hide();
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
				aDeferred.resolve(data);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function which will register the select2 elements for columns selection
	 */
	registerSelect2ElementForReportColumns : function() {
		var selectElement = this.getReportsColumnsList();
		app.changeSelectElementView(selectElement, 'select2', {maximumSelectionSize: 25,dropdownCss : {'z-index' : 0}});
	},

	/**
	 * Function which will get the selected columns with order preserved
	 * @return : array of selected values in order
	 */
	getSelectedColumns : function() {
		var columnListSelectElement = this.getReportsColumnsList();
		var select2Element = app.getSelect2ElementFromSelect(columnListSelectElement);

		var selectedValuesByOrder = new Array();
		var selectedOptions = columnListSelectElement.find('option:selected');

		var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
		orderedSelect2Options.each(function(index,element){
			var chosenOption = jQuery(element);
			var choiceElement = chosenOption.closest('.select2-search-choice');
			var choiceValue = choiceElement.data('select2Data').id;
			selectedOptions.each(function(optionIndex, domOption){
				var option = jQuery(domOption);
				if(option.val() == choiceValue) {
					selectedValuesByOrder.push(option.val());
					return false;
				}
			});
		});
		return selectedValuesByOrder;
	},

	/**
	 * Function which will arrange the select2 element choices in order
	 */
	arrangeSelectChoicesInOrder : function() {
		var selectElement = this.getReportsColumnsList();
		var chosenElement = app.getSelect2ElementFromSelect(selectElement);
		var choicesContainer = chosenElement.find('ul.select2-choices');
		var choicesList = choicesContainer.find('li.select2-search-choice');

		//var coulmnListSelectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		var selectedOptions = selectElement.find('option:selected');
		var selectedOrder = JSON.parse(this.getSelectedFields().val());
		var selectedOrderKeys = [];
		for(var key in selectedOrder) {
			if(selectedOrder.hasOwnProperty(key)){
				selectedOrderKeys.push(key);
			}
		}
		for(var index=selectedOrderKeys.length ; index > 0 ; index--) {
			var selectedValue = selectedOrder[selectedOrderKeys[index-1]];
			var option = selectedOptions.filter('[value="'+selectedValue+'"]');
			choicesList.each(function(choiceListIndex,element){
				var liElement = jQuery(element);
				if(liElement.find('div').html() == option.html()){
					choicesContainer.prepend(liElement);
					return false;
				}
			});
		}
	},

	/**
	 * Function to regiser the event to make the columns list sortable
	 */
	makeColumnListSortable : function() {
		var thisInstance = this;
		var selectElement = thisInstance.getReportsColumnsList();
		var select2Element = app.getSelect2ElementFromSelect(selectElement);
		//TODO : peform the selection operation in context this might break if you have multi select element in advance filter
		//The sorting is only available when Select2 is attached to a hidden input field.
		var chozenChoiceElement = select2Element.find('ul.select2-choices');
		chozenChoiceElement.sortable({
                containment: 'parent',
                start: function() {thisInstance.getSelectedFields().select2("onSortStart");},
                update: function() {thisInstance.getSelectedFields().select2("onSortEnd");}
            });
	},

	/**
	 * Function is used to limit the calculation for line item fields and inventory module fields.
	 * only one of these fields can be used at a time
	 */
	registerLineItemCalculationLimit : function() {
		var thisInstance = this;
		var primaryModule = jQuery('input[name="primary_module"]').val();
        var inventoryModules = ['Invoice', 'Quotes', 'PurchaseOrder', 'SalesOrder'];
        // To limit the calculation fields if secondary module contains inventoryModule
        var secodaryModules = jQuery('input[name="secondary_modules"]').val();
        var secondaryIsInventory = false;
		inventoryModules.forEach(function(entry){
           if(secodaryModules.indexOf(entry) != -1){
               secondaryIsInventory = true;
           } 
        });
		if(jQuery.inArray(primaryModule, inventoryModules) !== -1 || secondaryIsInventory) {
			jQuery('.CalculationFields').on('change', 'input[type="checkbox"]', function(e) {
				var element = jQuery(e.currentTarget);
				var value = element.val();
				var reg = new RegExp(/cb:vtiger_inventoryproductrel*/);
				var attr = element.is(':checked');
				var moduleCalculationFields = jQuery('.CalculationFields input[type="checkbox"]').not('[value^="cb:vtiger_inventoryproductrel"]');
				var lineItemCalculationFields = jQuery('.CalculationFields').find('[value^="cb:vtiger_inventoryproductrel"]');
				if(reg.test(value)) {	// line item field selected
					if(attr) {	// disable all the other checkboxes
						moduleCalculationFields.attr('checked',false).attr('disabled',true);
					} else {
						var otherLineItemFieldsCheckedLength = lineItemCalculationFields.filter(':checked').length;
						if(otherLineItemFieldsCheckedLength == 0) moduleCalculationFields.attr('disabled',false);
						else moduleCalculationFields.attr('checked',false).attr('disabled',true);
					}
				} else {		// some other field is selected
					if(attr) {
						lineItemCalculationFields.attr('checked',false).attr('disabled',true)
					} else {
						var moduleCalculationFieldLength = moduleCalculationFields.filter(':checked').length
						if(moduleCalculationFieldLength == 0) lineItemCalculationFields.attr('disabled', false);
						else lineItemCalculationFields.attr('disabled', true).attr('checked',false);
					}
				}
				thisInstance.displayLineItemFieldLimitationMessage();
			});
		}
	},
	displayLineItemFieldLimitationMessage : function() {
		var message = app.vtranslate('JS_CALCULATION_LINE_ITEM_FIELDS_SELECTION_LIMITATION');
		if(jQuery('#calculationLimitationMessage').length == 0) {
			jQuery('.CalculationFields').parent().append('<div id="calculationLimitationMessage" class="pull-right alert alert-info">'+message+'</div>');
		} else {
			jQuery('#calculationLimitationMessage').html(message);
		}
	},

	registerLineItemCalculationLimitOnLoad : function() {
		var moduleCalculationFields = jQuery('.CalculationFields input[type="checkbox"]').not('[value^="cb:vtiger_inventoryproductrel"]');
		var lineItemFields = jQuery('.CalculationFields').find('[value^="cb:vtiger_inventoryproductrel"]');
		if(moduleCalculationFields.filter(':checked').length != 0) {
			lineItemFields.attr('checked', false).attr('disabled', true);
			this.displayLineItemFieldLimitationMessage();
		} else if(lineItemFields.filter(':checked').length != 0) {
			moduleCalculationFields.attr('checked', false).attr('disabled', true);
			this.displayLineItemFieldLimitationMessage();
		}
	},

	registerEvents : function(){
		var container = this.getContainer();
		//If the container is reloading, containers cache should be reset
		this.reportsColumnsList = false;
		this.selectedFields = false;
		this.registerSelect2ElementForReportColumns();
		this.arrangeSelectChoicesInOrder();
		this.makeColumnListSortable();
		this.registerLineItemCalculationLimit();
		this.registerLineItemCalculationLimitOnLoad();
		app.changeSelectElementView(container);
		container.validationEngine({
			// to prevent the page reload after the validation has completed
			'onValidationComplete' : function(form,valid){
                return valid;
			}
		});
	}
});


