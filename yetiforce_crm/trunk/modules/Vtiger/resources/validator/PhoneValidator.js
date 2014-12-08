/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_BaseValidator_Js("Vtiger_PhoneValidator_Js",{},{
	error: "",
	validate: function(){
		var field = this.fieldInfo;
		var fieldValue = field.val();
		var strippedValue = fieldValue.replace(/[\(\)\.\-\ ]/g, '');

	   if (fieldValue == "") {

			this.getEmptyPhoneNumberError();

		} else if (isNaN(parseInt(strippedValue))) {

			this.getPhoneNumberIllegalCharacterError();

		} else if (!(strippedValue.length == 10)) {
			
			this.getPhoneNumberWrongLengthError();

		}
	},

	getEmptyPhoneNumberError: function(){
		this.error = "You didn't enter a phone number.\n";
		return this.error;
	},

	getPhoneNumberIllegalCharacterError: function(){
		this.error = "The phone number contains illegal characters.\n";
		return this.error;
	},

	getPhoneNumberWrongLengthError: function(){
		this.error = "The phone number is the wrong length. Make sure you included an area code.\n";
		return this.error;
	}
})