/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Vtiger_Base_Validator_Js',
	{
		moduleName: false,
		/**
		 *Function which invokes field validation
		 *@param accepts field element as parameter
		 * @return error if validation fails true on success
		 */
		invokeValidation: function (field, rules, k, options) {
			//If validation engine already maked the field as error
			// we dont want to proceed
			if (typeof options !== 'undefined') {
				if (options.isError === true) {
					return;
				}
			}
			let listOfValidators = Vtiger_Base_Validator_Js.getValidator(field);
			for (let i in listOfValidators) {
				let validatorList = listOfValidators[i],
					validatorName = validatorList.name,
					validatorInstance = new validatorName(),
					result;
				validatorInstance.setElement(field);
				if (validatorList.hasOwnProperty('params')) {
					result = validatorInstance.validate(validatorList.params);
				} else {
					result = validatorInstance.validate();
				}
				if (!result) {
					return validatorInstance.getError();
				}
			}
		},
		/**
		 *Function which gets the complete list of validators based on type and data-validator
		 *@param accepts field element as parameter
		 * @return list of validators for field
		 */
		getValidator: function (field) {
			let listOfValidators = [],
				fieldData = field.data(),
				fieldInfo = fieldData.fieldinfo;
			if (typeof fieldInfo === 'string') {
				fieldInfo = JSON.parse(fieldInfo);
			}
			let dataValidator = 'validator',
				moduleEle = field.closest('form').find('[name="module"]');
			if (Vtiger_Base_Validator_Js.moduleName === false && moduleEle.length > 0) {
				Vtiger_Base_Validator_Js.moduleName = moduleEle.val();
			}

			let fieldInstance = Vtiger_Field_Js.getInstance(fieldInfo),
				validatorsOfType = Vtiger_Base_Validator_Js.getValidatorsFromFieldType(fieldInstance),
				key,
				value;
			for (key in validatorsOfType) {
				//IE for loop fix
				if (!validatorsOfType.hasOwnProperty(key)) {
					continue;
				}
				value = validatorsOfType[key];
				if (value !== '') {
					listOfValidators.push({ name: value });
				}
			}
			if (fieldData.hasOwnProperty(dataValidator)) {
				let specialValidators = fieldData[dataValidator];
				for (let s in specialValidators) {
					//IE for loop fix
					if (!specialValidators.hasOwnProperty(s)) {
						continue;
					}
					let specialValidator = specialValidators[s],
						tempSpecialValidator = jQuery.extend({}, specialValidator),
						validatorOfNames = Vtiger_Base_Validator_Js.getValidatorClassName(specialValidator.name);
					if (validatorOfNames !== '') {
						tempSpecialValidator.name = validatorOfNames;
						if (!jQuery.isEmptyObject(tempSpecialValidator)) {
							listOfValidators.push(tempSpecialValidator);
						}
					}
				}
			}
			return listOfValidators;
		},
		/**
		 *Function which gets the list of validators based on data type of field
		 *@param accepts fieldInstance as parameter
		 * @return list of validators for particular field type
		 */
		getValidatorsFromFieldType: function (fieldInstance) {
			var fieldType = fieldInstance.getType();
			var validatorsOfType = [];
			fieldType = fieldType.charAt(0).toUpperCase() + fieldType.slice(1).toLowerCase();
			validatorsOfType.push(Vtiger_Base_Validator_Js.getValidatorClassName(fieldType));
			return validatorsOfType;
		},
		getValidatorClassName: function (validatorName) {
			var validatorsOfType = '';
			var className = Vtiger_Base_Validator_Js.getClassName(validatorName);
			var fallBackClassName = Vtiger_Base_Validator_Js.getFallBackClassName(validatorName);
			if (typeof window[className] !== 'undefined') {
				validatorsOfType = window[className];
			} else if (typeof window[fallBackClassName] !== 'undefined') {
				validatorsOfType = window[fallBackClassName];
			}
			return validatorsOfType;
		},
		/**
		 *Function which gets validator className
		 *@param accepts validatorName as parameter
		 * @return module specific validator className
		 */
		getClassName: function (validatorName) {
			let moduleName;
			if (Vtiger_Base_Validator_Js.moduleName != false) {
				moduleName = Vtiger_Base_Validator_Js.moduleName;
			} else {
				moduleName = app.getModuleName();
			}
			return moduleName + '_' + validatorName + '_Validator_Js';
		},
		/**
		 *Function which gets validator className
		 *@param accepts validatorName as parameter
		 * @return generic validator className
		 */
		getFallBackClassName: function (validatorName) {
			return 'Vtiger_' + validatorName + '_Validator_Js';
		}
	},
	{
		field: '',
		error: '',
		/**
		 *Function which validates the field data
		 * @return true
		 */
		validate: function () {
			return true;
		},
		/**
		 *Function which gets error message
		 * @return error message
		 */
		getError: function () {
			if (this.error != null) {
				return this.error;
			}
			return 'Validation Failed';
		},
		/**
		 *Function which sets error message
		 * @return Instance
		 */
		setError: function (errorInfo) {
			this.error = errorInfo;
			return this;
		},
		/**
		 *Function which sets field attribute of class
		 * @return Instance
		 */
		setElement: function (field) {
			this.field = field;
			return this;
		},
		/**
		 *Function which gets field attribute of class
		 * @return Instance
		 */
		getElement: function () {
			return this.field;
		},
		/**
		 *Function which gets trimed field value
		 * @return fieldValue
		 */
		getFieldValue: function () {
			var field = this.getElement();
			return jQuery.trim(field.val());
		}
	}
);
