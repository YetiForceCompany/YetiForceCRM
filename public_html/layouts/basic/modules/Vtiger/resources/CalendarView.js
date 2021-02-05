/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class representing a standard calendar.
 * @extends Calendar_Js
 */
window.Vtiger_Calendar_Js = class Vtiger_Calendar_Js extends (
	Calendar_Js
) {
	constructor(container, readonly) {
		super(container, readonly, false);
		this.browserHistory = false;
		this.calendarContainer = false;
		this.addCommonMethodsToYearView();
		this.calendar = this.getCalendarView();
	}
	getCalendarSidebarData() {}
	registerEditForm() {}
	registerCacheSettings() {}
	registerPinUser() {}

	addCommonMethodsToYearView() {
		const self = this;
		FC.views.year = FC.views.year.extend({
			baseInstance: self,
			selectDays: self.selectDays,
			getCalendarCreateView: self.getCalendarCreateView,
			getSidebarView: self.getSidebarView,
			getCurrentCvId: self.getCurrentCvId,
			getCalendarView: self.getCalendarView,
			showRightPanelForm: self.showRightPanelForm,
			getSelectedUsersCalendar: self.getSelectedUsersCalendar,
			registerClearFilterButton: self.registerClearFilterButton,
			clearFilterButton: self.clearFilterButton,
			registerFilterTabChange: self.registerFilterTabChange,
			sidebarView: self.sidebarView,
			registerAfterSubmitForm: self.registerAfterSubmitForm,
			registerViewRenderEvents: self.registerViewRenderEvents,
			appendSubDateRow: self.appendSubDateRow,
			refreshDatesRowView: self.refreshDatesRowView,
			generateYearList: self.generateYearList,
			updateCountTaskCalendar: self.updateCountTaskCalendar,
			registerDatesChange: self.registerDatesChange,
			addHeaderButtons: self.addHeaderButtons,
			browserHistoryConfig: self.browserHistoryConfig,
			readonly: self.readonly,
			container: self.container,
			module: self.module,
			showChangeDateButtons: self.showChangeDateButtons,
			showTodayButtonCheckbox: self.showTodayButtonCheckbox,
			getDefaultParams: self.getDefaultParams
		});
	}

	/**
	 * Date bar with counts
	 * @param object calendarView
	 */
	refreshDatesRowView(calendarView) {
		const self = this;
		switch (calendarView.type) {
			case 'year':
				self.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
				break;
			case 'month':
				self.generateSubMonthList(calendarView.intervalStart, calendarView.intervalEnd);
				break;
			case 'week':
			case 'agendaWeek':
				self.generateSubWeekList(calendarView.start, calendarView.end);
				break;
			default:
				self.generateSubDaysList(calendarView.start, calendarView.end);
		}
		self.updateCountTaskCalendar();
		self.registerDatesChange();
	}
	generateSubDaysList(dateStart, dateEnd) {
		let datesView = this.container.find('.js-dates-row'),
			prevDays = moment(dateStart).subtract(5, 'days'),
			actualDay = moment(dateStart).format('DDD'),
			nextDays = moment(dateStart).add(7, 'days'),
			daysToShow = nextDays.diff(prevDays, 'days'),
			html = '';
		for (let day = 0; day < daysToShow; ++day) {
			let active = '';
			if (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all') {
				if ($.inArray(prevDays.day(), app.getMainParams('hiddenDays', true)) !== -1) {
					prevDays = moment(prevDays).add(1, 'days');
					daysToShow++;
					continue;
				}
			}
			if (prevDays.format('DDD') === actualDay) {
				active = ' active';
			}
			html +=
				'<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="days" data-date="' +
				prevDays.format('YYYY-MM-DD') +
				'" data-js="click | class: active">' +
				'<div class="sub-record-content nav-link' +
				active +
				'">' +
				'<div class="sub-date-name">' +
				app.vtranslate('JS_DAY_SHORT') +
				' ' +
				prevDays.format('DD') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevDays = moment(prevDays).add(1, 'days');
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	generateSubWeekList(dateStart, dateEnd) {
		let datesView = this.container.find('.js-dates-row'),
			prevWeeks = moment(dateStart).subtract(5, 'weeks'),
			actualWeek = moment(dateStart).format('WW'),
			nextWeeks = moment(dateStart).add(6, 'weeks'),
			html = '';
		while (prevWeeks <= nextWeeks) {
			let active = '';
			if (prevWeeks.format('WW') === actualWeek) {
				active = ' active';
			}
			html +=
				'<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="weeks" data-date="' +
				prevWeeks.format('YYYY-MM-DD') +
				'" data-js="click | class: active">' +
				'<div class="sub-record-content nav-link' +
				active +
				'">' +
				'<div class="sub-date-name">' +
				app.vtranslate('JS_WEEK_SHORT') +
				' ' +
				prevWeeks.format('WW') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevWeeks = moment(prevWeeks).add(1, 'weeks');
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	generateYearList(dateStart, dateEnd) {
		const datesView = this.container.find('.js-dates-row');
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
			html += `<div class="js-sub-record sub-record col-4 nav-item" data-date="${prevYear.format(
				'YYYY'
			)}" data-type="years" data-js="click | class: active">
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
	}
	registerDatesChange() {
		this.container.find('.js-dates-row .js-sub-record').on('click', (e) => {
			let currentTarget = $(e.currentTarget);
			currentTarget.addClass('active');
			this.getCalendarView().fullCalendar('gotoDate', moment(currentTarget.data('date'), 'YYYY-MM-DD'));
		});
	}
	selectDays(startDate, endDate) {
		if (!this.container.find('.js-right-panel-event-link').length) {
			return false;
		}
		this.container.find('.js-right-panel-event-link').tab('show');
		let start_hour = app.getMainParams('startHour'),
			end_hour = app.getMainParams('endHour'),
			view = this.getCalendarView().fullCalendar('getView');
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
					let activityType = data.find('[name="activitytype"]').val();
					let activityDurations = JSON.parse(data.find('[name="defaultOtherEventDuration"]').val());
					let minutes = 0;
					for (let i in activityDurations) {
						if (activityDurations[i].activitytype === activityType) {
							minutes = parseInt(activityDurations[i].duration);
							break;
						}
					}
					endDate = moment(endDate).add(minutes, 'minutes').toISOString();
				}
			}

			let dateFormat = CONFIG.dateFormat.toUpperCase();
			let timeFormat = CONFIG.hourFormat;
			let dateField = data.find('[name="date_start"]');
			if (dateField.length) {
				dateFormat = dateField.data('dateFormat').toUpperCase();
			}
			let timeField = data.find('[name="time_start"]');
			if (timeField.length) {
				timeFormat = timeField.data('format');
			}
			let defaultTimeFormat = '';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm A';
			}
			data.find('[name="date_start"]').val(moment(startDate).format(dateFormat));
			data.find('[name="due_date"]').val(moment(endDate).format(dateFormat));
			if (data.find('.js-autofill').prop('checked') === true) {
				Calendar_Edit_Js.getInstance().getFreeTime(data);
			} else {
				data.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
				data.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
			}
		});
	}
	updateCountTaskCalendar() {
		let options = this.getDefaultParams();
		delete options.start;
		delete options.end;
		let datesView = this.container.find('.js-dates-row'),
			subDatesElements = datesView.find('.js-sub-record'),
			dateArray = {},
			userDateFormat = CONFIG.dateFormat.toUpperCase();
		subDatesElements.each(function (key, element) {
			let data = $(this).data('date'),
				type = $(this).data('type');
			if (type === 'years') {
				dateArray[key] = [
					moment(data + '-01').format(userDateFormat) + ' 00:00:00',
					moment(data + '-01')
						.endOf('year')
						.format(userDateFormat) + ' 23:59:59'
				];
			} else if (type === 'months') {
				dateArray[key] = [
					moment(data).format(userDateFormat) + ' 00:00:00',
					moment(data).endOf('month').format(userDateFormat) + ' 23:59:59'
				];
			} else if (type === 'weeks') {
				dateArray[key] = [
					moment(data).format(userDateFormat) + ' 00:00:00',
					moment(data).add(6, 'day').format(userDateFormat) + ' 23:59:59'
				];
			} else if (type === 'days') {
				dateArray[key] = [
					moment(data).format(userDateFormat) + ' 00:00:00',
					moment(data).format(userDateFormat) + ' 23:59:59'
				];
			}
		});
		options.mode = 'getCountEventsGroup';
		options.dates = dateArray;
		AppConnector.request(options).done(function (events) {
			subDatesElements.each(function (key, element) {
				$(this).find('.js-count-events').removeClass('hide').html(events.result[key]);
			});
		});
	}
	generateSubMonthList(dateStart, dateEnd) {
		let datesView = this.container.find('.js-dates-row'),
			activeMonth = parseInt(moment(dateStart).locale('en').format('M')) - 1,
			html = '',
			active = '';
		for (let month = 0; 12 > month; ++month) {
			if (month === activeMonth) {
				active = 'active';
			} else {
				active = '';
			}
			html += `<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="months" data-date="${moment(
				dateStart
			)
				.month(month)
				.format('YYYY-MM')}" data-js="click | class: active">
					<div class="sub-record-content nav-link ${active}">
						<div class="sub-date-name">${App.Fields.Date.monthsTranslated[month]}
							<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
						</div>
					</div>
				</div>`;
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	getSelectedUsersCalendar() {
		const sidebar = this.getSidebarView();
		let selectedUsers = sidebar.find('.js-input-user-owner-id:checked'),
			selectedUsersAjax = sidebar.find('.js-input-user-owner-id-ajax'),
			selectedRolesAjax = sidebar.find('.js-input-role-owner-id-ajax'),
			users = [];
		if (selectedUsers.length > 0) {
			selectedUsers.each(function () {
				users.push($(this).val());
			});
		} else if (selectedUsersAjax.length > 0) {
			users = selectedUsersAjax.val().concat(selectedRolesAjax.val());
		}
		return users;
	}

	clearFilterButton(user, cvid) {
		let currentUser = parseInt(app.getMainParams('userId')),
			time = app.getMainParams('showType'),
			statement =
				(user.length === 0 || (user.length === 1 && parseInt(user) === currentUser)) &&
				cvid === undefined &&
				time === 'current';
		$('.js-calendar__clear-filters').toggleClass('d-none', statement);
	}
	loadCalendarData(view = this.getCalendarView().fullCalendar('getView')) {
		const self = this;
		let options = this.getDefaultParams(view);
		let calendarInstance = this.getCalendarView();
		calendarInstance.fullCalendar('removeEvents');
		let progressInstance = $.progressIndicator({ blockInfo: { enabled: true } });
		self.clearFilterButton(options.user, self.getCurrentCvId());
		let connectorMethod = window['AppConnector']['request'];
		connectorMethod(options).done((events) => {
			calendarInstance.fullCalendar('removeEvents');
			calendarInstance.fullCalendar('addEventSource', events.result);
			progressInstance.progressIndicator({ mode: 'hide' });
		});
		self.registerViewRenderEvents(view);
		window.calendarLoaded = true;
	}
	/**
	 * Default params
	 * @returns {{module: *, action: string, mode: string, start: *, end: *, user: *, emptyFilters: boolean}}
	 */
	getDefaultParams(view = this.getCalendarView().fullCalendar('getView')) {
		let options = super.getDefaultParams();
		let formatDate = CONFIG.dateFormat.toUpperCase();
		let user = this.getSelectedUsersCalendar();
		if (0 === user.length) {
			user = app.getMainParams('usersId');
		}
		if (user === undefined) {
			user = [app.getMainParams('userId')];
		}
		if (view.type === 'agendaDay') {
			view.end = view.end.add(1, 'day');
		}
		options.user = user;
		options.start = view.start.format(formatDate);
		options.end = view.end.format(formatDate);
		options.time = app.getMainParams('showType');
		options.history = true;
		return options;
	}
	/**
	 * Function toggles next year/month and general arrows on view render
	 * @param view
	 * @param element
	 */
	registerViewRenderEvents(view) {
		this.calendarContainer = this.getCalendarView();
		let toolbar = this.calendarContainer.find('.fc-toolbar.fc-header-toolbar');
		this.showChangeDateButtons(view, toolbar);
		this.appendSubDateRow(toolbar);
		this.refreshDatesRowView(view);
		this.addHeaderButtons();
		this.showTodayButtonCheckbox(toolbar);
	}
	/**
	 * Function appends and shows today button's checkbox
	 * @param {jQuery} toolbar
	 */
	showTodayButtonCheckbox(toolbar) {
		let todayButton = toolbar.find('.fc-today-button'),
			todyButtonIcon = todayButton.hasClass('fc-state-disabled') ? 'fa-calendar-check' : 'fa-calendar',
			popoverContent = `${app.vtranslate('JS_CURRENT')} ${toolbar.find('.fc-state-active').text().toLowerCase()}`;
		todayButton.removeClass('.fc-button');
		todayButton.html(
			`<div class="js-popover-tooltip--day-btn" data-toggle="popover"><span class="far fa-lg ${todyButtonIcon}"></span></div>`
		);
		app.showPopoverElementView(todayButton.find('.js-popover-tooltip--day-btn'), {
			content: popoverContent,
			container: '.fc-today-button .js-popover-tooltip--day-btn'
		});
	}
	addHeaderButtons() {
		if (this.calendarContainer.find('.js-calendar__view-btn').length) {
			return;
		}
		let buttonsContainer = this.calendarContainer.prev('.js-calendar__header-buttons'),
			viewBtn = buttonsContainer.find('.js-calendar__view-btn').clone(),
			filters = buttonsContainer.find('.js-calendar__filter-container').clone();
		this.calendarContainer.find('.fc-left').prepend(viewBtn);
		this.calendarContainer.find('.fc-center').after(filters);
		this.registerClearFilterButton();
		this.registerFilterTabChange();
	}
	registerFilterTabChange() {
		const thisInstance = this;
		this.getCalendarView()
			.find('.js-calendar__extended-filter-tab')
			.on('shown.bs.tab', function () {
				thisInstance.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
			});
	}
	registerClearFilterButton() {
		const sidebar = this.getSidebarView(),
			calendarView = this.getCalendarView();
		let clearBtn = calendarView.find('.js-calendar__clear-filters');
		app.showPopoverElementView(clearBtn);
		clearBtn.on('click', () => {
			$('.js-calendar__extended-filter-tab a').removeClass('active');
			app.setMainParams('showType', 'current');
			app.moduleCacheSet('defaultShowType', 'current');
			sidebar.find('input:checkbox').prop('checked', false);
			sidebar.find('option:selected').prop('selected', false).trigger('change.select2');
			sidebar.find('.js-sidebar-filter-container').each((_, e) => {
				let element = $(e);
				let cacheName = element.data('cache');
				if (element.data('name') && cacheName) {
					app.moduleCacheSet(cacheName, '');
				}
			});
			let calendarSwitch = sidebar.find('.js-switch--showType [class*="js-switch--label"]'),
				actualUserCheckbox = sidebar.find('.js-input-user-owner-id[value=' + app.getMainParams('userId') + ']');
			calendarSwitch.last().removeClass('active');
			calendarSwitch.first().addClass('active');
			if (actualUserCheckbox.length) {
				actualUserCheckbox.prop('checked', true);
			} else {
				app.setMainParams('usersId', undefined);
			}
			calendarView.fullCalendar('getCalendar').view.options.loadView();
		});
	}
	/**
	 * Function shows change date buttons in calendar's header for specific view
	 * @param view
	 * @param toolbar
	 */
	showChangeDateButtons(view, toolbar) {
		let viewType = view.type.replace(/basic|agenda/g, '').toLowerCase(),
			nextPrevButtons = toolbar.find('.fc-prev-button, .fc-next-button'),
			yearButtons = toolbar.find('.fc-prevYear-button, .fc-nextYear-button');
		if (!window.calendarLoaded) {
			yearButtons.first().html(`<span class="fas fa-xs fa-minus mr-1"></span>${view.options.buttonText['year']}`);
			yearButtons.last().html(`${view.options.buttonText['year']}<span class="fas fa-xs fa-plus ml-1"></span>`);
		}
		if (view.type !== 'year') {
			nextPrevButtons.first().html(`<span class="fas fa-xs fa-minus mr-1"></span>${view.options.buttonText[viewType]}`);
			nextPrevButtons.last().html(`${view.options.buttonText[viewType]}<span class="fas fa-xs fa-plus ml-1"></span>`);
		}
		if (view.type === 'year') {
			nextPrevButtons.hide();
			yearButtons.show();
		} else if (view.type === 'month') {
			nextPrevButtons.show();
			yearButtons.show();
		} else {
			nextPrevButtons.show();
			yearButtons.hide();
		}
	}
	/**
	 * Appends subdate row to calendar header and register its scroll
	 * @param toolbar
	 */
	appendSubDateRow(toolbar) {
		if (!this.calendarContainer.find('.js-dates-row').length) {
			this.subDateRow = $(`
								<div class="js-scroll js-dates-row u-overflow-auto-lg-down order-4 flex-grow-1 position-relative my-1 w-100" data-js="perfectScrollbar | container">
									<div class="d-flex flex-nowrap w-100">
										<div class="js-sub-date-list w-100 sub-date-list row no-gutters flex-nowrap nav nav-tabs" data-js="data-type"></div>
									</div>
								</div>
								`);
			toolbar.append(this.subDateRow);
			if ($(window).width() > app.breakpoints.lg) {
				app.showNewScrollbar(this.subDateRow);
			}
		}
	}

	/**
	 * Get calendar create view.
	 * @returns {promise}
	 */
	getCalendarCreateView() {
		let self = this;
		let aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		let progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView()
			.done(function (data) {
				progressInstance.hide();
				self.calendarCreateView = data;
				aDeferred.resolve(data.clone(true, true));
			})
			.fail(function () {
				progressInstance.hide();
			});
		return aDeferred.promise();
	}
	/**
	 * Set calendar module options.
	 * @returns {{allDaySlot: boolean, dayClick: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		let self = this;
		return {
			allDaySlot: false,
			dayClick: this.eventCreate
				? function (date) {
						self.registerDayClickEvent(date.format());
						self.getCalendarView().fullCalendar('unselect');
				  }
				: false,
			selectable: false,
			eventClick: function (calEvent, jsEvent) {
				jsEvent.preventDefault();
				const link = $(this).attr('href');
				if (link && $.inArray('js-show-modal', calEvent.className) !== -1) {
					app.showModalWindow(null, link);
				}
			}
		};
	}
	/**
	 * Register day click event.
	 * @param {string} date
	 */
	registerDayClickEvent(date) {
		let self = this;
		self.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			let dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase(),
				timeFormat = data.find('[name="time_start"]').data('format'),
				defaultTimeFormat = 'hh:mm A';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			}
			let startDateInstance = Date.parse(date);
			let startDateString = moment(date).format(dateFormat);
			let startTimeString = moment(date).format(defaultTimeFormat);
			let endDateInstance = Date.parse(date);
			let endDateString = moment(date).format(dateFormat);

			let view = self.getCalendarView().fullCalendar('getView');
			let endTimeString;
			if ('month' == view.name) {
				let diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					let defaultFirstHour = app.getMainParams('startHour');
					let explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];
					let defaultLastHour = app.getMainParams('endHour');
					explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					let now = new Date();
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

			App.Components.QuickCreate.showModal(data, {
				callbackFunction(data) {
					self.addCalendarEvent(data.result, dateFormat);
				}
			});
		});
	}
	/**
	 * Add calendar event.
	 */
	addCalendarEvent(eventObject) {
		this.loadCalendarData();
	}
	/**
	 * @deprecated
	 * @param {*} eventObject
	 */
	isNewEventToDisplay(eventObject) {
		let users = this.getSelectedUsersCalendar();
		if (0 === users.length) {
			users = [app.getMainParams('usersId')];
		}
		if ($.inArray(eventObject.assigned_user_id.value, users) < 0) {
			this.refreshFilterValues(eventObject, ownerSelects);
			return false;
		}
		let calendarTypes = $('.js-calendar__filter__select[data-cache="calendar-types"]');
		if (calendarTypes.length) {
			if (!this.eventTypeKeyName) {
				this.setEventTypeKey(eventObject, calendarTypes.data('name'));
			}
			if (
				this.eventTypeKeyName &&
				calendarTypes.val().length &&
				$.inArray(eventObject[this.eventTypeKeyName]['value'], calendarTypes.val()) < 0
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Render calendar
	 */
	renderCalendar() {
		let self = this,
			basicOptions = this.setCalendarOptions(),
			options = {
				header: {
					left: 'year,month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
					center: 'prevYear,prev,title,next,nextYear',
					right: 'today'
				},
				views: {
					basic: {
						eventLimit: false
					},
					year: {
						eventLimit: 10,
						eventLimitText: app.vtranslate('JS_COUNT_RECORDS'),
						titleFormat: 'YYYY',
						select: function (start, end) {},
						loadView: function () {
							self.getCalendarView().fullCalendar('getCalendar').view.render();
						}
					},
					month: {
						titleFormat: this.parseDateFormat('month'),
						loadView: function () {
							self.loadCalendarData();
						}
					},
					week: {
						titleFormat: this.parseDateFormat('week'),
						loadView: function () {
							self.loadCalendarData();
						}
					},
					day: {
						titleFormat: this.parseDateFormat('day'),
						loadView: function () {
							self.loadCalendarData();
						}
					},
					basicDay: {
						type: 'agendaDay',
						loadView: function () {
							self.loadCalendarData();
						}
					}
				},
				select: function (start, end) {
					self.selectDays(start, end);
					self.getCalendarView().fullCalendar('unselect');
				},
				eventRender: function (event, element) {
					self.eventRenderer(event, element);
				},
				viewRender: function (view, element) {
					if (view.type !== 'year') {
						self.loadCalendarData(view);
					}
				},
				addCalendarEvent(calendarDetails) {
					self.getCalendarView().fullCalendar('renderEvent', self.getEventData(calendarDetails));
				}
			};
		options = Object.assign(basicOptions, options);
		this.calendar.fullCalendar(options);
	}

	registerSwitchEvents() {
		const calendarView = this.getCalendarView();
		let isWorkDays,
			switchShowTypeVal,
			switchContainer = $('.js-calendar__tab--filters'),
			switchShowType = switchContainer.find('.js-switch--showType'),
			showTypeState = switchShowType.find('.js-switch--label-on.active').length ? 'current' : 'history',
			switchSwitchingDays = switchContainer.find('.js-switch--switchingDays'),
			switchingDaysState = switchSwitchingDays.find('.js-switch--label-on.active').length ? 'workDays' : 'all';
		let historyParams = app.getMainParams('historyParams', true);
		if (historyParams === '') {
			isWorkDays =
				app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all';
			switchShowTypeVal =
				app.getMainParams('showType') === 'current' && app.moduleCacheGet('defaultShowType') !== 'history';
			if (!switchShowTypeVal) {
				switchShowType.find('.js-switch--label-off').button('toggle');
			}
		} else {
			if (historyParams.time !== undefined) {
				app.setMainParams('showType', historyParams.time);
			}
			app.setMainParams('switchingDays', historyParams.hiddenDays === '' ? 'all' : 'workDays');
		}
		switchShowType.on('change', 'input', (e) => {
			const currentTarget = $(e.currentTarget);
			if (typeof currentTarget.data('on-text') !== 'undefined') {
				app.setMainParams('showType', 'current');
				app.moduleCacheSet('defaultShowType', 'current');
			} else if (typeof currentTarget.data('off-text') !== 'undefined') {
				app.setMainParams('showType', 'history');
				app.moduleCacheSet('defaultShowType', 'history');
			}
			calendarView.fullCalendar('getCalendar').view.options.loadView();
		});
		if (app.getMainParams('showType') !== showTypeState) {
			$('label.active', switchShowType).find('input').filter(':first').change();
		}
		if (switchSwitchingDays.length) {
			if (typeof isWorkDays !== 'undefined' && !isWorkDays) {
				switchSwitchingDays.find('.js-switch--label-off').button('toggle');
			}
			switchSwitchingDays.on('change', 'input', (e) => {
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
				calendarView.fullCalendar('option', 'hiddenDays', hiddenDays);
				calendarView.fullCalendar('option', 'height', this.setCalendarHeight());
				if (calendarView.fullCalendar('getView').type === 'year') {
					this.registerViewRenderEvents(calendarView.fullCalendar('getView'));
				}
			});
			if (app.getMainParams('switchingDays') !== switchingDaysState) {
				$('label.active', switchSwitchingDays).find('input').filter(':first').change();
			}
		}
	}

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
	}

	registerEvents() {
		super.registerEvents();
		this.registerCacheSettings();
		this.registerSwitchEvents();
		ElementQueries.listen();
	}
};
