/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Reservations_Calendar_Js = class Reservations_Calendar_Js extends Calendar_Js {

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
		if ($("#calendarUserList").val() !== 'undefined' && $("#calendarUserList").val().length && $.inArray(calendarDetails.assigned_user_id.value, $("#calendarUserList").val()) < 0) {
			return;
		}
		if ($.inArray(calendarDetails.type.value, $("#reservationType").val()) < 0) {
			return;
		}
		var calendar = this.getCalendarView();
		var startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value);
		var endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
		var eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails.title.display_value,
			smownerid: calendarDetails.assigned_user_id ? calendarDetails.assigned_user_id.display_value : calendarDetails.smownerid,
			status: calendarDetails.reservations_status ? calendarDetails.reservations_status.display_value : calendarDetails.status,
			isPrivate: calendarDetails.isPrivate,
			start: startDate.toString(),
			end: endDate.toString(),
			start_display: calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value,
			end_display: calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value,
			url: 'index.php?module=Reservations&view=Detail&record=' + calendarDetails._recordId,
			className: 'ownerCBg_' + calendarDetails.assigned_user_id.value + ' picklistCBg_OSSTimeControl_timecontrol_type_' + calendarDetails.type.value,
			totalTime: calendarDetails.sum_time.display_value,
			type: calendarDetails.type.display_value,
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

	/**
	 * @inheritDoc
	 */
	getDefaultParams() {
		let params = super.getDefaultParams();
		if ($('#reservationType').length > 0) {
			params.types = $('#reservationType').val();
		}
		return params;
	}
}
