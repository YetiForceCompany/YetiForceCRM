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
	'Calendar_Edit_Js',
	{
		currencyInstance: false,
		checkEmail(field, rules, i, options) {
			if (Calendar_Edit_Js.currencyInstance.emailExists(field.val())) {
				return app.vtranslate('JS_DUPLICATE_RECORD') + ': ' + field.val();
			}
			return true;
		}
	},
	{
		init() {
			Calendar_Edit_Js.currencyInstance = this;
		},
		registerReminderFieldCheckBox: function () {
			let element = this.getForm().find('.js-reminder-field-checkbox');
			element.on('change', function (e) {
				let rowElement = element.closest('.js-reminder-field-element').find('.js-reminder-field-row');
				if (element.is(':checked')) {
					rowElement.removeClass('d-none');
				} else {
					rowElement.addClass('d-none');
				}
			});
			element.change();
		},
		/**
		 * Function which will register change event on recurrence field checkbox
		 */
		registerRecurrenceFieldCheckBox: function () {
			const form = this.getForm(),
				element = form.find('input[name="reapeat"]');
			element.on('change', function (e) {
				let repeatUI = form.find('.js-repeat-ui'),
					container = form.find('[name="followup"]').closest('.fieldValue');
				if ($(e.currentTarget).is(':checked')) {
					repeatUI.closest('.fieldRow').removeClass('d-none');
					container.find('[name="followup_display"]').attr('disabled', 'disabled');
					container.find('button').attr('disabled', 'disabled');
				} else {
					container.find('[name="followup_display"]').removeAttr('disabled');
					container.find('button').removeAttr('disabled');
					repeatUI.closest('.fieldRow').addClass('d-none');
				}
			});
			element.change();
		},
		/**
		 * Function which will register the change event for recurring type
		 */
		registerRecurringTypeChangeEvent: function () {
			var container = this.getForm();
			var thisInstance = this;
			container.find('.recurringType').on('change', function (e) {
				thisInstance.changeRecurringTypesUIStyles(jQuery(e.currentTarget).val());
			});
			container.find('.js-repeat-ui [name="calendarEndType"]').on('change', function (e) {
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
				container.find('.repeatWeekUI').removeClass('show').addClass('d-none');
				container.find('.repeatMonthUI').removeClass('show').addClass('d-none');
			} else if (recurringType == 'WEEKLY') {
				container.find('.repeatWeekUI').removeClass('d-none').addClass('show');
				container.find('.repeatMonthUI').removeClass('show').addClass('d-none');
			} else if (recurringType == 'MONTHLY') {
				container.find('.repeatWeekUI').removeClass('show').addClass('d-none');
				container.find('.repeatMonthUI').removeClass('d-none').addClass('show');
			}
		},
		setDefaultEndTime: function (container) {
			const self = this;
			if (container.find('.js-autofill').is(':checked')) {
				self.getFreeTime(container);
			} else {
				if ('1' === $('[name="userChangedEndDateTime"]').val()) {
					return;
				}
				let dateStartElement = container.find('[name="date_start"]'),
					startTimeElement = container.find('[name="time_start"]'),
					endTimeElement = container.find('[name="time_end"]'),
					endDateElement = container.find('[name="due_date"]'),
					startDate = dateStartElement.val(),
					startTime = startTimeElement.val(),
					result = Vtiger_Time_Validator_Js.invokeValidation(startTimeElement);
				if (true !== result) {
					return;
				}
				let activityType = container.find('[name="activitytype"]');
				let activityTypeValue = activityType.val();
				if (activityType.is('[type="radio"]')) {
					activityTypeValue = activityType.filter(':checked').val();
				}
				let startDateTime = startDate + ' ' + startTime,
					dateFormat = container.find('[name="due_date"]').data('dateFormat').toUpperCase(),
					timeFormat = endTimeElement.data('format'),
					activityDurations = JSON.parse(container.find('[name="defaultOtherEventDuration"]').val()),
					minutes = 0;
				for (let i in activityDurations) {
					if (activityDurations[i].activitytype === activityTypeValue) {
						minutes = parseInt(activityDurations[i].duration);
						break;
					}
				}
				let defaultTimeFormat = 'HH:mm';
				if (12 === timeFormat) {
					defaultTimeFormat = 'hh:mm A';
				}
				let endDate = moment(startDateTime, dateFormat + ' ' + defaultTimeFormat).add(minutes, 'minutes');
				endDateElement.val(endDate.format(dateFormat));
				endTimeElement.val(endDate.format(defaultTimeFormat));
			}
		},
		/**
		 * Function to change the end time based on default call duration
		 */
		registerActivityTypeChangeEvent: function (container) {
			const thisInstance = this;
			container.on('change', '[name="activitytype"]', function (e) {
				thisInstance.setDefaultEndTime(container);
			});
		},
		/**
		 * Function to change the end time based on default call duration
		 */
		registerTimeStartChangeEvent(container) {
			const thisInstance = this;
			container.find('input[name="time_start"]').on('change', function (e) {
				thisInstance.setDefaultEndTime(container);
			});
			container.find('[name="date_start"]').on('change', function (e) {
				let endDateElement = container.find('[name="due_date"]'),
					start = thisInstance.getDateInstance(container, 'start'),
					end = thisInstance.getDateInstance(container, 'end'),
					dateFormat = CONFIG.dateFormat.toUpperCase();
				container.find('.js-autofill').trigger('change');
				if (start > end) {
					end = start;
					endDateElement.val(moment(end).format(dateFormat));
					App.Fields.Date.register(container);
				}
			});
			container.find('input[name="time_start"]').on('focus', function (e) {
				let element = $(e.currentTarget);
				element.data('prevValue', element.val());
			});
			container.find('input[name="time_start"]').on('blur', function (e, data) {
				if (typeof data === 'undefined') {
					data = {};
				}
				if (typeof data.forceChange === 'undefined') {
					data.forceChange = false;
				}
				let element = $(e.currentTarget);
				if (element.val() !== element.data('prevValue') || data.forceChange) {
					e = $.Event('keydown');
					e.which = 13;
					e.keyCode = 13;
					element.trigger(e);
				}
			});
		},
		registerEndDateTimeChangeLogger: function (container) {
			container.find('[name="time_end"]').on('change', function (e) {
				let timeElement = jQuery(e.currentTarget);
				let result = Vtiger_Time_Validator_Js.invokeValidation(timeElement);
				if (result != true) {
					return;
				}
				let timeDateElement = timeElement.closest('.fieldValue').find('[name="due_date"]');
				jQuery('[name="userChangedEndDateTime"]').val('1');
				timeDateElement.data('userChangedTime', true);
			});

			container.find('[name="due_date"]').on('change', function (e) {
				let dueDateElement = jQuery(e.currentTarget);
				let result = Vtiger_Date_Validator_Js.invokeValidation(dueDateElement);
				if (result != true) {
					return;
				}
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
			var endValue = form.find('.js-repeat-ui [name="calendarEndType"]:checked').val();
			if (endValue === 'count') {
				rule += ';COUNT=' + form.find('.countEvents').val();
			} else if (endValue === 'until') {
				let date = App.Fields.Date.dateToDbFormat(App.Fields.Date.getDateInstance(form.find('.calendarUntil').val()));
				rule += ';UNTIL=' + date.replace(/-/gi, '') + 'T235959';
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
				const dateInstance = App.Fields.Date.getDateInstance(form.find('[name="date_start"]').val());
				let dayOfMonth = dateInstance.getDate();
				if (form.find('.calendarMontlyType:checked').val() == 'DAY') {
					let dayOfWeekLabel = '';
					switch (dateInstance.getDay()) {
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
		 * @param {jQuery} form
		 */
		registerFormSubmitEvent: function (form) {
			var thisInstance = this;
			var lockSave = true;
			if (app.getRecordId()) {
				form.on(Vtiger_Edit_Js.recordPreSave, function (e) {
					if (lockSave && form.find('input[name="reapeat"]').is(':checked')) {
						e.preventDefault();
						app.showModalWindow(form.find('.typeSavingModal').clone(), function (container) {
							container.find('.typeSavingBtn').on('click', function (e) {
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
				const recurringCheck = form.find('input[name="reapeat"]').is(':checked');
				if (recurringCheck) {
					if (app.getRecordId() && lockSave) {
						e.preventDefault();
					}
					form.find('[name="recurrence"]').val(thisInstance.getRule());
				}
				let rows = form.find('.js-participants-content .js-participant-row');
				let invitees = [];
				rows.each(function (index, domElement) {
					let row = $(domElement);
					if (row.data('email')) {
						invitees.push([row.data('email'), row.data('crmid'), row.data('ivid'), row.data('name')]);
					}
				});
				$('<input type="hidden" name="inviteesid" />').appendTo(form).val(JSON.stringify(invitees));
			});
		},
		getFreeTime: function (container) {
			let ownerId = container.find('[name="assigned_user_id"], [data-element-name="assigned_user_id"]');
			if (ownerId.length === 0 || !ownerId.val()) {
				return;
			}
			let timeStart = container.find('[name="time_start"], [data-element-name="time_start"]');
			let timeEnd = container.find('[name="time_end"], [data-element-name="time_end"]');
			let dateStart = container.find('[name="date_start"], [data-element-name="date_start"]');
			let params = {
				module: 'Calendar',
				action: 'GetFreeTime',
				dateStart: dateStart.val(),
				ownerId: ownerId.val(),
				activitytype: container.find('[name="activitytype"]').val()
			};
			let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			AppConnector.request(params).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				if (data.result.date_start !== null) {
					timeStart.val(data.result.time_start);
					timeEnd.val(data.result.time_end);
					dateStart.val(data.result.date_start);
					container.find('[name="due_date"]').val(data.result.date_start);
				} else {
					app.showNotify({
						text: app.vtranslate('JS_ERROR_MAX_VALUE'),
						type: 'error'
					});
					container.find('.js-autofill').prop('checked', false).trigger('change');
				}
			});
		},
		registerAutoFillHours: function (container) {
			const thisInstance = this;
			let allDay = container.find('[name="allday"]'),
				timeStart = container.find('[name="time_start"]'),
				timeEnd = container.find('[name="time_end"]'),
				dateEnd = container.find('[name="due_date"]'),
				autoFill = container.find('.js-autofill');
			container.find('.js-autofill__icon').on('click', function (e) {
				if (autoFill.is(':checked')) {
					$(e.currentTarget).closest('.input-group-text').removeClass('bg-color-blue-700').removeClass('text-white');
					autoFill.prop('checked', false).trigger('change');
				} else {
					$(e.currentTarget).closest('.input-group-text').addClass('bg-color-blue-700').addClass('text-white');
					autoFill.prop('checked', true).trigger('change');
				}
			});
			container.find('.js-autofill').on('change', function (e) {
				let currentTarget = $(e.currentTarget);
				if (currentTarget.is(':checked')) {
					container.find('.js-autofill').prop('checked', true);
					thisInstance.getFreeTime(container);
					timeStart.attr('readonly', 'readonly');
					timeEnd.attr('readonly', 'readonly');
					timeStart.clockpicker('remove');
					timeEnd.clockpicker('remove');
					allDay.attr('disabled', 'disabled');
					allDay.prop('checked', false);
					allDay.trigger('change');
					dateEnd.attr('readonly', 'readonly');
				} else {
					container.find('.js-autofill').prop('checked', false);
					allDay.removeAttr('disabled');
					timeStart.removeAttr('readonly');
					timeEnd.removeAttr('readonly');
					dateEnd.removeAttr('readonly');
					app.registerEventForClockPicker(timeStart);
					app.registerEventForClockPicker(timeEnd);
				}
			});
		},
		registerMarkAsCompletedBtn: function (container) {
			container.find('.js-btn--mark-as-completed').on('click', function () {
				const self = $(this);
				if (self.hasClass('active')) {
					container.find('.js-completed').remove();
				} else {
					container.append(
						'<input class="js-completed" type=hidden name="activitystatus" value="PLL_COMPLETED" data-js="remove">'
					);
				}
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
			this.registerMarkAsCompletedBtn(container);
			this.registerInviteEvent(container);
			this.registerAddInvitation(container);
			this.registerFormSubmitEvent(container);
		},
		toggleTimesInputs: function (container) {
			container.find(':checkbox').on('change', function () {
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
			var dateFormat = CONFIG.dateFormat;
			if (type == 'start') {
				return App.Fields.Date.getDateInstance(startDate + ' ' + startTime, dateFormat);
			}
			if (type == 'end') {
				return App.Fields.Date.getDateInstance(endDate + ' ' + endTime, dateFormat);
			}
		},
		emailExists(email) {
			email = email.toLowerCase();
			let recordExist = false;
			this.getForm()
				.find('.js-participants-content')
				.find('.js-participant-row')
				.each((index, element) => {
					if ($(element).data('email').toLowerCase() === email) {
						recordExist = true;
						return false;
					}
				});
			return recordExist;
		},
		registerAddInvitation(container) {
			container.find('.js-btn-add-invitation').on('click', (e) => {
				let progressIndicatorElement = $.progressIndicator();
				app.showModalWindow(null, 'index.php?module=Calendar&view=InviteEmail', (data) => {
					data.find('.js-modal__save').on('click', (e) => {
						let email = data.find('.js-invite-email-input').val();
						let nameAttendee = data.find('.js-invite-name-input').val();
						let participantsContent = container.find('.js-participants-content');
						let formEmail = data.find('.js-form');
						formEmail.validationEngine(app.validationEngineOptions);
						if (formEmail.validationEngine('validate')) {
							let participantRow = participantsContent.find('.d-none .js-participant-row').clone(true, true);
							participantRow.data('crmid', 0);
							participantRow.data('email', email);
							if (nameAttendee) {
								participantRow.find('.js-participant-name').data('content', nameAttendee).text(nameAttendee);
								participantRow.data('name', nameAttendee);
							} else {
								participantRow.find('.js-participant-name').data('content', email).text(email);
								participantRow.data('name', '');
							}
							participantsContent.append(participantRow);
							app.hideModalWindow();
						}
					});
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
		},
		registerInviteEvent: function (editViewForm) {
			this.registerRow(editViewForm);
			let participantsContent = editViewForm.find('.js-participants-content');
			let participantsSearch = editViewForm.find('.js-participants-search');
			$.widget('custom.ivAutocomplete', $.ui.autocomplete, {
				_create: function () {
					this._super();
					this.widget().menu('option', 'items', '> :not(.ui-autocomplete-category)');
				},
				_renderMenu: function (ul, items) {
					let that = this,
						currentCategory = '';
					$.each(items, function (_index, item) {
						if (item.category != currentCategory) {
							ul.append("<li class='ui-autocomplete-category'>" + item.category + '</li>');
							currentCategory = item.category;
						}
						that._renderItemData(ul, item);
					});
				},
				_renderItemData: function (ul, item) {
					return this._renderItem(ul, item).data('ui-autocomplete-item', item);
				},
				_renderItem: function (ul, item) {
					return $('<li>').data('item.autocomplete', item).append($('<a></a>').html(item.label)).appendTo(ul);
				}
			});
			participantsSearch.ivAutocomplete({
				delay: '600',
				minLength: '3',
				source: (request, response) => {
					AppConnector.request({
						module: 'Calendar',
						action: 'Invitees',
						mode: 'find',
						value: request.term
					}).done((result) => {
						let reponseDataList = result.result;
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
				select: (event, ui) => {
					let selected = ui.item;
					//To stop selection if no results is selected
					if (typeof selected.type !== 'undefined' && selected.type == 'no results') {
						return false;
					}
					let recordExist = true;
					participantsContent.find('.js-participant-row').each(function (index) {
						if ($(this).data('crmid') == selected.id) {
							recordExist = false;
						}
					});
					if (recordExist) {
						let participantRow = participantsContent.find('.d-none .js-participant-row').clone(true, true);
						Vtiger_Index_Js.getEmailFromRecord(selected.id, selected.module).done((email) => {
							participantRow.data('crmid', selected.id);
							participantRow.data('email', email);
							participantRow
								.find('.js-participant-name')
								.data('content', selected.fullLabel + email)
								.text(selected.label);
							participantRow
								.find('.js-participant-icon .c-badge__icon')
								.removeClass('fas fa-envelope')
								.addClass('yfm-' + selected.module);
							participantsContent.append(participantRow);
						});
					} else {
						app.showNotify({
							text: app.vtranslate('JS_DUPLICATE_RECORD') + ': ' + selected.fullLabel,
							type: 'info'
						});
					}
				},
				close: (event, ui) => {
					participantsSearch.val('');
				}
			});
		},
		registerRow(row) {
			row.on('click', '.js-participant-remove', (e) => {
				$(e.target).closest('.js-participant-row').remove();
			});
		},
		registerEvents() {
			if (!this.proceedRegisterEvents()) {
				return;
			}
			this.registerReminderFieldCheckBox();
			this.registerRecurrenceFieldCheckBox();
			this.registerRecurringTypeChangeEvent();
			this._super();
		}
	}
);
