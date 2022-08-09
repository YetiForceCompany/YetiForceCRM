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
	'Vtiger_AdvanceFilter_Js',
	{
		getInstance: function (container) {
			var module = app.getModuleName();
			var moduleClassName1 = module + '_AdvanceFilterEx_Js';
			var moduleClassName2 = module + '_AdvanceFilter_Js';
			var moduleClassName3 = 'Vtiger_AdvanceFilterEx_Js';
			var moduleClassName4 = 'Vtiger_AdvanceFilter_Js';
			var instance;
			if (typeof window[moduleClassName1] !== 'undefined') {
				instance = new window[moduleClassName1](container);
			} else if (typeof window[moduleClassName2] !== 'undefined') {
				instance = new window[moduleClassName2](container);
			} else if (typeof window[moduleClassName3] !== 'undefined') {
				instance = new window[moduleClassName3](container);
			} else {
				instance = new window[moduleClassName4](container);
			}
			return instance;
		}
	},
	{
		filterContainer: false,
		//Hold the conditions for a particular field type
		fieldTypeConditionMapping: false,
		//Hold the condition and their label translations
		conditonOperatorLabelMapping: false,
		dateConditionInfo: false,
		fieldModelInstance: false,
		//Holds fields type and conditions for which it needs validation
		validationSupportedFieldConditionMap: {
			email: ['e', 'n']
		},
		//Hols field type for which there is validations always needed
		allConditionValidationNeededFieldList: ['double', 'integer', 'currency'],
		//used to eliminate mutiple times validation registrations
		validationForControlsRegistered: false,
		init: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('.filterContainer');
			}

			if (container.is('.filterContainer')) {
				this.setFilterContainer(container);
			} else {
				this.setFilterContainer(jQuery('.filterContainer', container));
			}
			this.initialize();
		},
		getModuleName: function () {
			return 'AdvanceFilter';
		},
		/**
		 * Function  to initialize the advance filter
		 */
		initialize: function () {
			this.registerEvents();
			this.initializeOperationMappingDetails();
			this.loadFieldSpecificUiForAll();
		},
		/**
		 * Function which will save the field condition mapping condition label mapping
		 */
		initializeOperationMappingDetails: function () {
			var filterContainer = this.getFilterContainer();
			this.fieldTypeConditionMapping = jQuery('input[name="advanceFilterOpsByFieldType"]', filterContainer).data(
				'value'
			);
			this.conditonOperatorLabelMapping = jQuery('input[name="advanceFilterOptions"]', filterContainer).data('value');
			this.dateConditionInfo = jQuery('[name="date_filters"]').data('value');
			return this;
		},
		/**
		 * Function to get the container which holds all the filter elements
		 * @return jQuery object
		 */
		getFilterContainer: function () {
			return this.filterContainer;
		},
		/**
		 * Function to set the filter container
		 * @params : element - which represents the filter container
		 * @return : current instance
		 */
		setFilterContainer: function (element) {
			this.filterContainer = element;
			return this;
		},
		getDateSpecificConditionInfo: function () {
			return this.dateConditionInfo;
		},
		/**
		 * Function which will return set of condition for the given field type
		 * @return array of conditions
		 */
		getConditionListFromType: function (fieldType) {
			var fieldTypeConditions = this.fieldTypeConditionMapping[fieldType];
			if (fieldType == 'D' || fieldType == 'DT') {
				fieldTypeConditions = fieldTypeConditions.concat(this.getDateConditions(fieldType));
			}
			return fieldTypeConditions;
		},
		getDateConditions: function (fieldType) {
			if (fieldType != 'D' && fieldType != 'DT') {
				return [];
			}
			var dateFilters = this.getDateSpecificConditionInfo();
			return Object.keys(dateFilters);
		},
		/**
		 * Function to get the condition label
		 * @param : key - condition key
		 * @reurn : label for the condition or key if it doest not contain in the condition label mapping
		 */
		getConditionLabel: function (key) {
			if (key in this.conditonOperatorLabelMapping) {
				return this.conditonOperatorLabelMapping[key];
			}
			if (key in this.getDateSpecificConditionInfo()) {
				return this.getDateSpecificConditionInfo()[key]['label'];
			}
			return key;
		},
		/**
		 * Function to check if the field selected is empty field
		 * @params : select element which represents the field
		 * @return : boolean true/false
		 */
		isEmptyFieldSelected: function (fieldSelect) {
			var selectedOption = fieldSelect.find('option:selected');
			//assumption that empty field will be having value none
			if (selectedOption.val() == 'none') {
				return true;
			}
			return false;
		},
		/**
		 * Function to get the add condition elements
		 * @returns : jQuery object which represents the add conditions elements
		 */
		getAddConditionElement: function () {
			var filterContainer = this.getFilterContainer();
			return jQuery('.addCondition button', filterContainer);
		},
		/**
		 * Function to add new condition row
		 * @params : condtionGroupElement - group where condtion need to be added
		 * @return : current instance
		 */
		addNewCondition: function (conditionGroupElement) {
			let basicElement = $('.basic', conditionGroupElement);
			let newRowElement = basicElement.find('.js-conditions-row').clone(true, true);
			let selectElement = newRowElement.find('select').addClass('select2');
			let conditionList = $('.conditionList', conditionGroupElement);
			conditionList.append(newRowElement);
			App.Fields.Picklist.showSelect2ElementView(selectElement, {
				dropdownParent: conditionGroupElement
			});
			return this;
		},
		/**
		 * Function/Handler  which will triggered when user clicks on add condition
		 */
		addConditionHandler: function (e) {
			var element = jQuery(e.currentTarget);
			var conditionGroup = element.closest('div.conditionGroup');
			this.addNewCondition(conditionGroup);
		},
		getFieldSpecificType: function (fieldSelected) {
			var fieldInfo = fieldSelected.data('fieldinfo');
			var type = fieldInfo.type;
			if (type == 'reference') {
				return 'V';
			}
			return fieldSelected.data('fieldtype');
		},
		/**
		 * Function to load condition list for the selected field
		 * @params : fieldSelect - select element which will represents field list
		 * @return : select element which will represent the condition element
		 */
		loadConditions: function (fieldSelect) {
			var row = fieldSelect.closest('div.js-conditions-row');
			var conditionSelectElement = row.find('select[name="comparator"]');
			var group = row.find('[name="column_condition"]');
			var conditionSelected = conditionSelectElement.val();
			var fieldSelected = fieldSelect.find('option:selected');
			var fieldSpecificType = this.getFieldSpecificType(fieldSelected);
			var conditionList = this.getConditionListFromType(fieldSpecificType);
			var fieldInfo = fieldSelected.data('fieldinfo');
			//for none in field name
			if (typeof conditionList === 'undefined') {
				conditionList = {};
				conditionList['none'] = 'None';
			}
			var options = '';
			for (var key in conditionList) {
				if (
					jQuery.inArray(fieldInfo.type, ['rangeTime', 'image']) != -1 &&
					jQuery.inArray(conditionList[key], ['y', 'ny']) == -1
				) {
					continue;
				}
				if (
					jQuery.inArray(fieldInfo.type, [
						'userCreator',
						'owner',
						'picklist',
						'modules',
						'tree',
						'inventoryLimit',
						'languages',
						'currencyList',
						'fileLocationType'
					]) != -1 &&
					jQuery.inArray(conditionList[key], ['s', 'ew', 'c', 'k']) != -1
				) {
					continue;
				}
				if (
					conditionList[key] === 'om' &&
					jQuery.inArray(fieldInfo.type, ['owner', 'sharedOwner', 'userCreator']) == -1
				) {
					continue;
				}
				if (
					jQuery.inArray(conditionList[key], ['wr', 'nwr']) != -1 &&
					jQuery.inArray(fieldInfo.type, ['owner']) == -1
				) {
					continue;
				}
				if (
					jQuery.inArray(conditionList[key], ['s', 'ew']) != -1 &&
					jQuery.inArray(fieldInfo.type, ['taxes', 'multipicklist', 'categoryMultipicklist', 'sharedOwner']) != -1
				) {
					continue;
				}
				if (
					jQuery.inArray(conditionList[key], ['bw', 'm', 'h']) != -1 &&
					jQuery.inArray(fieldInfo.type, ['time']) != -1
				) {
					continue;
				}
				if (conditionList[key] === 'd' && group.val() !== 'and') {
					continue;
				}
				//IE Browser consider the prototype properties also, it should consider has own properties only.
				if (conditionList.hasOwnProperty(key)) {
					var conditionValue = conditionList[key];
					var conditionLabel = this.getConditionLabel(conditionValue);
					options += '<option value="' + conditionValue + '"';
					if (conditionValue == conditionSelected) {
						options += ' selected="selected" ';
					}
					options += '>' + conditionLabel + '</option>';
				}
			}
			conditionSelectElement.empty().html(options).trigger('change');
			return conditionSelectElement;
		},
		/**
		 * Functiont to get the field specific ui for the selected field
		 * @prarms : fieldSelectElement - select element which will represents field list
		 * @return : jquery object which represents the ui for the field
		 */
		getFieldSpecificUi: function (fieldSelectElement) {
			let fieldModel = this.fieldModelInstance,
				html;
			if (fieldModel.get('comparatorElementVal') === 'd') {
				html = '<div class="checkbox"><label><input type="checkbox" name="' + fieldModel.getName() + '" value="0" ';
				if (fieldModel.getValue() === 1 || fieldModel.getValue() === '1') {
					html += 'checked';
				}
				html += ' >' + app.vtranslate('JS_IGNORE_EMPTY_VALUES') + '</label></div>';
				return $(html);
			} else if (
				fieldModel.getType().toLowerCase() === 'boolean' ||
				fieldSelectElement.find('option:selected').data('fieldtype') === 'C'
			) {
				let selectedValue = fieldSelectElement.closest('.js-conditions-row').find('[data-value="value"]').val();
				html = '<select class="select2" name="' + fieldModel.getName() + '">';
				html += '<option value="0"';
				if (selectedValue === '0') {
					html += ' selected="selected" ';
				}
				html += '>' + app.vtranslate('JS_IS_DISABLED') + '</option>';

				html += '<option value="1"';
				if (selectedValue === '1') {
					html += ' selected="selected" ';
				}
				html += '>' + app.vtranslate('JS_IS_ENABLED') + '</option>';
				html += '</select>';
				return $(html);
			} else {
				return $(fieldModel.getUiTypeSpecificHtml());
			}
		},
		/**
		 * Function which will load the field specific ui for a selected field
		 * @prarms : fieldSelect - select element which will represents field list
		 * @return : current instance
		 */
		loadFieldSpecificUi: function (fieldSelect) {
			const selectedOption = fieldSelect.find('option:selected');
			const row = fieldSelect.closest('div.js-conditions-row');
			const fieldUiHolder = row.find('.fieldUiHolder');
			const conditionSelectElement = row.find('select[name="comparator"]');
			let fieldInfo = selectedOption.data('fieldinfo');
			const valueType = fieldUiHolder.find(`[name="${fieldInfo.name}"]`).data('valuetype');
			let fieldType = 'string';
			if (typeof fieldInfo !== 'undefined') {
				fieldType = fieldInfo.type;
			} else {
				fieldInfo = {};
			}

			var comparatorElementVal = (fieldInfo.comparatorElementVal = conditionSelectElement.val());
			if (fieldType == 'date' || fieldType == 'datetime') {
				fieldInfo.dateSpecificConditions = this.getDateSpecificConditionInfo();
			}
			var moduleName = this.getModuleName();
			var fieldModel = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
			this.fieldModelInstance = fieldModel;
			var fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);

			//remove validation since we dont need validations for all eleements
			// Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
			var fieldName = fieldModel.getName();

			if (
				$.inArray(fieldModel.getType(), [
					'multipicklist',
					'sharedOwner',
					'multiReferenceValue',
					'taxes',
					'categoryMultipicklist'
				]) > -1
			) {
				fieldName = fieldName + '[]';
			} else if (
				$.inArray(fieldModel.getType(), [
					'userCreator',
					'picklist',
					'owner',
					'languages',
					'modules',
					'inventoryLimit',
					'currencyList',
					'fileLocationType'
				]) > -1 &&
				fieldSpecificUi.is('select') &&
				(comparatorElementVal == 'e' || comparatorElementVal == 'n')
			) {
				fieldName = fieldName + '[]';
			}
			fieldSpecificUi.filter('[name="' + fieldName + '"]').addClass('form-control');
			fieldSpecificUi
				.filter('[name="' + fieldName + '"]')
				.attr('data-value', 'value')
				.removeAttr('data-validation-engine');
			fieldSpecificUi
				.find('[name="' + fieldName + '"]')
				.attr('data-value', 'value')
				.removeAttr('data-validation-engine');

			if (fieldModel.getType() == 'currency') {
				fieldSpecificUi
					.filter('[name="' + fieldName + '"]')
					.attr('data-decimal-separator', fieldInfo.decimal_separator)
					.attr('data-group-separator', fieldInfo.group_separator);
				fieldSpecificUi
					.find('[name="' + fieldName + '"]')
					.attr('data-decimal-separator', fieldInfo.decimal_separator)
					.attr('data-group-separator', fieldInfo.group_separator);
			}
			if (valueType) {
				this.fieldModelInstance.data.workflow_valuetype = valueType;
			}
			fieldUiHolder.html(fieldSpecificUi);

			if (fieldSpecificUi.is('select')) {
				App.Fields.Picklist.showSelect2ElementView(fieldSpecificUi, { dropdownParent: row });
			} else if (fieldSpecificUi.has('input.dateField').length > 0) {
				App.Fields.Date.register(fieldSpecificUi);
			} else if (fieldSpecificUi.has('input.dateRangeField').length > 0) {
				App.Fields.Date.registerRange(fieldSpecificUi, { ranges: false });
			} else if (fieldSpecificUi.has('input.clockPicker').length > 0) {
				app.registerEventForClockPicker(fieldSpecificUi);
			}
			this.addValidationToFieldIfNeeded(fieldSelect);

			// Is Empty, today, tomorrow, yesterday conditions does not need any field input value - hide the UI
			// re-enable if condition element is chosen.
			var specialConditions = [
				'y',
				'today',
				'tomorrow',
				'yesterday',
				'smallerthannow',
				'greaterthannow',
				'ny',
				'om',
				'nom',
				'wr',
				'nwr'
			];
			if (specialConditions.indexOf(conditionSelectElement.val()) != -1) {
				fieldUiHolder.hide();
			} else {
				fieldUiHolder.show();
			}

			return this;
		},
		/**
		 * Function to load field specific ui for all the select elements - this is used on load
		 * to show field specific ui for all the fields
		 */
		loadFieldSpecificUiForAll: function () {
			var conditionsContainer = jQuery('.conditionList');
			var fieldSelectElement = jQuery('select[name="columnname"]', conditionsContainer);
			jQuery.each(fieldSelectElement, function (i, elem) {
				var currentElement = jQuery(elem);
				if (currentElement.val() != 'none') {
					currentElement.trigger('change', { _intialize: true });
				}
			});
			return this;
		},
		/**
		 * Function to add the validation if required
		 * @prarms : selectFieldElement - select element which will represents field list
		 */
		addValidationToFieldIfNeeded: function (selectFieldElement) {
			var selectedOption = selectFieldElement.find('option:selected');
			var row = selectFieldElement.closest('div.js-conditions-row');
			var fieldSpecificElement = row.find('[data-value="value"]');
			var validator = selectedOption.attr('data-validator');

			if (this.isFieldSupportsValidation(selectFieldElement)) {
				//data attribute will not be present while attaching validation engine events . so we are
				//depending on the fallback option which is class
				fieldSpecificElement
					.addClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]')
					.attr('data-validation-engine', 'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]')
					.attr('data-fieldinfo', JSON.stringify(selectedOption.data('fieldinfo')));
				if (typeof validator !== 'undefined') {
					fieldSpecificElement.attr('data-validator', validator);
				}
			} else {
				fieldSpecificElement
					.removeClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]')
					.removeAttr('data-validation-engine');
			}
			return this;
		},
		/**
		 * Check if field supports validation
		 * @prarms : selectFieldElement - select element which will represents field list
		 * @return - boolen true/false
		 */
		isFieldSupportsValidation: function (fieldSelect) {
			var fieldModel = this.fieldModelInstance;
			var type = fieldModel.getType();

			if (jQuery.inArray(type, this.allConditionValidationNeededFieldList) >= 0) {
				return true;
			}

			var row = fieldSelect.closest('div.js-conditions-row');
			var conditionSelectElement = row.find('select[name="comparator"]');

			var conditionValue = conditionSelectElement.val();

			if (type in this.validationSupportedFieldConditionMap) {
				if (jQuery.inArray(conditionValue, this.validationSupportedFieldConditionMap[type]) >= 0) {
					return true;
				}
			}
			return false;
		},
		/**
		 * Function to retrieve the values of the filter
		 * @return : object
		 */
		getValues: function () {
			const thisInstance = this;
			let fieldList = ['columnname', 'comparator', 'value', 'column_condition'],
				filterContainer = this.getFilterContainer(),
				conditionGroups = $('.conditionGroup', filterContainer),
				values = {},
				columnIndex = 0;
			$('.conditionGroup', filterContainer).each(function (index, domElement) {
				let groupElement = $(domElement);
				values[index + 1] = {};
				values[index + 1]['columns'] = {};
				$('.conditionList .js-conditions-row', groupElement).each(function (i, conditionDomElement) {
					let rowElement = $(conditionDomElement),
						fieldSelectElement = $('[name="columnname"]', rowElement),
						valueSelectElement = $('[data-value="value"]', rowElement);
					//To not send empty fields to server
					if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
						return true;
					}
					let fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo'),
						fieldType = fieldDataInfo.type,
						rowValues = {},
						key,
						field;
					if (fieldType === 'owner' || fieldType === 'userCreator') {
						for (key in fieldList) {
							field = fieldList[key];
							if (field === 'value' && valueSelectElement.is('select')) {
								let newValuesArr = valueSelectElement.val();
								if (!newValuesArr) {
									rowValues[field] = '';
								} else {
									rowValues[field] = newValuesArr.join(',');
								}
							} else if (field === 'value' && valueSelectElement.is('input')) {
								rowValues[field] =
									valueSelectElement.attr('type') === 'checkbox' && valueSelectElement.prop('checked')
										? 1
										: valueSelectElement.val();
							} else {
								rowValues[field] = $('[name="' + field + '"]', rowElement).val();
							}
						}
					} else if (
						$.inArray(fieldType, [
							'picklist',
							'multipicklist',
							'modules',
							'sharedOwner',
							'multiReferenceValue',
							'inventoryLimit',
							'languages',
							'currencyList',
							'taxes',
							'fileLocationType',
							'categoryMultipicklist'
						]) > -1
					) {
						for (key in fieldList) {
							field = fieldList[key];
							if (field === 'value' && valueSelectElement.attr('type') === 'checkbox') {
								rowValues[field] = valueSelectElement.prop('checked') ? 1 : valueSelectElement.val();
							} else if (field === 'value' && valueSelectElement.is('input')) {
								let pickListValues = valueSelectElement.data('picklistvalues'),
									valuesArr = valueSelectElement.val().split(','),
									newValuesArr = [];
								for (i = 0; i < valuesArr.length; i++) {
									if (typeof pickListValues[valuesArr[i]] !== 'undefined') {
										newValuesArr.push(pickListValues[valuesArr[i]]);
									} else {
										newValuesArr.push(valuesArr[i]);
									}
								}
								rowValues[field] = newValuesArr.join(',');
							} else if (
								field === 'value' &&
								valueSelectElement.is('select') &&
								$.inArray(fieldType, [
									'picklist',
									'multipicklist',
									'modules',
									'sharedOwner',
									'multiReferenceValue',
									'inventoryLimit',
									'languages',
									'currencyList',
									'taxes',
									'fileLocationType',
									'categoryMultipicklist'
								]) > -1
							) {
								let value = valueSelectElement.val();
								if (value == null) {
									rowValues[field] = value;
								} else {
									rowValues[field] = value.join(',');
								}
							} else {
								rowValues[field] = $('[name="' + field + '"]', rowElement).val();
							}
						}
					} else {
						for (key in fieldList) {
							field = fieldList[key];
							if (field === 'value') {
								rowValues[field] =
									valueSelectElement.attr('type') === 'checkbox' && valueSelectElement.prop('checked')
										? 1
										: valueSelectElement.val();
							} else {
								rowValues[field] = $('[name="' + field + '"]', rowElement).val();
							}
						}
					}

					if (rowElement.is(':last-child')) {
						rowValues['column_condition'] = '';
					}
					values[index + 1]['columns'][columnIndex] = rowValues;
					columnIndex++;
				});
				if (groupElement.find('div.groupCondition').length > 0) {
					values[index + 1]['condition'] = conditionGroups.find('div.groupCondition [name="condition"]').val();
				}
			});
			return values;
		},
		/**
		 * Event handle which will be triggred on deletion of a condition row
		 */
		deleteConditionHandler: function (e) {
			var element = jQuery(e.currentTarget);
			var row = element.closest('.js-conditions-row');
			row.remove();
		},
		/**
		 * Event handler which is invoked on add condition
		 */
		registerAddCondition: function () {
			var thisInstance = this;
			this.getAddConditionElement().on('click', function (e) {
				thisInstance.addConditionHandler(e);
			});
		},
		/**
		 * Function which will register field change event
		 */
		registerFieldChange: function () {
			var filterContainer = this.getFilterContainer();
			var thisInstance = this;
			filterContainer.on('change', 'select[name="columnname"]', function (e, data) {
				var currentElement = jQuery(e.currentTarget);
				if (typeof data === 'undefined' || data._intialize != true) {
					var row = currentElement.closest('div.js-conditions-row');
					var conditionSelectElement = row.find('select[name="comparator"]');
					conditionSelectElement.empty();
				}
				thisInstance.loadConditions(currentElement);
				thisInstance.loadFieldSpecificUi(currentElement);
			});
		},
		/**
		 * Function which will register condition change
		 */
		registerConditionChange: function () {
			var filterContainer = this.getFilterContainer();
			var thisInstance = this;
			filterContainer.on('change', 'select[name="comparator"]', function (e) {
				var comparatorSelectElement = jQuery(e.currentTarget);
				var row = comparatorSelectElement.closest('div.js-conditions-row');
				var fieldSelectElement = row.find('select[name="columnname"]');
				//To handle the validation depending on condtion
				thisInstance.loadFieldSpecificUi(fieldSelectElement);
				thisInstance.addValidationToFieldIfNeeded(fieldSelectElement);
			});
		},
		/**
		 * Function to regisgter delete condition event
		 */
		registerDeleteCondition: function () {
			var thisInstance = this;
			var filterContainer = this.getFilterContainer();
			filterContainer.on('click', '.js-condition-delete', function (e) {
				thisInstance.deleteConditionHandler(e);
			});
		},
		/**
		 * Function which will regiter all events for this page
		 */
		registerEvents: function () {
			this.registerAddCondition();
			this.registerFieldChange();
			this.registerDeleteCondition();
			this.registerConditionChange();
		}
	}
);

Vtiger_Field_Js(
	'AdvanceFilter_Field_Js',
	{},
	{
		getUiTypeSpecificHtml: function () {
			var uiTypeModel = this.getUiTypeModel();
			return uiTypeModel.getUi();
		},
		getModuleName: function () {
			var currentModule = app.getModuleName();

			var type = this.getType();
			if (
				$.inArray(type, [
					'picklist',
					'userCreator',
					'multipicklist',
					'owner',
					'userCreator',
					'modules',
					'date',
					'datetime',
					'sharedOwner',
					'multiReferenceValue',
					'inventoryLimit',
					'languages',
					'currencyList',
					'taxes',
					'fileLocationType',
					'categoryMultipicklist'
				]) > -1
			) {
				currentModule = 'AdvanceFilter';
			}
			return currentModule;
		}
	}
);

Vtiger_Picklist_Field_Js(
	'AdvanceFilter_Picklist_Field_Js',
	{},
	{
		getUi: function () {
			var html = '<select class="select2" multiple name="' + this.getName() + '[]">';
			var pickListValues = this.getPickListValues();
			var selectedOption = app.htmlDecode(this.getValue());
			var selectedOptionsArray = selectedOption.split(',');
			for (var option in pickListValues) {
				html += '<option value="' + option + '" ';
				if (jQuery.inArray(option, selectedOptionsArray) != -1) {
					html += ' selected ';
				}
				html += '>' + pickListValues[option] + '</option>';
			}
			html += '</select>';
			var selectContainer = jQuery(html);
			this.addValidationToElement(selectContainer);
			return selectContainer;
		}
	}
);

AdvanceFilter_Picklist_Field_Js('AdvanceFilter_Modules_Field_Js', {}, {});

AdvanceFilter_Picklist_Field_Js('AdvanceFilter_Inventorylimit_Field_Js', {}, {});

AdvanceFilter_Picklist_Field_Js('AdvanceFilter_Filelocationtype_Field_Js', {}, {});

Vtiger_Multipicklist_Field_Js('AdvanceFilter_Multipicklist_Field_Js', {}, {});

Vtiger_Categorymultipicklist_Field_Js(
	'AdvanceFilter_Categorymultipicklist_Field_Js',
	{},
	{
		getReadOnly: function () {
			let result = '';
			if (this.getValue()) {
				result = ' readonly="true" ';
			}
			return result;
		},
		/**
		 * Function to get the user date format
		 */
		getTreeTemplate: function () {
			return this.get('treetemplate');
		},
		/**
		 * Function to get the user date format
		 */
		getModuleName: function () {
			return this.get('modulename');
		},
		getUi: function () {
			let pickListValues = this.getPickListValues();
			let value = this.getValue() || '';
			let values = value.split(',').map((v) => pickListValues[v] || '');
			let displayValue = values.join(', ', values);

			let multiple = 1;
			if (this.getType() === 'tree') {
				multiple = 0;
			}

			const treeContainer = document.createElement('div');
			treeContainer.setAttribute('class', 'js-tree-container fieldValue');
			const sourceInput = document.createElement('input');
			sourceInput.setAttribute('name', this.getName());
			sourceInput.setAttribute('type', 'hidden');
			sourceInput.setAttribute('value', this.getValue());
			sourceInput.setAttribute('class', 'sourceField');
			sourceInput.setAttribute('fieldinfo', JSON.stringify(this.getData()));
			sourceInput.setAttribute('data-multiple', multiple);
			sourceInput.setAttribute('data-value', 'value');
			sourceInput.setAttribute('data-treetemplate', this.getTreeTemplate());
			sourceInput.setAttribute('data-module-name', this.getModuleName());
			const inputGroup = document.createElement('div');
			inputGroup.setAttribute('class', 'input-group');

			const clearBtn = document.createElement('span');
			clearBtn.setAttribute('class', 'input-group-prepend clearTreeSelection u-cursor-pointer');
			const clearBtnText = document.createElement('span');
			clearBtnText.setAttribute('class', 'input-group-text');
			const clearBtnTextIcon = document.createElement('span');
			clearBtnTextIcon.setAttribute('class', 'fas fa-times-circle');
			clearBtnText.appendChild(clearBtnTextIcon);
			clearBtn.appendChild(clearBtnText);

			const sourceDisplay = document.createElement('input');
			sourceDisplay.setAttribute('name', this.getName() + '_display');
			sourceDisplay.setAttribute('id', this.getName() + '_display');
			sourceDisplay.setAttribute('type', 'text');
			sourceDisplay.setAttribute('class', 'ml-0 treeAutoComplete form-control');
			sourceDisplay.setAttribute('value', displayValue);
			sourceInput.setAttribute('fieldinfo', JSON.stringify(this.getData()));
			if (this.getReadOnly()) {
				sourceDisplay.setAttribute('readonly', 'readonly');
			}

			const searchBtn = document.createElement('span');
			searchBtn.setAttribute('class', 'input-group-append js-tree-modal u-cursor-pointer');
			const searchBtnText = document.createElement('span');
			searchBtnText.setAttribute('class', 'input-group-text');
			const searchBtnTextIcon = document.createElement('span');
			searchBtnTextIcon.setAttribute('class', 'fas fa-search');
			searchBtnText.appendChild(searchBtnTextIcon);
			searchBtn.appendChild(searchBtnText);

			inputGroup.appendChild(clearBtn);
			inputGroup.appendChild(sourceDisplay);
			inputGroup.appendChild(searchBtn);

			treeContainer.appendChild(sourceInput);
			treeContainer.appendChild(inputGroup);

			let selectContainer = $(treeContainer);
			App.Fields.Tree.register(selectContainer);
			this.addValidationToElement(selectContainer);
			return selectContainer;
		}
	}
);

AdvanceFilter_Categorymultipicklist_Field_Js('Vtiger_Tree_Field_Js', {}, {});

AdvanceFilter_Picklist_Field_Js('AdvanceFilter_Languages_Field_Js', {}, {});

AdvanceFilter_Picklist_Field_Js('AdvanceFilter_Currencylist_Field_Js', {}, {});

AdvanceFilter_Multipicklist_Field_Js('AdvanceFilter_Taxes_Field_Js', {}, {});

Vtiger_Owner_Field_Js(
	'AdvanceFilter_Owner_Field_Js',
	{},
	{
		getUi: function () {
			let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
				html = '',
				selectContainer;
			if (
				comparatorSelectedOptionVal === 'e' ||
				comparatorSelectedOptionVal === 'n' ||
				(this.getName() === 'shownerid' && $.inArray(comparatorSelectedOptionVal, ['c', 'k']) != -1)
			) {
				html = '<select class="select2" multiple name="' + this.getName() + '[]">';
				let pickListValues = this.getPickListValues(),
					selectedOption = app.htmlDecode(this.getValue()),
					optGroup;
				for (optGroup in pickListValues) {
					html += '<optgroup label="' + optGroup + '">';
					let optionGroupValues = pickListValues[optGroup],
						option;
					for (option in optionGroupValues) {
						html += '<option value="' + option + '" ';
						if ($.inArray(option, selectedOption.split(',')) != -1) {
							html += ' selected ';
						}
						html += '>' + optionGroupValues[option] + '</option>';
					}
					html += '</optgroup>';
				}
				html += '</select>';
				selectContainer = $(html);
				this.addValidationToElement(selectContainer);
				return selectContainer;
			} else {
				let tagsArray = [];
				$.each(this.getPickListValues(), function (groups, blocks) {
					$.each(blocks, function (i, e) {
						tagsArray.push($.trim(e));
					});
				});
				html = '<input data-tags="' + tagsArray + '" type="hidden" class="row select2" name="' + this.getName() + '">';
				selectContainer = $(html).val(this.getValue());
				selectContainer.data('tags', tagsArray);
				this.addValidationToElement(selectContainer);
				return selectContainer;
			}
		}
	}
);

Vtiger_Multireferencevalue_Field_Js('AdvanceFilter_Multireferencevalue_Field_Js', {}, {});

AdvanceFilter_Owner_Field_Js('AdvanceFilter_Sharedowner_Field_Js', {}, {});

AdvanceFilter_Owner_Field_Js('AdvanceFilter_Usercreator_Field_Js', {}, {});
Vtiger_Date_Field_Js(
	'AdvanceFilter_Date_Field_Js',
	{},
	{
		/**
		 * Function to get the ui
		 * @return - input text field
		 */
		getUi: function () {
			let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
				dateSpecificConditions = this.get('dateSpecificConditions'),
				html = '';
			if (comparatorSelectedOptionVal === 'bw' || comparatorSelectedOptionVal === 'custom') {
				html =
					'<div class="date"><input class="dateRangeField form-control" data-calendar-type="range" name="' +
					this.getName() +
					'" data-date-format="' +
					this.getDateFormat() +
					'" type="text" ReadOnly="true" value="' +
					this.getValue() +
					'"></div>';
				let element = $(html),
					dateFieldUi = element.find('.dateRangeField');
				if (dateFieldUi.val().indexOf(',') !== -1) {
					let valueArray = this.getValue().split(','),
						startDateTime = valueArray[0],
						endDateTime = valueArray[1];
					if (startDateTime.indexOf(' ') !== -1) {
						let dateTime = startDateTime.split(' ');
						startDateTime = dateTime[0];
					}
					if (endDateTime.indexOf(' ') !== -1) {
						let dateTimeValue = endDateTime.split(' ');
						endDateTime = dateTimeValue[0];
					}
					dateFieldUi.val(startDateTime + ',' + endDateTime);
				} else {
					// while changing to between/custom from equal/notequal/... we'll only have one value
					let value = this.getValue().split(' '),
						startDate = value[0],
						endDate = value[0];
					if (startDate !== '' && endDate !== '') {
						dateFieldUi.val(startDate + ',' + endDate);
					}
				}
				return this.addValidationToElement(element);
			} else if (comparatorSelectedOptionVal in dateSpecificConditions) {
				let startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'],
					endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];
				if (
					comparatorSelectedOptionVal === 'today' ||
					comparatorSelectedOptionVal === 'tomorrow' ||
					comparatorSelectedOptionVal === 'yesterday'
				) {
					html = '<input name="' + this.getName() + '" type="text" ReadOnly="true" value="' + startValue + '">';
				} else {
					html =
						'<input name="' +
						this.getName() +
						'" type="text" ReadOnly="true" value="' +
						startValue +
						',' +
						endValue +
						'">';
				}
				return $(html);
			} else {
				let fieldUi = this._super(),
					dateTimeFieldValue = fieldUi.find('.dateField').val(),
					dateValue = dateTimeFieldValue.split(' ');
				if (dateValue[1] === '00:00:00') {
					dateTimeFieldValue = dateValue[0];
				} else if (
					comparatorSelectedOptionVal === 'e' ||
					comparatorSelectedOptionVal === 'n' ||
					comparatorSelectedOptionVal === 'b' ||
					comparatorSelectedOptionVal === 'a'
				) {
					let dateTimeArray = dateTimeFieldValue.split(' ');
					dateTimeFieldValue = dateTimeArray[0];
				}
				fieldUi.find('.dateField').val(dateTimeFieldValue);
				return fieldUi;
			}
		}
	}
);

AdvanceFilter_Date_Field_Js('AdvanceFilter_Datetime_Field_Js', {}, {});
