/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Calendar_CalendarView_Js", {
	currentInstance: false,
	getInstanceByView: function () {
		var view = jQuery('#currentView').val();
		var jsFileName = view + 'View';
		var moduleClassName = view + "_" + jsFileName + "_Js";
		if (typeof window[moduleClassName] != 'undefined') {
			var instance = new window[moduleClassName]();
		} else {
			instance = new Calendar_CalendarView_Js();
		}
		return instance;
	},
	registerUserListWidget: function () {
		var thisInstance = this.getInstanceByView();
		var widgetContainer = $('#Calendar_sideBar_LBL_ACTIVITY_TYPES');
		widgetContainer.hover(
			function () {
				$(this).css('overflow','visible');
			}, function () {
				$(this).css('overflow','hidden');
			}
		);
		app.changeSelectElementView(widgetContainer);
		widgetContainer.find(".refreshCalendar").click(function () {
			thisInstance.loadCalendarData();
		});
	}
}, {
	calendarView: false,
	calendarCreateView: false,
	weekDaysArray: {Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3, Thursday: 4, Friday: 5, Saturday: 6},
	registerCalendar: function () {
		var thisInstance = this;
		//User preferred default view
		var userDefaultActivityView = jQuery('#activity_view').val();
		if (userDefaultActivityView == 'Today') {
			userDefaultActivityView = 'agendaDay';
		} else if (userDefaultActivityView == 'This Week') {
			userDefaultActivityView = 'agendaWeek';
		} else {
			userDefaultActivityView = 'month';
		}

		//Default time format
		var userDefaultTimeFormat = jQuery('#time_format').val();
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm)tt';
		}

		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		var convertedFirstDay = thisInstance.weekDaysArray[defaultFirstDay];

		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];

		thisInstance.getCalendarView().fullCalendar({
			header: {
				left: 'month,agendaWeek,agendaDay',
				center: 'title today',
				right: 'prev,next'
			},
			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			firstHour: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: true,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: true,
			selectable: true,
			selectHelper: true,
			select: function (start, end) {
				thisInstance.selectDays(start.format(), end.format());
				thisInstance.getCalendarView().fullCalendar('unselect');
			},
			eventDrop: function (event, delta, revertFunc) {
				thisInstance.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				thisInstance.updateEvent(event, delta, revertFunc);
			},
			monthNames: [app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
				app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
				app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
			monthNamesShort: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')],
			dayNames: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')],
			dayNamesShort: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')],
			buttonText: {
				today: app.vtranslate('JS_TODAY'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY'),
			eventLimitText: app.vtranslate('JS_MORE')
		});
	},
	loadCalendarData: function (allEvents) {
		var progressInstance = jQuery.progressIndicator();
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date = view.end.format();
		if (jQuery('#calendarUserList').length == 0) {
			var user = jQuery('#current_user_id').val();
		} else {
			var user = jQuery('#calendarUserList').val();
		}
		if (jQuery('#calendarTypes').length > 0) {
			var types = jQuery('#calendarTypes').val();	
		}
		
		if(allEvents == true || types != null){
			var params = {
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: start_date,
				end: end_date,
				user: user,
				types: types
			}
			AppConnector.request(params).then(function (events) {
				thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.hide();
			});
		} else {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			progressInstance.hide();
		}
	},
	updateEvent: function (event, delta, revertFunc) {
		var progressInstance = jQuery.progressIndicator();
		var start = event.start.format();

		if(event.allDay){
			var end = start;
		}else if(event.end != null){
			var end = event.end.format();
		}else{
			var endDate = Date.parse(start);
			endDate.addMinutes('60');
			var end = endDate.toString('yyyy-mm-ddTHH:mm');
		}
		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			end: end,
			allDay: event.allDay
		}
		AppConnector.request(params).then(function (response) {
			progressInstance.hide();
			if (!response['result']) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			}
		},
		function (error) {
			progressInstance.hide();
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
			revertFunc();
		});
	},
	selectDays: function (start, end) {
		var thisInstance = this;
		this.getCalendarCreateView().then(function (data) {
			if (data.length <= 0) {
				return;
			}
				
			var dateFormat = data.find('[name="date_start"]').data('dateFormat');
			var timeFormat = data.find('[name="time_start"]').data('format');
			if(timeFormat == 24){
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm tt';
			}
			var startDateInstance = Date.parse(start);
			var startDateString = app.getDateInVtigerFormat(dateFormat, startDateInstance);
			var startTimeString = startDateInstance.toString(defaultTimeFormat);
			var endDateInstance = Date.parse(end);
			var endDateString = app.getDateInVtigerFormat(dateFormat, endDateInstance);
			var endTimeString = endDateInstance.toString(defaultTimeFormat);

			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);
			data.find('.tabbable').before( '<input type="hidden" name="selectedTimeStart" value="'+endTimeString+'">' );

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
				thisInstance.addCalendarEvent(data.result);
			}});
			jQuery('.modal-body').css({'max-height': '500px', 'overflow-y': 'auto'});
		});
	},
	addCalendarEvent: function (calendarDetails) {
		var isAllowed = this.isAllowedToAddCalendarEvent(calendarDetails);
		if (isAllowed == false)
			return;

		var eventObject = {};
		eventObject.id = calendarDetails._recordId;
		eventObject.title = calendarDetails.name.display_value;
		var startDate = Date.parse(calendarDetails.date_start.display_value + 'T' + calendarDetails.time_start.display_value);
		eventObject.start = startDate.toString();
		var endDate = Date.parse(calendarDetails.due_date.display_value + 'T' + calendarDetails.time_end.display_value);
		var assignedUserId = calendarDetails.assigned_user_id.value;
		eventObject.end = endDate.toString();
		eventObject.url = 'index.php?module=Calendar&view=Detail&record=' + calendarDetails._recordId;
		eventObject.activitytype = calendarDetails.activitytype.value;
		if (calendarDetails.activitytype.value == 'Task') {
			eventObject.allDay = true;
			eventObject.status = calendarDetails.taskstatus.value;
		} else {
			eventObject.status = calendarDetails.eventstatus.value;
			eventObject.allDay = false;
		}
		eventObject.className = 'userColor_' + calendarDetails.assigned_user_id.value;
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
	},
	isAllowedToAddCalendarEvent: function (calendarDetails) {
		var activityType = calendarDetails.activitytype.value;
		console.log(activityType);
		if(activityType == 'Calendar'  && jQuery('[data-calendar-feed="Calendar"]').is(':checked')) {
			return true;
		} else if(jQuery('[data-calendar-feed="Events"]').is(':checked')){
			return true;
		} else {
			return false;
		}
	},
	getCalendarCreateView: function () {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		var progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView().then(
			function (data) {
				progressInstance.hide();
				thisInstance.calendarCreateView = data;
				aDeferred.resolve(data.clone(true, true));
			},
			function () {
				progressInstance.hide();
			}
		);
		return aDeferred.promise();
	},
	loadCalendarCreateView: function () {
		var aDeferred = jQuery.Deferred();
		var quickCreateCalendarElement = jQuery('#quickCreateModules').find('[data-name="Calendar"]');
		var url = quickCreateCalendarElement.data('url');
		var name = quickCreateCalendarElement.data('name');
		var headerInstance = new Vtiger_Header_Js();
		headerInstance.getQuickCreateForm(url, name).then(
			function (data) {
				aDeferred.resolve(jQuery(data));
			},
			function () {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	getCalendarView: function () {
		if (this.calendarView == false) {
			this.calendarView = jQuery('#calendarview');
		}
		return this.calendarView;
	},
	registerChangeView: function () {
		var thisInstance = this;
		thisInstance.getCalendarView().find("button.fc-button").click(function () {
			thisInstance.loadCalendarData();
		});
	},
	createAddButton: function () {
		var thisInstance = this;
		var calendarview = this.getCalendarView();
		jQuery('<span class="pull-left"><button class="btn addButton">' + app.vtranslate('JS_ADD_EVENT_TASK') + '</button></span>')
			.prependTo(calendarview.find('.fc-toolbar .fc-right')).on('click', 'button', function (e) {
			thisInstance.getCalendarCreateView().then(function (data) {
				var headerInstance = new Vtiger_Header_Js();
				headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
					thisInstance.addCalendarEvent(data.result);
				}});
			});
		})
	},
	registerEvents: function () {
		this.registerCalendar();
		this.createAddButton();
		this.loadCalendarData(true);
		this.registerChangeView();
	}
});
jQuery(document).ready(function () {
	var instance = Calendar_CalendarView_Js.getInstanceByView();
	instance.registerEvents()
	Calendar_CalendarView_Js.currentInstance = instance;
})