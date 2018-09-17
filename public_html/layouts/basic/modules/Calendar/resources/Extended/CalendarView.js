/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Calendar_CalendarView_Js('Calendar_CalendarExtendedView_Js', {}, {
	/**
	 * Calendar scroll
	 */
	registerCalendarScroll() {
		app.showScrollBar($('.js-calendar--scroll'), {
			railVisible: true,
			alwaysVisible: true,
			position: 'left'
		});
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
				thisInstance.actionOnRender(event, element);
			},
			eventClick: function (calEvent, jsEvent, view) {
				jsEvent.preventDefault();
				let link = new URL($(this)[0].href);
				let url = 'index.php?module=Calendar&view=ActivityState&record=' +
					link.searchParams.get("record");
				thisInstance.showStatusUpdate(url);
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
	showStatusUpdate(params) {
		const thisInstance = this;
		AppConnector.request(params).then((data) => {
			let sideBar = thisInstance.getSidebarView();
			sideBar.find('.qcForm').html(data);
			thisInstance.showRightPanelForm();
			sideBar.find('.js-activity-state .summaryCloseEdit').on('click', function () {
				thisInstance.getCalendarCreateView();
			});
			sideBar.find('.js-activity-state .editRecord').on('click', function () {
				thisInstance.getCalendarEditView($(this).data('id'));
			});
		});
	},
	actionOnRender(event, element) {
		const thisInstance = this;
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
	},
	getDatesColumnView: function () {
		this.datesColumnView = $('#datesColumn');
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
		thisInstance.registerFilterTabChange();
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
	getCurrentCvId: function () {
		return $(".js-calendar-extended-filter-tab .active").parent('.js-filter-tab').data('cvid');
	},
	registerFilterTabChange: function () {
		const thisInstance = this;
		$(".js-calendar-extended-filter-tab").on('shown.bs.tab', function () {
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
		this.sidebarView = $('#rightPanel');
		return this.sidebarView;
	},
	countEventsInRange: function (dateStart, dateEnd) {
		var thisInstance = this;
		var aDeferred = $.Deferred();
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
			cvid: thisInstance.getCurrentCvId()
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
		const thisInstance = this;
		const datesView = thisInstance.getDatesColumnView();
		let prevYear = moment(dateStart).subtract(1, 'year');
		let actualYear = moment(dateStart);
		let nextYear = moment(dateStart).add(1, 'year');
		let html = '';
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
		const thisInstance = this;
		const datesView = thisInstance.getDatesColumnView();
		let prevMonth = moment(dateStart).subtract(1, 'months');
		let actualMonth = moment(dateStart);
		let nextMonth = moment(dateStart).add(1, 'months');
		let html = '';
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
		const thisInstance = this;
		const datesView = thisInstance.getDatesColumnView();
		let prevMonth = moment(dateStart).subtract(1, 'week');
		let actualMonth = moment(dateStart);
		let nextMonth = moment(dateStart).add(1, 'week');
		let html = '';
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
	loadCalendarEditView: function (id) {
		var aDeferred = $.Deferred();
		var params = {
			'module': app.getModuleName(),
			'view': 'EventForm',
			'record': id
		};
		AppConnector.request(params).then(
			function (data) {
				aDeferred.resolve($(data));
			},
			function () {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	getCalendarEditView(id) {
		const thisInstance = this;
		const aDeferred = $.Deferred();
		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		const progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		this.loadCalendarEditView(id).then(
			function (data) {
				progressInstance.progressIndicator({mode: 'hide'});
				var sideBar = thisInstance.getSidebarView();
				thisInstance.showRightPanelForm();
				sideBar.find('.qcForm').html(data);
				var rightFormCreate = $(document).find('form[name="QuickCreate"]');
				var moduleName = sideBar.find('[name="module"]').val();
				var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
				var headerInstance = new Vtiger_Header_Js();
				editViewInstance.registerBasicEvents(rightFormCreate);
				rightFormCreate.validationEngine(app.validationEngineOptions);
				headerInstance.registerHelpInfo(rightFormCreate);
				thisInstance.registerSelect2();
				thisInstance.registerSubmitForm();
				var rightFormStatus = sideBar.find('.summaryCloseEdit');
				rightFormStatus.on('click', function () {
					thisInstance.getCalendarCreateView();
				});
				headerInstance.registerQuickCreateSidebarPostLoadEvents(rightFormCreate, {});
				var customConfig = {
					height: '5em',
					toolbar: 'Min'
				};
				$.each(sideBar.find('.ckEditorSource'), function (key, element) {
					var ckEditorInstance = new Vtiger_CkEditor_Js();
					ckEditorInstance.loadCkEditor($(element), customConfig);
				});
				aDeferred.resolve(sideBar.find('.qcForm'));
			},
			function () {
				progressInstance.progressIndicator({mode: 'hide'});
			}
		);
		return aDeferred.promise();
	},
	loadCalendarData: function (allEvents) {
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		const thisInstance = this;
		const view = thisInstance.getCalendarView().fullCalendar('getView');
		let types = [];
		let user = [];
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		let formatDate = CONFIG.dateFormat.toUpperCase();
		thisInstance.refreshDatesColumnView(view);
		types = thisInstance.getTypesCalendar();
		if (types.length == 0) {
			allEvents = true;
		}
		user = thisInstance.getSelectedUsersCalendar();
		if (user.length == 0) {
			user = [app.getMainParams('current_user_id')];
		}
		let filters = [];
		$(".calendarFilters .filterField").each(function (index) {
			let element = $(this);
			let name, value;
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
			AppConnector.request({
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: view.start.format(formatDate),
				end: view.end.format(formatDate),
				user: user,
				time: app.getMainParams('showType'),
				types: types,
				filters: filters,
				cvid: thisInstance.getCurrentCvId()
			}).then(function (events) {
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
		let thisInstance = this;
		let datesView = thisInstance.getDatesColumnView();
		let activeMonth = parseInt(moment(dateStart).locale('en').format('M')) - 1;
		let html = '';
		for (let month = 0; 12 > month; ++month) {
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
		let thisInstance = this;
		let datesView = thisInstance.getDatesColumnView();
		let prevWeeks = moment(dateStart).subtract(5, 'weeks');
		let actualWeek = moment(dateStart).format('WW');
		let nextWeeks = moment(dateStart).add(5, 'weeks');
		let html = '';
		while (prevWeeks <= nextWeeks) {
			let active = '';
			if (prevWeeks.format('WW') === actualWeek) {
				active = ' subActive';
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
	generateSubDaysList(dateStart, dateEnd) {
		const thisInstance = this;
		let datesView = thisInstance.getDatesColumnView();
		let prevDays = moment(dateStart).subtract(5, 'days');
		let actualDay = moment(dateStart).format('DDD');
		let nextDays = moment(dateStart).add(5, 'days');
		let daysToShow = nextDays.diff(prevDays, 'days');
		let html = '';
		for (let day = 0; day < daysToShow; ++day) {
			let active = '';
			if (prevDays.format('DDD') === actualDay) {
				active = ' subActive';
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
	selectDays(startDate, endDate) {
		const thisInstance = this;
		let start_hour = $('#start_hour').val();
		let end_hour = $('#end_hour').val();
		if (endDate.hasTime() == false) {
			endDate.add(-1, 'days');
		}
		startDate = startDate.format();
		endDate = endDate.format();
		let view = thisInstance.getCalendarView().fullCalendar('getView');
		if (start_hour == '') {
			start_hour = '00';
		}
		if (end_hour == '') {
			end_hour = '00';
		}
		this.getCalendarCreateView().then(function (data) {
			if (data.length <= 0) {
				return;
			}
			if (view.name != 'agendaDay' && view.name != 'agendaWeek') {
				startDate = startDate + 'T' + start_hour + ':00';
				endDate = endDate + 'T' + start_hour + ':00';
				if (startDate == endDate) {
					let defaulDuration = 0;
					if (data.find('[name="activitytype"]').val() == 'Call') {
						defaulDuration = data.find('[name="defaultCallDuration"]').val();
					} else {
						defaulDuration = data.find('[name="defaultOtherEventDuration"]').val();
					}
					endDate = moment(endDate).add(defaulDuration, 'minutes').toISOString();
				}
			}
			let dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase();
			let timeFormat = data.find('[name="time_start"]').data('format');
			let defaultTimeFormat = '';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm A';
			}
			data.find('[name="date_start"]').val(moment(startDate).format(dateFormat));
			data.find('[name="due_date"]').val(moment(endDate).format(dateFormat));
			data.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
			data.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
		});
	},
	registerUsersChange: function () {
		const thisInstance = this;
		thisInstance.getSidebarView().find('.usersForm input[type=checkbox]').on('click', () => {
			thisInstance.loadCalendarData();
		});
	},
	addCalendarEvent(calendarDetails) {
		let calendar = this.getCalendarView();
		let startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value);
		let endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
		let eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails.subject.display_value,
			start: startDate.toString(),
			end: endDate.toString(),
			url: 'index.php?module=Calendar&view=Detail&record=' + calendarDetails._recordId,
			activitytype: calendarDetails.activitytype.value,
			allDay: calendarDetails.allday.value == 'on',
			state: calendarDetails.state.value,
			vis: calendarDetails.visibility.value,
			sta: calendarDetails.activitystatus.value,
			className: 'ownerCBg_' + calendarDetails.assigned_user_id.value + ' picklistCBr_Calendar_activitytype_' + calendarDetails.activitytype.value,
			start_display: calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value,
			end_display: calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value,
			smownerid: calendarDetails.assigned_user_id.display_value,
			pri: calendarDetails.taskpriority.value,
			lok: calendarDetails.location.display_value
		};
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
	},
	registerSubmitForm: function () {
		const thisInstance = this;
		$(document).find('form[name="QuickCreate"]').find('.save').on('click', (e) => {
			if ($(this).parents('form:first').validationEngine('validate')) {
				let formData = $(e.currentTarget).parents('form:first').serializeFormData();
				AppConnector.request(formData).then((data) => {
						if (data.success) {
							let textToShow = '';
							if (formData.record) {
								thisInstance.updateCalendarEvent(formData.record, data.result);
								textToShow = app.vtranslate('JS_TASK_IS_SUCCESSFULLY_UPDATED_IN_YOUR_CALENDAR');
							} else {
								thisInstance.addCalendarEvent(data.result);
								textToShow = app.vtranslate('JS_TASK_IS_SUCCESSFULLY_ADDED_TO_YOUR_CALENDAR');
							}
							thisInstance.getCalendarCreateView();
							Vtiger_Helper_Js.showPnotify({
								text: textToShow,
								type: 'success',
								animation: 'show'
							});
						}
					}
				);
			}
		});
	},
	showRightPanelForm() {
		if ($('.calendarRightPanel').hasClass('hideSiteBar')) {
			$('.toggleSiteBarRightButton').trigger('click');
		}
		if (!$('#rightPanelEvent').hasClass('active')) {
			$('a[href="#rightPanelEvent"]').trigger('click');
		}
	},
	loadCalendarCreateView() {
		let aDeferred = $.Deferred();
		AppConnector.request({
			'module': app.getModuleName(),
			'view': 'EventForm',
		}).then(
			(data) => {
				aDeferred.resolve($(data));
			},
			() => {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	getCalendarCreateView() {
		const thisInstance = this;
		let aDeferred = $.Deferred();
		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		this.loadCalendarCreateView().then(
			(data) => {
				let sideBar = thisInstance.getSidebarView();
				progressInstance.progressIndicator({mode: 'hide'});
				thisInstance.showRightPanelForm();
				sideBar.find('.qcForm').html(data);
				let rightFormCreate = $(document).find('form[name="QuickCreate"]');
				let moduleName = sideBar.find('[name="module"]').val();
				let editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
				let headerInstance = new Vtiger_Header_Js();
				App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
				editViewInstance.registerBasicEvents(rightFormCreate);
				rightFormCreate.validationEngine(app.validationEngineOptions);
				headerInstance.registerHelpInfo(rightFormCreate);
				thisInstance.registerSubmitForm();
				headerInstance.registerQuickCreateSidebarPostLoadEvents(rightFormCreate, {});
				$.each(sideBar.find('.ckEditorSource'), function (key, element) {
					var ckEditorInstance = new Vtiger_CkEditor_Js();
					ckEditorInstance.loadCkEditor($(element), {
						height: '5em',
						toolbar: 'Min'
					});
				});
				aDeferred.resolve(sideBar.find('.qcForm'));
			},
			() => {
				progressInstance.progressIndicator({mode: 'hide'});
			}
		);
		return aDeferred.promise();
	},
	/**
	 * Overwriting the parent function
	 */
	registerAddButton() {
	},
	/**
	 * Register load calendar data
	 */
	registerLoadCalendarData() {
		this.loadCalendarData(true);
	},
	registerAddForm: function () {
		const thisInstance = this;
		let sideBar = thisInstance.getSidebarView();
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getUsersList').then(
			function (data) {
				if (data) {
					sideBar.find('.usersForm').html(data);
					thisInstance.registerUsersChange();
				}
			}
		);
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getGroupsList').then(
			function (data) {
				if (data) {
					sideBar.find('.groupForm').html(data);
					thisInstance.registerUsersChange();
				}
			}
		);
		thisInstance.getSidebarView().slimScroll({
			width: '',
			height: ''
		});
	},
	/**
	 * Register events
	 */
	registerEvents() {
		this._super();
		this.registerAddForm();
	}
});
