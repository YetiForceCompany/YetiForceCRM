/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class representing a standard calendar.
 * @extends Calendar_Js
 */
window.Vtiger_Calendar_Js = class Vtiger_Calendar_Js extends Calendar_Js {
	/**
	 * Create calendar's options.
	 * @param {jQuery} container
	 * @param {bool} readonly
	 * @param {boolean} browserHistory
	 */
	constructor(container, readonly, browserHistory = false) {
		super(container, readonly, browserHistory);
	}
	/**
	 * Set calendar module options.
	 * @returns {{allDaySlot: boolean, dayClick: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		const self = this;
		return {
			allDaySlot: false,
			dateClick: (args) => {
				if (this.eventCreate) {
					self.registerDayClickEvent(args);
				}
			},
			selectable: false,
			eventClick: function (info) {
				info.jsEvent.preventDefault();
				const element = $(info.el);
				let link = element.attr('href');
				if (!link) {
					link = element.find('a').attr('href');
				}
				if (link && $.inArray('js-show-modal', info.event.classNames) !== -1) {
					app.showModalWindow(null, link.replace('view=', 'xview=') + '&view=QuickDetailModal');
				}
			}
		};
	}
	/**
	 * Set calendar module's options.
	 * @returns {object}
	 */
	setCalendarAdvancedOptions() {
		const self = this;
		return Object.assign(super.setCalendarAdvancedOptions(), {
			headerToolbar: {
				left: `dayGridMonth,${app.getMainParams('weekView')},${app.getMainParams('dayView')},listWeek,today`,
				center: 'prevYear,prev,title,next,nextYear',
				right: ''
			},
			select: function (info) {
				self.selectDays(info);
			},
			datesSet: function (dateInfo) {
				app.event.trigger('Calendar.DatesSet', dateInfo, this);
				if (self.fullCalendar.view !== 'year') {
					self.loadCalendarData();
				}
			}
		});
	}
	/**
	 * Function invokes by fullCalendar, sets selected days in form
	 * @param {object} info
	 */
	selectDays(info) {
		if (!this.container.find('.js-right-panel-event-link').length) {
			return false;
		}
		this.container.find('.js-right-panel-event-link').tab('show');
		super.selectDays(info);
	}
	/**
	 * Load calendar data
	 */
	loadCalendarData() {
		const self = this,
			defaultParams = this.getDefaultParams(),
			progressInstance = $.progressIndicator({ blockInfo: { enabled: true } });
		self.fullCalendar.removeAllEvents();
		self.clearFilterButton(defaultParams['user']);
		AppConnector.request(defaultParams).done((events) => {
			self.fullCalendar.removeAllEvents();
			self.fullCalendar.addEventSource(events.result);
			progressInstance.progressIndicator({ mode: 'hide' });
		});
	}
	/**
	 * Reload calendar data after changing search parameters
	 */
	reloadCalendarData() {
		super.reloadCalendarData();
		this.updateCountTaskCalendar();
	}
	/**
	 * Show/hide clear filter button
	 */
	clearFilterButton(user) {
		let currentUser = parseInt(app.getMainParams('userId')),
			time = app.getMainParams('showType'),
			statement =
				JSON.stringify(user['selectedIds']) === JSON.stringify([`${currentUser}`]) &&
				this.getCurrentCvId() === undefined &&
				time === 'current';
		$('.js-calendar__clear-filters').toggleClass('d-none', statement);
	}
	/**
	 * Default params
	 * @returns {{module: *, action: string, mode: string, start: *, end: *, user: *, emptyFilters: boolean}}
	 */
	getDefaultParams() {
		let options = super.getDefaultParams(),
			user = this.getSelectedUsersCalendar();
		if (0 === user.length) {
			user = app.getMainParams('usersId');
		}
		if (user === undefined) {
			user = [app.getMainParams('userId')];
		}
		if (this.fullCalendar.view === 'timeGridDay') {
			this.fullCalendar.view.activeEnd = this.fullCalendar.view.activeEnd.add(1, 'day');
		}
		const time = this.getSidebarView().find('.js-switch--showType input:checked').data('val');
		options.time = options.time !== undefined ? time : app.getMainParams('showType');
		options.history = true;
		options.user = user;
		return options;
	}
	/**
	 * Get selected users
	 * @returns {{ selectedIds: array, excludedIds: array }}
	 */
	getSelectedUsersCalendar() {
		const sidebar = this.getSidebarView();
		let selectedUsers = sidebar.find('.js-input-user-owner-id:checked'),
			notSelectedUsers = sidebar.find('.js-input-user-owner-id:not(:checked)'),
			selectedUsersAjax = sidebar.find('.js-input-user-owner-id-ajax'),
			selectedRolesAjax = sidebar.find('.js-input-role-owner-id-ajax'),
			checkboxSelectAll = sidebar.find('.js-select-all'),
			selectedIds = [],
			excludedIds = [];

		let ifSelectAllIsChecked = checkboxSelectAll.length > 0 && checkboxSelectAll.is(':checked');
		if (ifSelectAllIsChecked) {
			selectedIds.push('all');
		} else if (selectedUsers.length > 0) {
			selectedUsers.each(function () {
				selectedIds.push($(this).val());
			});
		}
		if (selectedUsersAjax.length > 0) {
			selectedIds = selectedUsersAjax.val().concat(selectedRolesAjax.val());
		}
		if (ifSelectAllIsChecked && notSelectedUsers) {
			notSelectedUsers.each(function () {
				excludedIds.push($(this).val());
			});
		}
		if (0 === selectedIds.length && CONFIG.userId) {
			selectedIds.push(CONFIG.userId);
		}
		return { selectedIds: selectedIds, excludedIds: excludedIds };
	}
	/**
	 * Register day click event.
	 * @param {object} info
	 */
	registerDayClickEvent(info) {
		const self = this,
			userFormat = App.Fields.Date.dateToUserFormat(info.date);
		if (!CONFIG.isQuickCreateSupported) {
			app.openUrl(
				'index.php?module=' +
					(this.module ? this.module : CONFIG.module) +
					'&view=Edit&date_start=' +
					userFormat +
					'&due_date=' +
					userFormat
			);
			return;
		}
		self.getCalendarCreateView().done((data) => {
			App.Components.QuickCreate.showModal(data, {
				callbackFunction: () => {
					self.reloadCalendarData();
				},
				callbackBeforeRegister: (modal) => {
					modal.find('.js-selected-date').val(App.Fields.Date.dateToDbFormat(info.date));
				},
				callbackPostShown: (modal) => {
					self.dayCallbackCreateModal(modal, info);
				}
			});
		});
	}
	/**
	 * Callback after shown create modal
	 * @param {jQuery} modal
	 * @param {object} info
	 */
	dayCallbackCreateModal(modal, info) {
		let dateFormat = modal.find('[name="date_start"]').data('dateFormat'),
			timeFormat = modal.find('[name="time_start"]').data('format'),
			defaultTimeFormat = 'hh:mm A',
			userFormat = App.Fields.Date.dateToUserFormat(info.date, dateFormat),
			endTimeString;

		if (timeFormat == 24) {
			defaultTimeFormat = 'HH:mm';
		}
		let startTimeString = moment(info.date).format(defaultTimeFormat);
		if ('dayGridMonth' == this.fullCalendar.view.type) {
			let now = new Date();
			startTimeString = moment(now).format(defaultTimeFormat);
			endTimeString = moment(now).add(15, 'minutes').format(defaultTimeFormat);
		} else {
			endTimeString = moment(info.date).add(30, 'minutes').format(defaultTimeFormat);
		}
		modal.find('[name="date_start"]').val(userFormat);
		modal.find('[name="due_date"]').val(userFormat);
		modal.find('[name="time_start"]').val(startTimeString);
		modal.find('[name="time_end"]').val(endTimeString);
	}
	/**
	 * Register switch events
	 */
	registerSwitchEvents() {
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
			this.reloadCalendarData();
		});
		if (app.getMainParams('showType') !== showTypeState) {
			$('label.active', switchShowType).find('input').filter(':first').trigger('change');
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
				this.fullCalendar.setOption('hiddenDays', hiddenDays);
				this.fullCalendar.setOption('height', this.setCalendarHeight());
			});
			if (app.getMainParams('switchingDays') !== switchingDaysState) {
				$('label.active', switchSwitchingDays).find('input').filter(':first').trigger('change');
			}
		}
	}

	/**
	 * Function toggles next year/month and general arrows on view render
	 */
	registerViewRenderEvents() {
		let toolbar = this.calendarView.find('.fc-toolbar.fc-header-toolbar');
		this.showChangeDateButtons(toolbar);
		this.appendSubDateRow(toolbar);
		this.refreshDatesRowView();
		this.addHeaderButtons();
		this.showTodayButtonCheckbox(toolbar);
		app.event.on('Calendar.DatesSet', () => {
			this.showChangeDateButtons(toolbar);
			this.refreshDatesRowView();
			this.showTodayButtonCheckbox(toolbar);
		});
	}
	/**
	 * Function shows change date buttons in calendar's header for specific view
	 * @param {jQuery} toolbar
	 */
	showChangeDateButtons(toolbar) {
		const view = this.fullCalendar.view;
		const buttonText = this.calendarOptions.buttonText;
		let nextPrevButtons = toolbar.find('.fc-prev-button, .fc-next-button'),
			yearButtons = toolbar.find('.fc-prevYear-button, .fc-nextYear-button');
		yearButtons.first().html(`<span class="fas fa-xs fa-minus mr-1"></span>${buttonText['year']}`);
		yearButtons.last().html(`${buttonText['year']}<span class="fas fa-xs fa-plus ml-1"></span>`);
		if (view.type !== 'year' && Calendar_Js.viewsNamesLabels[view.type]) {
			let viewType = Calendar_Js.viewsNamesLabels[view.type];
			nextPrevButtons.first().html(`<span class="fas fa-xs fa-minus mr-1"></span>${buttonText[viewType]}`);
			nextPrevButtons.last().html(`${buttonText[viewType]}<span class="fas fa-xs fa-plus ml-1"></span>`);
		}
		if (view.type === 'year') {
			nextPrevButtons.hide();
			yearButtons.show();
		} else if (view.type === 'dayGridMonth') {
			nextPrevButtons.show();
			yearButtons.show();
		} else if (view.type === 'list') {
			nextPrevButtons.hide();
			yearButtons.hide();
		} else {
			nextPrevButtons.show();
			yearButtons.hide();
		}
	}
	/**
	 * Appends sub date row to calendar header and register its scroll
	 * @param {jQuery} toolbar
	 */
	appendSubDateRow(toolbar) {
		if (!this.calendarView.find('.js-dates-row').length) {
			this.subDateRow =
				$(`<div class="js-scroll js-dates-row u-overflow-auto-xl-down order-4 flex-grow-1 position-relative my-1 w-100" data-js="perfectScrollbar | container">
						<div class="d-flex flex-nowrap w-100">
							<div class="js-sub-date-list w-100 sub-date-list row no-gutters flex-nowrap nav nav-tabs" data-js="data-type"></div>
						</div>
					</div>`);
			toolbar.append(this.subDateRow);
			if ($(window).width() > app.breakpoints.lg) {
				app.showNewScrollbar(toolbar);
			}
		}
	}
	/**
	 * Refresh date bar with counts
	 */
	refreshDatesRowView() {
		const self = this;
		switch (this.fullCalendar.view.type) {
			case 'year':
				self.generateYearList();
				break;
			case 'dayGridMonth':
				self.generateMonthList();
				break;
			case 'dayGridWeek':
			case 'timeGridWeek':
			case 'listWeek':
				self.generateWeekList();
				break;
			case 'dayGridWeek':
			case 'timeGridDay':
				self.generateDaysList();
				break;
			default:
				this.container.find('.js-dates-row .js-sub-date-list').html('');
				break;
		}
		self.updateCountTaskCalendar();
		self.registerDatesChange();
	}
	/**
	 * Generate days bar list
	 */
	generateDaysList() {
		const datesView = this.container.find('.js-dates-row'),
			activeDays = moment(this.fullCalendar.view.currentStart).format('DDD'),
			nextDays = moment(this.fullCalendar.view.currentStart).add(7, 'days');
		let prevDays = moment(this.fullCalendar.view.currentStart).subtract(5, 'days'),
			daysToShow = nextDays.diff(prevDays, 'days'),
			html = '';

		for (let day = 0; day < daysToShow; ++day) {
			if (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all') {
				if ($.inArray(prevDays.day(), app.getMainParams('hiddenDays', true)) !== -1) {
					prevDays = moment(prevDays).add(1, 'days');
					daysToShow++;
					continue;
				}
			}
			let date = prevDays.format('YYYY-MM-DD'),
				dateUser = App.Fields.Date.dateToUserFormat(date),
				active = '';
			if (prevDays.format('DDD') === activeDays) {
				active = 'active';
			}
			html += `<div data-date="${date}" data-dates="${date}|${date}" data-type="days"
				class="js-sub-record sub-record nav-item col-1 px-0" data-js="click">
				<div class="sub-record-content nav-link js-popover-tooltip ${active}"
					title="${App.Fields.Date.fullDaysTranslated[prevDays.format('d')]} ${dateUser}" data-js="class: active">
				<div class="sub-date-name">${app.vtranslate('JS_DAY_SHORT')} ${prevDays.format('DD')}
				<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
				</div></div></div>`;
			prevDays = moment(prevDays).add(1, 'days');
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	/**
	 * Generate weeks bar list
	 */
	generateWeekList() {
		const datesView = this.container.find('.js-dates-row'),
			activeWeek = moment(this.fullCalendar.view.currentStart).format('WW'),
			nextWeeks = moment(this.fullCalendar.view.currentStart).add(6, 'weeks');
		let prevWeeks = moment(this.fullCalendar.view.currentStart).subtract(5, 'weeks'),
			html = '';
		while (prevWeeks.format('YYYY-MM-DD') <= nextWeeks.format('YYYY-MM-DD')) {
			let date = prevWeeks.format('YYYY-MM-DD'),
				dateEnd = moment(prevWeeks).add(6, 'day').format('YYYY-MM-DD'),
				dateUser = App.Fields.Date.dateToUserFormat(date),
				dateEndUser = App.Fields.Date.dateToUserFormat(dateEnd),
				active = '';
			if (prevWeeks.format('WW') === activeWeek) {
				active = 'active';
			}
			html += `<div data-date="${date}" data-dates="${date}|${dateEnd}"
				class="js-sub-record sub-record nav-item col-1 px-0" data-type="weeks" data-js="click">
				<div class="sub-record-content nav-link js-popover-tooltip ${active}" title="${dateUser} > ${dateEndUser}" data-js="class: active">
				<div class="sub-date-name">${app.vtranslate('JS_WEEK_SHORT')} ${prevWeeks.format('WW')}
				<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
				</div></div></div>`;
			prevWeeks.add(1, 'weeks');
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	/**
	 * Generate month bar list
	 */
	generateMonthList() {
		const datesView = this.container.find('.js-dates-row'),
			activeMonth = this.fullCalendar.view.currentStart.getMonth(),
			activeYear = this.fullCalendar.view.currentStart.getFullYear();
		let html = '';
		for (let month = 0; 12 > month; ++month) {
			let m = month <= 8 ? '0' + (month + 1) : month + 1,
				lastDay = App.Fields.Date.getLastMonthDay(activeYear, m),
				date = activeYear + '-' + m + '-01',
				dateEnd = activeYear + '-' + m + '-' + lastDay,
				dateUser = App.Fields.Date.dateToUserFormat(date),
				dateEndUser = App.Fields.Date.dateToUserFormat(dateEnd),
				active = '';
			if (month === activeMonth) {
				active = 'active';
			}
			html += `<div data-date="${date}" data-dates="${date}|${dateEnd}"
				class="js-sub-record sub-record nav-item col-1 px-0" data-type="months" data-js="click">
				<div class="sub-record-content nav-link js-popover-tooltip ${active}" title="${dateUser} > ${dateEndUser}" data-js="class: active">
				<div class="sub-date-name">${App.Fields.Date.monthsTranslated[month]}
				<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
				</div></div></div>`;
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	/**
	 * Generate year bar list
	 */
	generateYearList() {
		const datesView = this.container.find('.js-dates-row'),
			activeYear = this.fullCalendar.view.currentStart.getFullYear(),
			nextYear = activeYear + 1;
		let prevYear = activeYear - 1,
			html = '';
		while (prevYear <= nextYear) {
			let date = prevYear + '-01-01',
				dateEnd = prevYear + '-12-31',
				active = '';
			if (prevYear === activeYear) {
				active = 'active';
			}
			html += `<div data-date="${date}" data-dates="${date}|${dateEnd}"
				class="js-sub-record sub-record col-4 nav-item" data-type="years" data-js="click">
				<div class="sub-record-content nav-link ${active}" data-js="class: active">
				<div class="sub-date-name">${prevYear}<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div></div>
				</div></div>`;
			prevYear = prevYear + 1;
		}
		datesView.find('.js-sub-date-list').html(html);
	}
	/**
	 * Counting the number of events in the bar for the current view
	 */
	updateCountTaskCalendar() {
		const datesView = this.container.find('.js-dates-row'),
			subDatesElements = datesView.find('.js-sub-record');
		let options = this.getDefaultParams(),
			dateArray = {};
		delete options.start;
		delete options.end;
		subDatesElements.each(function (key) {
			dateArray[key] = $(this).data('dates').split('|');
		});
		options.mode = 'getCountEventsGroup';
		options.dates = dateArray;
		AppConnector.request(options).done(function (events) {
			subDatesElements.each(function (key) {
				$(this).find('.js-count-events').removeClass('hide').html(events.result[key]);
			});
		});
	}
	/**
	 * Registration of the date change in the counting the number of events bar
	 */
	registerDatesChange() {
		this.container.find('.js-dates-row .js-sub-record').on('click', (e) => {
			let currentTarget = $(e.currentTarget);
			currentTarget.addClass('active');
			this.fullCalendar.gotoDate(currentTarget.data('date'));
		});
	}
	/**
	 * Add header buttons
	 */
	addHeaderButtons() {
		if (this.calendarView.find('.js-calendar__view-btn').length) {
			return;
		}
		let buttonsContainer = this.calendarView.prev('.js-calendar__header-buttons'),
			viewBtn = buttonsContainer.find('.js-calendar__view-btn').clone(),
			filters = buttonsContainer.find('.js-calendar__filter-container').clone(),
			toolbar = this.calendarView.find('.fc-toolbar-chunk');
		toolbar.first().addClass('fc-left');
		toolbar.eq(1).addClass('fc-center');
		this.calendarView.find('.fc-left .fc-button-group').prepend(viewBtn);
		this.calendarView.find('.fc-center').after(filters);
		this.registerClearFilterButton();
		this.registerFilterTabChange();
	}
	/**
	 * Register clear filter button
	 */
	registerClearFilterButton() {
		const sidebar = this.getSidebarView(),
			clearBtn = this.calendarView.find('.js-calendar__clear-filters');
		app.showPopoverElementView(clearBtn);
		clearBtn.on('click', () => {
			$('.js-calendar__extended-filter-tab a').removeClass('active');
			app.moduleCacheSet('CurrentCvId', null);
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
			$('input[data-val="current"]', calendarSwitch).prop('checked', true);
			if (actualUserCheckbox.length) {
				actualUserCheckbox.prop('checked', true);
			} else {
				app.setMainParams('usersId', undefined);
			}
			this.reloadCalendarData();
		});
	}
	/**
	 * Register filter tab change
	 */
	registerFilterTabChange() {
		this.calendarView.find('.js-calendar__extended-filter-tab').on('shown.bs.tab', () => {
			this.reloadCalendarData();
			app.moduleCacheSet('CurrentCvId', this.getCurrentCvId());
		});
	}
	/**
	 * Function appends and shows today button's checkbox
	 * @param {jQuery} toolbar
	 */
	showTodayButtonCheckbox(toolbar) {
		let todayButton = toolbar.find('.fc-today-button'),
			todyButtonIcon = todayButton.attr('disabled') ? 'fa-calendar-check' : 'fa-calendar',
			popoverContent = todayButton.attr('title');
		todayButton.html(`<div class="js-popover-tooltip"><span class="far fa-lg ${todyButtonIcon}"></span></div>`);
		app.showPopoverElementView(todayButton.find('.js-popover-tooltip'), {
			title: popoverContent
		});
	}
	/**
	 * Registration of the event being added to favorite users
	 */
	registerPinUser() {
		const self = this;
		this.getSidebarView()
			.find('.js-pin-user')
			.on('click', function () {
				const element = $(this);
				AppConnector.request({
					module: self.module,
					action: 'Calendar',
					mode: 'pinOrUnpinUser',
					element_id: element.data('elementid')
				}).done((data) => {
					if (data.result === 'unpin') {
						element.find('.js-pin-icon').removeClass('fas').addClass('far');
					} else if (data.result === 'pin') {
						element.find('.js-pin-icon').removeClass('far').addClass('fas');
					} else {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					}
				});
			});
	}
	/**
	 * Register cache settings
	 */
	registerCacheSettings() {}
	/**
	 * Register events
	 */
	registerEvents() {
		super.registerEvents();
		this.registerCacheSettings();
		this.registerSwitchEvents();
		this.registerPinUser();
		ElementQueries.listen();
	}
};
