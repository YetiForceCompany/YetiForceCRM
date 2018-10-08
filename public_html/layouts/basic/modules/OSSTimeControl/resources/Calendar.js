/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.OSSTimeControl_Calendar_Js = class OSSTimeControl_Calendar_Js extends BasicCalendar_Js {

	constructor() {
		super();
	}

	getCalendarModuleOptions() {
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
		if (usersList.length === 0) {
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

	eventRenderer(event, element) {
		app.showPopoverElementView(element.find('.fc-content'), {
			title: event.title + '<a href="index.php?module=OSSTimeControl&view=Edit&record=' + event.id + '" class="float-right"><span class="fas fa-edit"></span></a>' + '<a href="index.php?module=OSSTimeControl&view=Detail&record=' + event.id + '" class="float-right mx-1"><span class="fas fa-th-list"></span></a>',
			container: 'body',
			html: true,
			placement: 'auto',
			template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			content: '<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
				'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
				'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_TOTAL_TIME') + '</label>: ' + event.totalTime + '</div>' +
				'<div><span class="fas fa-bars"></span> <label>' + app.vtranslate('JS_NUMBER') + '</label>: ' + event.number + '</div>' +
				'<div><span class="fas fa-question-circle"></span> <label>' + app.vtranslate('JS_TYPE') + '</label>: <span class="picklistCT_OSSTimeControl_timecontrol_type_' + event.type_value + '">' + event.type + '</span></div>' +
				(event.status ? '<div><span class="far fa-star"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>: <span class="picklistCT_OSSTimeControl_osstimecontrol_status_' + event.sta + '">' + event.status + '</span></div>' : '') +
				(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: <a class="modCT_' + event.linkm + '" target="_blank" href="index.php?module=' + event.linkm + '&view=Detail&record=' + event.link + '">' + event.linkl + '</a></div>' : '') +
				(event.linkexl ? '<div><span class="userIcon-' + event.linkexm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION_EXTEND') + '</label>: <a class="modCT_' + event.linkexm + '" target="_blank" href="index.php?module=' + event.linkexm + '&view=Detail&record=' + event.linkextend + '">' + event.linkexl + '</a></div>' : '') +
				(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: <a class="modCT_' + event.procm + '" target="_blank" href="index.php?module=' + event.procm + '&view=Detail&record=' + event.process + '">' + event.procl + '</a></div>' : '') +
				(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: <a class="modCT_' + event.subprocm + '" target="_blank" href="index.php?module=' + event.subprocm + '&view=Detail&record=' + event.subprocess + '">' + event.subprocl + '</a></div>' : '') +
				(event.smownerid ? '<div><span class="fas fa-user"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
		});
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
