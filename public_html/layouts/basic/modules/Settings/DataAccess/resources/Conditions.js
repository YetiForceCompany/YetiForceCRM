/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
function DataAccessConditions() {
	this.getNextStep = function () {
		jQuery("#next_step").on('click', function (e) {
			var dsc = jQuery('[name="summary"]').val();
			if ("" == dsc) {
				e.preventDefault();
				var params = {
					title: app.vtranslate('JS_ERROR'),
					text: app.vtranslate('DES_REQUIRED'),
					animation: 'show'
				};

				Vtiger_Helper_Js.showPnotify(params);
			} else if (jQuery('[name="base_module"]').val() == 'All') {
				jQuery('[name="view"]').val('Step3');
			} else
				jQuery('[name="view"]').val('Step2');
		});
	};
	this.addCondiion = function () {
		var thisInstance = this;

		jQuery('button.add_condition').on('click', function () {
			var type = jQuery(this).data('type'),
					baseModule = jQuery('[name="base_module"]').val(),
					numCondition = jQuery('#condition_all select:last').data('num'),
					requestParams = {};

			requestParams.data = {module: 'DataAccess', parent: 'Settings', view: 'Condition', base_module: baseModule, num: numCondition}

			AppConnector.request(requestParams).then(function (data) {
				jQuery('#' + type).append(data);
				thisInstance.fieldHasChanged();
				thisInstance.comparatorHasChanged();
				thisInstance.fieldTypeHasChanged();
				app.changeSelectElementView(jQuery('#' + type));
			});
		});
	};
	this.submitEventRegister = function () {
		var thisInstance = this;

		jQuery('form[name="condition"]').on('submit', function () {

			jQuery('input[name="condition_all_json"]').val(thisInstance.toJson('condition_all'));
			jQuery('input[name="condition_option_json"]').val(thisInstance.toJson('condition_option'));
			var conditionAll = thisInstance.validateCondition('condition_all'),
					conditionOption = thisInstance.validateCondition('condition_option');

			return conditionAll && conditionOption;
		})
	};
	this.validateCondition = function (type) {
		var state = true,
				msgTab = [];
		var betweenValue = jQuery('input.dateField').data('calendar-type');

		jQuery('#' + type + ' .conditionRow').each(function () {
			var row = jQuery(this);
			var fieldInfo = jQuery(row).find('.comparator-select option:selected').data('info');
			var fieldType = fieldInfo.type;

			if (fieldType == 'email') {
				var emailInstance = new Vtiger_Email_Validator_Js();
				emailInstance.setElement(jQuery(row).find('[name="val"]'));
				var response = emailInstance.validate();
				if (response != true) {
					state = false;
					msgTab.push(fieldInfo.label + ' - ' + emailInstance.getError());
				}
			} else if (fieldType == 'integer') {
				var integerInstance = new Vtiger_Integer_Validator_Js();
				doubleInstance.setElement(jQuery(row).find('[name="val"]'));
				var response = doubleInstance.validate();
				if (response != true) {
					state = false;
					msgTab.push(fieldInfo.label + ' - ' + doubleInstance.getError());
				}
			} else if (fieldType == 'double') {
				var doubleInstance = new Vtiger_Double_Validator_Js();
				doubleInstance.setElement(jQuery(row).find('[name="val"]'));
				var response = doubleInstance.validate();
				if (response != true) {
					state = false;
					msgTab.push(fieldInfo.label + ' - ' + doubleInstance.getError());
				}
			} else if (fieldType == 'percentage') {
				var percentageInstance = new Vtiger_Percentage_Validator_Js();
				percentageInstance.setElement(jQuery(row).find('[name="val"]'));
				var response = percentageInstance.validate();
				if (response != true) {
					state = false;
					msgTab.push(fieldInfo.label + ' - ' + percentageInstance.getError());
				}
			} else if (fieldType == 'currency') {
				var currencyValidatorInstance = new Vtiger_Currency_Validator_Js();
				currencyValidatorInstance.setElement(jQuery(row).find('[name="val"]'));
				var response = currencyValidatorInstance.validate();
				if (response != true) {
					state = false;

					msgTab.push(fieldInfo.label + ' - ' + currencyValidatorInstance.getError());
				}
			} else if (fieldType == 'date' && (!betweenValue || betweenValue == 'undefined')) {
				var dateValidatorInstance = new Vtiger_Date_Validator_Js();
				dateValidatorInstance.setElement(jQuery(row).find('[name="val"]'));
				var response = dateValidatorInstance.validate();
				if (response != true) {
					state = false;
					msgTab.push(fieldInfo.label + ' - ' + dateValidatorInstance.getError());
				}
			} else if (fieldType == 'date' && betweenValue != 'undefined') {
				if (jQuery('input.dateField').val() == '') {
					state = false;
					msgTab.push(fieldInfo.label + ' - ' + app.vtranslate("JS_PLEASE_ENTER_VALID_DATE"));
				}
			}

		});

		if (!state) {

			var msg = '';

			for (var i = 0; i < msgTab.length; i++) {
				msg += msgTab[i] + '<br /><br />';
			}

			var params = {
				title: app.vtranslate('JS_ERROR'),
				text: msg,
				animation: 'show'
			};

			Vtiger_Helper_Js.showPnotify(params);
		}

		return state;
	};
	this.toJson = function (type) {
		var row = jQuery('#' + type + ' .conditionRow'),
				jsonTab = [],
				thisInstance = this;

		jQuery('#' + type + ' .conditionRow').each(function () {
			var tab = {};
			tab.field = jQuery(this).find('select.comparator-select').val();

			tab.name = jQuery(this).find('select[name="comparator"]').val();

			var fieledType = jQuery(this).find('select.comparator-select option:selected').data('info')['type'];

			if (fieledType == 'time') {
				if (jQuery(this).find('select.comparator-select option:selected').data('info')['time-format'] == '12') {
					tab.val = thisInstance.convertTimeFormat(jQuery(this).find('[name="val"]').val());
				} else {
					tab.val = jQuery(this).find('[name="val"]').val();
				}

				tab.type = fieledType;
			} else {
				tab.val = jQuery(this).find('[name="val"]').val();
				tab.type = fieledType;
			}
			jsonTab.push(tab);
		});

		return JSON.stringify(jsonTab);
	};
	this.filterTpl = function () {
		jQuery('#moduleFilter').on('change', function () {
			var val = jQuery(this).val(),
					requestParam = {};
			requestParam.data = {module: 'DataAccess', parent: 'Settings', view: 'ListDoc', base_module: val}
			AppConnector.request(requestParam).then(function (data) {
				jQuery('#list_doc').html(data)
			})

		});
	};
	this.docNamRequire = function () {
		jQuery('button:submit').on('click', function () {
			var docName = jQuery('input[name="doc_name"]');

			if (jQuery(docName).length && jQuery(docName).val() == '') {
				var params = {
					title: app.vtranslate('JS_ERROR'),
					text: app.vtranslate('DES_NAME_REQUIRED'),
					animation: 'show'
				};

				Vtiger_Helper_Js.showPnotify(params);
				return false;
			}
		})
	};
	this.fieldHasChanged = function () {
		jQuery('.comparator-select').on('change', function () {
			var comparator = jQuery(this).parents('.conditionRow').find('[name="comparator"]'),
					filedInfo = jQuery(this).find('option:selected').data('info'),
					conditionList = JSON.parse(jQuery('div#condition_list').text());
			var value = comparator.val();
			comparator.find('option').remove();
			jQuery.each(conditionList[filedInfo.type], function (i, item) {
				comparator.append(jQuery('<option>', {
					value: item,
					text: app.vtranslate(item)
				}));
			});
			comparator.val(value);
			comparator.trigger("chosen:updated");
		});
	};
	this.fieldTypeHasChanged = function () {
		var thisInstance = this;

		jQuery('.comparator-select').on('change', function () {
			var valInput = jQuery(this).parents('.conditionRow').find('[name="val"]'),
					fieldInfo = jQuery(this).find('option:selected').data('info');
			if (fieldInfo.type == 'picklist' || 'tree' == fieldInfo.type) {
				thisInstance.showPicklist(this);
			} else if (fieldInfo.type == 'boolean') {
				thisInstance.hideValElement(this);
			} else if (fieldInfo.type == 'date') {
				thisInstance.showDataInput(this);
			} else if (fieldInfo.type == 'multipicklist') {
				thisInstance.showMultiPicklist(this);
			} else if (fieldInfo.type == 'time') {
				thisInstance.showTime(this, fieldInfo);
			} else {
				thisInstance.showInput(this);
			}

		})
	};
	this.comparatorHasChanged = function () {
		var thisInstance = this;

		jQuery('[name="comparator"]').on('change', function () {
			var val = jQuery(this).val(),
					conditionToHideValElement = ['is not empty', 'is empty', 'is enabled', 'is disabled', 'is today', 'has changed'],
					conditionToShowInput = ['is', 'contains', "does not contain", "starts with", "ends with",
						"less than days ago", "more than days ago", "in less than", "in more than", "days ago",
						"days later", "before", "after", "is not"]

			if (jQuery.inArray(val, conditionToHideValElement) !== -1) {
				thisInstance.hideValElement(this);

			}

			if ('between' == val) {
				thisInstance.showBetweenDateInput(this);
			}

			if (jQuery.inArray(val, conditionToShowInput) !== -1) {

				var fieldInfo = jQuery(this).parents('.conditionRow').find('.comparator-select option:selected').data('info'),
						exceptions = ["less than days ago", "more than days ago", "in less than", "in more than", "days ago", "days later"];

				if (fieldInfo.type == 'date' && (jQuery.inArray(val, exceptions) === -1)) {
					thisInstance.showDataInput(this);
				} else {
					jQuery(this).closest('div').find('.comparator-select').trigger('change');
				}
			}
		})
	};
	this.showBetweenDateInput = function (element) {
		var valPlace = jQuery(element).parents('.conditionRow').find('.fieldUiHolder'),
				html = '<div class="date"><input class="dateField bw form-control" data-calendar-type="range" name="val" data-date-format="yyyy-mm-dd" type="text" readonly="true" placeholder="Click me" value="" data-value="value"></div>';

		valPlace.children().remove();
		valPlace.append(html);

		var valElement = jQuery(valPlace).find('div.date');

		var customParams = {
			calendars: 3,
			mode: 'range',
			className: 'rangeCalendar',
			onChange: function (formated) {
				valElement.find('.dateField').val(formated.join(','));
			}
		}

		app.registerEventForDatePickerFields(valElement, false, customParams);


	};
	this.showPicklist = function (element) {
		var valPlace = jQuery(element).parents('.conditionRow').find('.fieldUiHolder');
		var valElement = valPlace.find('[name="val"]');
		valPlace.children().remove();
		var select = jQuery('<select name="val" data-value="value" class="select2 form-control" ></select>').appendTo(valPlace);
		var fieldInfo = jQuery(element).find('option:selected').data('info');
		jQuery.each(fieldInfo.picklistvalues, function (i, item) {
			select.append(jQuery('<option>', {
				value: i,
				text: item
			}));
		});
		app.showSelect2ElementView(jQuery('select.select2'));

	};
	this.showMultiPicklist = function (element) {
		var valPlace = jQuery(element).parents('.conditionRow').find('.fieldUiHolder');
		valPlace.children().remove();
		var select = jQuery('<select name="val" multiple="multiple" data-value="value" class="select2 form-control" ></select>').appendTo(valPlace);
		var fieldInfo = jQuery(element).find('option:selected').data('info');

		jQuery.each(fieldInfo.picklistvalues, function (i, item) {
			select.append(jQuery('<option>', {
				value: i,
				text: item
			}));
		});

		app.showSelect2ElementView(jQuery('select.select2'));

	};
	this.showInput = function (element) {
		var valPlace = jQuery(element).parents('.conditionRow').find('.fieldUiHolder');

		if (valPlace.children().prop('tagName') != 'INPUT') {
			valPlace.children().remove();
			jQuery('<input type="text" name="val" class="form-control" data-value="value" />').appendTo(valPlace);
		} else {
			this.showValElement(valPlace.children());
		}
	};
	this.showDataInput = function (element) {
		var valPlace = jQuery(element).parents('.conditionRow').find('.fieldUiHolder');
		var valElement = valPlace.find('[name="val"]'),
				html = '<div class="input-group"><input class="col-md-9 dateField form-control" name="val" data-date-format="yyyy-mm-dd"><div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div></div>';

		if (!jQuery(valElement).hasClass('dateField') || jQuery(valElement).hasClass('bw')) {
			valPlace.children().remove();
			valPlace.append(html);
			app.registerEventForDatePickerFields(jQuery(valPlace).find('input[name="val"]'), true);
		}

	};
	this.showTime = function (element, info) {
		var valPlace = jQuery(element).parents('.conditionRow').find('.fieldUiHolder'),
				html = '<div class="input-group time"><input type="text" data-format="' + info['time-format'] + '" class="timepicker-default form-control ui-timepicker-input" name="val" autocomplete="off"><span class="input-group-addon cursorPointer"><i class="glyphicon glyphicon-time"></i></span></div>';

		valPlace.children().remove();
		valPlace.append(html);

		app.registerEventForClockPicker(jQuery(valPlace).find('[name="val"]'));
	};
	this.hideValElement = function (element) {
		jQuery(element).parents('.conditionRow').find('.fieldUiHolder').children().hide();
	};
	this.showValElement = function (element) {
		jQuery(element).parents('.conditionRow').find('.fieldUiHolder').children().show();
	};
	this.test = function () {
		jQuery(document).on('click', function () {
			var dateValidatorInstance = new Vtiger_Date_Validator_Js();
			dateValidatorInstance.setElement(jQuery('[name="val"]'));
			var response = dateValidatorInstance.validate();
		})
	};
	this.convertTimeFormat = function (time) {
		var hrs = Number(time.match(/^(\d+)/)[1]);
		var mnts = Number(time.match(/:(\d+)/)[1]);
		var format = time.match(/\s(.*)$/)[1];
		if (format == "PM" && hrs < 12)
			hrs = hrs + 12;
		if (format == "AM" && hrs == 12)
			hrs = hrs - 12;
		var hours = hrs.toString();
		var minutes = mnts.toString();
		if (hrs < 10)
			hours = "0" + hours;
		if (mnts < 10)
			minutes = "0" + minutes;
		return hours + ":" + minutes;
	};
	this.hideShowInput = function () {
		var thisInstance = this;

		jQuery('[name="comparator"]').each(function () {
			var val = jQuery(this).val(),
					conditionToHideValElement = ['is not empty', 'is empty', 'is enabled', 'is disabled', 'is today'],
					conditionToShowInput = ['is', 'contains', "does not contain", "starts with", "ends with",
						"less than days ago", "more than days ago", "in less than", "in more than", "days ago",
						"days later", "before", "after", "is not"]

			if (jQuery.inArray(val, conditionToHideValElement) !== -1) {
				thisInstance.hideValElement(this);

			}

			/*if ('between' == val) {
			 thisInstance.showBetweenDateInput(this);
			 }*/

			if (jQuery.inArray(val, conditionToShowInput) !== -1) {

				var fieldInfo = jQuery(this).parents('.conditionRow').find('.comparator-select option:selected').data('info'),
						exceptions = ["less than days ago", "more than days ago", "in less than", "in more than", "days ago", "days later"];

				if (fieldInfo.type == 'date' && (jQuery.inArray(val, exceptions) === -1)) {
					thisInstance.showDataInput(this);
				} else {

					if (fieldInfo.type == 'date') {
						thisInstance.showInput(this);
					}
				}
			}
		})
	};
	this.registerEvents = function () {
		this.hideShowInput();
		this.getNextStep();
		this.addCondiion();
		this.submitEventRegister();
		this.filterTpl();
		this.docNamRequire();
		this.fieldHasChanged();
		this.comparatorHasChanged();
		this.fieldTypeHasChanged();
		app.registerEventForDatePickerFields(jQuery('input.dateFieldNormal'), true);

		var customParams = {
			calendars: 3,
			mode: 'range',
			className: 'rangeCalendar',
			onChange: function (formated) {
				jQuery('input.bw').val(formated.join(','));
			}
		};
		app.registerEventForDatePickerFields(jQuery('input.bw'), false, customParams);
		app.registerEventForClockPicker();
	};
}

jQuery(document).ready(function () {
	var pt = new DataAccessConditions();
	pt.registerEvents();
});
