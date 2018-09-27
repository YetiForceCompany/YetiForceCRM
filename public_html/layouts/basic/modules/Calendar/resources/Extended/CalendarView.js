/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Calendar_Calendar_Js('Calendar_CalendarExtended_Js', {}, {
	isRegisterUsersChangeRegistered: false,
	datesRowView: false,
	sidebarView: {length: 0},
	calendar: false,
	/**
	 * Function extends FC.views.year with current class methods
	 */
	addCommonMethodsToYearView() {
		const self = this;
		FC.views.year = FC.views.year.extend({
			selectDays: self.selectDays,
			getCalendarCreateView: self.getCalendarCreateView,
			registerSubmitForm: self.registerSubmitForm,
			getSidebarView: self.getSidebarView,
			getCurrentCvId: self.getCurrentCvId,
			getCalendarView: self.getCalendarView,
			showRightPanelForm: self.showRightPanelForm,
			getSelectedUsersCalendar: self.getSelectedUsersCalendar,
			registerClearFilterButton: self.registerClearFilterButton,
			clearFilterButton: self.clearFilterButton,
			registerFilterTabChange: self.registerFilterTabChange,
			sidebarView: self.sidebarView,
			getActiveFilters: self.getActiveFilters
		});
	},
	/**
	 * Render calendar
	 */
	renderCalendar(readonly = false) {
		const self = this;
		this.calendar = self.getCalendarView();
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
		self.getDatesRowView().find('.js-sub-date-list').data('type', userDefaultActivityView);
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		this.addCommonMethodsToYearView();
		let options = {
			header: {
				left: 'year,month,' + weekView + ',' + dayView,
				center: 'prevYear,prev,title,next,nextYear',
				right: 'today'
			},
			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			scrollTime: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: !readonly,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			eventLimitText: app.vtranslate('JS_MORE'),
			selectable: true,
			selectHelper: true,
			hiddenDays: hiddenDays,
			height: app.setCalendarHeight(this.getContainer()),
			views: {
				basic: {
					eventLimit: false,
				},
				year: {
					eventLimit: 10,
					eventLimitText: app.vtranslate('JS_COUNT_RECORDS'),
					titleFormat: 'YYYY',
					select: function (start, end) {

					}
				},
				month: {
					titleFormat: 'YYYY MMMM'
				},
				week: {
					titleFormat: 'YYYY MMM D'
				},
				day: {
					titleFormat: 'YYYY MMM D'
				},
				basicDay: {
					type: 'agendaDay'
				}
			},
			select: function (start, end) {
				self.selectDays(start, end);
				self.getCalendarView().fullCalendar('unselect');
			},
			eventRender: function (event, element) {
				self.eventRender(event, element);
			},
			viewRender: function (view, element) {
				self.registerViewRenderEvents(view, true)
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
		if (!readonly) {
			options.eventDrop = function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			};
			options.eventResize = function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			};
			options.eventClick = function (calEvent, jsEvent, view) {
				jsEvent.preventDefault();
				let link = new URL($(this)[0].href),
					url = 'index.php?module=Calendar&view=ActivityState&record=' +
						link.searchParams.get("record");
				self.showStatusUpdate(url);
			};
		}
		if (app.moduleCacheGet('start') != null) {
			let s = moment(app.moduleCacheGet('start')).valueOf(),
				e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
		}
		this.calendar.fullCalendar('destroy');
		this.calendar.fullCalendar(options);
		this.createAddSwitch();
	},
	addHeaderButtons() {
		if (this.calendar.find('.js-calendar__view-btn').length) {
			return;
		}
		let buttonsContainer = this.calendar.prev('.js-calendar__header-buttons'),
			viewBtn = buttonsContainer.find('.js-calendar__view-btn').clone(),
			filters = buttonsContainer.find('.js-calendar__filter-container').clone();
		this.calendar.find('.fc-left').prepend(viewBtn);
		this.calendar.find('.fc-center').after(filters);
		this.registerClearFilterButton();
		this.registerFilterTabChange();
	},
	showStatusUpdate(params) {
		const thisInstance = this,
			progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		AppConnector.request(params).done((data) => {
			progressInstance.progressIndicator({mode: 'hide'});
			let sideBar = thisInstance.getSidebarView();
			sideBar.find('.js-qcForm').html(data);
			thisInstance.showRightPanelForm();
			sideBar.find('.js-activity-state .summaryCloseEdit').on('click', function () {
				thisInstance.getCalendarCreateView();
			});
			sideBar.find('.js-activity-state .editRecord').on('click', function () {
				thisInstance.getCalendarEditView($(this).data('id'));
			});
			thisInstance.calendarCreateView = false;
		});
	},
	createAddSwitch() {
		const calendarview = this.getCalendarView(),
			thisInstance = this;
		if ($('.js-calendar-switch-container').length) {
			return;
		}
		let switchAllDays = !(app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all'),
			switchContainer = $(`<div class="js-calendar-switch-container"></div>`).prependTo($('.formsContainer')),
			switchHistory = !(app.getMainParams('showType') === 'current' && app.moduleCacheGet('defaultShowType') !== 'history');

		$(this.switchTpl(app.vtranslate('JS_TO_REALIZE'), app.vtranslate('JS_HISTORY'), switchHistory)).prependTo(switchContainer)
			.on('change', 'input', (e) => {
				const currentTarget = $(e.currentTarget);
				if (typeof currentTarget.data('on-text') !== 'undefined') {
					app.setMainParams('showType', 'current');
					app.moduleCacheSet('defaultShowType', 'current');
				} else if (typeof currentTarget.data('off-text') !== 'undefined') {
					app.setMainParams('showType', 'history');
					app.moduleCacheSet('defaultShowType', 'history');
				}
				this.loadCalendarData();
			});
		if (app.getMainParams('hiddenDays', true) !== false) {
			$(this.switchTpl(app.vtranslate('JS_WORK_DAYS'), app.vtranslate('JS_ALL'), switchAllDays)).prependTo(switchContainer)
				.on('change', 'input', (e) => {
					const currentTarget = $(e.currentTarget);
					let hiddenDays = [];
					if (typeof currentTarget.data('on-text') !== 'undefined') {
						app.setMainParams('switchingDays', 'workDays');
						app.moduleCacheSet('defaultSwitchingDays', 'workDays');
						hiddenDays = app.getMainParams('hiddenDays', true);
					} else if (typeof currentTarget.data('off-text') !== 'undefined') {
						app.setMainParams('switchingDays', 'all');
						app.moduleCacheSet('defaultSwitchingDays', 'all');
					}
					calendarview.fullCalendar('option', 'hiddenDays', hiddenDays);
					if (thisInstance.getCalendarView().fullCalendar('getView').type !== 'year') {
						thisInstance.subDateRow = false;
						this.loadCalendarData();
					}
				});
		}
	},
	eventRender: function (event, element) {
		const self = this;
		let valueEventVis = '';
		if (event.vis !== '') {
			valueEventVis = app.vtranslate('JS_' + event.vis);
		}
		app.showPopoverElementView(element.find('.fc-content'), {
			title: event.title + '<a href="javascript:void(0);" class="float-right mx-1 js-edit-element" data-js="click"><span class="fas fa-edit float-right"></span></a>' + '<a href="index.php?module=' + event.module + '&view=Detail&record=' + event.id + '" class="float-right mx-1"><span class="fas fa-th-list"></span></a>',
			container: 'body',
			html: true,
			placement: 'auto',
			callbackShown: function () {
				$('.js-calendar-popover' + event.id).find('.js-edit-element').on('click', function () {
					self.getCalendarEditView(event.id);
				});
			},
			template: '<div class="popover calendarPopover js-calendar-popover' + event.id + '" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			content: '<div><span class="fas fa-clock"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
				'<div><span class="fas fa-clock"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
				(event.lok ? '<div><span class="fas fa-globe"></span> <label>' + app.vtranslate('JS_LOCATION') + '</label>: ' + event.lok + '</div>' : '') +
				(event.pri ? '<div><span class="fas fa-exclamation-circle"></span> <label>' + app.vtranslate('JS_PRIORITY') + '</label>: <span class="picklistCT_Calendar_taskpriority_' + event.pri + '">' + app.vtranslate('JS_' + event.pri) + '</span></div>' : '') +
				'<div><span class="fas fa-question-circle"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>:  <span class="picklistCT_Calendar_activitystatus_' + event.sta + '">' + app.vtranslate('JS_' + event.sta) + '</span></div>' +
				(event.accname ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ACCOUNTS') + '</label>: <span class="modCT_Accounts">' + event.accname + '</span></div>' : '') +
				(event.linkexl ? '<div><span class="userIcon-' + event.linkexm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION_EXTEND') + '</label>: <a class="modCT_' + event.linkexm + '" target="_blank" href="index.php?module=' + event.linkexm + '&view=Detail&record=' + event.linkextend + '">' + event.linkexl + '</a></div>' : '') +
				(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: <a class="modCT_' + event.linkm + '" target="_blank" href="index.php?module=' + event.linkm + '&view=Detail&record=' + event.link + '">' + event.linkl + '</span></a></div>' : '') +
				(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: <a class="modCT_' + event.procm + '"target="_blank" href="index.php?module=' + event.procm + '&view=Detail&record=' + event.process + '">' + event.procl + '</a></div>' : '') +
				(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: <a class="modCT_' + event.subprocm + '" target="_blank" href="index.php?module=' + event.subprocm + '&view=Detail&record=' + event.subprocess + '">' + event.subprocl + '</a></div>' : '') +
				(event.state ? '<div><span class="fas fa-star"></span> <label>' + app.vtranslate('JS_STATE') + '</label>:  <span class="picklistCT_Calendar_state_' + event.state + '">' + app.vtranslate(event.state) + '</span></div>' : '') +
				'<div><span class="fas fa-eye"></span> <label>' + app.vtranslate('JS_VISIBILITY') + '</label>:  <span class="picklistCT_Calendar_visibility_' + event.vis + '">' + valueEventVis + '</div>' +
				(event.smownerid ? '<div><span class="fas fa-user"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
		});
		if (event.rendering === 'background') {
			element.append(`<span class="${event.icon} mr-1"></span>${event.title}`)
		}
	},
	getDatesRowView() {
		this.datesRowView = $('.js-dates-row');
		return this.datesRowView;
	},
	/**
	 * Appends subdate row to calendar header and register its scroll
	 * @param toolbar
	 */
	appendSubDateRow(toolbar) {
		if (!this.calendar.find('.js-dates-row').length) {
			this.subDateRow = $(`
								<div class="js-scroll js-dates-row u-overflow-auto-lg-down order-4 flex-grow-1 position-relative my-1 w-100" data-js="perfectScrollbar | container">
									<div class="d-flex flex-nowrap w-100">
										<div class="js-sub-date-list w-100 sub-date-list row no-gutters flex-nowrap nav nav-tabs" data-js="data-type"></div>
									</div>
								</div>
								`);
			toolbar.append(this.subDateRow);
			if ($(window).width() > app.breakpoints.lg) {
				app.showNewScrollbar(this.subDateRow, {
					suppressScrollY: true
				});
			}
		}
	},
	/**
	 * Function toggles next year/month and general arrows on view render
	 * @param view
	 * @param element
	 */
	registerViewRenderEvents(view, noCounting) {
		this.calendar = this.getCalendarView();
		let toolbar = this.calendar.find('.fc-toolbar.fc-header-toolbar'),
			nextPrevButtons = toolbar.find('.fc-prev-button, .fc-next-button'),
			yearButtons = toolbar.find('.fc-prevYear-button, .fc-nextYear-button');
		this.appendSubDateRow(toolbar);
		this.refreshDatesRowView(view, noCounting);
		this.addHeaderButtons();
		if (view.type === 'year') {
			nextPrevButtons.hide();
			yearButtons.show();
		} else {
			nextPrevButtons.show();
			yearButtons.hide();
		}
	},
	refreshDatesRowView(calendarView, noCounting) {
		const self = this;
		let dateListUnit = calendarView.type,
			subDateListUnit = 'week';
		if ('year' === dateListUnit) {
			subDateListUnit = 'year';
		} else if ('month' === dateListUnit) {
			subDateListUnit = 'month';
		} else if ('week' === dateListUnit) {
			subDateListUnit = 'week';
		} else if ('day' === dateListUnit || 'agendaDay' === dateListUnit) {
			subDateListUnit = 'day';
		}
		if ('year' === subDateListUnit) {
			self.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
		} else if ('month' === subDateListUnit) {
			self.generateSubMonthList(calendarView.intervalStart, calendarView.intervalEnd);
		} else if ('week' === subDateListUnit) {
			self.generateSubWeekList(calendarView.start, calendarView.end);
		} else if ('day' === subDateListUnit) {
			self.generateSubDaysList(calendarView.start, calendarView.end);
		}
		if (!noCounting) {
			self.updateCountTaskCalendar();
		}
		self.registerDatesChange();
	},
	registerDatesChange() {
		const thisInstance = this;
		let datesView = thisInstance.getDatesRowView().find('.js-date-record'),
			subDatesView = thisInstance.getDatesRowView().find('.js-sub-record');
		datesView.on('click', function () {
			datesView.removeClass('date-active');
			$(this).addClass('date-active');
			thisInstance.getCalendarView().fullCalendar('gotoDate', moment($(this).data('date') + '-01-01', "YYYY-MM-DD"));
			thisInstance.loadCalendarData();
		});
		subDatesView.on('click', function () {
			datesView.removeClass('active');
			$(this).addClass('active');
			thisInstance.getCalendarView().fullCalendar('gotoDate', moment($(this).data('date'), "YYYY-MM-DD"));
			thisInstance.loadCalendarData();
		});
	},
	getCurrentCvId() {
		return $(".js-calendar__extended-filter-tab .active").parent('.js-filter-tab').data('cvid');
	},
	registerFilterTabChange() {
		const thisInstance = this;
		this.getCalendarView().find(".js-calendar__extended-filter-tab").on('shown.bs.tab', function () {
			thisInstance.loadCalendarData();
		});
	},
	getSelectedUsersCalendar() {
		const self = this;
		let selectedUsers = self.getSidebarView().find('.js-inputUserOwnerId:checked'),
			selectedUsersAjax = self.getSidebarView().find('.js-inputUserOwnerIdAjax'),
			selectedRolesAjax = self.getSidebarView().find('.js-inputRoleOwnerIdAjax'),
			users = [];
		if (selectedUsers.length > 0) {
			selectedUsers.each(function () {
				users.push($(this).val());
			});
		} else if (selectedUsersAjax.length > 0) {
			users = selectedUsersAjax.val().concat(selectedRolesAjax.val());
		}
		return users;
	},
	getSidebarView() {
		if (!this.sidebarView.length) {
			this.sidebarView = $('#rightPanel');
		}
		return this.sidebarView;
	},
	updateCountTaskCalendar() {
		let datesView = this.getDatesRowView(),
			subDatesElements = datesView.find('.js-sub-record'),
			dateArray = {},
			user = this.getSelectedUsersCalendar();
		if (user.length === 0) {
			user = [app.getMainParams('userId')];
		}
		subDatesElements.each(function (key, element) {
			let data = $(this).data('date'),
				type = $(this).data('type');
			if (type === 'years') {
				dateArray[key] = [moment(data + '-01').format('YYYY-MM-DD') + ' 00:00:00', moment(data + '-01').endOf('year').format('YYYY-MM-DD') + ' 23:59:59'];
			} else if (type === 'months') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD') + ' 00:00:00', moment(data).endOf('month').format('YYYY-MM-DD') + ' 23:59:59'];
			} else if (type === 'weeks') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD') + ' 00:00:00', moment(data).add(6, 'day').format('YYYY-MM-DD') + ' 23:59:59'];
			} else if (type === 'days') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD') + ' 00:00:00', moment(data).format('YYYY-MM-DD') + ' 23:59:59'];
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
		}).done(function (events) {
			subDatesElements.each(function (key, element) {
				$(this).find('.js-count-events').removeClass('hide').html(events.result[key]);
			});
		});
	},
	loadCalendarEditView(id) {
		const aDeferred = $.Deferred();
		AppConnector.request({
			'module': app.getModuleName(),
			'view': 'EventForm',
			'record': id
		}).done((data) => {
			aDeferred.resolve($(data));
		}).fail((error) => {
			aDeferred.reject();
			app.errorLog(error);
		});
		return aDeferred.promise();
	},
	getCalendarEditView(id) {
		const thisInstance = this,
			aDeferred = $.Deferred();
		const progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		thisInstance.loadCalendarEditView(id).done((data) => {
			progressInstance.progressIndicator({mode: 'hide'});
			let sideBar = thisInstance.getSidebarView();
			thisInstance.showRightPanelForm();
			sideBar.find('.js-qcForm').html(data);
			let rightFormCreate = $(document).find('form[name="QuickCreate"]'),
				editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('[name="module"]').val()),
				headerInstance = new Vtiger_Header_Js();
			editViewInstance.registerBasicEvents(rightFormCreate);
			rightFormCreate.validationEngine(app.validationEngineOptions);
			headerInstance.registerHelpInfo(rightFormCreate);
			App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
			thisInstance.registerSubmitForm();
			sideBar.find('.summaryCloseEdit').on('click', function () {
				thisInstance.getCalendarCreateView();
			});
			headerInstance.registerQuickCreatePostLoadEvents(rightFormCreate, {});
			$.each(sideBar.find('.ckEditorSource'), function (key, element) {
				let ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor($(element), {
					height: '5em',
					toolbar: 'Min'
				});
			});
			thisInstance.calendarCreateView = false;
			aDeferred.resolve(sideBar.find('.js-qcForm'));
		}).fail((error) => {
			progressInstance.progressIndicator({mode: 'hide'});
			app.errorLog(error);
		});
		return aDeferred.promise();
	},
	loadCalendarData() {
		const self = this,
			view = self.getCalendarView().fullCalendar('getView');
		let user = [],
			filters = this.getActiveFilters(),
			formatDate = CONFIG.dateFormat.toUpperCase(),
			cvid = self.getCurrentCvId();
		self.getCalendarView().fullCalendar('removeEvents');
		if (view.type !== 'year') {
			let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
			user = self.getSelectedUsersCalendar();
			if (0 === user.length) {
				user = [app.getMainParams('userId')];
			}
			$(".calendarFilters .filterField").each(function (index) {
				let element = $(this),
					name, value;
				if (element.attr('type') === 'checkbox') {
					name = element.val();
					value = element.prop('checked') ? 1 : 0;
				} else {
					name = element.attr('name');
					value = element.val();
				}
				filters.push({name: name, value: value});
			});
			self.clearFilterButton(user, filters, cvid);
			AppConnector.request({
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: view.start.format(formatDate),
				end: view.end.format(formatDate),
				user: user,
				time: app.getMainParams('showType'),
				filters: filters,
				cvid: cvid
			}).done((events) => {
				self.getCalendarView().fullCalendar('removeEvents');
				self.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.progressIndicator({mode: 'hide'});
			});
		}
		self.registerViewRenderEvents(self.getCalendarView().fullCalendar('getView'), false);
	},
	clearFilterButton(user, filters, cvid) {
		let currentUser = parseInt(app.getMainParams('userId')),
			time = app.getMainParams('showType'),
			statement = ((user.length === 0 || (user.length === 1 && parseInt(user) === currentUser)) && filters.length === 0 && cvid === undefined && time === 'current');
		$(".js-calendar__clear-filters").toggleClass('d-none', statement);

	},
	registerClearFilterButton() {
		const sidebar = this.getSidebarView();
		let clearBtn = this.getCalendarView().find('.js-calendar__clear-filters');
		app.showPopoverElementView(clearBtn);
		clearBtn.on('click', () => {
			$(".js-calendar__extended-filter-tab a").removeClass('active');
			$(".js-calendar-switch-container .js-switch").eq(1).find('.js-switch--label-on').click();
			sidebar.find("input:checkbox").prop('checked', false);
			sidebar.find("option:selected").prop('selected', false).trigger('change');
			sidebar.find(".js-inputUserOwnerId[value=" + app.getMainParams('userId') + "]").prop('checked', true);
			this.loadCalendarData();
		});
	},
	generateYearList(dateStart, dateEnd) {
		const thisInstance = this,
			datesView = thisInstance.getDatesRowView();
		let prevYear = moment(dateStart).subtract(1, 'year'),
			actualYear = moment(dateStart),
			nextYear = moment(dateStart).add(1, 'year'),
			html = '',
			active = '';
		while (prevYear <= nextYear) {
			if (prevYear.format('YYYY') === actualYear.format('YYYY')) {
				active = 'active';
			} else {
				active = '';
			}
			html +=
				`<div class="js-sub-record sub-record col-4 nav-item" data-date="${prevYear.format('YYYY')}" data-type="years" data-js="click|class:date-active">
					<div class="sub-record-content nav-link ${active}">
						<div class="sub-date-name">
							${prevYear.format('YYYY')}
							<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
						</div>
					</div>
				</div>`;
			prevYear = moment(prevYear).add(1, 'year');
		}
		datesView.find('.js-sub-date-list').html(html);
	},
	generateSubMonthList(dateStart, dateEnd) {
		let datesView = this.getDatesRowView(),
			activeMonth = parseInt(moment(dateStart).locale('en').format('M')) - 1,
			html = '',
			active = '';
		for (let month = 0; 12 > month; ++month) {
			if (month === activeMonth) {
				active = 'active';
			} else {
				active = '';
			}
			html +=
				`<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="months" data-date="${moment(dateStart).month(month).format('YYYY-MM')}">
					<div class="sub-record-content nav-link ${active}">
						<div class="sub-date-name">${app.vtranslate('JS_' + moment().month(month).format('MMM').toUpperCase()).toUpperCase()}
							<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
						</div>
					</div>
				</div>`;
		}
		datesView.find('.js-sub-date-list').html(html);
	},
	generateSubWeekList(dateStart, dateEnd) {
		let datesView = this.getDatesRowView(),
			prevWeeks = moment(dateStart).subtract(5, 'weeks'),
			actualWeek = moment(dateStart).format('WW'),
			nextWeeks = moment(dateStart).add(6, 'weeks'),
			html = '';
		while (prevWeeks <= nextWeeks) {
			let active = '';
			if (prevWeeks.format('WW') === actualWeek) {
				active = ' active';
			}
			html += '<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="weeks" data-date="' + prevWeeks.format('YYYY-MM-DD') + '">' +
				'<div class="sub-record-content nav-link' + active + '">' +
				'<div class="sub-date-name">' + app.vtranslate('JS_WEEK_SHORT') + ' ' + prevWeeks.format('WW') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevWeeks = moment(prevWeeks).add(1, 'weeks');
		}
		datesView.find('.js-sub-date-list').html(html);
	},
	generateSubDaysList(dateStart, dateEnd) {
		const thisInstance = this;
		let datesView = thisInstance.getDatesRowView(),
			prevDays = moment(dateStart).subtract(5, 'days'),
			actualDay = moment(dateStart).format('DDD'),
			nextDays = moment(dateStart).add(7, 'days'),
			daysToShow = nextDays.diff(prevDays, 'days'),
			html = '';
		for (let day = 0; day < daysToShow; ++day) {
			let active = '';
			if (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all') {
				if (prevDays.day() === 0 || prevDays.day() === 6) {
					prevDays = moment(prevDays).add(1, 'days');
					daysToShow++;
					continue;
				}
			}
			if (prevDays.format('DDD') === actualDay) {
				active = ' active';
			}
			html += '<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="days" data-date="' + prevDays.format('YYYY-MM-DD') + '">' +
				'<div class="sub-record-content nav-link' + active + '">' +
				'<div class="sub-date-name">' + app.vtranslate('JS_DAY_SHORT') + ' ' + prevDays.format('DD') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevDays = moment(prevDays).add(1, 'days');
		}
		datesView.find('.js-sub-date-list').html(html);
	},
	selectDays(startDate, endDate) {
		const thisInstance = this;
		let start_hour = $('#start_hour').val(),
			end_hour = $('#end_hour').val(),
			view = thisInstance.getCalendarView().fullCalendar('getView');
		if (endDate.hasTime() == false) {
			endDate.add(-1, 'days');
		}
		startDate = startDate.format();
		endDate = endDate.format();
		if (start_hour == '') {
			start_hour = '00';
		}
		if (end_hour == '') {
			end_hour = '00';
		}
		this.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			if (view.name != 'agendaDay' && view.name != 'agendaWeek') {
				startDate = startDate + 'T' + start_hour + ':00';
				endDate = endDate + 'T' + end_hour + ':00';
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
			let dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase(),
				timeFormat = data.find('[name="time_start"]').data('format'),
				defaultTimeFormat = '';
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
		const self = this,
			sidebar = self.getSidebarView();
		if (self.isRegisterUsersChangeRegistered) {
			sidebar.off('change', '.js-inputUserOwnerId');
			sidebar.off('change', '.js-inputUserOwnerIdAjax');
		}
		self.isRegisterUsersChangeRegistered = true;
		sidebar.find('.js-inputUserOwnerId').on('change', () => {
			self.loadCalendarData();
		});
		sidebar.find('.js-inputUserOwnerIdAjax').on('change', () => {
			self.loadCalendarData();
		});
		self.registerPinUser();
	},
	addCalendarEvent(calendarDetails) {
		let calendar = this.getCalendarView(),
			startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value),
			endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value),
			eventObject = {
				id: calendarDetails._recordId,
				title: calendarDetails.subject.display_value,
				start: startDate.format(),
				end: endDate.format(),
				module: 'Calendar',
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
		$('.js-save-event').on('click', function (e) {
			if ($(this).parents('form:first').validationEngine('validate')) {
				let formData = $(e.currentTarget).parents('form:first').serializeFormData();
				AppConnector.request(formData).done((data) => {
						if (data.success) {
							let textToShow = '';
							if (formData.record) {
								thisInstance.updateCalendarEvent(formData.record, data.result);
								textToShow = app.vtranslate('JS_TASK_IS_SUCCESSFULLY_UPDATED_IN_YOUR_CALENDAR');
							} else {
								thisInstance.addCalendarEvent(data.result);
								textToShow = app.vtranslate('JS_TASK_IS_SUCCESSFULLY_ADDED_TO_YOUR_CALENDAR');
							}
							thisInstance.calendarCreateView = false;
							//thisInstance.getCalendarCreateView();
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
		let calendarRightPanel = $('.js-calendarRightPanel'),
			sitebarButton = $('.js-toggleSiteBarRightButton');
		if (calendarRightPanel.hasClass('hideSiteBar')) {
			sitebarButton.trigger('click');
		}
		if (!$('.js-rightPanelEvent').hasClass('active')) {
			$('.js-rightPanelEventLink').trigger('click');
		}
		$('.js-show-sitebar').on('click', () => {
			if (calendarRightPanel.hasClass('hideSiteBar')) {
				sitebarButton.trigger('click');
			}
		})
	},
	loadCalendarCreateView() {
		let aDeferred = $.Deferred();
		AppConnector.request({
			'module': app.getModuleName(),
			'view': 'EventForm',
		}).done((data) => {
			aDeferred.resolve($(data));
		}).fail((error) => {
			aDeferred.reject();
			app.errorLog(error);
		});
		return aDeferred.promise();
	},
	updateCalendarEvent(calendarEventId, eventData) {
		const calendar = this.getCalendarView();
		let recordToUpdate = calendar.fullCalendar('clientEvents', calendarEventId)[0],
			calendarDetails = eventData,
			startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value),
			endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
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
		if (thisInstance.calendarCreateView !== false) {
			return thisInstance.calendarCreateView;
		}
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		this.loadCalendarCreateView().done((data) => {
			let sideBar = thisInstance.getSidebarView();
			progressInstance.progressIndicator({mode: 'hide'});
			thisInstance.showRightPanelForm();
			sideBar.find('.js-qcForm').html(data);
			let rightFormCreate = $(document).find('form[name="QuickCreate"]'),
				moduleName = sideBar.find('[name="module"]').val(),
				editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName),
				headerInstance = new Vtiger_Header_Js();
			App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
			editViewInstance.registerBasicEvents(rightFormCreate);
			rightFormCreate.validationEngine(app.validationEngineOptions);
			headerInstance.registerHelpInfo(rightFormCreate);
			thisInstance.registerSubmitForm();
			headerInstance.registerQuickCreatePostLoadEvents(rightFormCreate, {});
			$.each(sideBar.find('.ckEditorSource'), function (key, element) {
				let ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor($(element), {
					height: '5em',
					toolbar: 'Min'
				});
			});
			aDeferred.resolve(sideBar.find('.js-qcForm'));
		}).fail((error) => {
			progressInstance.progressIndicator({mode: 'hide'});
			app.errorLog(error);
		});
		thisInstance.calendarCreateView = aDeferred.promise();
		return thisInstance.calendarCreateView;
	},
	registerPinUser() {
		$('.js-pinUser').off('click').on('click', function () {
			const thisInstance = $(this);
			AppConnector.request({
				'module': app.getModuleName(),
				'action': 'Calendar',
				'mode': 'pinOrUnpinUser',
				'element_id': thisInstance.data('elementid'),
			}).done((data) => {
				let response = data.result;
				if (response === 'unpin') {
					thisInstance.find('.fa-thumbtack').addClass('u-opacity-muted');
				} else if (response === 'pin') {
					thisInstance.find('.fa-thumbtack').removeClass('u-opacity-muted');
				} else {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_ERROR'));
				}
			});
		});
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
		this.loadCalendarData();
		this.registerFilterTabChange();
	},
	registerAddForm() {
		const thisInstance = this;
		let sideBar = thisInstance.getSidebarView();
		thisInstance.getCalendarCreateView().done(function () {
			app.showNewScrollbar($('.js-calendar__form__wrapper'), {
				suppressScrollX: true
			});
		});
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getUsersList').done(
			function (data) {
				if (data) {
					let formContainer = sideBar.find('.js-usersForm');
					formContainer.html(data);
					thisInstance.registerUsersChange();
					App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
					app.showNewScrollbar(formContainer, {
						suppressScrollX: true
					});
				}
			}
		);
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getGroupsList').done(
			function (data) {
				if (data) {
					let formContainer = sideBar.find('.js-groupForm');
					formContainer.html(data);
					thisInstance.registerUsersChange();
					App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
					formContainer.addClass('u-min-h-30per');
					app.showNewScrollbar(formContainer, {
						suppressScrollX: true
					});
				}
			}
		);
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
	 * Get active filters
	 * @returns {Array}
	 */
	getActiveFilters() {
		let filters = [];
		$(".calendarFilters .filterField").each(function () {
			let element = $(this),
				name, value;
			if (element.attr('type') === 'checkbox') {
				name = element.val();
				value = element.prop('checked') ? 1 : 0;
			} else {
				name = element.attr('name');
				value = element.val();
			}
			filters.push({name: name, value: value});
		});
		return filters;
	},
	/**
	 * Register events
	 */
	registerEvents() {
		this._super();
		this.registerAddForm();
		this.registerFilterForm();
		ElementQueries.listen();
	}
});
