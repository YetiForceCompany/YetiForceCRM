/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

var Vtiger_CustomView_Js = {

	contentsCotainer : false,
	columnListSelect2Element : false,
	advanceFilterInstance : false,

	//This will store the columns selection container
	columnSelectElement : false,

	//This will store the input hidden selectedColumnsList element
	selectedColumnsList : false,

	loadFilterView : function(url) {
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(url).then(
			function(data){
				app.hideModalWindow();
				var contents = jQuery(".contentsDiv").html(data);
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				Vtiger_CustomView_Js.registerEvents();
				Vtiger_CustomView_Js.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',contents));
			},
			function(error,err){

			}
		);
	},

	loadDateFilterValues : function(){
		var selectedDateFilter = jQuery('#standardDateFilter option:selected');
		var currentDate = selectedDateFilter.data('currentdate');
		var endDate = selectedDateFilter.data('enddate');
		jQuery("#standardFilterCurrentDate").val(currentDate);
		jQuery("#standardFilterEndDate").val(endDate);
	},

	/**
	 * Function to get the contents container
	 * @return : jQuery object of contents container
	 */
	getContentsContainer : function() {
		if(Vtiger_CustomView_Js.contentsCotainer == false) {
			Vtiger_CustomView_Js.contentsCotainer = jQuery('div.contentsDiv');
		}
		return Vtiger_CustomView_Js.contentsCotainer;
	},

	getColumnListSelect2Element : function() {
		return Vtiger_CustomView_Js.columnListSelect2Element;
	},

	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement : function() {
		if(Vtiger_CustomView_Js.columnSelectElement == false) {
			Vtiger_CustomView_Js.columnSelectElement = jQuery('#viewColumnsSelect');
		}
		return Vtiger_CustomView_Js.columnSelectElement;
	},

	/**
	 * Function to get the selected columns list
	 * @return : jQuery object of selectedColumnsList
	 */
	getSelectedColumnsList : function() {
		if(Vtiger_CustomView_Js.selectedColumnsList == false) {
			Vtiger_CustomView_Js.selectedColumnsList = jQuery('#selectedColumnsList');
		}
		return Vtiger_CustomView_Js.selectedColumnsList;
	},

	/**
	 * Function to regiser the event to make the columns list sortable
	 */
	makeColumnListSortable : function() {
		var select2Element = Vtiger_CustomView_Js.getColumnListSelect2Element();
		//TODO : peform the selection operation in context this might break if you have multi select element in advance filter
		//The sorting is only available when Select2 is attached to a hidden input field.
		var chozenChoiceElement = select2Element.find('ul.select2-choices');
		chozenChoiceElement.sortable({
                'containment': chozenChoiceElement,
                start: function() { Vtiger_CustomView_Js.getSelectedColumnsList().select2("onSortStart"); },
                update: function() { Vtiger_CustomView_Js.getSelectedColumnsList().select2("onSortEnd"); }
            });
	},

	/**
	 * Function which will get the selected columns with order preserved
	 * @return : array of selected values in order
	 */
	getSelectedColumns : function() {
		var columnListSelectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		var select2Element = Vtiger_CustomView_Js.getColumnListSelect2Element();

		var selectedValuesByOrder = new Array();
		var selectedOptions = columnListSelectElement.find('option:selected');

		var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
		orderedSelect2Options.each(function(index,element){
			var chosenOption = jQuery(element);
			selectedOptions.each(function(optionIndex, domOption){
				var option = jQuery(domOption);
				if(option.html() == chosenOption.html()) {
					selectedValuesByOrder.push(option.val());
					return false;
				}
			});
		});
		return selectedValuesByOrder;
	},

	/**
	 * Function which will arrange the chosen element choices in order
	 */
	arrangeSelectChoicesInOrder : function() {
		var contentsContainer = Vtiger_CustomView_Js.getContentsContainer();
		var chosenElement = Vtiger_CustomView_Js.getColumnListSelect2Element();
		var choicesContainer = chosenElement.find('ul.select2-choices');
		var choicesList = choicesContainer.find('li.select2-search-choice');
		var coulmnListSelectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		var selectedOptions = coulmnListSelectElement.find('option:selected');
		var selectedOrder = JSON.parse(jQuery('input[name="columnslist"]', contentsContainer).val());

		for(var index=selectedOrder.length ; index > 0 ; index--) {
			var selectedValue = selectedOrder[index-1];
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

	saveFilter : function() {
		var aDeferred = jQuery.Deferred();
		var formElement = jQuery("#CustomView");
		var formData = formElement.serializeFormData();

		var progressIndicatorInstance = jQuery.progressIndicator({
			'blockInfo' : {
				'enabled' : true
			}
		});

		AppConnector.request(formData).then(
			function(data){
				progressIndicatorInstance.progressIndicator({
					'mode' : 'hide'
				})
				aDeferred.resolve(data);
			},
			function(error){
				progressIndicatorInstance.progressIndicator({
					'mode' : 'hide'
				})
				aDeferred.reject(error);
			}
		)
		return aDeferred.promise();
	},

	saveAndViewFilter : function(){
		Vtiger_CustomView_Js.saveFilter().then(
			function(response){
				if (response.success) {
					if( app.getParentModuleName() == 'Settings'){
						var url = 'index.php?module=CustomView&parent=Settings&view=Index';
					}else{
						var url = response['result']['listviewurl'];
					}
					window.location.href=url;
				} else {
					var params = {
						title: app.vtranslate('JS_DUPLICATE_RECORD'),
						text: response.error['message']
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			},
			function(error) {

			}
		);
	},

	/**
	 * Function which will register the select2 elements for columns selection
	 */
	registerSelect2ElementForColumnsSelection : function() {
		var selectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		app.changeSelectElementView(selectElement, 'select2', {maximumSelectionSize: 12,dropdownCss : {'z-index' : 0}});
	},

	registerEvents: function(){
		Vtiger_CustomView_Js.registerSelect2ElementForColumnsSelection();
		var contentsContainer = Vtiger_CustomView_Js.getContentsContainer();
		jQuery('.stndrdFilterDateSelect').datepicker();
		jQuery('.chzn-select').chosen();

		var select2Element = app.getSelect2ElementFromSelect(Vtiger_CustomView_Js.getColumnSelectElement());
		Vtiger_CustomView_Js.columnListSelect2Element = select2Element;

		//To arrange the chosen choices in the order that is selected
		Vtiger_CustomView_Js.arrangeSelectChoicesInOrder();

		jQuery("#standardDateFilter").change(function(){
			Vtiger_CustomView_Js.loadDateFilterValues();
		});

		Vtiger_CustomView_Js.makeColumnListSortable();

		jQuery("#CustomView").submit(function(e) {
			var selectElement = Vtiger_CustomView_Js.getColumnSelectElement();
			var select2Element = app.getSelect2ElementFromSelect(selectElement);
			var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(selectElement);
			if(result != true){
				select2Element.validationEngine('showPrompt', result , 'error','bottomLeft',true);
				e.preventDefault();
				return;
			} else {
				select2Element.validationEngine('hide');
			}
            if(jQuery('#viewname').val().length > 40) {
                var params = {
                    title : app.vtranslate('JS_MESSAGE'),
                    text : app.vtranslate('JS_VIEWNAME_ALERT')
                }
                Vtiger_Helper_Js.showPnotify(params);
                e.preventDefault();
                return;
            }

			//Mandatory Fields selection validation
			//Any one Mandatory Field should select while creating custom view.
			var mandatoryFieldsList = JSON.parse(jQuery('#mandatoryFieldsList').val());
			var selectedOptions = selectElement.val();
			var mandatoryFieldsMissing = true;
			for(var i=0; i<selectedOptions.length; i++) {
				if(jQuery.inArray(selectedOptions[i], mandatoryFieldsList) >= 0) {
					mandatoryFieldsMissing = false;
					break;
				}
			}
			if(mandatoryFieldsMissing){
				var result = app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_MANDATORY_FIELD');
				select2Element.validationEngine('showPrompt', result , 'error','bottomLeft',true);
				e.preventDefault();
				return;
			} else {
				select2Element.validationEngine('hide');
			}
			//Mandatory Fields validation ends

			var result = jQuery(e.currentTarget).validationEngine('validate');
			if(result == true){
				//handled standard filters saved values.
				var stdfilterlist = {};

				if((jQuery('#standardFilterCurrentDate').val() != '') && (jQuery('#standardFilterEndDate').val()!= '') && (jQuery('select.standardFilterColumn option:selected').val() != 'none')){
					stdfilterlist['columnname'] = jQuery('select.standardFilterColumn option:selected').val();
					stdfilterlist['stdfilter'] = jQuery('select#standardDateFilter option:selected').val();
					stdfilterlist['startdate'] = jQuery('#standardFilterCurrentDate').val();
					stdfilterlist['enddate'] = jQuery('#standardFilterEndDate').val();
					jQuery('#stdfilterlist').val(JSON.stringify(stdfilterlist));
				}

				//handled advanced filters saved values.
				var advfilterlist = Vtiger_CustomView_Js.advanceFilterInstance.getValues();
				jQuery('#advfilterlist').val(JSON.stringify(advfilterlist));
				jQuery('input[name="columnslist"]', contentsContainer).val(JSON.stringify(Vtiger_CustomView_Js.getSelectedColumns()));
				Vtiger_CustomView_Js.saveAndViewFilter();
				return false;
			} else {
				app.formAlignmentAfterValidation(jQuery(e.currentTarget));
			}
		});

		jQuery('#CustomView').validationEngine(app.validationEngineOptions);
	}
}
