/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
/**
 *  Class representing an extended calendar.
 * @extends Calendar_Calendar_Js
 */
window.calendarLoaded = false; //Global calendar flag needed for correct loading data from history browser in year view
window.Calendar_CalendarExtended_Js = class extends Calendar_Calendar_Js {

	constructor(container, readonly) {
		super(container, readonly);
		this.sidebarView = {
			length: 0
		};
		this.calendarContainer = false;
		this.addCommonMethodsToYearView();
		this.calendar = this.getCalendarView();
	}

	/**
	 * Function extends FC.views.year with current class methods
	 */
	addCommonMethodsToYearView() {
		const self = this;
		FC.views.year = FC.views.year.extend({
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
			showChangeDateButtons: self.showChangeDateButtons,
			showTodayButtonCheckbox: self.showTodayButtonCheckbox,
		});
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
				editable: !self.readonly,
				views: {
					basic: {
						eventLimit: false,
					},
					year: {
						eventLimit: 10,
						eventLimitText: app.vtranslate('JS_COUNT_RECORDS'),
						titleFormat: 'YYYY',
						select: function (start, end) {
						},
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
		if (!this.readonly) {
			options.eventClick = function (calEvent, jsEvent) {
				jsEvent.preventDefault();
				self.getCalendarSidebarData($(this).attr('href'));
			};
		} else {
			options.eventClick = function (calEvent, jsEvent) {
				jsEvent.preventDefault();
			};
		}
		this.calendar.fullCalendar(options);
	}

	registerChangeView() {
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

	registerSwitchEvents() {
		const calendarView = this.getCalendarView();
		let isWorkDays,
			switchShowTypeVal,
			switchContainer = $('.js-calendar__tab--filters'),
			switchShowType = switchContainer.find('.js-switch--showType'),
			switchSwitchingDays = switchContainer.find('.js-switch--switchingDays');
		let historyParams = app.getMainParams('historyParams', true);
		if (historyParams === '') {
			isWorkDays = (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all'),
				switchShowTypeVal = (app.getMainParams('showType') === 'current' && app.moduleCacheGet('defaultShowType') !== 'history');
			if (!switchShowTypeVal) {
				switchShowType.find('.js-switch--label-off').button('toggle');
			}
		} else {
			app.setMainParams('showType', historyParams.time);
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
		todayButton.html(`<div class="js-popover-tooltip--day-btn" data-toggle="popover"><span class="far fa-lg ${todyButtonIcon}"></span></div>`)
		app.showPopoverElementView(todayButton.find('.js-popover-tooltip--day-btn'), {
			content: popoverContent,
			container: '.fc-today-button .js-popover-tooltip--day-btn'
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

	registerDatesChange() {
		this.container.find('.js-dates-row .js-sub-record').on('click', (e) => {
			let currentTarget = $(e.currentTarget);
			currentTarget.addClass('active');
			this.getCalendarView().fullCalendar('gotoDate', moment(currentTarget.data('date'), "YYYY-MM-DD"));
		});
	}

	getCurrentCvId() {
		return $(".js-calendar__extended-filter-tab .active").parent('.js-filter-tab').data('cvid');
	}

	registerFilterTabChange() {
		const thisInstance = this;
		this.getCalendarView().find(".js-calendar__extended-filter-tab").on('shown.bs.tab', function () {
			thisInstance.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
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

	getSidebarView() {
		if (!this.sidebarView.length) {
			this.sidebarView = $('#rightPanel');
		}
		return this.sidebarView;
	}

	updateCountTaskCalendar() {
		let datesView = this.container.find('.js-dates-row'),
			subDatesElements = datesView.find('.js-sub-record'),
			dateArray = {},
			userDateFormat = CONFIG.dateFormat.toUpperCase(),
			user = this.getSelectedUsersCalendar();
		if (user.length === 0) {
			user = app.getMainParams('usersId');
		}
		if (user === undefined) {
			user = [app.getMainParams('userId')];
		}
		subDatesElements.each(function (key, element) {
			let data = $(this).data('date'),
				type = $(this).data('type');
			if (type === 'years') {
				dateArray[key] = [moment(data + '-01').format(userDateFormat) + ' 00:00:00', moment(data + '-01').endOf('year').format(userDateFormat) + ' 23:59:59'];
			} else if (type === 'months') {
				dateArray[key] = [moment(data).format(userDateFormat) + ' 00:00:00', moment(data).endOf('month').format(userDateFormat) + ' 23:59:59'];
			} else if (type === 'weeks') {
				dateArray[key] = [moment(data).format(userDateFormat) + ' 00:00:00', moment(data).add(6, 'day').format(userDateFormat) + ' 23:59:59'];
			} else if (type === 'days') {
				dateArray[key] = [moment(data).format(userDateFormat) + ' 00:00:00', moment(data).format(userDateFormat) + ' 23:59:59'];
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
	}

	/**
	 * Register events to EditView
	 * @param {jQuery} sideBar
	 */
	registerEditForm(sideBar) {
		let editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('[name="module"]').val()),
			headerInstance = new Vtiger_Header_Js(),
			params = [];
		let rightFormCreate = sideBar.find('form[name="QuickCreate"]');
		editViewInstance.registerBasicEvents(rightFormCreate);
		rightFormCreate.validationEngine(app.validationEngineOptions);
		headerInstance.registerHelpInfo(rightFormCreate);
		App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
		sideBar.find('.js-summary-close-edit').on('click', () => {
			this.getCalendarCreateView();
		});
		params.callbackFunction = this.registerAfterSubmitForm(this, rightFormCreate);
		headerInstance.registerQuickCreatePostLoadEvents(rightFormCreate, params);
		new App.Fields.Text.Editor(sideBar.find('.js-editor'), {height: '5em', toolbar: 'Min'});
	}

	/**
	 * EditView
	 * @param {Object}|{number} params
	 */
	getCalendarSidebarData(params) {
		const thisInstance = this,
			aDeferred = $.Deferred();
		const progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		if ($.isNumeric(params)) {
			params = {
				module: app.getModuleName(),
				view: 'EventForm',
				record: params
			};
		}
		AppConnector.request(params).done((data) => {
			thisInstance.openRightPanel();
			progressInstance.progressIndicator({mode: 'hide'});
			let sideBar = thisInstance.getSidebarView();
			sideBar.find('.js-qc-form').html(data);
			thisInstance.showRightPanelForm();
			if (sideBar.find('form').length) {
				thisInstance.registerEditForm(sideBar);
			} else {
				app.showNewScrollbar(sideBar.find('.js-calendar__form__wrapper'), {suppressScrollX: true});
				sideBar.find('.js-activity-state .js-summary-close-edit').on('click', function () {
					thisInstance.getCalendarCreateView();
				});
				sideBar.find('.js-activity-state .editRecord').on('click', function () {
					thisInstance.getCalendarSidebarData($(this).data('id'));
				});
			}
			aDeferred.resolve(sideBar.find('.js-qc-form'));
		}).fail((error) => {
			progressInstance.progressIndicator({mode: 'hide'});
			app.errorLog(error);
		});
		return aDeferred.promise();
	}

	loadCalendarData(view = this.getCalendarView().fullCalendar('getView')) {
		const self = this;
		let formatDate = CONFIG.dateFormat.toUpperCase(),
			cvid = self.getCurrentCvId(),
			calendarInstance = this.getCalendarView();
		calendarInstance.fullCalendar('removeEvents');
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}}),
			user = self.getSelectedUsersCalendar();
		if (0 === user.length) {
			user = app.getMainParams('usersId');
		}
		if (user === undefined) {
			user = [app.getMainParams('userId')];
		}
		self.clearFilterButton(user, cvid);
		if (view.type === 'agendaDay') {
			self.selectDays(view.start, view.end);
			view.end = view.end.add(1, 'day');
		}
		let options = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: view.start.format(formatDate),
			end: view.end.format(formatDate),
			user: user,
			time: app.getMainParams('showType'),
			cvid: cvid,
			historyUrl: `index.php?module=Calendar&view=CalendarExtended&history=true&viewType=${view.type}&start=${view.start.format(formatDate)}&end=${view.end.format(formatDate)}&user=${user}&time=${app.getMainParams('showType')}&cvid=${cvid}&hiddenDays=${view.options.hiddenDays}`
		};
		let connectorMethod = window["AppConnector"]["request"];
		if (!this.readonly && window.calendarLoaded) {
			connectorMethod = window["AppConnector"]["requestPjax"];
		}
		if (this.browserHistoryConfig && Object.keys(this.browserHistoryConfig).length && !window.calendarLoaded) {
			options = Object.assign(options, {
				start: this.browserHistoryConfig.start,
				end: this.browserHistoryConfig.end,
				user: this.browserHistoryConfig.user,
				time: this.browserHistoryConfig.time,
				cvid: this.browserHistoryConfig.cvid
			});
			connectorMethod = window["AppConnector"]["request"];
			app.setMainParams('showType', this.browserHistoryConfig.time);
			app.setMainParams('usersId', this.browserHistoryConfig.user);
		}
		connectorMethod(options).done((events) => {
			calendarInstance.fullCalendar('removeEvents');
			calendarInstance.fullCalendar('addEventSource', events.result);
			progressInstance.progressIndicator({mode: 'hide'});
		});
		self.registerViewRenderEvents(view);
		window.calendarLoaded = true;
	}

	clearFilterButton(user, cvid) {
		let currentUser = parseInt(app.getMainParams('userId')),
			time = app.getMainParams('showType'),
			statement = ((user.length === 0 || (user.length === 1 && parseInt(user) === currentUser)) && cvid === undefined && time === 'current');
		$(".js-calendar__clear-filters").toggleClass('d-none', statement);
	}

	registerClearFilterButton() {
		const sidebar = this.getSidebarView(),
			calendarView = this.getCalendarView();
		let clearBtn = calendarView.find('.js-calendar__clear-filters');
		app.showPopoverElementView(clearBtn);
		clearBtn.on('click', () => {
			$(".js-calendar__extended-filter-tab a").removeClass('active');
			app.setMainParams('showType', 'current');
			app.moduleCacheSet('defaultShowType', 'current');
			sidebar.find("input:checkbox").prop('checked', false);
			sidebar.find("option:selected").prop('selected', false);
			let calendarSwitch = sidebar.find('.js-switch--showType [class*="js-switch--label"]'),
				actualUserCheckbox = sidebar.find(".js-input-user-owner-id[value=" + app.getMainParams('userId') + "]");
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
			html +=
				`<div class="js-sub-record sub-record col-4 nav-item" data-date="${prevYear.format('YYYY')}" data-type="years" data-js="click | class: active">
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
			html +=
				`<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="months" data-date="${moment(dateStart).month(month).format('YYYY-MM')}" data-js="click | class: active">
					<div class="sub-record-content nav-link ${active}">
						<div class="sub-date-name">${app.vtranslate('JS_' + moment().month(month).format('MMM').toUpperCase()).toUpperCase()}
							<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
						</div>
					</div>
				</div>`;
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
			html += '<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="weeks" data-date="' + prevWeeks.format('YYYY-MM-DD') + '" data-js="click | class: active">' +
				'<div class="sub-record-content nav-link' + active + '">' +
				'<div class="sub-date-name">' + app.vtranslate('JS_WEEK_SHORT') + ' ' + prevWeeks.format('WW') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevWeeks = moment(prevWeeks).add(1, 'weeks');
		}
		datesView.find('.js-sub-date-list').html(html);
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
			html += '<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="days" data-date="' + prevDays.format('YYYY-MM-DD') + '" data-js="click | class: active">' +
				'<div class="sub-record-content nav-link' + active + '">' +
				'<div class="sub-date-name">' + app.vtranslate('JS_DAY_SHORT') + ' ' + prevDays.format('DD') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevDays = moment(prevDays).add(1, 'days');
		}
		datesView.find('.js-sub-date-list').html(html);
	}

	selectDays(startDate, endDate) {
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
			if (data.find('.js-autofill').prop('checked') === true) {
				Calendar_Edit_Js.getInstance().getFreeTime(data);
			} else {
				data.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
				data.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
			}
		});
	}

	registerUsersChange(formContainer) {
		formContainer.find('.js-input-user-owner-id-ajax, .js-input-user-owner-id').on('change', () => {
			this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
		this.registerPinUser();
	}

	/**
	 * Register actions to do after save record
	 * @param instance
	 * @param data
	 * @returns {function}
	 */
	registerAfterSubmitForm(self, data) {
		const calendarView = this.getCalendarView();
		let returnFunction = function (data) {
			if (data.success) {
				let recordActivityStatus = data.result.activitystatus.value,
					historyStatus = app.getMainParams('activityStateLabels', true).history,
					inHistoryStatus = $.inArray(recordActivityStatus, historyStatus),
					showType = app.getMainParams('showType');
				if ((-1 !== inHistoryStatus && 'history' === showType) || (-1 === inHistoryStatus && 'history' !== showType)) {
					if (calendarView.fullCalendar('clientEvents', data.result._recordId)[0]) {
						self.updateCalendarEvent(data.result._recordId, data.result);
					} else {
						const calendarInstance = calendarView.fullCalendar('getCalendar');
						if (calendarInstance.view.type !== 'year') {
							calendarInstance.view.options.addCalendarEvent(data.result);
						} else {
							calendarInstance.view.render();
						}
						if (data.result.followup.value !== undefined) {
							calendarView.fullCalendar('removeEvents', data.result.followup.value);
						}
					}
				}
				self.refreshDatesRowView(calendarView.fullCalendar('getView'));
				self.getSidebarView().find('.js-qc-form').html('');
				self.getCalendarCreateView();
				window.popoverCache = {};
			}
		};
		return returnFunction;
	}

	openRightPanel() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		if (calendarRightPanel.hasClass('hideSiteBar')) {
			calendarRightPanel.find('.js-toggle-site-bar-right-button').trigger('click');
		}
	}

	showRightPanelForm() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		if (!calendarRightPanel.find('.js-right-panel-event').hasClass('active')) {
			calendarRightPanel.find('.js-right-panel-event-link').trigger('click');
		}
		app.showNewScrollbar(calendarRightPanel.find('.js-calendar__form__wrapper'), {
			suppressScrollX: true
		});
	}

	registerSiteBarEvents() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		calendarRightPanel.find('.js-show-sitebar').on('click', () => {
			if (calendarRightPanel.hasClass('hideSiteBar')) {
				calendarRightPanel.find('.js-toggle-site-bar-right-button').trigger('click');
			}
		});
	}

	/**
	 * Parse calendar data
	 * @param {Object} calendarDetails
	 * @returns {{id: *, title: *, start: *, end: *, module: string, url: string, className: string[], start_display: *, end_display: *}}
	 */
	getEventData(calendarDetails) {
		let calendar = this.getCalendarView(),
			startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value),
			endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value),
			eventObject = {
				id: calendarDetails._recordId,
				title: calendarDetails.subject.display_value,
				start: startDate.format(),
				end: endDate.format(),
				module: 'Calendar',
				url: 'index.php?module=Calendar&view=ActivityState&record=' + calendarDetails._recordId,
				className: ['ownerCBg_' + calendarDetails.assigned_user_id.value, ' picklistCBr_Calendar_activitytype_' + calendarDetails.activitytype.value, 'js-popover-tooltip--record'],
				start_display: calendarDetails.date_start.display_value,
				end_display: calendarDetails.due_date.display_value
			};
		if (calendarDetails.isEditable && app.getMainParams('showEditForm')) {
			eventObject.url = 'index.php?module=Calendar&view=EventForm&record=' + eventObject.id;
		}
		return eventObject;
	}

	/**
	 * Update Event
	 * @param {Number} calendarEventId
	 * @param {Object} calendarDetails
	 */
	updateCalendarEvent(calendarEventId, calendarDetails) {
		const calendar = this.getCalendarView();
		let recordToUpdate = calendar.fullCalendar('clientEvents', calendarEventId)[0];
		$.extend(recordToUpdate, this.getEventData(calendarDetails));
		calendar.fullCalendar('updateEvent', recordToUpdate);
	}

	getCalendarCreateView() {
		let aDeferred = $.Deferred();
		if (this.eventCreate) {
			const thisInstance = this;
			let sideBar = thisInstance.getSidebarView(),
				qcForm = sideBar.find('.js-qc-form');
			if (qcForm.find('form').length > 0 && qcForm.find('input[name=record]').length === 0) {
				aDeferred.resolve(qcForm);
			} else {
				let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
				this.getCalendarSidebarData({'module': app.getModuleName(), 'view': 'EventForm',}).done(() => {
					progressInstance.progressIndicator({mode: 'hide'});
					thisInstance.registerAutofillTime();
					aDeferred.resolve(qcForm);
				}).fail((error) => {
					progressInstance.progressIndicator({mode: 'hide'});
					app.errorLog(error);
				});
			}
		} else {
			aDeferred.reject()
		}
		return aDeferred.promise();
	}

	/**
	 * Autoselect date in create view in extended calendar
	 */
	registerAutofillTime() {
		if (app.getMainParams('autofillTime')) {
			this.container.find('.js-autofill').prop('checked', 'checked').trigger('change');
		}
	}

	registerPinUser() {
		$('.js-pin-user').off('click').on('click', function () {
			const thisInstance = $(this);
			AppConnector.request({
				'module': app.getModuleName(),
				'action': 'Calendar',
				'mode': 'pinOrUnpinUser',
				'element_id': thisInstance.data('elementid'),
			}).done((data) => {
				let response = data.result;
				if (response === 'unpin') {
					thisInstance.find('.js-pin-icon').removeClass('fas').addClass('far');
				} else if (response === 'pin') {
					thisInstance.find('.js-pin-icon').removeClass('far').addClass('fas');
				} else {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_ERROR'));
				}
			});
		});
	}

	registerAddForm() {
		const thisInstance = this;
		let sideBar = thisInstance.getSidebarView();
		thisInstance.getCalendarCreateView();
		let user = app.getMainParams('usersId');
		if (user === undefined) {
			user = [app.getMainParams('userId')];
		}
		AppConnector.request(`index.php?module=Calendar&view=RightPanelExtended&mode=getUsersList&user=${user}`).done(
			function (data) {
				if (data) {
					let formContainer = sideBar.find('.js-users-form');
					formContainer.html(data);
					thisInstance.registerUsersChange(formContainer);
					App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
					app.showNewScrollbar(formContainer, {
						suppressScrollX: true
					});
				}
			}
		);
		AppConnector.request(`index.php?module=Calendar&view=RightPanelExtended&mode=getGroupsList&user=${user}`).done(
			function (data) {
				if (data) {
					let formContainer = sideBar.find('.js-group-form');
					formContainer.html(data);
					thisInstance.registerUsersChange(formContainer);
					App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
					formContainer.addClass('u-min-h-30per');
					app.showNewScrollbar(formContainer, {
						suppressScrollX: true
					});
				}
			}
		);
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

	/**
	 * Register filter for users and groups
	 */
	registerFilterForm() {
		const self = this;
		this.getSidebarView().find('a[data-toggle="tab"]').one('shown.bs.tab', function (e) {
			$(".js-filter__search").on('keyup', self.findElementOnList.bind(self));
		});
	}

	/**
	 * Register popover buttons' click
	 */
	registetPopoverButtonsClickEvent() {
		$(document).on('click', '.js-calendar-popover__button', (e) => {
			e.preventDefault();
			this.getCalendarSidebarData($(e.currentTarget).attr('href'));
		});
	}

	/**
	 * Register events
	 */
	registerEvents() {
		super.registerEvents();
		this.registerAddForm();
		this.registerSiteBarEvents();
		this.registerFilterForm();
		this.registetPopoverButtonsClickEvent();
		ElementQueries.listen();
	}
}
