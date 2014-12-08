/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_BaseValidator_Js("Vtiger_EmailValidator_Js",{},{
	error: "",
	validate: function(){
		var fieldValue = this.fieldInfo.val();
		var tfld = fieldValue.replace(/^\s+/,'').replace(/\s+$/,'');
		var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;
		var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;

		if (fieldValue.value == "") {

			this.getEmptyEmailError();

		} else if (!emailFilter.test(tfld)) {

			this.getInvalidEmailError();

		} else if (fieldValue.match(illegalChars)) {

			this.getIllegalCharacterEmailError();
		
		}
	},

	getEmptyEmailError: function(){
		this.error = "You didn't enter an email address.\n";
		return this.error;
	},

	getInvalidEmailError: function(){
		this.error = "Please enter a valid email address.\n";
		return this.error;
	},

	getIllegalCharacterEmailError: function(){
		this.error = "The email address contains illegal characters.\n";
		return this.error;
	}


})