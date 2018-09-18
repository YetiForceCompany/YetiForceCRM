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
		let eventLimit = app.getMainParams('eventLimit'),
			weekView = app.getMainParams('weekView'),
			dayView = app.getMainParams('dayView'),
			userDefaultActivityView = app.getMainParams('activity_view'),
			defaultView = app.moduleCacheGet('defaultView'),
			userDefaultTimeFormat = app.getMainParams('time_format'),
			convertedFirstDay = CONFIG.firstDayOfWeekNo,
			defaultFirstHour = app.getMainParams('start_hour') + ':00',
			hiddenDays = [];
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		if (userDefaultActivityView === 'Today') {
			userDefaultActivityView = dayView;
		} else if (userDefaultActivityView === 'This Week') {
			userDefaultActivityView = weekView;
		} else {
			userDefaultActivityView = 'month';
		}
		if (defaultView != null) {
			userDefaultActivityView = defaultView;
		}
		thisInstance.getDatesColumnView().find('.subDateList').data('type', userDefaultActivityView);
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		let options = {
			header: {
				left: 'year,month,' + weekView + ',' + dayView,
				center: 'prevYear,prev,title,next,nextYear',
				right: 'today'
			},
			axisFormat: userDefaultTimeFormat,
			scrollTime: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: 'year',
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
					eventLimitText: app.vtranslate('JS_COUNT_RECORDS'),
					titleFormat: 'YYYY'
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
				let link = new URL($(this)[0].href),
					url = 'index.php?module=Calendar&view=ActivityState&record=' +
						link.searchParams.get("record");
				thisInstance.showStatusUpdate(url);
			},
			viewRender: function (view, element) {
				let toolbar = element.closest('#calendarview').find('.fc-toolbar.fc-header-toolbar');
				let nextPrevButtons = toolbar.find('.fc-prev-button, .fc-next-button');
				let yearButtons = toolbar.find('.fc-prevYear-button, .fc-nextYear-button');
				if (view.name === 'year') {
					nextPrevButtons.hide();
					yearButtons.show();
				} else {
					nextPrevButtons.show();
					yearButtons.hide();
				}
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
			let s = moment(app.moduleCacheGet('start')).valueOf(),
				e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
		}
		thisInstance.getCalendarView().fullCalendar('destroy');
		thisInstance.getCalendarView().fullCalendar(options);
		thisInstance.registerCalendarScroll();
	},
	showStatusUpdate(params) {
		const thisInstance = this,
			progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		AppConnector.request(params).then((data) => {
			progressInstance.progressIndicator({mode: 'hide'});
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
		const thisInstance = this;
		thisInstance.registerDatesColumn(calendarView);
	},
	registerDatesColumn: function (calendarView) {
		const thisInstance = this;
		let dateListUnit = calendarView.type,
			subDateListUnit = 'week';
		if ('year' === dateListUnit) {
			subDateListUnit = 'year';
		} else if ('month' === dateListUnit) {
			subDateListUnit = 'month';
		} else if ('week' === dateListUnit) {
			subDateListUnit = 'week';
		} else if ('day' === dateListUnit) {
			subDateListUnit = 'day';
		}
		if ('year' === subDateListUnit) {
			thisInstance.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
			thisInstance.getDatesColumnView().find('.subDateList').html('');
		} else if ('month' === subDateListUnit) {
			thisInstance.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
			thisInstance.generateSubMonthList(calendarView.intervalStart, calendarView.intervalEnd);
		} else if ('week' === subDateListUnit) {
			thisInstance.generateMonthList(calendarView.intervalStart, calendarView.intervalEnd);
			thisInstance.generateSubWeekList(calendarView.start, calendarView.end);
		} else if ('day' === subDateListUnit) {
			thisInstance.generateWeekList(calendarView.start, calendarView.end);
			thisInstance.generateSubDaysList(calendarView.start, calendarView.end);
		}
		thisInstance.updateCountTaskCalendar();
		thisInstance.registerDatesChange();
	},
	registerDatesChange: function () {
		const thisInstance = this;
		let datesView = thisInstance.getDatesColumnView().find('.dateRecord'),
			subDatesView = thisInstance.getDatesColumnView().find('.subRecord');
		datesView.on('click', function () {
			datesView.removeClass('dateActive');
			$(this).addClass('dateActive');
			thisInstance.getCalendarView().fullCalendar('gotoDate', moment($(this).data('date') + '-01-01', "YYYY-MM-DD"));
			thisInstance.loadCalendarData();
		});
		subDatesView.on('click', function () {
			datesView.removeClass('subActive');
			$(this).addClass('subActive');
			thisInstance.getCalendarView().fullCalendar('gotoDate', moment($(this).data('date'), "YYYY-MM-DD"));
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
	getSelectedUsersCalendar() {
		const self = this;
		let selectedUsers = self.getSidebarView().find('.js-inputUserOwnerId:checked'),
			selectedUsersAjax = self.getSidebarView().find('.js-inputUserOwnerIdAjax'),
			users = [];
		if (selectedUsers.length > 0) {
			selectedUsers.each(function () {
				users.push($(this).val());
			});
		} else if (selectedUsersAjax.length > 0) {
			users = self.getSidebarView().find('.js-inputUserOwnerIdAjax').val();
		}
		return users;
	},
	getSelectedRolesCalendar() {
		const self = this;
		let selectedRoles = self.getSidebarView().find('.js-inputRoleOwnerId:checked'),
			selectedRolesAjax = self.getSidebarView().find('.js-inputRoleOwnerIdAjax'),
			roles = [];
		if (selectedRoles.length > 0) {
			selectedRoles.each(function () {
				roles.push($(this).val());
			});
		} else if (selectedRolesAjax.length > 0) {
			roles = self.getSidebarView().find('.js-inputRoleOwnerIdAjax').val();
		}
		return roles;
	},
	getSidebarView: function () {
		this.sidebarView = $('#rightPanel');
		return this.sidebarView;
	},
	updateCountTaskCalendar: function () {
		let datesView = this.getDatesColumnView(),
			subDatesElements = datesView.find('.subRecord'),
			dateArray = {},
			user = this.getSelectedUsersCalendar();
		if (user.length === 0) {
			user = [app.getMainParams('current_user_id')];
		}
		subDatesElements.each(function (key, element) {
			let data = $(this).data('date'),
				type = $(this).data('type');
			if (type === 'months') {
				dateArray[key] = [moment(data).format('YYYY-MM') + '-01', moment(data).endOf('month').format('YYYY-MM-DD')];
			} else if (type === 'weeks') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD'), moment(data).add(1, 'weeks').format('YYYY-MM-DD')];
			} else if (type === 'days') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD'), moment(data).format('YYYY-MM-DD')];
			}
		});
		AppConnector.request({
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getCountEventsGroup',
			dates: dateArray,
			user: user,
			time: app.getMainParams('showType'),
			cvid: this.getCurrentCvId()
		}).then(function (events) {
			subDatesElements.each(function (key, element) {
				$(this).find('.countEvents').removeClass('hide').html(events.result[key]);
			});
		});
	},
	generateYearList: function (dateStart, dateEnd) {
		const thisInstance = this,
			datesView = thisInstance.getDatesColumnView();
		let prevYear = moment(dateStart).subtract(1, 'year'),
			actualYear = moment(dateStart),
			nextYear = moment(dateStart).add(1, 'year'),
			html = '',
			active = '';
		while (prevYear <= nextYear) {
			if (prevYear.format('YYYY') === actualYear.format('YYYY')) {
				active = ' dateActive';
			} else {
				active = '';
			}
			html += '<div class="dateRecord' + active + '" data-date="' + prevYear.format('YYYY') + '">' +
				prevYear.format('YYYY') +
				'</div>';
			prevYear = moment(prevYear).add(1, 'year');
		}
		datesView.find('.dateList').html(html);
	},
	generateMonthList: function (dateStart, dateEnd) {
		const thisInstance = this,
			datesView = thisInstance.getDatesColumnView();
		let prevMonth = moment(dateStart).subtract(1, 'months'),
			actualMonth = moment(dateStart),
			nextMonth = moment(dateStart).add(1, 'months'),
			html = '',
			active = '';
		while (prevMonth <= nextMonth) {
			if (prevMonth.format('YYYY-MM') === actualMonth.format('YYYY-MM')) {
				active = ' dateActive';
			} else {
				active = '';
			}
			html += '<div class="dateRecord' + active + '" data-date="' + prevMonth.format('YYYY-MM-DD') + '">' +
				prevMonth.format('MMMM') +
				'</div>';
			prevMonth = moment(prevMonth).add(1, 'months');
		}
		datesView.find('.dateList').html(html);
	},
	generateWeekList: function (dateStart, dateEnd) {
		const thisInstance = this,
			datesView = thisInstance.getDatesColumnView();
		let prevMonth = moment(dateStart).subtract(1, 'week'),
			actualMonth = moment(dateStart),
			nextMonth = moment(dateStart).add(1, 'week'),
			html = '',
			active = '';
		while (prevMonth <= nextMonth) {
			if (prevMonth.format('WW') === actualMonth.format('WW') && prevMonth.format('YYYY') === actualMonth.format('YYYY')) {
				active = ' dateActive';
			} else {
				active = '';
			}
			html += '<div class="dateRecord' + active + '" data-date="' + prevMonth.format('YYYY-MM-DD') + '">' +
				prevMonth.format('WW') +
				'</div>';
			prevMonth = moment(prevMonth).add(1, 'week');
		}
		datesView.find('.dateList').html(html);
	},
	loadCalendarEditView(id) {
		const aDeferred = $.Deferred();
		AppConnector.request({
			'module': app.getModuleName(),
			'view': 'EventForm',
			'record': id
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
	getCalendarEditView(id) {
		const thisInstance = this,
			aDeferred = $.Deferred();
		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		const progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		thisInstance.loadCalendarEditView(id).then((data) => {
				progressInstance.progressIndicator({mode: 'hide'});
				let sideBar = thisInstance.getSidebarView();
				thisInstance.showRightPanelForm();
				sideBar.find('.qcForm').html(data);
				let rightFormCreate = $(document).find('.js-qc-form'),
					editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('.js-module-name').val()),
					headerInstance = new Vtiger_Header_Js();
				editViewInstance.registerBasicEvents(rightFormCreate);
				rightFormCreate.validationEngine(app.validationEngineOptions);
				headerInstance.registerHelpInfo(rightFormCreate);
				App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
				thisInstance.registerSubmitForm();
				sideBar.find('.summaryCloseEdit').on('click', function () {
					thisInstance.getCalendarCreateView();
				});
				headerInstance.registerQuickCreateSidebarPostLoadEvents(rightFormCreate, {});
				$.each(sideBar.find('.ckEditorSource'), function (key, element) {
					let ckEditorInstance = new Vtiger_CkEditor_Js();
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
	loadCalendarData(allEvents) {
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		const thisInstance = this;
		const view = thisInstance.getCalendarView().fullCalendar('getView');
		let user = [];
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		let formatDate = CONFIG.dateFormat.toUpperCase();
		thisInstance.refreshDatesColumnView(view);
		user = thisInstance.getSelectedUsersCalendar();
		if (0 === user.length) {
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
		AppConnector.request({
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: view.start.format(formatDate),
			end: view.end.format(formatDate),
			user: user,
			time: app.getMainParams('showType'),
			filters: filters,
			cvid: thisInstance.getCurrentCvId()
		}).then((events) => {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
			progressInstance.progressIndicator({mode: 'hide'});
		});
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
	registerUsersChange() {
		const thisInstance = this;
		thisInstance.getSidebarView().find('.js-inputUserOwnerId').on('change', () => {
			thisInstance.loadCalendarData();
		});
		thisInstance.getSidebarView().find('.js-inputUserOwnerIdAjax').on('change', () => {
			thisInstance.loadCalendarData();
		});
	},
	registerGroupChange() {
		const thisInstance = this;
		thisInstance.getSidebarView().find('.js-inputRoleOwnerId').on('change', () => {
			thisInstance.loadCalendarData();
		});
		thisInstance.getSidebarView().find('.js-inputRoleOwnerIdAjax').on('change', () => {
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
			start: startDate.format(),
			end: endDate.format(),
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
	registerSubmitForm() {
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
		if ($('.js-calendarRightPanel').hasClass('hideSiteBar')) {
			$('.js-toggleSiteBarRightButton').trigger('click');
		}
		if (!$('.js-rightPanelEvent').hasClass('active')) {
			$('.js-rightPanelEventLink').trigger('click');
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
	updateCalendarEvent(calendarEventId, eventData) {
		const calendar = this.getCalendarView();
		let recordToUpdate = calendar.fullCalendar('clientEvents', calendarEventId)[0];
		let calendarDetails = eventData;
		let startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value);
		let endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
		recordToUpdate.title = calendarDetails.subject.display_value;
		recordToUpdate.start = startDate.format();
		recordToUpdate.end = endDate.format();
		recordToUpdate.url = 'index.php?module=Calendar&view=Detail&record=' + calendarEventId;
		recordToUpdate.ctivitytype = calendarDetails.activitytype.value;
		recordToUpdate.allDay = calendarDetails.allday.value == 'on';
		recordToUpdate.state = calendarDetails.state.value;
		recordToUpdate.vis = calendarDetails.visibility.value;
		recordToUpdate.sta = calendarDetails.activitystatus.value;
		recordToUpdate.className = ['ownerCBg_' + calendarDetails.assigned_user_id.value, 'picklistCBr_Calendar_activitytype_' + calendarDetails.activitytype.value];
		recordToUpdate.start_display = calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value;
		recordToUpdate.end_display = calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value;
		recordToUpdate.smownerid = calendarDetails.assigned_user_id.display_value;
		recordToUpdate.pri = calendarDetails.taskpriority.value;
		recordToUpdate.lok = calendarDetails.location.display_value;
		calendar.fullCalendar('updateEvent', recordToUpdate);
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
		this.registerFilterTabChange();
	},
	registerAddForm() {
		const thisInstance = this;
		let sideBar = thisInstance.getSidebarView();
		thisInstance.getCalendarCreateView();
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getUsersList').then(
			function (data) {
				if (data) {
					sideBar.find('.usersForm').html(data);
					thisInstance.registerUsersChange();
					App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
				}
			}
		);
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getGroupsList').then(
			function (data) {
				if (data) {
					sideBar.find('.groupForm').html(data);
					thisInstance.registerGroupChange();
					App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
				}
			}
		);
		thisInstance.getSidebarView().slimScroll({
			width: '',
			height: ''
		});
	},
	/**
	 * Find element on list (user, group)
	 * @param {jQuery.Event} e
	 */
	findElementOnList(e) {
		let target = $(e.target),
			value = target.val().toLowerCase(),
			container = target.closest('.js-filter__container');
		container.find('.js-filter__item__value').filter(function () {
			let item = $(this).closest('.js-filter__item__container');
			if ($(this).text().trim().toLowerCase().indexOf(value) > -1) {
				item.removeClass('d-none');
			} else {
				item.addClass('d-none');
			}
		});
	},
	/**
	 * Register filter for users and groups
	 */
	registerFilterForm() {
		const self = this;
		this.getSidebarView().find('a[data-toggle="tab"]').one('shown.bs.tab', function (e) {
			$(".js-filter__search").on('keyup', self.findElementOnList.bind(self));
		});
	},
	/**
	 * Register events
	 */
	registerEvents() {
		this._super();
		this.registerAddForm();
		this.registerFilterForm();
	}
});
