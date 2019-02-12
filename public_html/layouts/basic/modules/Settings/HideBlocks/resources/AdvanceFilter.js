/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_AdvanceFilter_Js('HideBlocks_AdvanceFilter_Js', {}, {

	validationSupportedFieldConditionMap: {
		'email': ['e', 'n'],
		'date': ['is'],
		'datetime': ['is']
	},
	//Hols field type for which there is validations always needed
	allConditionValidationNeededFieldList: ['double', 'integer'],

	// comparators which do not have any field Specific UI.
	comparatorsWithNoValueBoxMap: ['has changed', 'is empty', 'is not empty', 'is added'],

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
		newRowElement.find('[name="columnname"]').find('optgroup:first option:first').attr('selected', 'selected').trigger('change');
		return this;
	},

	/**
	 * Function to load condition list for the selected field
	 * (overrrided to remove "has changed" condition for related record fields in workflows)
	 * @params : fieldSelect - select element which will represents field list
	 * @return : select element which will represent the condition element
	 */
	loadConditions: function (fieldSelect) {
		var row = fieldSelect.closest('div.js-conditions-row');
		var conditionSelectElement = row.find('select[name="comparator"]');
		var conditionSelected = row.find('[name="comparatorValue"]').val();
		var fieldSelected = fieldSelect.find('option:selected');
		var fieldLabel = fieldSelected.val();
		var match = fieldLabel.match(/\((\w+)\) (\w+)/);
		var fieldSpecificType = this.getFieldSpecificType(fieldSelected)
		var conditionList = this.getConditionListFromType(fieldSpecificType);
		//for none in field name
		if (typeof conditionList === "undefined") {
			conditionList = {};
			conditionList['none'] = 'None';
		}

		var options = '';
		for (var key in conditionList) {
			//IE Browser consider the prototype properties also, it should consider has own properties only.
			if (conditionList.hasOwnProperty(key)) {
				var conditionValue = conditionList[key];
				var conditionLabel = this.getConditionLabel(conditionValue);
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
		conditionSelectElement.empty().html(options).trigger("change");
		return conditionSelectElement;
	},

	/**
	 * Function to retrieve the values of the filter
	 * @return : object
	 */
	getValues: function () {
		const thisInstance = this,
			filterContainer = this.getFilterContainer(),
			fieldList = new Array('columnname', 'comparator', 'value', 'valuetype', 'column_condition'),
			values = {};
		let columnIndex = 0,
			conditionGroups = jQuery('.conditionGroup', filterContainer);
		conditionGroups.each(function (index, domElement) {
			let groupElement = jQuery(domElement),
				conditions = jQuery('.conditionList .js-conditions-row', groupElement);
			if (conditions.length <= 0) {
				return true;
			}

			let iterationValues = {};
			conditions.each(function (i, conditionDomElement) {
				let rowElement = jQuery(conditionDomElement),
					fieldSelectElement = jQuery('[name="columnname"]', rowElement),
					valueSelectElement = jQuery('[data-value="value"]', rowElement);
				//To not send empty fields to server
				if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
					return true;
				}
				let fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo'),
					fieldType = fieldDataInfo.type,
					rowValues = {},
					key,
					field;
				if (fieldType == 'picklist' || fieldType == 'multipicklist') {
					for (key in fieldList) {
						field = fieldList[key];
						if (field == 'value' && valueSelectElement.is('input')) {
							let commaSeperatedValues = valueSelectElement.val(),
								pickListValues = valueSelectElement.data('picklistvalues'),
								valuesArr = commaSeperatedValues.split(','),
								newValuesArr = [];
							for (i = 0; i < valuesArr.length; i++) {
								if (typeof pickListValues[valuesArr[i]] !== "undefined") {
									newValuesArr.push(pickListValues[valuesArr[i]]);
								} else {
									newValuesArr.push(valuesArr[i]);
								}
							}
							let reconstructedCommaSeperatedValues = newValuesArr.join(',');
							rowValues[field] = reconstructedCommaSeperatedValues;
						} else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'picklist') {
							rowValues[field] = valueSelectElement.val();
						} else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'multipicklist') {
							let value = valueSelectElement.val();
							if (value == null) {
								rowValues[field] = value;
							} else {
								rowValues[field] = value.join(',');
							}
						} else {
							rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
						}
					}
				} else {
					for (key in fieldList) {
						field = fieldList[key];
						if (field == 'value') {
							rowValues[field] = valueSelectElement.val();
						} else {
							rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
						}
					}
				}

				if (jQuery('[name="valuetype"]', rowElement).val() == 'false' || (jQuery('[name="valuetype"]', rowElement).length == 0)) {
					rowValues['valuetype'] = 'rawtext';
				}

				if (index == '0') {
					rowValues['groupid'] = '0';
				} else {
					rowValues['groupid'] = '1';
				}

				if (rowElement.is(":last-child")) {
					rowValues['column_condition'] = '';
				}
				iterationValues[columnIndex] = rowValues;
				columnIndex++;
			});

			if (!jQuery.isEmptyObject(iterationValues)) {
				values[index + 1] = {};
				//values[index+1]['columns'] = {};
				values[index + 1]['columns'] = iterationValues;
			}
			if (groupElement.find('div.groupCondition').length > 0 && !jQuery.isEmptyObject(values[index + 1])) {
				values[index + 1]['condition'] = conditionGroups.find('div.groupCondition [name="condition"]').val();
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
});

Vtiger_Field_Js('Workflows_Field_Js', {}, {

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
		var html = '<input type="text" class="getPopupUi" name="' + this.getName() + '"  /><input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		html = jQuery(html);
		html.filter('.getPopupUi').val(app.htmlDecode(this.getValue()));
		return this.addValidationToElement(html);
	}
});

Vtiger_Date_Field_Js('Workflows_Date_Field_Js', {}, {

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
			if (comparatorSelectedOptionVal == 'between' || comparatorSelectedOptionVal == 'custom') {
				html = '<div class="date"><input class="dateRangeField form-control"' +
					'data-calendar-type="range" name="' + this.getName() +
					'" data-date-format="' + this.getDateFormat() +
					'" type="text" ReadOnly="true" value="' + this.getValue() + '"></div>';
				element = jQuery(html);
				return this.addValidationToElement(element);
			} else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
				html = '<input name="' + this.getName() + '" class="form-control" type="text" value="' +
					this.getValue() + '" data-validation-engine="' +
					'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"' +
					' data-validator="[{"name":"PositiveNumber"}]">' +
					'<input type="hidden" name="valuetype" value="' +
					this.get('workflow_valuetype') + '" />';
				return jQuery(html);
			} else if (comparatorSelectedOptionVal in dateSpecificConditions) {
				let startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'],
					endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];
				html = '<input name="' + this.getName() + '" class="form-control" type="text" ReadOnly="true" value="' + startValue + ',' + endValue + '">'
				return jQuery(html);
			} else if (comparatorSelectedOptionVal == 'is today') {
				//show nothing
			} else {
				return this._super();
			}
		} else {
			html = '<input type="text" class="getPopupUi form-control date" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
				'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />'
			element = jQuery(html);
			return this.addValidationToElement(element);
		}
	},

	_specialDateComparator: function (comp) {
		var specialComparators = ['less than days ago', 'more than days ago', 'in less than', 'in more than', 'days ago', 'days later'];
		for (var index in specialComparators) {
			if (comp == specialComparators[index]) {
				return true;
			}
		}
		return false;
	}
});

Vtiger_Date_Field_Js('Workflows_Datetime_Field_Js', {}, {
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
		let html,
			element;
		if (this._specialDateTimeComparator(this.get('comparatorElementVal'))) {
			html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validator="[{name:PositiveNumber}]"><input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
			element = jQuery(html);
		} else {
			html = '<input type="text" class="getPopupUi date" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
				'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />'
			element = jQuery(html);
		}
		return element;
	},

	_specialDateTimeComparator: function (comp) {
		var specialComparators = ['less than hours before', 'less than hours later', 'more than hours later', 'more than hours before'];
		for (var index in specialComparators) {
			if (comp == specialComparators[index]) {
				return true;
			}
		}
		return false;
	}

});

Vtiger_Currency_Field_Js('Workflows_Currency_Field_Js', {}, {

	getUi: function () {
		var html = '<input type="text" class="getPopupUi marginLeftZero" name="' + this.getName() + '" value="' + this.getValue() + '"  />' +
			'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}

});

Vtiger_Time_Field_Js('Workflows_Time_Field_Js', {}, {

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		var html = '<input type="text" class="getPopupUi time" name="' + this.getName() + '"  value="' + this.getValue() + '" />' +
			'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Percentage_Field_Js', {}, {

	/**
	 * Function to get the ui
	 * @return - input percentage field
	 */
	getUi: function () {
		var html = '<input type="text" class="getPopupUi" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
			'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Text_Field_Js', {}, {

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		var html = '<input type="text" class="getPopupUi" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
			'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Boolean_Field_Js', {}, {

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		var html = '<input type="text" class="getPopupUi boolean" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
			'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Owner_Field_Js('Workflows_Owner_Field_Js', {}, {

	getUi: function () {
		var html = '<select class="select2" name="' + this.getName() + '">';
		var pickListValues = this.getPickListValues();
		var selectedOption = this.getValue();
		for (var optGroup in pickListValues) {
			html += '<optgroup label="' + optGroup + '">'
			var optionGroupValues = pickListValues[optGroup];
			for (var option in optionGroupValues) {
				html += '<option value="' + option + '" ';
				if (option == selectedOption) {
					html += ' selected ';
				}
				html += '>' + optionGroupValues[option] + '</option>';
			}
			html += '</optgroup>'
		}

		html += '</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Picklist_Field_Js('Workflows_Picklist_Field_Js', {}, {

	getUi: function () {
		var selectedOption = app.htmlDecode(this.getValue());
		var pickListValues = this.getPickListValues();
		var tagsArray = [];
		jQuery.map(pickListValues, function (val, i) {
			tagsArray.push(val);
		});
		var pickListValuesArrayFlip = {};
		for (var key in pickListValues) {
			var pickListValue = pickListValues[key];
			pickListValuesArrayFlip[pickListValue] = key;
		}
		var html = '<input type="hidden" class="row select2" name="' + this.getName() + '">';
		var selectContainer = jQuery(html).val(pickListValues[selectedOption]);
		selectContainer.data('tags', tagsArray).data('picklistvalues', pickListValuesArrayFlip);
		this.addValidationToElement(selectContainer);
		App.Fields.Picklist.showSelect2ElementView(selectContainer, {
			placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
			closeOnSelect: true,
			maximumSelectionLength: 1
		});
		return selectContainer;
	}
});
