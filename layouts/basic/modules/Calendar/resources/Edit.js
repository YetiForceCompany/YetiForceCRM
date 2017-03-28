/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Calendar_Edit_Js", {
}, {
	isEvents: function () {
		var form = this.getForm();
		var moduleName = form.find('[name="module"]').val();
		if (moduleName == 'Events') {
			return true;
		}
		return false;
	},
	registerReminderFieldCheckBox: function () {
		this.getForm().find('input[name="set_reminder"]').on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var closestDiv = element.closest('div').next();
			if (element.is(':checked')) {
				closestDiv.removeClass('hide');
			} else {
				closestDiv.addClass('hide');
			}
		});
	},
	/**
	 * Function which will register change event on recurrence field checkbox
	 */
	registerRecurrenceFieldCheckBox: function () {
		var thisInstance = this;
		var form = thisInstance.getForm();
		form.find('input[name="reapeat"]').on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var repeatUI = form.find('.repeatUI');
			var container = form.find('[name="followup"]').closest('.fieldValue');
			if (element.is(':checked')) {
				repeatUI.removeClass('hide');
				container.find('[name="followup_display"]').attr('disabled', 'disabled');
				container.find('button').attr('disabled', 'disabled');
			} else {
				container.find('[name="followup_display"]').removeAttr('disabled');
				container.find('button').removeAttr('disabled');
				repeatUI.addClass('hide');
			}
		});
		if (form.find('input[name="reapeat"]').is(':checked')) {
			form.find('.repeatUI').removeClass('hide');
			var container = form.find('[name="followup"]').closest('.fieldValue');
			container.find('[name="followup_display"]').attr('disabled', 'disabled');
			container.find('button').attr('disabled', 'disabled');
		}
	},
	/**
	 * Function which will register the change event for recurring type
	 */
	registerRecurringTypeChangeEvent: function () {
		var container = this.getForm();
		var thisInstance = this;
		container.find('.recurringType').on('change', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var recurringType = currentTarget.val();
			thisInstance.changeRecurringTypesUIStyles(recurringType);
		});
		container.find('.repeatUI [name="calendarEndType"]').on('change', function (e) {
			var currentTarget = $(e.currentTarget);
			var value = currentTarget.val();
			if (value === 'never') {
				container.find('.countEvents').attr('disabled', 'disabled');
				container.find('.calendarUntil').attr('disabled', 'disabled');
			} else if (value === 'count') {
				container.find('.countEvents').removeAttr('disabled');
				container.find('.calendarUntil').attr('disabled', 'disabled');
			} else if (value === 'until') {
				container.find('.countEvents').attr('disabled', 'disabled');
				container.find('.calendarUntil').removeAttr('disabled');
			}
		});
	},
	/**
	 * Function which will change the UI styles based on recurring type
	 * @params - recurringType - which recurringtype is selected
	 */
	changeRecurringTypesUIStyles: function (recurringType) {
		var container = this.getForm();
		if (recurringType == 'DAILY' || recurringType == 'YEARLY') {
			container.find('.repeatWeekUI').removeClass('show').addClass('hide');
			container.find('.repeatMonthUI').removeClass('show').addClass('hide');
		} else if (recurringType == 'WEEKLY') {
			container.find('.repeatWeekUI').removeClass('hide').addClass('show');
			container.find('.repeatMonthUI').removeClass('show').addClass('hide');
		} else if (recurringType == 'MONTHLY') {
			container.find('.repeatWeekUI').removeClass('show').addClass('hide');
			container.find('.repeatMonthUI').removeClass('hide').addClass('show');
		}
	},
	setDefaultEndTime: function (container) {
		var dateStartElement = container.find('[name="date_start"]');
		var startTimeElement = container.find('[name="time_start"]');
		var endTimeElement = container.find('[name="time_end"]');
		var endDateElement = container.find('[name="due_date"]');

		if (jQuery('[name="userChangedEndDateTime"]').val() == '1') {
			return;
		}

		var startDate = dateStartElement.val();
		var startTime = startTimeElement.val();

		var result = Vtiger_Time_Validator_Js.invokeValidation(startTimeElement);
		if (result != true) {
			return;
		}
		var startDateTime = startDate + ' ' + startTime;
		var dateFormat = container.find('[name="due_date"]').data('dateFormat');
		var timeFormat = endTimeElement.data('format');
		startDate = Vtiger_Helper_Js.getDateInstance(startDateTime, dateFormat);
		var startDateInstance = Date.parse(startDate);
		var endDateInstance = false;

		if (container.find('[name="activitytype"]').val() == 'Call') {
			var defaulCallDuration = container.find('[name="defaultCallDuration"]').val();
			endDateInstance = startDateInstance.addMinutes(defaulCallDuration);
		} else {
			var defaultOtherEventDuration = container.find('[name="defaultOtherEventDuration"]').val();
			endDateInstance = startDateInstance.addMinutes(defaultOtherEventDuration);
		}
		var endDateString = app.getDateInVtigerFormat(dateFormat, endDateInstance);
		if (timeFormat == 24) {
			var defaultTimeFormat = 'HH:mm';
		} else {
			defaultTimeFormat = 'hh:mm tt';
		}
		var endTimeString = startDateInstance.toString(defaultTimeFormat);

		endDateElement.val(endDateString);
		endTimeElement.val(endTimeString);
	},
	/**
	 * Function to change the end time based on default call duration
	 */
	registerActivityTypeChangeEvent: function (container) {
		var thisInstance = this;
		container.on('change', 'select[name="activitytype"]', function (e) {
			thisInstance.setDefaultEndTime(container);
		});
	},
	/**
	 * Function to change the end time based on default call duration
	 */
	registerTimeStartChangeEvent: function (container) {
		var thisInstance = this;
		container.find('input[name="time_start"]').on('change', function (e) {
			thisInstance.setDefaultEndTime(container);
		});

		container.find('[name="date_start"]').on('change', function (e) {
			var startDateElement = jQuery(e.currentTarget);
			var endDateElement = container.find('[name="due_date"]');

			var start = thisInstance.getDateInstance(container, 'start');
			var end = thisInstance.getDateInstance(container, 'end');
			var dateFormat = $('#userDateFormat').val();
			var timeFormat = $('#userTimeFormat').val();
			container.find('.autofill:visible').trigger('change');
			if (start > end) {
				end = start;
				var endDateString = app.getDateInVtigerFormat(dateFormat, end);
				endDateElement.val(endDateString);
				app.registerEventForDatePickerFields(container);
				thisInstance.setVisibilityBtnSaveAndClose(container);
			}
			var timeStartElement = startDateElement.closest('.fieldValue').find('[name="time_start"]');
			timeStartElement.trigger('changeTime');
		});

		container.find('input[name="time_start"]').on('focus', function (e) {
			var element = jQuery(e.currentTarget);
			element.data('prevValue', element.val());
		});

		container.find('input[name="time_start"]').on('blur', function (e, data) {
			if (typeof data == 'undefined') {
				data = {};
			}

			if (typeof data.forceChange == 'undefined') {
				data.forceChange = false;
			}
			var element = jQuery(e.currentTarget);
			var currentValue = element.val();
			var prevValue = element.data('prevValue');
			if (currentValue != prevValue || data.forceChange) {
				var list = element.data('timepicker-list');
				if (!list) {
					//To generate the list 
					element.timepicker('show');
					element.timepicker('hide');
					list = element.data('timepicker-list');
				}
				e = jQuery.Event("keydown");
				e.which = 13;
				e.keyCode = 13;
				element.trigger(e);
			}
		});
	},
	setVisibilityBtnSaveAndClose: function (container) {
		var secondDate = container.find('input[name="due_date"]');
		var secondDateFormat = secondDate.data('date-format');
		var secondDateValue = secondDate.val();
		var secondTime = container.find('input[name="time_end"]');
		var secondTimeValue = secondTime.val();
		var secondDateTimeValue = secondDateValue + ' ' + secondTimeValue;
		var secondDateInstance = Vtiger_Helper_Js.getDateInstance(secondDateTimeValue, secondDateFormat);
		var timeBetweenDates = secondDateInstance - new Date();
		if (timeBetweenDates >= 0) {
			container.find('.saveAndComplete').addClass('hide');
		} else {
			container.find('.saveAndComplete').removeClass('hide');
		}
	},
	registerEndDateTimeChangeLogger: function (container) {
		var thisInstance = this;
		container.find('[name="time_end"]').on('change', function (e) {
			var timeElement = jQuery(e.currentTarget);
			var result = Vtiger_Time_Validator_Js.invokeValidation(timeElement);
			if (result != true) {
				return;
			}
			var timeDateElement = timeElement.closest('.fieldValue').find('[name="due_date"]');
			jQuery('[name="userChangedEndDateTime"]').val('1');
			timeDateElement.data('userChangedTime', true);
		});

		container.find('[name="due_date"]').on('change', function (e) {
			var dueDateElement = jQuery(e.currentTarget);
			var result = Vtiger_Date_Validator_Js.invokeValidation(dueDateElement);
			if (result != true) {
				return;
			}
			thisInstance.setVisibilityBtnSaveAndClose(container);
			jQuery('[name="userChangedEndDateTime"]').val('1');
			dueDateElement.data('userChangedTime', true);
		});
	},
	/**
	 * 
	 * @returns {String}
	 */
	getRule: function () {
		var form = this.getForm();
		var freq = form.find('.recurringType').val();
		var rule = 'FREQ=' + freq;
		rule += ';INTERVAL=' + form.find('.repeatFrequency').val();
		var endValue = form.find('.repeatUI [name="calendarEndType"]:checked').val();
		if (endValue === 'count') {
			rule += ';COUNT=' + form.find('.countEvents').val();
		} else if (endValue === 'until') {
			var date = form.find('.calendarUntil').val();
			date = app.getDateInDBInsertFormat(app.getMainParams('userDateFormat'), date);
			rule += ';UNTIL=' + date.replace(/-/gi, '') + 'T000000';
		}
		if (freq === 'WEEKLY') {
			var checkedElements = [];
			form.find('.repeatWeekUI [type="checkbox"]').each(function () {
				var currentTarget = $(this);
				if (currentTarget.is(':checked')) {
					checkedElements.push(currentTarget.val());
				}
			});
			if (checkedElements.length > 0) {
				rule += ';BYDAY=' + checkedElements.join(',');
			}
		}
		if (freq === 'MONTHLY') {
			var dayOfWeek = Vtiger_Helper_Js.getDay(form.find('[name="date_start"]').val());
			var dateInstance = Vtiger_Helper_Js.getDateInstance(form.find('[name="date_start"]').val(), app.getMainParams('userDateFormat'));
			var dayOfMonth = dateInstance.getDate();
			var option = form.find('.calendarMontlyType:checked').val();
			if (option == 'DAY') {
				var dayOfWeekLabel = '';
				switch (dayOfWeek) {
					case 0:
						dayOfWeekLabel = 'SU';
						break;
					case 1:
						dayOfWeekLabel = 'MO';
						break;
					case 2:
						dayOfWeekLabel = 'TU';
						break;
					case 3:
						dayOfWeekLabel = 'WE';
						break;
					case 4:
						dayOfWeekLabel = 'TU';
						break;
					case 5:
						dayOfWeekLabel = 'FR';
						break;
					case 6:
						dayOfWeekLabel = 'SA';
						break;
				}
				rule += ';BYDAY=' + (parseInt((dayOfMonth - 1) / 7) + 1) + dayOfWeekLabel;
			} else {
				rule += ';BYMONTHDAY=' + dayOfMonth;
			}
		}
		return rule;
	},
	/**
	 * This function will register the submit event on form
	 */
	registerFormSubmitEvent: function () {
		var thisInstance = this;
		var form = this.getForm();
		var lockSave = true;
		if (app.getRecordId()) {
			form.on(Vtiger_Edit_Js.recordPreSave, function (e) {
				if (lockSave && form.find('input[name="reapeat"]').is(':checked')) {
					e.preventDefault();
					app.showModalWindow(form.find('.typeSavingModal').clone(), function (container) {
						container.find('.typeSavingBtn').click(function (e) {
							var currentTarget = $(e.currentTarget);
							form.find('[name="typeSaving"]').val(currentTarget.data('value'));
							app.hideModalWindow();
							lockSave = false;
							form.submit();
						});
					});
				}
			});
		}
		form.on('submit', function (e) {
			var recurringCheck = form.find('input[name="reapeat"]').is(':checked');
			if (recurringCheck) {
				if (app.getRecordId() && lockSave) {
					e.preventDefault();
				}
				form.find('[name="recurrence"]').val(thisInstance.getRule());
			}
			if (thisInstance.isEvents()) {
				var rows = form.find(".inviteesContent .inviteRow");
				var invitees = [];
				rows.each(function (index, domElement) {
					var row = jQuery(domElement);
					if (row.data('crmid') != '') {
						invitees.push([row.data('email'), row.data('crmid'), row.data('ivid')]);
					}
				});
				jQuery('<input type="hidden" name="inviteesid" />').appendTo(form).val(JSON.stringify(invitees));
			}
		});
	},
	getFreeTime: function (container) {
		var timeStart = container.find('[name="time_start"], [data-element-name="time_start"]');
		var timeEnd = container.find('[name="time_end"], [data-element-name="time_end"]');
		var dateStart = container.find('[name="date_start"], [data-element-name="date_start"]');
		var params = {
			module: 'Calendar',
			action: 'GetFreeTime',
			dateStart: dateStart.val()
		};
		container.progressIndicator({});
		AppConnector.request(params).then(function (data) {
			container.progressIndicator({mode: 'hide'});
			timeStart.val(data.result.time_start);
			timeEnd.val(data.result.time_end);
			dateStart.val(data.result.date_start);
			container.find('[name="due_date"]').val(data.result.date_start);
		});
	},
	registerAutoFillHours: function (container) {
		var thisInstance = this;
		var allDay = container.find('[name="allday"]');
		var timeStart = container.find('[name="time_start"]');
		var timeEnd = container.find('[name="time_end"]');
		var dateEnd = container.find('[name="due_date"]');
		container.find('.autofill').on('change', function (e) {
			var currentTarget = $(e.currentTarget);
			if (currentTarget.is(':checked')) {
				container.find('.autofill').attr('checked', 'checked');
				thisInstance.getFreeTime(container);
				timeStart.attr('readonly', 'readonly');
				timeEnd.attr('readonly', 'readonly');
				allDay.attr('disabled', 'disabled');
				allDay.removeAttr('checked');
				allDay.trigger('change');
				dateEnd.attr('readonly', 'readonly');
			} else {
				container.find('.autofill').removeAttr('checked');
				allDay.removeAttr('disabled');
				timeStart.removeAttr('readonly');
				timeEnd.removeAttr('readonly');
				dateEnd.removeAttr('readonly');
			}
		});
	},
	registerSaveAndCloseBtn: function (container) {
		this.setVisibilityBtnSaveAndClose(container);
		container.find('.saveAndComplete').on('click', function () {
			var invalidFields = container.data('jqv').InvalidFields;
			if (invalidFields.length == 0) {
				container.append('<input type=hidden name="saveAndClose" value="PLL_COMPLETED">');
			}
			container.find('[type="submit"]').trigger('click');
		});
	},
	registerBasicEvents: function (container) {
		this._super(container);
		this.toggleTimesInputs(container);
		this.registerTimesInputs(container);
		this.registerTimeStartChangeEvent(container);
		this.registerActivityTypeChangeEvent(container);
		this.registerEndDateTimeChangeLogger(container);
		this.registerAutoFillHours(container);
		this.registerSaveAndCloseBtn(container);
	},
	toggleTimesInputs: function (container) {
		container.find(':checkbox').change(function () {
			var checkboxName = $(this).attr('name');
			if ('allday' == checkboxName) {
				var checkboxIsChecked = $(this).is(':checked');
				if (!container.find('#quickCreate').length) {
					if (checkboxIsChecked) {
						container.find('.time').hide();
					} else {
						container.find('.time').show();
					}
				}
			}
		});
	},
	registerTimesInputs: function (container) {
		var allday = container.find('[name="allday"]:checkbox');
		if (allday.prop('checked')) {
			container.find('.time').hide();
		}

	},
	getDateInstance: function (container, type) {
		var startDateElement = container.find('[name="date_start"]');
		var endDateElement = container.find('[name="due_date"]');
		var endTimeElement = container.find('[name="time_end"]');
		var startTimeElement = container.find('[name="time_start"]');
		var startDate = startDateElement.val();
		var startTime = startTimeElement.val();
		var endTime = endTimeElement.val();
		var endDate = endDateElement.val();
		var dateFormat = $('#userDateFormat').val();
		if (type == 'start') {
			return Vtiger_Helper_Js.getDateInstance(startDate + ' ' + startTime, dateFormat);
		}
		if (type == 'end') {
			return Vtiger_Helper_Js.getDateInstance(endDate + ' ' + endTime, dateFormat);
		}
	},
	registerInviteEvent: function (editViewForm) {
		var thisInstance = this;
		this.registerRow(editViewForm);
		var inviteesContent = editViewForm.find('.inviteesContent');
		var inviteesSearch = editViewForm.find('input.inviteesSearch');
		$.widget("custom.ivAutocomplete", $.ui.autocomplete, {
			_create: function () {
				this._super();
				this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
			},
			_renderMenu: function (ul, items) {
				var that = this, currentCategory = "";
				$.each(items, function (index, item) {
					var li;
					if (item.category != currentCategory) {
						ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
						currentCategory = item.category;
					}
					that._renderItemData(ul, item);
				});
			},
			_renderItemData: function (ul, item) {
				return this._renderItem(ul, item).data("ui-autocomplete-item", item);
			},
			_renderItem: function (ul, item) {
				return $("<li>")
						.data("item.autocomplete", item)
						.append($("<a></a>").html(item.label))
						.appendTo(ul);
			},
		});
		inviteesSearch.ivAutocomplete({
			delay: '600',
			minLength: '3',
			source: function (request, response) {
				AppConnector.request({
					module: 'Calendar',
					action: 'Invitees',
					mode: 'find',
					value: request.term
				}).then(function (result) {
					var reponseDataList = result.result;
					if (reponseDataList.length <= 0) {
						reponseDataList.push({
							label: app.vtranslate('JS_NO_RESULTS_FOUND'),
							type: 'no results',
							category: ''
						});
					}
					response(reponseDataList);
				});
			},
			select: function (event, ui) {
				var selected = ui.item;

				//To stop selection if no results is selected
				if (typeof selected.type != 'undefined' && selected.type == "no results") {
					return false;
				}
				var recordExist = true;
				inviteesContent.find('.inviteRow').each(function (index) {
					if ($(this).data('crmid') == selected.id) {
						recordExist = false;
					}
				});
				if (recordExist) {
					var inviteRow = inviteesContent.find('.hide .inviteRow').clone(true, true);
					Vtiger_Index_Js.getEmailFromRecord(selected.id, selected.module).then(function (email) {
						inviteRow.data('crmid', selected.id);
						inviteRow.data('email', email);
						inviteRow.find('.inviteName').data('content', selected.fullLabel + email).text(selected.label);
						inviteRow.find('.inviteIcon .glyphicon').removeClass('glyphicon glyphicon-envelope').addClass('userIcon-' + selected.module);
						inviteesContent.append(inviteRow);
					});
				}
			},
			close: function (event, ui) {
				inviteesSearch.val('');
			}

		});
	},
	registerRow: function (row) {
		var thisInstance = this;
		row.on("click", '.inviteRemove', function (e) {
			$(e.target).closest('.inviteRow').remove();
		});
	},
	registerEvents: function () {
		var statusToProceed = this.proceedRegisterEvents();
		if (!statusToProceed) {
			return;
		}
		var editViewForm = this.getForm();
		this.registerReminderFieldCheckBox();
		this.registerRecurrenceFieldCheckBox();
		this.registerFormSubmitEvent();
		this.registerRecurringTypeChangeEvent();
		if (this.isEvents()) {
			this.registerInviteEvent(editViewForm);
		}
		this._super();
	}
});

