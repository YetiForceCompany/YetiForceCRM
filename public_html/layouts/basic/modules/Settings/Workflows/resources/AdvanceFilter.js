/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_AdvanceFilter_Js(
	'Workflows_AdvanceFilter_Js',
	{},
	{
		validationSupportedFieldConditionMap: {
			email: ['e', 'n'],
			date: ['is'],
			datetime: ['is']
		},
		//Hols field type for which there is validations always needed
		allConditionValidationNeededFieldList: ['double', 'integer'],

		// comparators which do not have any field Specific UI.
		comparatorsWithNoValueBoxMap: [
			'is record open',
			'is record closed',
			'has changed',
			'not has changed',
			'is empty',
			'is not empty',
			'is Watching Record',
			'is Not Watching Record',
			'not created by owner'
		],

		getFieldSpecificType: function (fieldSelected) {
			var fieldInfo = fieldSelected.data('fieldinfo');
			var type = fieldInfo.type;
			return type;
		},

		getModuleName: function () {
			return app.getModuleName();
		},

		/**
		 * Function to add new condition row
		 * @params : condtionGroupElement - group where condtion need to be added
		 * @return : current instance
		 */
		addNewCondition: function (conditionGroupElement) {
			var basicElement = jQuery('.basic', conditionGroupElement);
			var newRowElement = basicElement.find('.js-conditions-row').clone(true, true);
			jQuery('select', newRowElement).addClass('select2');
			var conditionList = jQuery('.conditionList', conditionGroupElement);
			conditionList.append(newRowElement);
			//change in to select elements
			App.Fields.Picklist.changeSelectElementView(newRowElement);
			newRowElement
				.find('[name="columnname"]')
				.find('optgroup:first option:first')
				.attr('selected', 'selected')
				.trigger('change');
			return this;
		},

		/**
		 * Function to load condition list for the selected field
		 * (overrrided to remove "has changed" condition for related record fields in workflows)
		 * @params : fieldSelect - select element which will represents field list
		 * @return : select element which will represent the condition element
		 */
		loadConditions: function (fieldSelect) {
			let row = fieldSelect.closest('div.js-conditions-row');
			let conditionSelectElement = row.find('select[name="comparator"]');
			let conditionSelected = conditionSelectElement.val();
			let fieldSelected = fieldSelect.find('option:selected');
			let fieldLabel = fieldSelected.val();
			let match = fieldLabel.match(/\((\w+)\) (\w+)/);
			let fieldSpecificType = this.getFieldSpecificType(fieldSelected);
			let conditionList = this.getConditionListFromType(fieldSpecificType);
			let fieldInfo = fieldSelected.data('fieldinfo');
			//for none in field name
			if (typeof conditionList === 'undefined') {
				conditionList = {};
				conditionList['none'] = 'None';
			}

			let options = '';
			for (let key in conditionList) {
				//IE Browser consider the prototype properties also, it should consider has own properties only.
				if (conditionList.hasOwnProperty(key)) {
					let conditionValue = conditionList[key];
					let conditionLabel = this.getConditionLabel(conditionValue);
					if (
						fieldInfo.type === 'picklist' &&
						jQuery.inArray(conditionValue, ['is record open', 'is record closed']) !== -1 &&
						'undefined' !== typeof fieldInfo.field_params &&
						'undefined' !== typeof fieldInfo.field_params.isProcessStatusField &&
						!fieldInfo.field_params.isProcessStatusField
					) {
						continue;
					}
					if (match != null) {
						if (conditionValue != 'has changed') {
							options += '<option value="' + conditionValue + '"';
							if (conditionValue == conditionSelected) {
								options += ' selected="selected" ';
							}
							options += '>' + conditionLabel + '</option>';
						}
					} else {
						options += '<option value="' + conditionValue + '"';
						if (conditionValue == conditionSelected) {
							options += ' selected="selected" ';
						}
						options += '>' + conditionLabel + '</option>';
					}
				}
			}
			conditionSelectElement.empty().html(options).trigger('change');
			return conditionSelectElement;
		},

		/**
		 * Function to retrieve the values of the filter
		 * @return : object
		 */
		getValues: function () {
			const thisInstance = this;
			let fieldList = new Array('columnname', 'comparator', 'value', 'valuetype', 'column_condition'),
				values = {},
				columnIndex = 0,
				conditionGroups = jQuery('.conditionGroup', this.getFilterContainer());
			conditionGroups.each(function (index, domElement) {
				let groupElement = jQuery(domElement),
					conditions = jQuery('.conditionList .js-conditions-row', groupElement),
					iterationValues = {};
				if (conditions.length <= 0) {
					return true;
				}
				conditions.each(function (i, conditionDomElement) {
					let rowElement = $(conditionDomElement),
						fieldSelectElement = $('[name="columnname"]', rowElement),
						valueSelectElement = $('[data-value="value"]', rowElement);
					//To not send empty fields to server
					if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
						return true;
					}
					let fieldType = fieldSelectElement.find('option:selected').data('fieldinfo').type,
						rowValues = {},
						key,
						field;
					if ($.inArray(fieldType, ['picklist', 'multipicklist', 'multiReferenceValue']) > -1) {
						for (key in fieldList) {
							field = fieldList[key];
							if (field === 'value' && valueSelectElement.is('input')) {
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
							} else if (field === 'value' && valueSelectElement.is('select') && fieldType === 'picklist') {
								rowValues[field] = valueSelectElement.val();
							} else if (
								field === 'value' &&
								valueSelectElement.is('select') &&
								$.inArray(fieldType, ['multipicklist', 'multiReferenceValue', 'categoryMultipicklist']) > -1
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
								rowValues[field] = valueSelectElement.val();
							} else {
								rowValues[field] = $('[name="' + field + '"]', rowElement).val();
							}
						}
					}

					if (
						$('[name="valuetype"]', rowElement).val() === 'false' ||
						$('[name="valuetype"]', rowElement).length === 0
					) {
						rowValues['valuetype'] = 'rawtext';
					}

					if (index === 0) {
						rowValues['groupid'] = 0;
					} else {
						rowValues['groupid'] = 1;
					}

					if (rowElement.is(':last-child')) {
						rowValues['column_condition'] = '';
					}
					iterationValues[columnIndex] = rowValues;
					columnIndex++;
				});

				if (!$.isEmptyObject(iterationValues)) {
					values[index + 1] = {};
					values[index + 1]['columns'] = iterationValues;
				}
				if (groupElement.find('div.groupCondition').length > 0) {
					values[index + 1]['condition'] = groupElement.find('div.groupCondition [name="condition"]').val();
				}
			});
			return values;
		},

		/**
		 * Functiont to get the field specific ui for the selected field
		 * @prarms : fieldSelectElement - select element which will represents field list
		 * @return : jquery object which represents the ui for the field
		 */
		getFieldSpecificUi: function (fieldSelectElement) {
			var fieldSelected = fieldSelectElement.find('option:selected');
			var fieldInfo = fieldSelected.data('fieldinfo');
			if (jQuery.inArray(fieldInfo.comparatorElementVal, this.comparatorsWithNoValueBoxMap) != -1) {
				return jQuery('');
			} else {
				return this._super(fieldSelectElement);
			}
		}
	}
);

Vtiger_Field_Js(
	'Workflows_Field_Js',
	{},
	{
		getUiTypeSpecificHtml: function () {
			var uiTypeModel = this.getUiTypeModel();
			return uiTypeModel.getUi();
		},

		getModuleName: function () {
			var currentModule = app.getModuleName();
			return currentModule;
		},

		/**
		 * Funtion to get the ui for the field  - generally this will be extend by the child classes to
		 * give ui type specific ui
		 * return <String or Jquery> it can return either plain html or jquery object
		 */
		getUi: function () {
			var html =
				'<input type="text" class="getPopupUi form-control" name="' +
				this.getName() +
				'"  /><input type="hidden" name="valuetype" value="' +
				this.get('workflow_valuetype') +
				'" />';
			html = jQuery(html);
			html.filter('.getPopupUi').val(app.htmlDecode(this.getValue()));
			return this.addValidationToElement(html);
		}
	}
);

Vtiger_Date_Field_Js(
	'Workflows_Date_Field_Js',
	{},
	{
		/**
		 * Function to get the user date format
		 */
		getDateFormat: function () {
			return this.get('date-format');
		},

		/**
		 * Function to get the ui
		 * @return - input text field
		 */
		getUi: function () {
			let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
				dateSpecificConditions = this.get('dateSpecificConditions'),
				html,
				element;
			if (comparatorSelectedOptionVal.length > 0) {
				if (comparatorSelectedOptionVal === 'between' || comparatorSelectedOptionVal === 'custom') {
					html =
						'<div class="date"><input class="form-control dateRangeField" data-calendar-type="range" name="' +
						this.getName() +
						'" data-date-format="' +
						this.getDateFormat() +
						'" type="text" value="' +
						this.getValue() +
						'"></div>';
					element = jQuery(html);
					return this.addValidationToElement(element);
				} else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
					html =
						'<input name="' +
						this.getName() +
						'" type="text" value="' +
						this.getValue() +
						'" data-validation-engine="' +
						'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"' +
						' data-validator="[{"name":"PositiveNumber"}]">' +
						'<input type="hidden" name="valuetype" value="' +
						this.get('workflow_valuetype') +
						'" />';
					return jQuery(html);
				} else if (comparatorSelectedOptionVal in dateSpecificConditions) {
					let startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'],
						endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];
					html =
						'<input name="' +
						this.getName() +
						'"  type="text" ReadOnly="true" value="' +
						startValue +
						',' +
						endValue +
						'">';
					return jQuery(html);
				} else if (comparatorSelectedOptionVal === 'is today') {
					//show nothing
				} else {
					return this._super();
				}
			} else {
				html =
					'<input type="text" class="getPopupUi date form-control" name="' +
					this.getName() +
					'"  data-date-format="' +
					this.getDateFormat() +
					'"  value="' +
					this.getValue() +
					'" />' +
					'<input type="hidden" name="valuetype" value="' +
					this.get('workflow_valuetype') +
					'" />';
				element = jQuery(html);
				return this.addValidationToElement(element);
			}
		},

		_specialDateComparator: function (comp) {
			var specialComparators = [
				'less than days ago',
				'more than days ago',
				'in less than',
				'in more than',
				'days ago',
				'days later'
			];
			for (var index in specialComparators) {
				if (comp == specialComparators[index]) {
					return true;
				}
			}
			return false;
		}
	}
);

Vtiger_Date_Field_Js(
	'Workflows_Datetime_Field_Js',
	{},
	{
		/**
		 * Function to get the user date format
		 */
		getDateFormat: function () {
			return this.get('date-format');
		},

		/**
		 * Function to get the ui
		 * @return - input text field
		 */
		getUi: function () {
			let html, element;
			if (this._specialDateTimeComparator(this.get('comparatorElementVal'))) {
				html =
					'<input name="' +
					this.getName() +
					'" type="text" value="' +
					this.getValue() +
					'" data-validator="[{name:PositiveNumber}]"><input type="hidden" name="valuetype" value="' +
					this.get('workflow_valuetype') +
					'" />';
				element = $(html);
			} else {
				html =
					'<input type="text" class="getPopupUi date form-control" name="' +
					this.getName() +
					'"  data-date-format="' +
					this.getDateFormat() +
					'"  value="' +
					this.getValue() +
					'" />' +
					'<input type="hidden" name="valuetype" value="' +
					this.get('workflow_valuetype') +
					'" />';
				element = $(html);
			}
			return element;
		},

		_specialDateTimeComparator: function (comp) {
			var specialComparators = [
				'less than hours before',
				'less than hours later',
				'more than hours later',
				'more than hours before'
			];
			for (var index in specialComparators) {
				if (comp == specialComparators[index]) {
					return true;
				}
			}
			return false;
		}
	}
);

Vtiger_Currency_Field_Js(
	'Workflows_Currency_Field_Js',
	{},
	{
		getUi: function () {
			var html =
				'<input type="text" class="getPopupUi marginLeftZero form-control" name="' +
				this.getName() +
				'" value="' +
				this.getValue() +
				'"  />' +
				'<input type="hidden" name="valuetype" value="' +
				this.get('workflow_valuetype') +
				'" />';
			var element = jQuery(html);
			return this.addValidationToElement(element);
		}
	}
);

Vtiger_Time_Field_Js(
	'Workflows_Time_Field_Js',
	{},
	{
		/**
		 * Function to get the ui
		 * @return - input text field
		 */
		getUi: function () {
			var html =
				'<input type="text" class="getPopupUi time form-control" name="' +
				this.getName() +
				'"  value="' +
				this.getValue() +
				'" />' +
				'<input type="hidden" name="valuetype" value="' +
				this.get('workflow_valuetype') +
				'" />';
			var element = jQuery(html);
			return this.addValidationToElement(element);
		}
	}
);

Vtiger_Field_Js(
	'Vtiger_Percentage_Field_Js',
	{},
	{
		/**
		 * Function to get the ui
		 * @return - input percentage field
		 */
		getUi: function () {
			var html =
				'<input type="text" class="getPopupUi form-control" name="' +
				this.getName() +
				'" value="' +
				this.getValue() +
				'" />' +
				'<input type="hidden" name="valuetype" value="' +
				this.get('workflow_valuetype') +
				'" />';
			var element = jQuery(html);
			return this.addValidationToElement(element);
		}
	}
);

Vtiger_Field_Js(
	'Vtiger_Text_Field_Js',
	{},
	{
		/**
		 * Function to get the ui
		 * @return - input text field
		 */
		getUi: function () {
			var html =
				'<input type="text" class="getPopupUi form-control" name="' +
				this.getName() +
				'" value="' +
				this.getValue() +
				'" />' +
				'<input type="hidden" name="valuetype" value="' +
				this.get('workflow_valuetype') +
				'" />';
			var element = jQuery(html);
			return this.addValidationToElement(element);
		}
	}
);

Vtiger_Field_Js(
	'Vtiger_Boolean_Field_Js',
	{},
	{
		/**
		 * Function to get the ui
		 * @return - input text field
		 */
		getUi: function () {
			var html =
				'<input type="text" class="getPopupUi form-control boolean" name="' +
				this.getName() +
				'" value="' +
				this.getValue() +
				'" />' +
				'<input type="hidden" name="valuetype" value="' +
				this.get('workflow_valuetype') +
				'" />';
			var element = jQuery(html);
			return this.addValidationToElement(element);
		}
	}
);

Vtiger_Owner_Field_Js(
	'Workflows_Owner_Field_Js',
	{},
	{
		getUi: function () {
			var html = '<select class="select2" data-value="value" name="' + this.getName() + '">';
			var pickListValues = this.getPickListValues();
			var selectedOption = this.getValue();
			for (var optGroup in pickListValues) {
				html += '<optgroup label="' + optGroup + '">';
				var optionGroupValues = pickListValues[optGroup];
				for (var option in optionGroupValues) {
					html += '<option value="' + option + '" ';
					if (option == selectedOption) {
						html += ' selected ';
					}
					html += '>' + optionGroupValues[option] + '</option>';
				}
				html += '</optgroup>';
			}

			html += '</select>';
			var selectContainer = jQuery(html);
			this.addValidationToElement(selectContainer);
			return selectContainer;
		}
	}
);
Workflows_Owner_Field_Js('Workflows_Sharedowner_Field_Js', {}, {});
Vtiger_Picklist_Field_Js('Workflows_Picklist_Field_Js', {}, {});
AdvanceFilter_Categorymultipicklist_Field_Js('Workflows_Categorymultipicklist_Field_Js', {}, {});
Workflows_Owner_Field_Js('Workflows_Usercreator_Field_Js', {}, {});
Vtiger_Picklist_Field_Js('Workflows_Country_Field_Js', {}, {});
