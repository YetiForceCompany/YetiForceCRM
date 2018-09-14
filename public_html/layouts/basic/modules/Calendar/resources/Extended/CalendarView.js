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
					title: event.title + '<a href="index.php?module=' + event.module + '&view=Edit&record=' + event.id + '" class="btn btn-default btn-xs pull-right"><span class="fas fa-pencil"></span></a>' + '<a href="index.php?module=' + event.module + '&view=Detail&record=' + event.id + '" class="btn btn-default btn-xs pull-right"><span class="fas fa-th-list"></span></a>',
					container: 'body',
					html: true,
					placement: 'auto',
					template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
					content: '<div><span class="fas fa-time" aria-hidden="true"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
						'<div><span class="fas fa-time" aria-hidden="true"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
						(event.lok ? '<div><span class="fas fa-globe" aria-hidden="true"></span> <label>' + app.vtranslate('JS_LOCATION') + '</label>: ' + event.lok + '</div>' : '') +
						(event.pri ? '<div><span class="fas fa-warning-sign" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PRIORITY') + '</label>: ' + app.vtranslate('JS_' + event.pri) + '</div>' : '') +
						'<div><span class="fas fa-question-sign" aria-hidden="true"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>: ' + app.vtranslate('JS_' + event.sta) + '</div>' +
						(event.accname ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ACCOUNTS') + '</label>: ' + event.accname + '</div>' : '') +
						(event.linkexl ? '<div><span class="userIcon-' + event.linkexm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION_EXTEND') + '</label>: <a target="_blank" href="index.php?module=' + event.linkexm + '&view=Detail&record=' + event.linkextend + '">' + event.linkexl + '</a></div>' : '') +
						(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: <a target="_blank" href="index.php?module=' + event.linkm + '&view=Detail&record=' + event.link + '">' + event.linkl + '</a></div>' : '') +
						(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: <a target="_blank" href="index.php?module=' + event.procm + '&view=Detail&record=' + event.process + '">' + event.procl + '</a></div>' : '') +
						(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: <a target="_blank" href="index.php?module=' + event.subprocm + '&view=Detail&record=' + event.subprocess + '">' + event.subprocl + '</a></div>' : '') +
						(event.state ? '<div><span class="fas fa-star-empty" aria-hidden="true"></span> <label>' + app.vtranslate('JS_STATE') + '</label>: ' + app.vtranslate(event.state) + '</div>' : '') +
						'<div><span class="fas fa-eye-open" aria-hidden="true"></span> <label>' + app.vtranslate('JS_VISIBILITY') + '</label>: ' + app.vtranslate('JS_' + event.vis) + '</div>' +
						(event.smownerid ? '<div><span class="fas fa-user" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
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
	getDatesColumnView: function () {
		this.datesColumnView = jQuery('#datesColumn');
		return this.datesColumnView;
	},
	refreshDatesColumnView: function (calendarView) {
		var thisInstance = this;
		thisInstance.registerDatesColumn(calendarView);
	},
	registerDatesColumn: function (calendarView) {
		var thisInstance = this;
		var dateListUnit = calendarView.type;
		var subDateListUnit = 'week';

		if (dateListUnit === 'year') {
			subDateListUnit = 'year';
		} else if (dateListUnit === 'month') {
			subDateListUnit = 'month';
		} else if (dateListUnit === 'week') {
			subDateListUnit = 'week';
		} else if (dateListUnit === 'day') {
			subDateListUnit = 'day';
		}

		if (subDateListUnit === 'year') {
			thisInstance.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
			var datesView = thisInstance.getDatesColumnView();
			datesView.find('.subDateList').html('');
		} else if (subDateListUnit === 'month') {
			thisInstance.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
			thisInstance.generateSubMonthList(calendarView.intervalStart, calendarView.intervalEnd);
		} else if (subDateListUnit === 'week') {
			thisInstance.generateMonthList(calendarView.intervalStart, calendarView.intervalEnd);
			thisInstance.generateSubWeekList(calendarView.start, calendarView.end);
		} else if (subDateListUnit === 'day') {
			thisInstance.generateWeekList(calendarView.start, calendarView.end);
			thisInstance.generateSubDaysList(calendarView.start, calendarView.end);
		}
		thisInstance.updateCountTaskCalendar();
		thisInstance.registerDatesChange();
	},
	registerDatesChange: function () {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView().find('.dateRecord');
		datesView.on('click', function () {
			datesView.removeClass('dateActive');
			$(this).addClass('dateActive');
			var momentData = moment($(this).data('date') + '-01-01', "YYYY-MM-DD");
			thisInstance.getCalendarView().fullCalendar('gotoDate', momentData);
			var view = thisInstance.getCalendarView().fullCalendar('getView');
			thisInstance.refreshDatesColumnView(view);
			thisInstance.loadCalendarData();
		});
		var subDatesView = thisInstance.getDatesColumnView().find('.subRecord');
		subDatesView.on('click', function () {
			datesView.removeClass('subActive');
			$(this).addClass('subActive');
			var momentData = moment($(this).data('date'), "YYYY-MM-DD");
			thisInstance.getCalendarView().fullCalendar('gotoDate', momentData);
			var view = thisInstance.getCalendarView().fullCalendar('getView');
			thisInstance.refreshDatesColumnView(view);
			thisInstance.loadCalendarData();
		});
	},
	getTypesCalendar: function () {
		var thisInstance = this;
		var filterButtons = thisInstance.getSidebarView().find('.calendarFilters[data-selected="true"]');
		var types = [];
		filterButtons.each(function () {
			types.push($(this).data('type'));
		});
		return types;
	},
	getSelectedUsersCalendar: function () {
		var thisInstance = this;
		var selectedUsers = thisInstance.getSidebarView().find('.usersForm input:checked');
		var users = [];
		selectedUsers.each(function () {
			users.push($(this).val());
		});
		return users;
	},
	getSidebarView: function () {
		this.sidebarView = jQuery('#rightPanel');
		return this.sidebarView;
	},
	countEventsInRange: function (dateStart, dateEnd) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var types = thisInstance.getTypesCalendar();
		var user = thisInstance.getSelectedUsersCalendar();
		if (user.length == 0) {
			user = [app.getMainParams('current_user_id')];
		}
		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getCountEvents',
			start: dateStart,
			end: dateEnd,
			types: types,
			user: user,
			time: app.getMainParams('showType'),
		};
		AppConnector.request(params).then(function (events) {
			aDeferred.resolve(events.result);
		});
		return aDeferred.promise();
	},
	updateCountTaskCalendar: function () {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var subDatesElements = datesView.find('.subRecord');
		subDatesElements.each(function () {
			var thisElement = $(this);
			var data = $(this).data('date');
			var type = $(this).data('type');

			if (type == 'months') {
				thisInstance.countEventsInRange(moment(data).format('YYYY-MM') + '-01', moment(data).endOf('month').format('YYYY-MM-DD')).then(function (count) {
					thisElement.find('.countEvents').removeClass('hide').html(count);
				});
			} else if (type == 'weeks') {
				thisInstance.countEventsInRange(moment(data).format('YYYY-MM-DD'), moment(data).add(1, 'weeks').format('YYYY-MM-DD')).then(function (count) {
					thisElement.find('.countEvents').removeClass('hide').html(count);
				});
			} else if (type == 'days') {
				thisInstance.countEventsInRange(moment(data).format('YYYY-MM-DD'), moment(data).format('YYYY-MM-DD')).then(function (count) {
					thisElement.find('.countEvents').removeClass('hide').html(count);
				});
			}
		});
	},
	generateYearList: function (dateStart, dateEnd) {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var prevYear = moment(dateStart).subtract(1, 'year');
		var actualYear = moment(dateStart);
		var nextYear = moment(dateStart).add(1, 'year');
		var html = '';
		while (prevYear <= nextYear) {
			if (prevYear.format('YYYY') === actualYear.format('YYYY')) {
				var active = ' dateActive';
			} else {
				var active = '';
			}
			html += '<div class="dateRecord' + active + '" data-date="' + prevYear.format('YYYY') + '">' +
				prevYear.format('YYYY') +
				'</div>';
			prevYear = moment(prevYear).add(1, 'year');
		}
		datesView.find('.dateList').html(html);
	},
	generateMonthList: function (dateStart, dateEnd) {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var prevMonth = moment(dateStart).subtract(1, 'months');
		var actualMonth = moment(dateStart);
		var nextMonth = moment(dateStart).add(1, 'months');
		var html = '';
		while (prevMonth <= nextMonth) {
			if (prevMonth.format('YYYY-MM') === actualMonth.format('YYYY-MM')) {
				var active = ' dateActive';
			} else {
				var active = '';
			}
			html += '<div class="dateRecord' + active + '" data-date="' + prevMonth.format('YYYY-MM-DD') + '">' +
				prevMonth.format('MMMM') +
				'</div>';
			prevMonth = moment(prevMonth).add(1, 'months');
		}
		datesView.find('.dateList').html(html);
	},
	generateWeekList: function (dateStart, dateEnd) {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var prevMonth = moment(dateStart).subtract(1, 'week');
		var actualMonth = moment(dateStart);
		var nextMonth = moment(dateStart).add(1, 'week');
		var html = '';
		while (prevMonth <= nextMonth) {
			if (prevMonth.format('WW') === actualMonth.format('WW') && prevMonth.format('YYYY') === actualMonth.format('YYYY')) {
				var active = ' dateActive';
			} else {
				var active = '';
			}
			html += '<div class="dateRecord' + active + '" data-date="' + prevMonth.format('YYYY-MM-DD') + '">' +
				prevMonth.format('WW') +
				'</div>';
			prevMonth = moment(prevMonth).add(1, 'week');
		}
		datesView.find('.dateList').html(html);
	},
	loadCalendarData: function (allEvents) {
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		var thisInstance = this;
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var types = [];
		var user = [];
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var formatDate = CONFIG.dateFormat.toUpperCase();
		thisInstance.refreshDatesColumnView(view);
		types = thisInstance.getTypesCalendar();
		if (types.length == 0) {
			allEvents = true;
		}
		user = thisInstance.getSelectedUsersCalendar();
		if (user.length == 0) {
			user = [app.getMainParams('current_user_id')];
		}
		var filters = [];
		$(".calendarFilters .filterField").each(function (index) {
			var element = $(this);
			var name, value;
			if (element.attr('type') == 'checkbox') {
				name = element.val();
				value = element.prop('checked') ? 1 : 0;
			} else {
				name = element.attr('name');
				value = element.val();
			}
			filters.push({name: name, value: value});
		});
		if (allEvents == true || types.length > 0) {
			var params = {
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: view.start.format(formatDate),
				end: view.end.format(formatDate),
				user: user,
				time: app.getMainParams('showType'),
				types: types,
				filters: filters
			};
			AppConnector.request(params).then(function (events) {
				thisInstance.getCalendarView().fullCalendar('removeEvents');
				thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.progressIndicator({mode: 'hide'});
			});
		} else {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			progressInstance.progressIndicator({mode: 'hide'});
		}
	},
	generateSubMonthList: function (dateStart, dateEnd) {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var activeMonth = parseInt(moment(dateStart).locale('en').format('M')) - 1;
		var html = '';
		for (var month = 0; 12 > month; month++) {
			if (month === activeMonth) {
				var active = ' subActive';
			} else {
				var active = '';
			}
			html += '<div class="subRecord' + active + '" data-type="months" data-date="' + moment(dateStart).month(month).format('YYYY-MM') + '">' +
				'<div class="subDateName">' + app.vtranslate('JS_' + moment().month(month).format('MMM').toUpperCase()).toUpperCase() + '</div>' +
				'<div class="subDateCount">' +
				'<div class="countEvents">0</div>' +
				'</div>' +
				'</div>';
		}
		datesView.find('.subDateList').html(html);
	},
	generateSubWeekList: function (dateStart, dateEnd) {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var prevWeeks = moment(dateStart).subtract(5, 'weeks');
		var actualWeek = moment(dateStart).format('WW');
		var nextWeeks = moment(dateStart).add(5, 'weeks');
		var html = '';
		while (prevWeeks <= nextWeeks) {
			if (prevWeeks.format('WW') === actualWeek) {
				var active = ' subActive';
			} else {
				var active = '';
			}
			html += '<div class="subRecord' + active + '" data-type="weeks" data-date="' + prevWeeks.format('YYYY-MM-DD') + '">' +
				'<div class="subDateName">' + app.vtranslate('JS_WEEK') + ' ' + prevWeeks.format('WW') + '</div>' +
				'<div class="subDateCount">' +
				'<div class="countEvents">0</div>' +
				'</div>' +
				'</div>';
			prevWeeks = moment(prevWeeks).add(1, 'weeks');
		}
		datesView.find('.subDateList').html(html);
	},
	generateSubDaysList: function (dateStart, dateEnd) {
		var thisInstance = this;
		var datesView = thisInstance.getDatesColumnView();
		var prevDays = moment(dateStart).subtract(5, 'days');
		var actualDay = moment(dateStart).format('DDD');
		var nextDays = moment(dateStart).add(5, 'days');
		var daysToShow = nextDays.diff(prevDays, 'days');
		var html = '';
		for (var day = 0; day < daysToShow; day++) {
			if (prevDays.format('DDD') === actualDay) {
				var active = ' subActive';
			} else {
				var active = '';
			}
			html += '<div class="subRecord' + active + '" data-type="days" data-date="' + prevDays.format('YYYY-MM-DD') + '">' +
				'<div class="subDateName">' + app.vtranslate('JS_DAY') + ' ' + prevDays.format('DD') + '</div>' +
				'<div class="subDateCount">' +
				'<div class="countEvents">0</div>' +
				'</div>' +
				'</div>';
			prevDays = moment(prevDays).add(1, 'days');
		}
		datesView.find('.subDateList').html(html);
	},
	registerLoadCalendarData: function () {
		var thisInstance = this;
		thisInstance.loadCalendarData(true);
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
