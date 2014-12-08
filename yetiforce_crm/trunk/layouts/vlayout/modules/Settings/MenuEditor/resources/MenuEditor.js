/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_Menu_Editor_Js', {}, {
	
	//This will store the MenuEditor Container
	menuEditorContainer : false,
	
	//This will store the  MenuList selectElement
	menuListSelectElement : false,
	
	//This will store the MenuEditor Form
	menuEditorForm : false,
	
	/**
	 * Function to get the MenuEditor container
	 */
	getContainer : function() {
		if(this.menuEditorContainer == false) {
			this.menuEditorContainer = jQuery('#menuEditorContainer');
		}
		return this.menuEditorContainer;
	},
	
	/**
	 * Function to get the MenuList select element
	 */
	getMenuListSelectElement : function() {
		if(this.menuListSelectElement == false) {
			this.menuListSelectElement = jQuery('#menuListSelectElement');
		}
		return this.menuListSelectElement;
	},
	
	/**
	 * Function to get the MenuEditor form
	 */
	getForm : function() {
		if(this.menuEditorForm == false) {
			this.menuEditorForm = jQuery('#menuEditorContainer');
		}
		return this.menuEditorForm;
	},
	
	/**
	 * Function to regiser the event to make the menu items list sortable
	 */
	makeMenuItemsListSortable : function() {
		var thisInstance = this;
		var selectElement = this.getMenuListSelectElement();
		var select2Element = app.getSelect2ElementFromSelect(selectElement);
		
		//TODO : peform the selection operation in context this might break if you have multi select element in menu editor
		//The sorting is only available when Select2 is attached to a hidden input field.
		var select2ChoiceElement = select2Element.find('ul.select2-choices');
		select2ChoiceElement.sortable({
                'containment': select2ChoiceElement,
                start: function() { jQuery('#selectedMenus').select2("onSortStart"); },
                update: function() { 
					jQuery('#selectedMenus').select2("onSortEnd");
					//If sorting happened save button should show
					thisInstance.showSaveButton();
				}
            });
	},
	
	/**
	 * Function which will arrange the selected element choices in order
	 */
	arrangeSelectChoicesInOrder : function() {
		var container = this.getContainer();
		var selectElement = this.getMenuListSelectElement();
		var select2Element = app.getSelect2ElementFromSelect(selectElement);
		
		var choicesContainer = select2Element.find('ul.select2-choices');
		var choicesList = choicesContainer.find('li.select2-search-choice');
		var selectedOptions = selectElement.find('option:selected');
		var selectedOrder = JSON.parse(jQuery('input[name="topMenuIdsList"]', container).val());
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
	
	/**
	 * Function which will get the selected columns with order preserved
	 * @return : array of selected values in order
	 */
	getSelectedColumns : function() {
		var selectElement = this.getMenuListSelectElement();
		var select2Element = app.getSelect2ElementFromSelect(selectElement);

		var selectedValuesByOrder = {};
		var selectedOptions = selectElement.find('option:selected');
		var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
		var i = 1;
		orderedSelect2Options.each(function(index,element){
			var chosenOption = jQuery(element);
			selectedOptions.each(function(optionIndex, domOption){
				var option = jQuery(domOption);
				if(option.html() == chosenOption.html()) {
					selectedValuesByOrder[i++] = option.val();
					return false;
				}
			});
		});
		
		return selectedValuesByOrder;
	},
	
	/**
	 * Function which will show the save button in menuEditor Container
	 */
	showSaveButton : function() {
		var container = this.getContainer();
		var saveButton = jQuery('[name="saveMenusList"]', container);
		
		if(app.isHidden(saveButton)) {
			saveButton.removeClass('hide');
		}
	},
	
	registerEvents : function(e){
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var selectElement = thisInstance.getMenuListSelectElement();
		var select2Element = app.getSelect2ElementFromSelect(selectElement);
		var form = thisInstance.getForm();
		
		//register all select2 Elements
		app.showSelect2ElementView(container.find('select.select2'), {_maximumSelectionSize: 7, dropdownCss : {'z-index' : 0}});
		
		//On change of menus list only will show the save button
		selectElement.on('change', function() {
			select2Element.validationEngine('hide');
			thisInstance.showSaveButton();
		});
		
		//To arrange the select choices in the order those are selected
		thisInstance.arrangeSelectChoicesInOrder();
		
		//To make the menu items list sortable
		thisInstance.makeMenuItemsListSortable();
		
		var params = app.getvalidationEngineOptions(true);
		params.onValidationComplete = function(form, valid){
			if(valid) {
				var progressIndicatorElement = jQuery.progressIndicator({
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});
				//before saving, updtae the selected modules list
				jQuery('input[name="selectedModulesList"]', container).val(JSON.stringify(thisInstance.getSelectedColumns()));
				return valid;
			}
		}
		
		form.validationEngine(params);
	}
});


jQuery(document).ready(function(){
	var settingMenuEditorInstance = new Settings_Menu_Editor_Js();
	settingMenuEditorInstance.registerEvents();
})
