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
		var html = '<select class="select2 form-control" multiple name="'+ this.getName() +'[]">';
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
		var html = '<select class="row chzn-select form-control" name="'+ this.getName() +'">';
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
		var html = 	'<div class="date input-group">'+
						'<input class="dateField form-control" type="text" name="'+ this.getName() +'"  data-date-format="'+ this.getDateFormat() +'"  value="'+  this.getValue() + '" />'+
						'<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>'+
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
		var html = '<div class="input-group">'+
						'<span class="input-group-addon">'+ this.getCurrencySymbol()+'</span>'+
						'<input type="text" name="'+ this.getName() +'" value="'+  this.getValue() + '" class="form-control" data-decimal-seperator="'+this.getData().decimalSeperator+'" data-group-seperator="'+this.getData().groupSeperator+'"/>'+
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
		var html = '<div class="input-group row">'+
									'<input type="number" class="form-control" min="0" max="100" name="'+this.getName() +'" value="'+  this.getValue() + '" step="any"/>'+
									'<span class="input-group-addon">%</span>'+
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
		var html = '<div class="input-group time">'+
							'<input class="timepicker-default" class="form-control" type="text" name="'+ this.getName() +'"  value="'+  this.getValue() + '" />'+
							'<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>'+
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
				
		html += '<div class="input-group">'+
				'<span class="input-group-addon clearReferenceSelection cursorPointer">'+
							'<i  class="glyphicon glyphicon-remove-sign" title=""></i>'+
						'</span>'+
				'<input id="'+ fieldName +'_display" type="text" class="autoComplete referenceFieldDisplay form-control" placeholder="'+app.vtranslate('JS_TYPE_TO_SEARCH')+'"/>'+
				'<span class="input-group-addon relatedPopup cursorPointer">'+
							'<i class="glyphicon glyphicon-search relatedPopup"></i>'+
						'</span>'+
						'<span class="input-group-addon cursorPointer createReferenceRecord">'+
							'<i class="glyphicon glyphicon-plus"></i>'+
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
		var html =	'<input class="input-lg form-control" type="text" name="'+ this.getName() +'" readonly />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});
