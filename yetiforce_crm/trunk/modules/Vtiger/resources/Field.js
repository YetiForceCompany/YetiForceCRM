/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Vtiger_Field_Js",{

	/**
	 * Function to get Instance of the class based on moduleName
	 * @param data,data to set
	 * @param moduleName module for which Instance should be created
	 * @return Instance of field class
	 */
    getInstance : function(data,moduleName){
		if(typeof moduleName == 'undefined'){
			var moduleName = app.getModuleName();
		}
        var moduleField = moduleName+"_Field_Js";
		var moduleFieldObj = window[moduleField];
        if (typeof moduleFieldObj != 'undefined'){
			 var fieldClass = moduleFieldObj;
		}else{
            var fieldClass = Vtiger_Field_Js;
        }
        var fieldObj = new fieldClass();

		if(typeof data == 'undefined'){
			data = {};
		}
		fieldObj.setData(data);
		return fieldObj;
	}
},{
	data : {},
	/**
	 * Function to check whether field is mandatory or not
	 * @return true if feld is madatory
	 * @return false if field is not mandatory
	 */
	isMandatory : function(){
        return this.get('mandatory');
	},


	/**
	 * Function to get the value of particular key in object
	 * @return value for the passed key
	 */

    get : function(key){
		if(key in this.data){
			return this.data[key];
		}
        return '';
    },


	/**
	 * Function to get type attribute of the object
	 * @return type attribute of the object
	 */
	getType : function(){
		return this.get('type');
	},

	/**
	 * Function to get name of the field
	 * @return <String> name of the field
	 */
	getName : function() {
		return this.get('name');
	},

	/**
	 * Function to get value of the field
	 * @return <Object> value of the field or empty of there is not value
	 */
	getValue : function() {
		if('value' in this.getData()){
			return this.get('value');
		} else if('defaultValue' in this.getData()){
			return this.get('defaultValue');
		}
		return '';
	},

	/**
	 * Function to get the whole data
	 * @return <object>
	 */
	getData : function() {
		return this.data;
	},

	/**
	 * Function to set data attribute of the class
	 * @return Instance of the class
	 */
    setData : function(fieldInfo){
        this.data = fieldInfo;
		return this;
    },
	
	getModuleName : function() {
		return app.getModuleName();
	},

	/**
	 * Function to get the ui type specific model
	 */
	getUiTypeModel : function() {
		var currentModule = this.getModuleName();

		var type = this.getType();
		var typeClassName = type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();

		var moduleUiTypeClassName = window[currentModule + "_" + typeClassName+"_Field_Js"];
		var BasicUiTypeClassName = window["Vtiger_"+ typeClassName + "_Field_Js"];

		if(typeof moduleUiTypeClassName != 'undefined') {
			var instance =  new moduleUiTypeClassName();
			return instance.setData(this.getData());
		}else if (typeof BasicUiTypeClassName != 'undefined') {
			var instance =  new BasicUiTypeClassName();
			return instance.setData(this.getData());
		}
		return this;
	},

	/**
	 * Funtion to get the ui for the field  - generally this will be extend by the child classes to
	 * give ui type specific ui
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi : function() {
		var html = '<input type="text" name="'+ this.getName() +'"  />';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));
		return this.addValidationToElement(html);
	},

	/**
	 * Function to get the ui for a field depending on the ui type
	 * this will get the specific ui depending on the field type
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUiTypeSpecificHtml : function() {
		var uiTypeModel = this.getUiTypeModel();
		return uiTypeModel.getUi();
	},

	/**
	 * Function to add the validation for the element
	 */
	addValidationToElement : function(element) {
		var element = jQuery(element);
		var addValidationToElement = element;
		var elementInStructure = element.find('[name="'+this.getName()+'"]'); 
		if(elementInStructure.length > 0){ 
			addValidationToElement = elementInStructure; 
		}
		var validationHandler = 'validate[';
		if(this.isMandatory()) {
			validationHandler +="required,";
		}
		validationHandler +="funcCall[Vtiger_Base_Validator_Js.invokeValidation]]";
		addValidationToElement.attr('data-validation-engine', validationHandler).attr('data-fieldinfo',JSON.stringify(this.getData())).attr('data-validator',JSON.stringify(this.getData().specialValidator));
		return element;
	}
})


Vtiger_Field_Js('Vtiger_Picklist_Field_Js',{},{

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
		var html = '<select class="row-fluid chzn-select" name="'+ this.getName() +'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());
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

Vtiger_Field_Js('Vtiger_Multipicklist_Field_Js',{},{
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
		var html = '<select class="select2" multiple name="'+ this.getName() +'[]">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());
		var selectedOptionsArray = selectedOption.split(',')
		for(var option in pickListValues) {
			html += '<option value="'+option+'" ';
			if(jQuery.inArray(option,selectedOptionsArray) != -1){
				html += ' selected ';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
}),

Vtiger_Field_Js('Vtiger_Boolean_Field_Js',{},{

	/**
	 * Function to check whether the field is checked or not
	 * @return <Boolean>
	 */
	isChecked : function() {
		var value = this.getValue();
		if(value==1 || value == '1' || value.toLowerCase() == 'on'){
			return true;
		}
		return false;
	},

	/**
	 * Function to get the ui
	 * @return - checkbox element
	 */
	getUi : function() {
		var	html = '<input type="hidden" name="'+this.getName() +'" value="0"/><input type="checkbox" name="'+ this.getName() +'" ';
		if(this.isChecked()) {
			html += 'checked';
		}
		html += ' />'
		return this.addValidationToElement(html);
	}
});


Vtiger_Field_Js('Vtiger_Date_Field_Js',{},{

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

Vtiger_Field_Js('Vtiger_Currency_Field_Js',{},{

	/**
	 * get the currency symbol configured for the user
	 */
	getCurrencySymbol : function() {
		return this.get('currency_symbol');
	},

	getUi : function() {
		var html = '<div class="input-prepend row-fluid">'+
									'<span class="add-on">'+ this.getCurrencySymbol()+'</span>'+
									'<input type="text" name="'+ this.getName() +'" value="'+  this.getValue() + '"  />'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});


Vtiger_Field_Js('Vtiger_Owner_Field_Js',{},{

	/**
	 * Function to get the picklist values
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	getUi : function() {
		var html = '<select class="row-fluid chzn-select" name="'+ this.getName() +'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = this.getValue();
		for(var optGroup in pickListValues){
			html += '<optgroup label="'+ optGroup +'">'
			var optionGroupValues = pickListValues[optGroup];
			for(var option in optionGroupValues) {
				html += '<option value="'+option+'" ';
				//comparing with the value instead of key , because saved value is giving username instead of id.
				if(optionGroupValues[option] == selectedOption) {
					html += ' selected ';
				}
				html += '>'+optionGroupValues[option]+'</option>';
			}
			html += '</optgroup>'
		}

		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
})


Vtiger_Date_Field_Js('Vtiger_Datetime_Field_Js',{},{

});

Vtiger_Field_Js('Vtiger_Time_Field_Js',{},{
	
	/**
	 * Function to get the user date format
	 */
	getTimeFormat : function(){
		return this.get('time-format');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html = '<div class="input-append time">'+
							'<input class="timepicker-default" type="text" data-format="'+ this.getTimeFormat() +'" name="'+ this.getName() +'"  value="'+  this.getValue() + '" />'+
							'<span class="add-on"><i class="icon-time"></i></span>'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Text_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html = '<textarea class="input-xxlarge" name="'+ this.getName() +'"  value="'+  this.getValue() + '" style="width:100%">'+  this.getValue() + '</textarea>';
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
Vtiger_Field_Js('Vtiger_Recurrence_Field_Js',{},{

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
		var html = '<select class="row-fluid chzn-select" name="'+ this.getName() +'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());
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