/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.OSSTimeControl_Calendar_Js = class OSSTimeControl_Calendar_Js extends Calendar_Js {

	constructor() {
		super();
	}

	setCalendarModuleOptions() {
		let self = this;
		return {
			allDaySlot: false,
			dayClick: function (date, jsEvent, view) {
				self.selectDay(date.format());
				self.getCalendarView().fullCalendar('unselect');
			},
			selectable: false
		};
	}

	addCalendarEvent(calendarDetails, dateFormat) {
		let usersList = $("#calendarUserList").val();
		if (typeof usersList === 'undefined' || usersList.length === 0) {
			usersList = [CONFIG.userId.toString()];
		}
		if ($.inArray(calendarDetails.assigned_user_id.value, usersList) < 0) {
			return;
		}
		const types = $("#timecontrolTypes").val();
		if ($.inArray(calendarDetails.timecontrol_type.value, types) < 0 && types.length > 0) {
			return;
		}
		const calendar = this.getCalendarView();
		const eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails.name.display_value,
			start: calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value).format(),
			end: calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value).format(),
			start_display: calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value,
			end_display: calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value,
			url: 'index.php?module=OSSTimeControl&view=Detail&record=' + calendarDetails._recordId,
			className: 'ownerCBg_' + calendarDetails.assigned_user_id.value + ' picklistCBr_OSSTimeControl_timecontrol_type_' + calendarDetails.timecontrol_type.value,
			totalTime: calendarDetails.sum_time.display_value,
			number: calendarDetails.osstimecontrol_no.display_value,
			type: calendarDetails.timecontrol_type.display_value,
			type_value: calendarDetails.timecontrol_type.value,
			status: calendarDetails.osstimecontrol_status.display_value,
			sta: calendarDetails.osstimecontrol_status.value,
			smownerid: calendarDetails.assigned_user_id.display_value,
		};
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
	}

	selectDay(date) {
		var thisInstance = this;

		thisInstance.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase();
			var timeFormat = data.find('[name="time_start"]').data('format');
			if (timeFormat == 24) {
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm A';
			}
			var startDateInstance = Date.parse(date);
			var startDateString = moment(date).format(dateFormat);
			var startTimeString = moment(date).format(defaultTimeFormat);
			var endDateInstance = Date.parse(date);
			var endDateString = moment(date).format(dateFormat);

			var view = thisInstance.getCalendarView().fullCalendar('getView');
			var endTimeString;
			if ('month' == view.name) {
				var diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					var defaultFirstHour = jQuery('#start_hour').val();
					var explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];
					var defaultLastHour = jQuery('#end_hour').val();
					explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					var now = new Date();
					startTimeString = moment(now).format(defaultTimeFormat);
					endTimeString = moment(now).add(15, 'minutes').format(defaultTimeFormat);
				}
			} else {
				endTimeString = moment(endDateInstance).add(30, 'minutes').format(defaultTimeFormat);
			}
			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {
				callbackFunction(data) {
					thisInstance.addCalendarEvent(data.result, dateFormat);
				}
			});
		});
	}
}
