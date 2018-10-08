/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Reservations_Calendar_Js = class Reservations_Calendar_Js extends BasicCalendar_Js {

	constructor() {
		super();
	}

	addCalendarEvent(calendarDetails, dateFormat) {
		if ($("#calendarUserList").val().length && $.inArray(calendarDetails.assigned_user_id.value, $("#calendarUserList").val()) < 0) {
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
			url: 'index.php?module=Reservations&view=Detail&record=' + calendarDetails._recordId,
			className: 'ownerCBg_' + calendarDetails.assigned_user_id.value + ' picklistCBg_OSSTimeControl_timecontrol_type_' + calendarDetails.type.value,
			totalTime: calendarDetails.sum_time.display_value,
			type: calendarDetails.type.display_value,
		};
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
	}

	eventRenderer(event, element) {
		let self = this;
		app.showPopoverElementView(element.find('.fc-content'), {
			title: event.title + '<a href="index.php?module=Reservations&view=Edit&record=' + event.id + '" class="float-right"><span class="fas fa-edit"></span></a>' + '<a href="index.php?module=Reservations&view=Detail&record=' + event.id + '" class="float-right mx-1"><span class="fas fa-th-list"></span></a>',
			container: 'body',
			html: true,
			placement: 'auto',
			template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			content: '<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
				'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
				'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_TOTAL_TIME') + '</label>: ' + event.totalTime + '</div>' +
				'<div><span class="fas fa-question-circle"></span> <label>' + app.vtranslate('JS_TYPE') + '</label>: ' + event.type + '</div>' +
				(event.status ? '<div><span class="far fa-star"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>: <span class="picklistCT_Reservations_reservations_status_' + event.status + '">' + app.vtranslate(event.status) + '</span></div>' : '') +
				(event.company ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_COMPANY') + '</label>: <span class="modCT_Accounts">' + event.company + '</span></div>' : '') +
				(event.process ? '<div><span class="userIcon-' + event.processType + '" aria-hidden="true"></span> <label>' + event.processLabel + '</label>: <a class="modCT_' + event.processType + '" target="_blank" href="index.php?module=' + event.processType + '&view=Detail&record=' + event.processId + '">' + event.process + '</a></div>' : '') +
				(event.smownerid ? '<div><span class="fas fa-user"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
		});
	}
}
