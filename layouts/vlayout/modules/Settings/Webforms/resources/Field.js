/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Field_Js("Webforms_Field_Js",{},{})

Vtiger_Field_Js('Webforms_Multipicklist_Field_Js',{},{
	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},
	
	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		var html = '<select class="select2" multiple name="'+ this.getName() +'[]" style="width:60%">';
		var pickListValues = this.getPickListValues();
		var selectedOption = this.getValue();
		var selectedOptionsArray = selectedOption.split(' |##| ')
		for(var option in pickListValues) {
			html += '<option value="'+option+'" ';
			if(jQuery.inArray(option,selectedOptionsArray) != -1){
				html += ' selected ';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';
		var selectContainer = jQuery(html);
		return selectContainer;
	}
});

Vtiger_Field_Js('Webforms_Picklist_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		var html = '<select class="row-fluid chzn-select" name="'+ this.getName() +'" style="width:220px">';
		var pickListValues = this.getPickListValues();
		var selectedOption = this.getValue();
		for(var option in pickListValues) {
			html += '<option value="'+option+'" ';
			if(option == selectedOption) {
				html += ' selected ';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Field_Js('Webforms_Date_Field_Js',{},{

	/**
	 * Function to get the user date format
	 */
	getDateFormat : function(){
		return this.get('date-format');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html = '<div class="input-append">'+
						'<div class="date">'+
							'<input class="dateField" type="text" name="'+ this.getName() +'"  data-date-format="'+ this.getDateFormat() +'"  value="'+  this.getValue() + '" />'+
							'<span class="add-on"><i class="icon-calendar"></i></span>'+
						'</div>'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Webforms_Currency_Field_Js',{},{

	/**
	 * get the currency symbol configured for the user
	 */
	getCurrencySymbol : function() {
		return this.get('currency_symbol');
	},

	getUi : function() {
		var html = '<div class="input-prepend">'+
						'<span class="add-on">'+ this.getCurrencySymbol()+'</span>'+
						'<input type="text" name="'+ this.getName() +'" value="'+  this.getValue() + '" class="input-medium" style="width:210px" data-decimal-seperator="'+this.getData().decimalSeperator+'" data-group-seperator="'+this.getData().groupSeperator+'"/>'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Percentage_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input percentage field
	 */
	getUi : function() {
		var html = '<div class="input-append row-fluid">'+
									'<input type="number" class="input-medium" min="0" max="100" name="'+this.getName() +'" value="'+  this.getValue() + '" step="any"/>'+
									'<span class="add-on">%</span>'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Webforms_Time_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html = '<div class="input-append time">'+
							'<input class="timepicker-default" type="text" name="'+ this.getName() +'"  value="'+  this.getValue() + '" />'+
							'<span class="add-on"><i class="icon-time"></i></span>'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});


Vtiger_Field_Js('Webforms_Reference_Field_Js',{},{
	
	getReferenceModules : function(){
		return this.get('referencemodules');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var referenceModules = this.getReferenceModules();
		var html;
		
		var fieldName = this.getName();
		var referredModuleName = referenceModules[0];
		html = '<input name="popupReferenceModule" type="hidden" value="'+referredModuleName+'" />'+
					'<input name="'+ fieldName +'" type="hidden" value="'+ this.getValue()+ '" class="sourceField"  />';
				
		html += '<div class="row-fluid input-prepend input-append">'+
				'<span class="add-on clearReferenceSelection cursorPointer">'+
							'<i  class="icon-remove-sign" title=""></i>'+
						'</span>'+
				'<input id="'+ fieldName +'_display" type="text" class="span7 marginLeftZero autoComplete referenceFieldDisplay" placeholder="'+app.vtranslate('JS_TYPE_TO_SEARCH')+'"/>'+
				'<span class="add-on relatedPopup cursorPointer">'+
							'<i class="icon-search relatedPopup"></i>'+
						'</span>'+
						'<span class="add-on cursorPointer createReferenceRecord">'+
							'<i class="icon-plus"></i>'+
						'</span>'
					'</div>';
					
		var referenceFieldNames = fieldName.split('[defaultvalue]');
		var referredFieldName = referenceFieldNames[0]+'[referenceModule]';
		html += '<input type="hidden" name="'+referredFieldName+'" value="'+referredModuleName+'" class="referenceModuleName">';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Webforms_Image_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html =	'<input class="input-large" type="text" name="'+ this.getName() +'" readonly />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});