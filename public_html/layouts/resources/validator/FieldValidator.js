/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 *************************************************************************************/
'use strict';

Vtiger_Base_Validator_Js("Vtiger_Email_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var emailInstance = new Vtiger_Email_Validator_Js();
		emailInstance.setElement(field);
		var response = emailInstance.validate();
		if (response != true) {
			return emailInstance.getError();
		}
	}
}, {
	/**
	 *Overwrites base function to avoid trimming and validate white spaces
	 * @return fieldValue
	 * */
	getFieldValue: function () {
		return this.getElement().val();
	},
	/**
	 * Function to validate the email field data
	 */
	validate: function () {
		var fieldValue = this.getFieldValue();
		return this.validateValue(fieldValue);
	},
	/**
	 * Function to validate the email field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validateValue: function (fieldValue) {
		var emailFilter = /^[_/a-zA-Z0-9*]+([!"#$%&'()*+,./:;<=>?\^_`{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\_\-\.]?[a-zA-Z0-9]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/;
		var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

		if (!emailFilter.test(fieldValue)) {
			this.setError(app.vtranslate('JS_PLEASE_ENTER_VALID_EMAIL_ADDRESS'));
			return false;

		} else if (fieldValue.match(illegalChars)) {
			this.setError(app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS'));
			return false;
		}
		var field = this.getElement();
		var fieldData = field.data();
		var fieldInfo = fieldData.fieldinfo;
		if (fieldInfo && fieldInfo.restrictedDomains && fieldInfo.restrictedDomains.indexOf(fieldValue.split('@').pop()) !== -1) {
			this.setError(app.vtranslate('JS_EMAIL_RESTRICTED_DOMAINS'));
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_Phone_Validator_Js", {}, {
	/**
	 * Function to validate the phone field data
	 */
	validate: function () {
		var fieldValue = this.getFieldValue();
		return this.validateValue(fieldValue);
	},
	/**
	 * Function to validate the phone field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validateValue: function (fieldValue) {
		if (fieldValue == '') {
			return true;
		}
		var field = this.getElement();
		var form = field.closest('form');
		var fieldData = field.data();
		var result = true;
		if (fieldData.advancedVerification == 1) {
			var thisInstance = this;
			var fieldInfo = fieldData.fieldinfo;
			var group = field.closest('.input-group');
			var phoneCountryList = group.find('.phoneCountryList');
			field.attr('readonly', true);
			let moduleName = form.find('[name="module"]').length ? form.find('[name="module"]').val() : app.getModuleName();
			if (moduleName === 'LayoutEditor') {
				moduleName = $('#selectedModuleName').val();
			}
			AppConnector.request({
				async: false,
				data: {
					module: moduleName,
					action: 'Fields',
					mode: 'verifyPhoneNumber',
					fieldName: fieldInfo.name,
					phoneNumber: fieldValue,
					phoneCountry: phoneCountryList.val(),
				}
			}).done(function (data) {
				if (data.result.isValidNumber == false) {
					thisInstance.setError(data.result.message);
					result = false;
				} else {
					field.val(data.result.number);
					field.attr('title', data.result.geocoding + ' ' + data.result.carrier);
					if (phoneCountryList.val() != data.result.country) {
						phoneCountryList.val(data.result.country).trigger('change');
					}
				}
				field.attr('readonly', false);
			})
		}
		return result;
	}
});

Vtiger_Base_Validator_Js("Vtiger_UserName_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var usernameInstance = new Vtiger_UserName_Validator_Js();
		usernameInstance.setElement(field);
		var response = usernameInstance.validate();
		if (response != true) {
			return usernameInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the User Name
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var fieldValue = this.getFieldValue();
		var negativeRegex = /^[a-zA-Z0-9_.@]{3,32}$/;
		var result = negativeRegex.test(fieldValue);
		if (!result) {
			var errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_Integer_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var integerInstance = new Vtiger_Integer_Validator_Js();
		integerInstance.setElement(field);
		var response = integerInstance.validate();
		if (response != true) {
			return integerInstance.getError();
		}
	}
}, {
	/**
	 * Function to validate the Integre field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		let fieldValue = this.getFieldValue(),
			groupSeperator = CONFIG.currencyGroupingSeparator,
			integerRegex = new RegExp('(^[-+]?[\\d\\' + groupSeperator + ']+)$', 'g'),
			decimalIntegerRegex = new RegExp('(^[-+]?[\\d\\' + groupSeperator + ']?).\\d+$', 'g');
		if ((!fieldValue.match(integerRegex))) {
			if (!fieldValue.match(decimalIntegerRegex)) {
				var errorInfo = app.vtranslate("JS_PLEASE_ENTER_INTEGER_VALUE");
				this.setError(errorInfo);
				return false;
			}
		}
		let fieldInfo = this.getElement().data().fieldinfo;
		if (!fieldInfo || !fieldInfo.maximumlength) {
			return true;
		}
		let ranges = fieldInfo.maximumlength.split(',');
		if (ranges.length === 2) {
			if (fieldValue > parseFloat(ranges[1]) || fieldValue < parseFloat(ranges[0])) {
				errorInfo = app.vtranslate('JS_ERROR_MAX_VALUE');
				this.setError(errorInfo);
				return false;
			}
		} else {
			if (fieldValue > parseFloat(ranges[0]) || fieldValue < 0) {
				errorInfo = app.vtranslate('JS_ERROR_MAX_VALUE');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
});

Vtiger_Integer_Validator_Js("Vtiger_Double_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var doubleValidator = new Vtiger_Double_Validator_Js();
		doubleValidator.setElement(field);
		var response = doubleValidator.validate();
		if (response != true) {
			return doubleValidator.getError();
		}
	}
}, {
	/**
	 * Function to validate the Decimal field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var response = this._super();
		if (response == false) {
			var fieldValue = this.getFieldValue();
			var doubleRegex = /(^[-+]?\d+)\.\d+$/;
			if (!fieldValue.match(doubleRegex)) {
				var errorInfo = app.vtranslate("JS_PLEASE_ENTER_DECIMAL_VALUE");
				this.setError(errorInfo);
				return false;
			}
			return true;
		}
		return response;
	}
});

Vtiger_Base_Validator_Js("Vtiger_PositiveNumber_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var positiveNumberInstance = new Vtiger_PositiveNumber_Validator_Js();
		positiveNumberInstance.setElement(field);
		var response = positiveNumberInstance.validate();
		if (response != true) {
			return positiveNumberInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var response = this._super();
		if (response !== true) {
			return response;
		}
		var fieldValue = this.getFieldValue();
		var negativeRegex = /(^[-]+\d+)$/;
		var parseFieldValue = App.Fields.Double.formatToDb(this.getFieldValue())
		if (isNaN(parseFieldValue) || fieldValue < 0 || fieldValue.match(negativeRegex)) {
			var errorInfo = app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER');
			this.setError(errorInfo);
			return false;
		}
		var maximumLength = null;
		if (this.getElement().data().fieldinfo) {
			maximumLength = this.getElement().data().fieldinfo.maximumlength;
		} else {
			maximumLength = this.getElement().data('maximumlength');
		}
		if (!maximumLength) {
			return true;
		}
		let ranges = maximumLength.split(',');
		if (ranges.length === 2) {
			if (fieldValue > parseFloat(ranges[1]) || fieldValue < parseFloat(ranges[0])) {
				errorInfo = app.vtranslate('JS_ERROR_MAX_VALUE');
				this.setError(errorInfo);
				return false;
			}
		} else {
			if (fieldValue > parseFloat(ranges[0]) || fieldValue < 0) {
				errorInfo = app.vtranslate('JS_ERROR_MAX_VALUE');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
});

Vtiger_PositiveNumber_Validator_Js("Vtiger_Percentage_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var percentageInstance = new Vtiger_Percentage_Validator_Js();
		percentageInstance.setElement(field);
		var response = percentageInstance.validate();
		if (response != true) {
			return percentageInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the percentage field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate() {
		const response = this._super();
		if (response != true) {
			return response;
		} else {
			if (App.Fields.Double.formatToDb(this.getFieldValue()) > 100) {
				this.setError(app.vtranslate('JS_PERCENTAGE_VALUE_SHOULD_BE_LESS_THAN_100'));
				return false;
			}
			return true;
		}
	}
});

Vtiger_Base_Validator_Js('Vtiger_Url_Validator_Js', {}, {
	/**
	 * Function to validate the Url
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var fieldValue = this.getFieldValue();
		var regexp = /(^|\s)((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/gi;
		var result = regexp.test(fieldValue);
		if (!result) {
			if (fieldValue.indexOf('http://') === 0 || fieldValue.indexOf('https://') === 0 || fieldValue.indexOf('ftp://') === 0 || fieldValue.indexOf('ftps://') === 0 || fieldValue.indexOf('telnet://') === 0 || fieldValue.indexOf('smb://') === 0 || fieldValue.indexOf('www.') === 0) {
				result = true;
			}
		}
		if (!result) {
			var errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');//"Please enter valid url";
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_MultiSelect_Validator_Js", {
	invokeValidation: function (field, rules, i, options) {
		var validatorInstance = new Vtiger_MultiSelect_Validator_Js();
		validatorInstance.setElement(field);
		var result = validatorInstance.validate();
		if (result == true) {
			return result;
		} else {
			return validatorInstance.getError();
		}
	}
}, {
	/**
	 * Function to validate the Multi select
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var fieldInstance = this.getElement();
		var selectElementValue = fieldInstance.val();
		if (selectElementValue == null) {
			var errorInfo = app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}

});


Vtiger_Email_Validator_Js("Vtiger_MultiEmails_Validator_Js", {
	invokeValidation: function (field) {
		var validatorInstance = new Vtiger_MultiEmails_Validator_Js();
		validatorInstance.setElement(field);
		var result = validatorInstance.validate();
		if (!result) {
			return validatorInstance.getError();
		}
	}
}, {
	/**
	 * Function to validate the Multi select
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var fieldInstance = this.getElement();
		var fieldInstanceValue = fieldInstance.val();
		if (fieldInstanceValue != '') {
			var emailsArr = fieldInstanceValue.split(',');
			var i;
			for (i = 0; i < emailsArr.length; ++i) {
				var result = this.validateValue(emailsArr[i]);
				if (result == false) {
					return result;
				}
			}
			return true;
		}
	}

});

Vtiger_Double_Validator_Js("Vtiger_GreaterThanZero_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {

		var GreaterThanZeroInstance = new Vtiger_GreaterThanZero_Validator_Js();
		GreaterThanZeroInstance.setElement(field);
		var response = GreaterThanZeroInstance.validate();
		if (response != true) {
			return GreaterThanZeroInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers and greater than zero value
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {

		var response = this._super();
		if (response != true) {
			return response;
		} else {
			var fieldValue = this.getFieldValue();
			if (fieldValue <= 0) {
				var errorInfo = app.vtranslate('JS_VALUE_SHOULD_BE_GREATER_THAN_ZERO');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
});

Vtiger_PositiveNumber_Validator_Js("Vtiger_WholeNumber_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var instance = new Vtiger_WholeNumber_Validator_Js();
		instance.setElement(field);
		var response = instance.validate();
		if (response != true) {
			return instance.getError();
		}
	}
}, {
	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return boolean true if validation is successful or false if validation error occurs
	 */
	validate: function () {
		let response = this._super();
		if (response !== true) {
			return response;
		}
		let field = this.getElement(),
			fieldValue = this.getFieldValue(),
			fieldData = field.data(),
			fieldInfo = fieldData.fieldinfo,
			errorInfo;
		if ((fieldValue % 1) !== 0) {
			if (!jQuery.isEmptyObject(fieldInfo)) {
				errorInfo = app.vtranslate('INVALID_NUMBER_OF') + " " + fieldInfo.label;
			} else {
				errorInfo = app.vtranslate('INVALID_NUMBER');
			}
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_lessThanToday_Validator_Js", {}, {
	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try {
			var fieldDateInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, fieldDateFormat);
		} catch (err) {
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0, 0, 0, 0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0, 0, 0, 0);
		var comparedDateVal = todayDateInstance - fieldDateInstance;
		if (comparedDateVal <= 0) {
			var errorInfo = fieldInfo.label + " " + app.vtranslate('JS_SHOULD_BE_LESS_THAN_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_lessThanOrEqualToToday_Validator_Js", {}, {
	/**
	 * Function to validate the datesold field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try {
			var fieldDateInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, fieldDateFormat);
		} catch (err) {
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0, 0, 0, 0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0, 0, 0, 0);
		var comparedDateVal = todayDateInstance - fieldDateInstance;
		if (comparedDateVal < 0) {
			var errorInfo = fieldInfo.label + " " + app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO') + " " + app.vtranslate('JS_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js('Vtiger_greaterThanOrEqualToToday_Validator_Js', {}, {
	/**
	 * Function to validate the dateinservice field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try {
			var fieldDateInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, fieldDateFormat);
		} catch (err) {
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0, 0, 0, 0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0, 0, 0, 0);
		var comparedDateVal = todayDateInstance - fieldDateInstance;
		if (comparedDateVal > 0) {
			var errorInfo = fieldInfo.label + " " + app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO') + " " + app.vtranslate('JS_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_greaterThanDependentField_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var fieldForValidation = field[0];
		if (jQuery(fieldForValidation).attr('name') == 'followup_date_start') {
			var dependentFieldList = new Array('date_start');
		}
		var instance = new Vtiger_greaterThanDependentField_Validator_Js();
		instance.setElement(field);
		var response = instance.validate(dependentFieldList);
		if (response != true) {
			return instance.getError();
		}
	}
}, {
	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function (dependentFieldList) {
		var field = this.getElement();
		var fieldInfo = field.data('fieldinfo');
		var fieldLabel;
		if (typeof fieldInfo === "undefined") {
			fieldLabel = jQuery(field).attr('name');
		} else {
			fieldLabel = fieldInfo.label;
		}
		var contextFormElem = field.closest('form');
		for (var i = 0; i < dependentFieldList.length; i++) {
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name=' + dependentField + ']', contextFormElem);
			if (dependentFieldInContext.length > 0) {
				var dependentFieldLabel = dependentFieldInContext.data('fieldinfo').label;
				var fieldDateInstance = this.getDateTimeInstance(field);
				var dependentFieldDateInstance = this.getDateTimeInstance(dependentFieldInContext);
				var comparedDateVal = fieldDateInstance - dependentFieldDateInstance;
				if (comparedDateVal < 0) {
					var errorInfo = fieldLabel + ' ' + app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO') + ' ' + dependentFieldLabel + '';
					this.setError(errorInfo);
					return false;
				}
			}
		}
		return true;
	},
	getDateTimeInstance: function (field) {
		var dateFormat = field.data('dateFormat');
		var fieldValue = field.val();
		try {
			var dateTimeInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, dateFormat);
		} catch (err) {
			this.setError(err);
			return false;
		}
		return dateTimeInstance;
	}
});

Vtiger_Base_Validator_Js("Vtiger_dateAndTimeGreaterThanDependentField_Validator_Js", {}, {
	fieldDateTime: '',
	fieldDateTimeInstance: [],
	dateFormat: '',
	/**
	 * Function to validate the date field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function (dependentFieldList) {
		var thisInstance = this;
		var field = this.getElement();
		var fieldDateTime = '';
		var fieldDateTimeInstance = [];
		var contextFormElem = field.closest('form');
		var view = contextFormElem.attr('name');
		if (view == 'EditView') {
			return true;
		}
		var j = 0;
		for (var i = 0; i < dependentFieldList.length; i++) {
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name=' + dependentField + ']', contextFormElem);
			if (dependentFieldInContext.length > 0) {
				if (typeof dependentFieldInContext.data('dateFormat') === "undefined" && fieldDateTime) {
					fieldDateTime += ' ' + dependentFieldInContext.val();
					fieldDateTimeInstance[j] = Vtiger_Helper_Js.getDateInstance(fieldDateTime, dateFormat);
					j++;
				} else if (typeof dependentFieldInContext.data('dateFormat') !== "undefined") {
					var dateFormat = dependentFieldInContext.data('dateFormat');
					fieldDateTime = dependentFieldInContext.val();
				}
			}
		}
		return thisInstance.difference(fieldDateTimeInstance);
	},
	difference: function (fieldDateTimeInstance) {
		if (fieldDateTimeInstance.length == 2) {
			var comparedDateVal = fieldDateTimeInstance[1] - fieldDateTimeInstance[0];
			if (comparedDateVal < 0) {
				var errorInfo = app.vtranslate('JS_AN_INCORRECT_RANGE_OF_DATES_WAS_ENTERED');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_futureEventCannotBeHeld_Validator_Js", {}, {
	/**
	 * Function to validate event status , which cannot be held for future events
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function (dependentFieldList) {
		var field = this.getElement();
		var fieldLabel = field.data('fieldinfo').label;
		var status = field.val();
		var contextFormElem = field.closest('form');
		for (var i = 0; i < dependentFieldList.length; i++) {
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name=' + dependentField + ']', contextFormElem);
			if (dependentFieldInContext.length > 0) {
				var dependentFieldLabel = dependentFieldInContext.data('fieldinfo').label;
				var todayDateInstance = new Date();
				var dateFormat = dependentFieldInContext.data('dateFormat');
				var time = jQuery('input[name=time_start]', contextFormElem);
				var fieldValue = dependentFieldInContext.val() + " " + time.val();
				var dependentFieldDateInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, dateFormat);
				var comparedDateVal = todayDateInstance - dependentFieldDateInstance;
				if (comparedDateVal < 0 && status == "Held") {
					var errorInfo = fieldLabel + ' ' + app.vtranslate('JS_FUTURE_EVENT_CANNOT_BE_HELD') + ' ' + dependentFieldLabel + '';
					this.setError(errorInfo);
					return false;
				}
			}
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_lessThanDependentField_Validator_Js", {}, {
	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function (dependentFieldList) {
		var field = this.getElement();
		var fieldLabel = field.data('fieldinfo').label;
		var contextFormElem = field.closest('form');
		//No need to validate if value is empty
		if (field.val().length == 0) {
			return;
		}
		for (var i = 0; i < dependentFieldList.length; i++) {
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name=' + dependentField + ']', contextFormElem);
			if (dependentFieldInContext.length > 0) {
				var dependentFieldLabel = dependentFieldInContext.data('fieldinfo').label;
				var fieldDateInstance = this.getDateTimeInstance(field);
				//No need to validate if value is empty
				if (dependentFieldInContext.val().length == 0) {
					continue;
				}
				var dependentFieldDateInstance = this.getDateTimeInstance(dependentFieldInContext);
				var comparedDateVal = fieldDateInstance - dependentFieldDateInstance;
				if (comparedDateVal > 0) {
					var errorInfo = fieldLabel + ' ' + app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO') + ' ' + dependentFieldLabel + '';
					this.setError(errorInfo);
					return false;
				}
			}
		}
		return true;
	},
	getDateTimeInstance: function (field) {
		var dateFormat = field.data('dateFormat');
		var fieldValue = field.val();
		try {
			var dateTimeInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, dateFormat);
		} catch (err) {
			this.setError(err);
			return false;
		}
		return dateTimeInstance;
	}
});

Vtiger_Base_Validator_Js('Vtiger_Currency_Validator_Js', {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var currencyValidatorInstance = new Vtiger_Currency_Validator_Js();
		currencyValidatorInstance.setElement(field);
		var response = currencyValidatorInstance.validate();
		if (response != true) {
			return currencyValidatorInstance.getError();
		}
	}
}, {
	/**
	 * Function to validate the Currency Field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		let response = this._super();
		if (response != true) {
			return response;
		}
		let fieldData = this.getElement().data();
		let decimalSeparator = fieldData.decimalSeparator ? fieldData.decimalSeparator : CONFIG.currencyDecimalSeparator;
		let groupSeparator = fieldData.groupSeparator ? fieldData.groupSeparator : CONFIG.currencyGroupingSeparator;

		let strippedValue = this.getFieldValue().replace(decimalSeparator, '');
		let spacePattern = /\s/;
		if (spacePattern.test(decimalSeparator) || spacePattern.test(groupSeparator))
			strippedValue = strippedValue.replace(/ /g, '');
		let errorInfo;

		if (groupSeparator === "$") {
			groupSeparator = "\\$";
		}
		if (groupSeparator === ".") {
			groupSeparator = "\\.";
		}
		let regex = new RegExp(groupSeparator, 'g');
		strippedValue = strippedValue.replace(regex, '');
		//Note: Need to review if we should allow only positive values in currencies
		/*if(strippedValue < 0){
		 var errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');//"currency value should be greater than or equal to zero";
		 this.setError(errorInfo);
		 return false;
		 }*/
		if (isNaN(strippedValue)) {
			errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');
			this.setError(errorInfo);
			return false;
		}
		if (strippedValue < 0) {
			errorInfo = app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER');
			this.setError(errorInfo);
			return false;
		}
		const maximumLength = typeof fieldData.fieldinfo !== 'undefined' ? fieldData.fieldinfo.maximumlength : null;
		if (maximumLength && strippedValue > parseFloat(maximumLength)) {
			errorInfo = app.vtranslate('JS_ERROR_MAX_VALUE');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});
Vtiger_Currency_Validator_Js("Vtiger_NumberUserFormat_Validator_Js", {
	/**
	 *Function which invokes field validation
	 * @param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		let instance = new Vtiger_Currency_Validator_Js();
		instance.setElement(field);
		if (instance.validate() !== true) {
			return instance.getError();
		}
	}

});

Vtiger_Base_Validator_Js("Vtiger_ReferenceField_Validator_Js", {}, {
	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var parentElement = field.closest('.fieldValue');
		var referenceField = parentElement.find('.sourceField');
		var referenceFieldValue = referenceField.val();
		if (referenceFieldValue == "") {
			var errorInfo = app.vtranslate('JS_REQUIRED_FIELD');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_Date_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var dateValidatorInstance = new Vtiger_Date_Validator_Js();
		dateValidatorInstance.setElement(field);
		var response = dateValidatorInstance.validate();
		if (response != true) {
			return dateValidatorInstance.getError();
		}
		return response;
	}

}, {
	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldValue = this.getFieldValue();
		try {
			if (fieldData.calendarType === 'range') {
				fieldValue = fieldValue.split(',');
				if (fieldValue.length !== 2) {
					throw new Error();
				}
			} else {
				fieldValue = [fieldValue];
			}
			for (let key in fieldValue) {
				Vtiger_Helper_Js.getDateInstance(fieldValue[key], fieldDateFormat);
			}
		} catch (err) {
			var errorInfo = app.vtranslate("JS_PLEASE_ENTER_VALID_DATE");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});
Vtiger_Date_Validator_Js("Vtiger_Datetime_Validator_Js", {}, {});
Vtiger_Base_Validator_Js("Vtiger_Time_Validator_Js", {
	/**
	 * Function which invokes field validation
	 * @param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var validatorInstance = new Vtiger_Time_Validator_Js();
		validatorInstance.setElement(field);
		var result = validatorInstance.validate();
		if (result == true) {
			return result;
		} else {
			return validatorInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Time Fields
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		let format = CONFIG.hourFormat;
		if (this.field.data('format') && [12, 24].indexOf(this.field.data('format')) != -1) {
			format = this.field.data('format');
		}
		let regexp = '';
		switch (format) {
			case 12:
				regexp = new RegExp('^([0][0-9]|1[0-2]):([0-5][0-9])([ ]PM|[ ]AM|PM|AM)$');
				break;
			default:
				regexp = new RegExp('^(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9])$');
				break;
		}
		if (!regexp.test(this.getFieldValue())) {
			var errorInfo = app.vtranslate("JS_PLEASE_ENTER_VALID_TIME");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_Twitter_Validator_Js", {
	/**
	 * Function which invokes field validation
	 * @param {jQuery} field - accepts field element as parameter
	 * @return string|true - error text if validation fails, true on success
	 */
	invokeValidation(field, rules, i, options) {
		let validatorInstance = new Vtiger_Twitter_Validator_Js();
		validatorInstance.setElement(field);
		let result = validatorInstance.validate();
		if (result == true) {
			return result;
		} else {
			return validatorInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Twwiter Account
	 * @return bool true if validation is successfull
	 */
	validate() {
		let fieldValue = this.getFieldValue();
		if (!fieldValue.match(/^[a-zA-Z0-9_]{1,15}$/g)) {
			this.setError(app.vtranslate("JS_PLEASE_ENTER_VALID_TWITTER_ACCOUNT"));
			return false;
		}
		return true;
	}
});

Vtiger_Email_Validator_Js("Vtiger_MultiEmail_Validator_Js", {
	/**
	 * Function which invokes field validation
	 * @param {jQuery} field - accepts field element as parameter
	 * @return string|true - error text if validation fails, true on success
	 */
	invokeValidation(field) {
		let validatorInstance = new Vtiger_MultiEmail_Validator_Js();
		validatorInstance.setElement(field);
		let result = validatorInstance.validate();
		if (result == true) {
			return result;
		} else {
			return validatorInstance.getError();
		}
	}
}, {
	/**
	 * Function to validate the Multi email. Check if the email address is duplicated.
	 * @return bool true if validation is successfull
	 */
	validate() {
		let fieldValue = this.getFieldValue();
		if (fieldValue === '') {
			return true;
		}
		if (this.validateValue(fieldValue) === false) {
			return false;
		}
		let allFields = $(this.field).closest('div.js-multi-email').eq(0).find('[class*=js-multi-email-row]');
		let arrayLength = allFields.length;
		for (let i = 0; i < arrayLength; ++i) {
			let inputField = $(allFields[i]).find('input.js-email').eq(0);
			if (inputField.val() === '') {
				continue;
			}
			let inputClass1 = $(allFields[i]).closest("div[class*=js-multi-email-row-]").eq(0).attr('class');
			let inputClass2 = $(this.field).closest("div[class*=js-multi-email-row-]").eq(0).attr('class');
			if (inputClass1 !== inputClass2 && inputField.val() === fieldValue) {
				this.setError(app.vtranslate("JS_EMAIL_DUPLICATED"));
				return false;
			}
		}
		return true;
	}
});

//Calendar Specific validators
// We have placed it here since quick create will not load module specific validators

Vtiger_greaterThanDependentField_Validator_Js("Calendar_greaterThanDependentField_Validator_Js", {}, {
	getDateTimeInstance: function (field) {
		let form = field.closest('form'),
			timeField,
			timeFieldValue;
		if (field.attr('name') === 'date_start') {
			timeField = form.find('[name="time_start"]');
			timeFieldValue = timeField.val();
		} else if (field.attr('name') === 'due_date') {
			timeField = form.find('[name="time_end"]');
			if (timeField.length > 0) {
				timeFieldValue = timeField.val();
			} else {
				//Max value for the day
				timeFieldValue = '11:59 PM';
			}
		}

		let dateFieldValue = field.val() + " " + timeFieldValue,
			dateFormat = field.data('dateFormat');
		return Vtiger_Helper_Js.getDateInstance(dateFieldValue, dateFormat);
	}

});

Vtiger_Base_Validator_Js('Calendar_greaterThanToday_Validator_Js', {}, {
	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try {
			var fieldDateInstance = Vtiger_Helper_Js.getDateInstance(fieldValue, fieldDateFormat);
		} catch (err) {
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0, 0, 0, 0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0, 0, 0, 0);
		var comparedDateVal = todayDateInstance - fieldDateInstance;
		if (comparedDateVal >= 0) {
			var errorInfo = fieldInfo.label + " " + app.vtranslate('JS_SHOULD_BE_GREATER_THAN_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Calendar_RepeatMonthDate_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var repeatMonthDateValidatorInstance = new Calendar_RepeatMonthDate_Validator_Js();
		repeatMonthDateValidatorInstance.setElement(field);
		var response = repeatMonthDateValidatorInstance.validate();
		if (response != true) {
			return repeatMonthDateValidatorInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var fieldValue = this.getFieldValue();

		if ((parseInt(parseFloat(fieldValue))) != fieldValue || fieldValue == '' || parseInt(fieldValue) > '31' || parseInt(fieldValue) <= 0) {
			var result = app.vtranslate('JS_NUMBER_SHOULD_BE_LESS_THAN_32');
			this.setError(result);
			return false;
		}
		return true;
	}
});

Vtiger_WholeNumber_Validator_Js("Vtiger_WholeNumberGreaterThanZero_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {

		var WholeNumberGreaterThanZero = new Vtiger_WholeNumberGreaterThanZero_Validator_Js();
		WholeNumberGreaterThanZero.setElement(field);
		var response = WholeNumberGreaterThanZero.validate();
		if (response != true) {
			return WholeNumberGreaterThanZero.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers and greater than zero value
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {

		var response = this._super();
		if (response != true) {
			return response;
		} else {
			var fieldValue = this.getFieldValue();
			if (fieldValue == 0) {
				var errorInfo = app.vtranslate('JS_VALUE_SHOULD_BE_GREATER_THAN_ZERO');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
});
Vtiger_Base_Validator_Js("Vtiger_AlphaNumeric_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var alphaNumericInstance = new Vtiger_AlphaNumeric_Validator_Js();
		alphaNumericInstance.setElement(field);
		var response = alphaNumericInstance.validate();
		if (response != true) {
			return alphaNumericInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldValue = field.val();
		var alphaNumericRegex = /^[a-z0-9 _-]*$/i;
		if (!fieldValue.match(alphaNumericRegex)) {
			var errorInfo = app.vtranslate("JS_CONTAINS_ILLEGAL_CHARACTERS");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});
Vtiger_Base_Validator_Js("Vtiger_AlphaNumericWithSlashesCurlyBraces_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var alphaNumericInstance = new Vtiger_AlphaNumericWithSlashesCurlyBraces_Validator_Js();
		alphaNumericInstance.setElement(field);
		var response = alphaNumericInstance.validate();
		if (response != true) {
			return alphaNumericInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var field = this.getElement();
		var fieldValue = field.val();
		var alphaNumericRegex = /^[\/a-z\\0-9{}: _-]*$/i;
		if (!fieldValue.match(alphaNumericRegex)) {
			var errorInfo = app.vtranslate("JS_CONTAINS_ILLEGAL_CHARACTERS");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});
Vtiger_Base_Validator_Js("Vtiger_InputMask_Validator_Js", {
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var maskInstance = new Vtiger_InputMask_Validator_Js();
		maskInstance.setElement(field);
		var response = maskInstance.validate();
		if (response != true) {
			return maskInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the Positive Numbers
	 * @return  boolean true if validation is successful false if validation error occurs
	 */
	validate: function () {
		let response = this._super();
		if (response !== true) {
			return response;
		}
		let field = this.getElement(),
			errorInfo;
		if (field.attr('data-inputmask')) {
			let unMaskedValue = field.inputmask('unmaskedvalue'),
				getMetaData = field.inputmask("getmetadata"),
				maskLength = (getMetaData.match(/9/g) || []).length + (getMetaData.match(/A/g) || []).length + (getMetaData.match(/'*'/g) || []).length;
			if (unMaskedValue.length !== 0 && maskLength > unMaskedValue.length) {
				errorInfo = app.vtranslate("JS_INVALID_LENGTH");
				this.setError(errorInfo);
				window.inputMaskValidation = true;
				return false;
			} else {
				window.inputMaskValidation = false;
			}
		}
		if (window.inputMaskValidation) {
			errorInfo = app.vtranslate("JS_INVALID_LENGTH");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});
Vtiger_Base_Validator_Js("Vtiger_Textparser_Validator_Js", {
	invokeValidation: function (field, rules, i, options) {
		var instance = new Vtiger_TextParser_Validator_Js();
		instance.setElement(field);
		var response = instance.validate();
		if (response != true) {
			return instance.getError();
		}
	}

}, {
	validate: function () {
		var response = this._super();
		if (response != true) {
			return response;
		}
		var field = this.getElement();
		var fieldValue = field.val();
		var regex = /^\$\((\w+) : ([,"\+\-\[\]\&\w\s\|]+)\)\$$/;
		if (!regex.test(fieldValue)) {
			var errorInfo = app.vtranslate('JS_INVALID_LENGTH');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
});

Vtiger_Base_Validator_Js("Vtiger_YetiForceCompanyName_Validator_Js", {
	invokeValidation: function (field, rules, i, options) {
		var instance = new Vtiger_YetiForceCompanyName_Validator_Js();
		instance.setElement(field);
		var response = instance.validate();
		if (response != true) {
			return instance.getError();
		}
	}

}, {
	validate: function () {
		let response = this._super();
		if (response != true) {
			return response;
		}
		const field = this.getElement();
		const fieldValue = field.val();
		if (fieldValue.toLowerCase().indexOf('yetiforce') >= 0) {
			this.setError(app.vtranslate('JS_YETIFORCE_COMPANY_NAME_NOT_ALLOWED'));
			return false;
		}
		return true;
	}
});
Vtiger_Base_Validator_Js("Vtiger_MultiImage_Validator_Js", {
	invokeValidation(field, rules, i, options) {
		const instance = new Vtiger_MultiImage_Validator_Js();
		instance.setElement(field);
		if (instance.validate() != true) {
			return instance.getError();
		}
	}
}, {
	validate() {
		let response = this._super();
		if (response != true) {
			return response;
		}
		const field = this.getElement();
		const fieldValue = field.val();
		if (field.data('fieldinfo').mandatory && JSON.parse(fieldValue).length === 0) {
			this.setError(app.vtranslate('JS_REQUIRED_FIELD'));
			return false;
		}
		return true;
	}
});
Vtiger_Base_Validator_Js("Vtiger_MaxSizeInByte_Validator_Js", {
	invokeValidation(field, rules, i, options) {
		const instance = new Vtiger_MaxSizeInByte_Validator_Js();
		instance.setElement(field);
		if (instance.validate() != true) {
			return instance.getError();
		}
	}
}, {
	validate() {
		let response = this._super();
		if (response != true) {
			return response;
		}
		const field = this.getElement();
		const fieldValue = field.val();
		if (field.data('fieldinfo').maximumlength && new TextEncoder().encode(fieldValue).byteLength > field.data('fieldinfo').maximumlength) {
			this.setError(
				app.vtranslate('JS_MAXIMUM_TEXT_SIZE_IN_BYTES') + ' ' + field.data('fieldinfo').maximumlength
			);
			return false;
		}
		return true;
	}
});
