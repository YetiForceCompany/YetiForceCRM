/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 *  Class representing a time control calendar.
 * @extends Calendar_Unselectable_Js
 */
window.OSSTimeControl_Calendar_Js = class extends Calendar_Unselectable_Js {

	constructor() {
		super();
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
}
