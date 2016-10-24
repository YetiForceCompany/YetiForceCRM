/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

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
				element.trigger("chosen:updated");
			} else {
				element.data('selectedValue', selectedValue);
			}
		})
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
				element.trigger("chosen:updated");
			} else {
				element.data('selectedValue', selectedValue);
			}
		})
	}

}, {
	duplicateCheckCache: {},
	userExistCheckCache: {},
	passCheckCache: {},
	//Hold the conditions for a hour format
	hourFormatConditionMapping: false,
	registerWidthChangeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		jQuery('#currentWidthType').html(jQuery('li[data-class="' + widthType + '"]').html());
		jQuery('#widthType').on('click', 'li', function (e) {
			var value = jQuery(e.currentTarget).data('class');
			app.cacheSet('widthType', value);
			jQuery('#currentWidthType').html(jQuery(e.currentTarget).html());
			window.location.reload();
		});
	},
	registerHourFormatChangeEvent: function () {

	},
	getHourValues: function(list, currentValue) {
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
			if (typeof thisInstance.hourFormatConditionMapping == 'undefined') {
				return false;
			}
			var list = thisInstance.hourFormatConditionMapping['hour_format'][hourFormatVal]['start_hour'];
			startHourElement.html(thisInstance.getHourValues(list, conditionStartSelected)).trigger("chosen:updated");
			endHourElement.html(thisInstance.getHourValues(list, conditionEndSelected)).trigger("chosen:updated");
		});
	},
	triggerHourFormatChangeEvent: function (form) {
		this.hourFormatConditionMapping = jQuery('input[name="timeFormatOptions"]', form).data('value');
		this.changeStartHourValuesEvent(form);
		jQuery('select[name="hour_format"]', form).trigger('change');
	},
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent: function (form) {
		var thisInstance = this;
		form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			var userName = jQuery('input[name="user_name"]').val();
			var newPassword = jQuery('input[name="user_password"]').val();
			var confirmPassword = jQuery('input[name="confirm_password"]').val();
			var record = jQuery('input[name="record"]').val();
			var email = jQuery('[name="email1"]').val();
			var pass = jQuery('[name="user_password"]').val();
			var progressIndicatorElement = jQuery.progressIndicator({
				'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			thisInstance.checkEmail(email).then(
					function (data) {
					},
					function (data, error) {
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
						e.preventDefault();
					}
			);
			if (record == '') {
				if (newPassword != confirmPassword) {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_REENTER_PASSWORDS'));
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					e.preventDefault();
				}
				if (!(userName in thisInstance.duplicateCheckCache)) {
					thisInstance.checkDuplicateUser(userName).then(
							function (data) {
								if (data.result) {
									thisInstance.duplicateCheckCache[userName] = data.result;
									progressIndicatorElement.progressIndicator({'mode': 'hide'});
									Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_EXISTS'));
									e.preventDefault();
								}
							},
							function (data, error) {
								thisInstance.duplicateCheckCache[userName] = data.result;
								InitialFormData = form.serialize();
								//form.submit();
							}
					);
				} else if (thisInstance.duplicateCheckCache[userName] == true) {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_EXISTS'));
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					e.preventDefault();
				}

				if (thisInstance.passCheckCache.name != pass) {
					thisInstance.checkPass(pass).then(
							function (data) {
								thisInstance.passCheckCache = {name: pass, result: data.result}
								Vtiger_Helper_Js.showPnotify(data.result);
								e.preventDefault();
							},
							function (data, error) {
								thisInstance.passCheckCache = {name: pass, result: data.result}
							}
					)
				} else if (thisInstance.passCheckCache.result != false) {
					Vtiger_Helper_Js.showPnotify(thisInstance.passCheckCache.result);
					e.preventDefault();
				}
			}
		})
	},
	checkEmail: function (email) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		if (thisInstance.userExistCheckCache.name != email) {
			thisInstance.userExist(email).then(
					function (data) {
						thisInstance.userExistCheckCache = {name: email, status: data.result}
						aDeferred.resolve(true);
					},
					function (data, error) {
						thisInstance.userExistCheckCache = {name: email, status: data.result}
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_MAIL_EXIST'));
						aDeferred.reject();
					}
			);
		} else if (!thisInstance.userExistCheckCache.status) {
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_MAIL_EXIST'));
			aDeferred.reject();
		}
		return aDeferred.promise();
	},
	registerCalendarSharedType: function (form) {
		form.find('select[name="calendarsharedtype"]').on('change', function (e) {
			var type = jQuery(e.currentTarget).val();
			if (type == 'seletedusers') {
				form.find('#selectUsers').show();
			} else {
				form.find('#selectUsers').hide();
			}
		})
	},
	checkDuplicateUser: function (userName) {
		var aDeferred = jQuery.Deferred();
		var params = {
			'module': app.getModuleName(),
			'action': "SaveAjax",
			'mode': 'userExists',
			'user_name': userName
		}
		AppConnector.request(params).then(
				function (data) {
					if (data.result) {
						aDeferred.resolve(data);
					} else {
						aDeferred.reject(data);
					}
				}
		);
		return aDeferred.promise();
	},
	userExist: function (email) {
		var aDeferred = jQuery.Deferred();
		var params = {},
				userId = jQuery('[name="record"]').val();
		params.data = {module: 'Users', action: 'CheckUserEmail', email: email, cUser: userId};
		params.async = false;
		if (email) {
			AppConnector.request(params).then(
					function (data) {
						if (data.result) {
							aDeferred.resolve(data);
						} else {
							aDeferred.reject(data);
						}
					}
			);
		}
		return aDeferred.promise();
	},
	checkPass: function (pass) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		if (pass.length) {
			params.data = {module: 'Users', action: 'CheckUserPass', pass: pass};
			params.async = false;
			AppConnector.request(params).then(
					function (data) {
						if (data.result) {
							aDeferred.resolve(data);
						} else {
							aDeferred.reject(data);
						}
					}
			);
		}
		return aDeferred.promise();
	},
	registerEvents: function () {
		this._super();
		var form = this.getForm();
		this.registerWidthChangeEvent();
		this.triggerHourFormatChangeEvent(form);
		this.registerRecordPreSaveEvent(form);
		this.registerCalendarSharedType(form);
		Users_Edit_Js.registerChangeEventForCurrencySeparator();
	}
});
