/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

Vtiger_Edit_Js("Users_Edit_Js", {
	/**
	 * Function to register change event for currency separator
	 */
	registerChangeEventForCurrencySeparator: function () {
		var form = jQuery('form');
		jQuery('[name="currency_decimal_separator"]', form).on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var selectedValue = element.val();
			var groupingSeparatorValue = jQuery('[name="currency_grouping_separator"]', form).data('selectedValue');
			if (groupingSeparatorValue == selectedValue) {
				var message = app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME');
				var params = {
					text: message,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				var previousSelectedValue = element.data('selectedValue');
				element.find('option').removeAttr('selected');
				element.find('option[value="' + previousSelectedValue + '"]').attr('selected', 'selected');
				element.trigger('change');
			} else {
				element.data('selectedValue', selectedValue);
			}
		});
		jQuery('[name="currency_grouping_separator"]', form).on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var selectedValue = element.val();
			var decimalSeparatorValue = jQuery('[name="currency_decimal_separator"]', form).data('selectedValue');
			if (decimalSeparatorValue == selectedValue) {
				var message = app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME');
				var params = {
					text: message,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				var previousSelectedValue = element.data('selectedValue');
				element.find('option').removeAttr('selected');
				element.find('option[value="' + previousSelectedValue + '"]').attr('selected', 'selected');
				element.trigger('change');
			} else {
				element.data('selectedValue', selectedValue);
			}
		});
	}

}, {
	duplicateCheckCache: {},
	userExistCheckCache: {},
	passCheckCache: {},
	//Hold the conditions for a hour format
	hourFormatConditionMapping: false,
	registerHourFormatChangeEvent: function () {

	},
	getHourValues: function (list, currentValue) {
		var options = '';
		for (var key in list) {
			//IE Browser consider the prototype properties also, it should consider has own properties only.
			if (list.hasOwnProperty(key)) {
				var conditionValue = list[key];
				options += '<option value="' + key + '"';
				if (key == currentValue) {
					options += ' selected="selected" ';
				}
				options += '>' + conditionValue + '</option>';
			}
		}
		return options;
	},
	changeStartHourValuesEvent: function (form) {
		var thisInstance = this;
		form.on('change', 'select[name="hour_format"]', function (e) {
			var hourFormatVal = jQuery(e.currentTarget).val();
			var startHourElement = jQuery('select[name="start_hour"]', form);
			var endHourElement = jQuery('select[name="end_hour"]', form);
			var conditionStartSelected = startHourElement.val();
			var conditionEndSelected = endHourElement.val();
			if (typeof thisInstance.hourFormatConditionMapping === "undefined") {
				return false;
			}
			var list = thisInstance.hourFormatConditionMapping['hour_format'][hourFormatVal]['start_hour'];
			startHourElement.html(thisInstance.getHourValues(list, conditionStartSelected)).trigger('change');
			endHourElement.html(thisInstance.getHourValues(list, conditionEndSelected)).trigger('change');
		});
	},
	triggerHourFormatChangeEvent(form) {
		this.hourFormatConditionMapping = $('input[name="timeFormatOptions"]', form).data('value');
		this.changeStartHourValuesEvent(form);
		$('select[name="hour_format"]', form).trigger('change');
	},
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent: function (form) {
		var thisInstance = this;
		form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			var record = jQuery('input[name="record"]').val();
			var progressIndicatorElement = jQuery.progressIndicator({
				'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			if (record == '' && jQuery('input[name="user_password"]').val() != jQuery('input[name="confirm_password"]').val()) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_REENTER_PASSWORDS'));
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				e.preventDefault();
			}
			thisInstance.verifyFormData()
				.done(function (data) {
					if (data.result.message) {
						Vtiger_Helper_Js.showPnotify(data.result.message);
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
						e.preventDefault();
					}
				})
				.fail(function (data, error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					e.preventDefault();
				});
		});
	},
	verifyFormData: function () {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		thisInstance.verifyData().done(function (data) {
			aDeferred.resolve(data);
		}, function (data, error) {
			aDeferred.reject();
		});
		return aDeferred.promise();
	},
	verifyData: function () {
		var aDeferred = jQuery.Deferred();
		AppConnector.request({
			async: false,
			data: {
				module: 'Users',
				action: 'VerifyData',
				email: jQuery('[name="email1"]').val(),
				userName: jQuery('input[name="user_name"]').val(),
				record: jQuery('input[name="record"]').val(),
				password: jQuery('input[name="user_password"]').val(),

			}
		}).done(function (data) {
			if (data.result) {
				aDeferred.resolve(data);
			} else {
				aDeferred.reject(data);
			}
		});
		return aDeferred.promise();
	},
	registerEvents: function () {
		this._super();
		var form = this.getForm();
		this.triggerHourFormatChangeEvent(form);
		this.registerRecordPreSaveEvent(form);
		Users_Edit_Js.registerChangeEventForCurrencySeparator();
	}
});
