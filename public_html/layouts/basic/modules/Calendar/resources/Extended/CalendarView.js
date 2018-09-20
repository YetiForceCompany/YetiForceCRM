/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Calendar_CalendarView_Js('Calendar_CalendarExtendedView_Js', {
	getInstanceByView() {
		let view = $('#currentView').val();
		let jsFileName = view + 'View';
		let moduleClassName = view + "_" + jsFileName + "_Js";
		let instance;
		if (typeof window[moduleClassName] !== "undefined") {
			instance = new window[moduleClassName]();
		} else {
			instance = new Calendar_CalendarExtendedView_Js();
		}
		return instance;
	}
}, {
	/**
	 * Dates left side
	 * @returns {*}
	 */
	getDatesColumnView() {
		this.datesColumnView = $('#datesColumn');
		return this.datesColumnView;
	},
	/**
	 * Calendar scroll
	 */
	registerCalendarScroll() {
		app.showScrollBar($('.bodyContents'), {
			railVisible: true,
			alwaysVisible: true,
			position: 'left'
		});
	},
	/**
	 * Register load calendar data
	 */
	registerLoadCalendarData() {
		this.loadCalendarData(true);
	},
	/**
	 * Render calendar
	 */
	renderCalendar() {
		const thisInstance = this;
		let eventLimit = app.getMainParams('eventLimit');
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		let weekView = app.getMainParams('weekView');
		let dayView = app.getMainParams('dayView');

		//User preferred default view
		let userDefaultActivityView = app.getMainParams('activity_view');
		if (userDefaultActivityView == 'Today') {
			userDefaultActivityView = dayView;
		} else if (userDefaultActivityView == 'This Week') {
			userDefaultActivityView = weekView;
		} else {
			userDefaultActivityView = 'month';
		}
		let defaultView = app.moduleCacheGet('defaultView');
		if (defaultView != null) {
			userDefaultActivityView = defaultView;
		}
		thisInstance.getDatesColumnView().find('.subDateList').data('type', userDefaultActivityView);

		//Default time format
		let userDefaultTimeFormat = app.getMainParams('time_format');
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}

		//Default first day of the week
		let convertedFirstDay = CONFIG.firstDayOfWeekNo;

		//Default first hour of the day
		let defaultFirstHour = app.getMainParams('start_hour') + ':00';
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		let options = {
			header: {
				left: 'year,month,' + weekView + ',' + dayView,
				center: 'prev,title,next',
				right: 'today'
			},

			axisFormat: userDefaultTimeFormat,
			scrollTime: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: true,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			eventLimitText: app.vtranslate('JS_MORE'),
			selectable: true,
			selectHelper: true,
			hiddenDays: hiddenDays,
			height: 'auto',
			views: {
				basic: {
					eventLimit: false,
				},
				year: {
					eventLimit: 10,
					eventLimitText: app.vtranslate('JS_COUNT_RECORDS')
				},
				basicDay: {
					type: 'agendaDay'
				}
			},
			select: function (start, end) {
				thisInstance.selectDays(start, end);
				thisInstance.getCalendarView().fullCalendar('unselect');
			},
			eventDrop: function (event, delta, revertFunc) {
				thisInstance.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				thisInstance.updateEvent(event, delta, revertFunc);
			},
			eventRender: function (event, element) {
				app.showPopoverElementView(element.find('.fc-content'), {
					title: event.title + '<a href="index.php?module=' + event.module + '&view=Edit&record=' + event.id + '" class="btn btn-default btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span></a>' + '<a href="index.php?module=' + event.module + '&view=Detail&record=' + event.id + '" class="btn btn-default btn-xs pull-right"><span class="glyphicon glyphicon-th-list"></span></a>',
					container: 'body',
					html: true,
					placement: 'auto',
					template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
					content: '<div><span class="glyphicon glyphicon-time" aria-hidden="true"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
					'<div><span class="glyphicon glyphicon-time" aria-hidden="true"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
					(event.lok ? '<div><span class="glyphicon glyphicon-globe" aria-hidden="true"></span> <label>' + app.vtranslate('JS_LOCATION') + '</label>: ' + event.lok + '</div>' : '') +
					(event.pri ? '<div><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PRIORITY') + '</label>: ' + app.vtranslate('JS_' + event.pri) + '</div>' : '') +
					'<div><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>: ' + app.vtranslate('JS_' + event.sta) + '</div>' +
					(event.accname ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ACCOUNTS') + '</label>: ' + event.accname + '</div>' : '') +
					(event.linkexl ? '<div><span class="userIcon-' + event.linkexm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION_EXTEND') + '</label>: <a target="_blank" href="index.php?module=' + event.linkexm + '&view=Detail&record=' + event.linkextend + '">' + event.linkexl + '</a></div>' : '') +
					(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: <a target="_blank" href="index.php?module=' + event.linkm + '&view=Detail&record=' + event.link + '">' + event.linkl + '</a></div>' : '') +
					(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: <a target="_blank" href="index.php?module=' + event.procm + '&view=Detail&record=' + event.process + '">' + event.procl + '</a></div>' : '') +
					(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: <a target="_blank" href="index.php?module=' + event.subprocm + '&view=Detail&record=' + event.subprocess + '">' + event.subprocl + '</a></div>' : '') +
					(event.state ? '<div><span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span> <label>' + app.vtranslate('JS_STATE') + '</label>: ' + app.vtranslate(event.state) + '</div>' : '') +
					'<div><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <label>' + app.vtranslate('JS_VISIBILITY') + '</label>: ' + app.vtranslate('JS_' + event.vis) + '</div>' +
					(event.smownerid ? '<div><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
				});
				element.find('.fc-content, .fc-info').click(function () {
					var event = $(this).closest('.fc-event');
					var url = 'index.php?module=Calendar&view=ActivityState&record=' + event.data('id');
					thisInstance.showStatusUpdate(url);
				});
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
				year: app.vtranslate('JS_YEAR'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY'),
		};
		if (app.moduleCacheGet('start') != null) {
			var s = moment(app.moduleCacheGet('start')).valueOf();
			var e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
		}
		thisInstance.getCalendarView().fullCalendar('destroy');
		thisInstance.getCalendarView().fullCalendar(options);
		thisInstance.registerCalendarScroll();
	},
	/**
	 * Register events
	 */
	registerEvents() {
		this._super();
	}
});
/**
 * Create instance of Calendar_CalendarExtendedView_Js
 */
$(document).ready(function () {
	let instance = Calendar_CalendarExtendedView_Js.getInstanceByView();
	instance.registerEvents();
	Calendar_CalendarExtendedView_Js.currentInstance = instance;
});
