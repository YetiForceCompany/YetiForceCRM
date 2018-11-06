/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 *  Class representing a reservation calendar.
 * @extends Calendar_Unselectable_Js
 */
window.Reservations_Calendar_Js = class extends Calendar_Unselectable_Js {

	constructor() {
		super();
	}

	addCalendarEvent(calendarDetails, dateFormat) {
		if ($("#calendarUserList").val() !== 'undefined' && $("#calendarUserList").val().length && $.inArray(calendarDetails.assigned_user_id.value, $("#calendarUserList").val()) < 0) {
			return;
		}
		if ($.inArray(calendarDetails.type.value, $("#timecontrolTypes").val()) < 0) {
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
}
