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

Vtiger_Edit_Js(
	'Users_Edit_Js',
	{
		/**
		 * Function to register change event for currency separator
		 */
		registerChangeEventForCurrencySeparator: function () {
			let form = jQuery('form');
			jQuery('[name="currency_decimal_separator"]', form).on('change', function (e) {
				let element = jQuery(e.currentTarget);
				let selectedValue = element.val();
				let groupingSeparatorValue = jQuery('[name="currency_grouping_separator"]', form).data('selectedValue');
				if (groupingSeparatorValue == selectedValue) {
					let message = app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME');
					let params = {
						text: message,
						type: 'error'
					};
					Vtiger_Helper_Js.showMessage(params);
					let previousSelectedValue = element.data('selectedValue');
					element.find('option').removeAttr('selected');
					element.find('option[value="' + previousSelectedValue + '"]').attr('selected', 'selected');
					element.trigger('change');
				} else {
					element.data('selectedValue', selectedValue);
				}
			});
			jQuery('[name="currency_grouping_separator"]', form).on('change', function (e) {
				let element = jQuery(e.currentTarget);
				let selectedValue = element.val();
				let decimalSeparatorValue = jQuery('[name="currency_decimal_separator"]', form).data('selectedValue');
				if (decimalSeparatorValue == selectedValue) {
					let message = app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME');
					let params = {
						text: message,
						type: 'error'
					};
					Vtiger_Helper_Js.showMessage(params);
					let previousSelectedValue = element.data('selectedValue');
					element.find('option').removeAttr('selected');
					element.find('option[value="' + previousSelectedValue + '"]').attr('selected', 'selected');
					element.trigger('change');
				} else {
					element.data('selectedValue', selectedValue);
				}
			});
		}
	},
	{
		duplicateCheckCache: {},
		userExistCheckCache: {},
		passCheckCache: {},
		//Hold the conditions for a hour format
		hourFormatConditionMapping: false,
		registerHourFormatChangeEvent: function () {},
		getHourValues: function (list, currentValue) {
			let options = '';
			for (let key in list) {
				//IE Browser consider the prototype properties also, it should consider has own properties only.
				if (list.hasOwnProperty(key)) {
					let conditionValue = list[key];
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
			let thisInstance = this;
			form.on('change', 'select[name="hour_format"]', function (e) {
				let hourFormatVal = jQuery(e.currentTarget).val();
				let startHourElement = jQuery('select[name="start_hour"]', form);
				let endHourElement = jQuery('select[name="end_hour"]', form);
				let conditionStartSelected = startHourElement.val();
				let conditionEndSelected = endHourElement.val();
				if (typeof thisInstance.hourFormatConditionMapping === 'undefined') {
					return false;
				}
				let list = thisInstance.hourFormatConditionMapping['hour_format'][hourFormatVal]['start_hour'];
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
			let thisInstance = this;
			form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
				let record = jQuery('input[name="record"]').val();
				let progressIndicatorElement = jQuery.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				if (!record && jQuery('input[name="user_password"]').val() != jQuery('input[name="confirm_password"]').val()) {
					app.showNotify(app.vtranslate('JS_REENTER_PASSWORDS'));
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					e.preventDefault();
				}
				thisInstance
					.verifyFormData()
					.done(function (data) {
						if (data.result.message) {
							app.showNotify(data.result.message);
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							e.preventDefault();
						}
					})
					.fail(function (data, error) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						e.preventDefault();
					});
			});
		},
		verifyFormData: function () {
			let aDeferred = jQuery.Deferred();
			let thisInstance = this;
			thisInstance.verifyData().done(
				function (data) {
					aDeferred.resolve(data);
				},
				function (data, error) {
					aDeferred.reject();
				}
			);
			return aDeferred.promise();
		},
		verifyData: function () {
			let aDeferred = jQuery.Deferred();
			AppConnector.request({
				async: false,
				data: {
					module: 'Users',
					action: 'VerifyData',
					mode: 'recordPreSave',
					email: jQuery('[name="email1"]').val(),
					userName: jQuery('input[name="user_name"]').val(),
					record: jQuery('input[name="record"]').val(),
					password: jQuery('input[name="user_password"]').val()
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
		registerValidatePassword: function (form) {
			form.on('click', '.js-validate-password', function (e) {
				AppConnector.request({
					module: app.getModuleName(),
					action: 'VerifyData',
					mode: 'validatePassword',
					record: form.find('[name="record"]').val(),
					password: form.find('[name="' + $(e.currentTarget).data('field') + '"]').val()
				}).done(function (data) {
					if (data.success && data.result) {
						Vtiger_Helper_Js.showMessage({
							text: data.result.message,
							type: data.result.type
						});
					}
				});
			});
		},
		registerEvents: function () {
			this._super();
			let form = this.getForm();
			this.triggerHourFormatChangeEvent(form);
			this.registerRecordPreSaveEvent(form);
			this.registerValidatePassword(form);
			Users_Edit_Js.registerChangeEventForCurrencySeparator();
		}
	}
);
