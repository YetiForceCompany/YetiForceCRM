/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_AdvanceFilter_Js('Vtiger_AdvanceFilterEx_Js', {}, {
	validationSupportedFieldConditionMap: {
		'email': ['e', 'n'],
		'date': ['is'],
		'datetime': ['is']
	},
	//Hols field type for which there is validations always needed
	allConditionValidationNeededFieldList: ['double', 'integer'],
	// comparators which do not have any field Specific UI.
	comparatorsWithNoValueBoxMap: ['has changed', 'is empty', 'is not empty', 'is added'],
	init: function (container) {
		if (typeof container == 'undefined') {
			container = jQuery('#advanceFilterContainer');
		}

		if (container.is('#advanceFilterContainer')) {
			this.setFilterContainer(container);
		} else {
			this.setFilterContainer(jQuery('#advanceFilterContainer', container));
		}
		this.initialize();
	},
	getFieldSpecificType: function (fieldSelected) {
		var fieldInfo = fieldSelected.data('fieldinfo');
		var type = fieldInfo.type;
		return type;
	},
	getModuleName: function () {
		return 'AdvanceFilterEx';
	},
	/**
	 * Function to add new condition row
	 * @params : condtionGroupElement - group where condtion need to be added
	 * @return : current instance
	 */
	addNewCondition: function (conditionGroupElement) {
		var basicElement = jQuery('.basic', conditionGroupElement);
		var newRowElement = basicElement.find('.conditionRow').clone(true, true);
		jQuery('select', newRowElement).addClass('chzn-select');
		var conditionList = jQuery('.conditionList', conditionGroupElement);
		conditionList.append(newRowElement);

		//change in to chosen elements
		app.changeSelectElementView(newRowElement);
		newRowElement.find('[name="columnname"]').find('optgroup:first option:first').attr('selected', 'selected').trigger('chosen:updated').trigger('change');
		return this;
	},
	/**
	 * Function to load condition list for the selected field
	 * (overrrided to remove "has changed" condition for related record fields in workflows)
	 * @params : fieldSelect - select element which will represents field list
	 * @return : select element which will represent the condition element
	 */
	loadConditions: function (fieldSelect) {
		var row = fieldSelect.closest('div.conditionRow');
		var conditionSelectElement = row.find('select[name="comparator"]');
		var conditionSelected = conditionSelectElement.val();
		var fieldSelected = fieldSelect.find('option:selected');
		var fieldLabel = fieldSelected.val();
		var match = fieldLabel.match(/\((\w+)\) (\w+)/);
		var fieldSpecificType = this.getFieldSpecificType(fieldSelected);
		var conditionList = this.getConditionListFromType(fieldSpecificType);
		//for none in field name
		if (typeof conditionList == 'undefined') {
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
		conditionSelectElement.empty().html(options).trigger("chosen:updated");
		return conditionSelectElement;
	},
	/**
	 * Function to retrieve the values of the filter
	 * @return : object
	 */
	getValues: function () {
		var thisInstance = this;
		var filterContainer = this.getFilterContainer();

		var fieldList = new Array('columnname', 'comparator', 'value', 'valuetype', 'column_condition');

		var values = {};
		var columnIndex = 0;
		var conditionGroups = jQuery('.conditionGroup', filterContainer);
		conditionGroups.each(function (index, domElement) {
			var groupElement = jQuery(domElement);

			var conditions = jQuery('.conditionList .conditionRow', groupElement);
			if (conditions.length <= 0) {
				return true;
			}

			var iterationValues = {};
			conditions.each(function (i, conditionDomElement) {
				var rowElement = jQuery(conditionDomElement);
				var fieldSelectElement = jQuery('[name="columnname"]', rowElement);
				var valueSelectElement = jQuery('[data-value="value"]', rowElement);
				//To not send empty fields to server
				if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
					return true;
				}
				var fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo');
				var fieldType = fieldDataInfo.type;
				var rowValues = {};
				if ($.inArray(fieldType, ['picklist', 'multipicklist', 'multiReferenceValue']) > -1) {
					for (var key in fieldList) {
						var field = fieldList[key];
						if (field == 'value' && valueSelectElement.is('input')) {
							var commaSeperatedValues = valueSelectElement.val();
							var pickListValues = valueSelectElement.data('picklistvalues');
							var valuesArr = commaSeperatedValues.split(',');
							var newvaluesArr = [];
							for (i = 0; i < valuesArr.length; i++) {
								if (typeof pickListValues[valuesArr[i]] != 'undefined') {
									newvaluesArr.push(pickListValues[valuesArr[i]]);
								} else {
									newvaluesArr.push(valuesArr[i]);
								}
							}
							var reconstructedCommaSeperatedValues = newvaluesArr.join(',');
							rowValues[field] = reconstructedCommaSeperatedValues;
						} else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'picklist') {
							rowValues[field] = valueSelectElement.val();
						} else if (field == 'value' && valueSelectElement.is('select') && $.inArray(fieldType, ['multipicklist', 'multiReferenceValue']) > -1) {
							var value = valueSelectElement.val();
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
					for (var key in fieldList) {
						var field = fieldList[key];
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
	},
	getPopUp: function (container) {
		var thisInstance = this;
		if (typeof container == 'undefined') {
			container = thisInstance.getFilterContainer();
		}
		container.on('click', '.getPopupUi', function (e) {
			var fieldValueElement = jQuery(e.currentTarget);
			var fieldValue = fieldValueElement.val();
			var fieldUiHolder = fieldValueElement.closest('.fieldUiHolder');
			var valueType = fieldUiHolder.find('[name="valuetype"]').val();
			if (valueType == '') {
				valueType = 'rawtext';
			}
			var conditionsContainer = fieldValueElement.closest('.conditionsContainer');
			var conditionRow = fieldValueElement.closest('.conditionRow');

			var clonedPopupUi = conditionsContainer.find('.popupUi').clone(true, true).removeClass('popupUi').addClass('clonedPopupUi');
			clonedPopupUi.find('select').addClass('chzn-select');
			clonedPopupUi.find('.fieldValue').val(fieldValue);
			if (fieldValueElement.hasClass('date')) {
				clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
				var dataFormat = fieldValueElement.data('date-format');
				if (valueType == 'rawtext') {
					var value = fieldValueElement.val();
				} else {
					value = '';
				}
				var clonedDateElement = '<input type="text" class="dateField fieldValue col-md-4" value="' + value + '" data-date-format="' + dataFormat + '" data-input="true" >';
				clonedPopupUi.find('.fieldValueContainer').prepend(clonedDateElement);
			} else if (fieldValueElement.hasClass('time')) {
				clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
				if (valueType == 'rawtext') {
					var value = fieldValueElement.val();
				} else {
					value = '';
				}
				var clonedTimeElement = '<input type="text" class="timepicker-default fieldValue col-md-4 form-control" value="' + value + '" data-input="true" >';
				clonedPopupUi.find('.fieldValueContainer').prepend(clonedTimeElement);
			} else if (fieldValueElement.hasClass('boolean')) {
				clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
				if (valueType == 'rawtext') {
					var value = fieldValueElement.val();
				} else {
					value = '';
				}
				var clonedBooleanElement = '<input type="checkbox" class="fieldValue col-md-4" value="' + value + '" data-input="true" >';
				clonedPopupUi.find('.fieldValueContainer').prepend(clonedBooleanElement);

				var fieldValue = clonedPopupUi.find('.fieldValueContainer input').val();
				if (value == 'true:boolean' || value == '') {
					clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
				} else {
					clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
				}
			}
			var callBackFunction = function (data) {
				data.find('.clonedPopupUi').removeClass('hide');
				var moduleNameElement = conditionRow.find('[name="modulename"]');
				if (moduleNameElement.length > 0) {
					var moduleName = moduleNameElement.val();
					data.find('.useFieldElement').addClass('hide');
					data.find('[name="' + moduleName + '"]').removeClass('hide');
				}
				app.changeSelectElementView(data);
				app.registerEventForDatePickerFields(data);
				app.registerEventForTimeFields(data);
				thisInstance.postShowModalAction(data, valueType);
				thisInstance.registerChangeFieldEvent(data);
				thisInstance.registerSelectOptionEvent(data);
				thisInstance.registerPopUpSaveEvent(data, fieldUiHolder);
				thisInstance.registerRemoveModalEvent(data);
				data.find('.fieldValue').filter(':visible').trigger('focus');
				data.find('[data-close-modal="modal"]').off('click').on('click', function () {
					jQuery(this).closest('.modal').removeClass('in').css('display', 'none');
				});
			};
			conditionsContainer.find('.clonedPopUp').html(clonedPopupUi);	
			jQuery('.clonedPopupUi').on('shown.bs.modal', function () {
				if (typeof callBackFunction == 'function') {
					callBackFunction(jQuery('.clonedPopupUi', conditionsContainer));
				}
			});
			jQuery('.clonedPopUp', conditionsContainer).find('.clonedPopupUi').modal();
		});
	},
	postShowModalAction: function (data, valueType) {
		if (valueType == 'fieldname') {
			jQuery('.useFieldContainer', data).removeClass('hide');
			jQuery('.textType', data).val(valueType).trigger('chosen:updated');
		} else if (valueType == 'expression') {
			jQuery('.useFieldContainer', data).removeClass('hide');
			jQuery('.useFunctionContainer', data).removeClass('hide');
			jQuery('.textType', data).val(valueType).trigger('chosen:updated');
		}
		jQuery('#' + valueType + '_help', data).removeClass('hide');
		var uiType = jQuery('.textType', data).find('option:selected').data('ui');
		jQuery('.fieldValue', data).hide();
		jQuery('[data-' + uiType + ']', data).show();
	},
	registerChangeFieldEvent: function (data) {
		jQuery('.textType', data).on('change', function (e) {
			var valueType = jQuery(e.currentTarget).val();
			var useFieldContainer = jQuery('.useFieldContainer', data);
			var useFunctionContainer = jQuery('.useFunctionContainer', data);
			var uiType = jQuery(e.currentTarget).find('option:selected').data('ui');
			jQuery('.fieldValue', data).hide();
			jQuery('[data-' + uiType + ']', data).show();
			if (valueType == 'fieldname') {
				useFieldContainer.removeClass('hide');
				useFunctionContainer.addClass('hide');
			} else if (valueType == 'expression') {
				useFieldContainer.removeClass('hide');
				useFunctionContainer.removeClass('hide');
			} else {
				useFieldContainer.addClass('hide');
				useFunctionContainer.addClass('hide');
			}
			jQuery('.helpmessagebox', data).addClass('hide');
			jQuery('#' + valueType + '_help', data).removeClass('hide');
			data.find('.fieldValue').val('');
		});
	},
	registerSelectOptionEvent: function (data) {
		jQuery('.useField,.useFunction', data).on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var newValue = currentElement.val();
			var oldValue = data.find('.fieldValue').filter(':visible').val();
			if (currentElement.hasClass('useField')) {
				if (oldValue != '') {
					var concatenatedValue = oldValue + ' ' + newValue;
				} else {
					concatenatedValue = newValue;
				}
			} else {
				concatenatedValue = oldValue + newValue;
			}
			data.find('.fieldValue').val(concatenatedValue);
			currentElement.val('').trigger('chosen:updated');
		});
	},
	registerPopUpSaveEvent: function (data, fieldUiHolder) {
		jQuery('[name="saveButton"]', data).on('click', function (e) {
			var valueType = jQuery('.textType', data).val();

			fieldUiHolder.find('[name="valuetype"]').val(valueType);
			var fieldValueElement = fieldUiHolder.find('.getPopupUi');
			if (valueType != 'rawtext') {
				fieldValueElement.removeAttr('data-validation-engine');
				fieldValueElement.removeClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
			} else {
				fieldValueElement.addClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
				fieldValueElement.attr('data-validation-engine', 'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
			}
			var fieldType = data.find('.fieldValue').filter(':visible').attr('type');
			var fieldValue = data.find('.fieldValue').filter(':visible').val();
			//For checkbox field type, handling fieldValue
			if (fieldType == 'checkbox') {
				if (data.find('.fieldValue').filter(':visible').is(':checked')) {
					fieldValue = 'true:boolean';
				} else {
					fieldValue = 'false:boolean';
				}
			}
			fieldValueElement.val(fieldValue);
			data.modal('hide');
			fieldValueElement.validationEngine('hide');
		});
	},
	registerRemoveModalEvent: function (data) {
		data.on('click', '.closeModal', function (e) {
			data.modal('hide');
		});
	},
	/**
	 * Function which will regiter all events for this page
	 */
	registerEvents: function () {
		this._super();
		this.getPopUp();
	}
});

Vtiger_Field_Js('AdvanceFilterEx_Field_Js', {}, {
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
		var html = '<input type="text" class="getPopupUi form-control" name="' + this.getName() + '"  /><input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		html = jQuery(html);
		html.filter('.getPopupUi').val(app.htmlDecode(this.getValue()));
		return this.addValidationToElement(html);
	}
});

Vtiger_Date_Field_Js('AdvanceFilterEx_Date_Field_Js', {}, {
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
		var comparatorSelectedOptionVal = this.get('comparatorElementVal');
		var dateSpecificConditions = this.get('dateSpecificConditions');
		if (comparatorSelectedOptionVal.length > 0) {
			if (comparatorSelectedOptionVal == 'between' || comparatorSelectedOptionVal == 'custom') {
				var html = '<div class="date"><input class="dateField" data-calendar-type="range" name="' + this.getName() + '" data-date-format="' + this.getDateFormat() + '" type="text" ReadOnly="true" value="' + this.getValue() + '"></div>';
				var element = jQuery(html);
				return this.addValidationToElement(element);
			} else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
				var html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator="[{"name":"PositiveNumber"}]">\n\
							<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
				return jQuery(html);
			} else if (comparatorSelectedOptionVal in dateSpecificConditions) {
				var startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'];
				var endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];
				var html = '<input name="' + this.getName() + '"  type="text" ReadOnly="true" value="' + startValue + ',' + endValue + '">';
				return jQuery(html);
			} else if (comparatorSelectedOptionVal == 'is today') {
				//show nothing
			} else {
				return this._super();
			}
		} else {
			var html = '<input type="text" class="getPopupUi date form-control" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
					'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
			var element = jQuery(html);
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

Vtiger_Date_Field_Js('AdvanceFilterEx_Datetime_Field_Js', {}, {
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
		var comparatorSelectedOptionVal = this.get('comparatorElementVal');
		if (this._specialDateTimeComparator(comparatorSelectedOptionVal)) {
			var html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validator="[{name:PositiveNumber}]"><input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
			var element = jQuery(html);
		} else {
			var html = '<input type="text" class="getPopupUi date form-control" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
					'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
			var element = jQuery(html);
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

Vtiger_Currency_Field_Js('AdvanceFilterEx_Currency_Field_Js', {}, {
	getUi: function () {
		var html = '<input type="text" class="getPopupUi marginLeftZero form-control" name="' + this.getName() + '" value="' + this.getValue() + '"  />' +
				'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}

});

Vtiger_Time_Field_Js('AdvanceFilterEx_Time_Field_Js', {}, {
	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		var html = '<input type="text" class="getPopupUi time form-control" name="' + this.getName() + '"  value="' + this.getValue() + '" />' +
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
		var html = '<input type="text" class="getPopupUi form-control" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
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
		var html = '<input type="text" class="getPopupUi form-control" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
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
		var html = '<input type="text" class="getPopupUi form-control boolean" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
				'<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Owner_Field_Js('AdvanceFilterEx_Owner_Field_Js', {}, {
	getUi: function () {
		var html = '<select class="chzn-select" name="' + this.getName() + '">';
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
});

Vtiger_Picklist_Field_Js('AdvanceFilterEx_Picklist_Field_Js', {}, {
});
