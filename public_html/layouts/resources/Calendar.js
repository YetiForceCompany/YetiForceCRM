/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/** Class representing a calendar. */
window.Calendar_Js = class {
	static monthFormat = {
		'yyyy-mm-dd': 'YYYY-MMMM',
		'mm-dd-yyyy': 'MMMM-YYYY',
		'dd-mm-yyyy': 'MMMM-YYYY',
		'yyyy.mm.dd': 'YYYY.MMMM',
		'mm.dd.yyyy': 'MMMM.YYYY',
		'dd.mm.yyyy': 'MMMM.YYYY',
		'yyyy/mm/dd': 'YYYY/MMMM',
		'mm/dd/yyyy': 'MMMM/YYYY',
		'dd/mm/yyyy': 'MMMM/YYYY'
	};
	static viewsNamesMap = {
		month: 'dayGridMonth',
		basicWeek: 'dayGridWeek',
		basicDay: 'dayGridDay',
		timeGridWeek: 'timeGridWeek',
		timeGridDay: 'timeGridDay'
	};
	static viewsNamesLabels = {
		dayGridMonth: 'month',
		dayGridWeek: 'week',
		dayGridDay: 'day',
		listWeek: 'week',
		timeGridWeek: 'week',
		timeGridDay: 'day'
	};
	/**
	 * Create calendar's options.
	 * @param {jQuery} container
	 * @param {bool} readonly
	 * @param {boolean} browserHistory
	 */
	constructor(container = $('.js-base-container'), readonly = false, browserHistory = true) {
		this.calendarCreateView = false;
		this.container = container;
		this.calendarView = container.find('.js-calendar__container');
		this.readonly = readonly;
		this.eventCreate = app.getMainParams('eventCreate');
		this.eventEdit = app.getMainParams('eventEdit');
		this.browserHistory = !readonly && browserHistory;
		this.sidebarView = {
			length: 0
		};
		this.startHour = app.getMainParams('startHour');
		this.endHour = app.getMainParams('endHour');
		if (this.startHour == '') {
			this.startHour = '00';
		}
		if (this.endHour == '') {
			this.endHour = '00';
		}
		this.browserHistoryOptions = {};
		this.browserHistoryConfig = this.browserHistory ? this.setBrowserHistoryOptions() : {};
		this.calendarOptions = this.setCalendarOptions();
		this.eventTypeKeyName = false;
		this.module = app.getModuleName();

		app.event.on('QuickEdit.AfterSaveFinal', () => {
			this.reloadCalendarData();
		});
	}
	/**
	 * Set calendar's options.
	 * @returns {object}
	 */
	setCalendarOptions() {
		return Object.assign(
			this.setCalendarBasicOptions(),
			this.setCalendarAdvancedOptions(),
			this.setCalendarModuleOptions(),
			this.browserHistoryOptions
		);
	}
	/**
	 * Set calendar's basic options.
	 * @returns {object}
	 */
	setCalendarBasicOptions() {
		let eventLimit = app.getMainParams('eventLimit'),
			userView = app.getMainParams('activity_view'),
			defaultView = app.moduleCacheGet('defaultView'),
			userTimeFormat = CONFIG.hourFormat;
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		if (userView === 'Today') {
			userView = app.getMainParams('dayView');
		} else if (userView === 'This Week') {
			userView = app.getMainParams('weekView');
		} else if (userView === 'This Year') {
			userView = 'year';
		} else {
			userView = 'dayGridMonth';
		}
		if (defaultView != null) {
			userView = defaultView;
		}
		if (userTimeFormat == 24) {
			userTimeFormat = {
				hour: '2-digit',
				minute: '2-digit',
				hour12: false,
				meridiem: false
			};
		} else {
			userTimeFormat = {
				hour: 'numeric',
				minute: '2-digit',
				meridiem: 'short'
			};
		}

		let options = {
			eventTimeFormat: userTimeFormat,
			slotLabelFormat: userTimeFormat,
			initialView: Calendar_Js.viewsNamesMap[userView] ? Calendar_Js.viewsNamesMap[userView] : userView,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			dayMaxEvents: eventLimit,
			selectMirror: true,
			scrollTime: this.startHour + ':00'
		};
		if (app.moduleCacheGet('start') !== null && app.moduleCacheGet('start') !== undefined) {
			let s = App.Fields.Date.getDateInstance(app.moduleCacheGet('start'));
			let e = App.Fields.Date.getDateInstance(app.moduleCacheGet('end'));
			options.initialDate = App.Fields.Date.dateToDbFormat(new Date(e - (e - s) / 2));
		}

		return Object.assign(this.setCalendarMinimalOptions(), options);
	}
	/**
	 * Set calendar's minimal options.
	 * @returns {object}
	 */
	setCalendarMinimalOptions() {
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		return {
			navLinks: true,
			firstDay: CONFIG.firstDayOfWeekNo,
			selectable: true,
			hiddenDays: hiddenDays,
			moreLinkContent: app.vtranslate('JS_MORE'),
			allDayText: app.vtranslate('JS_ALL_DAY'),
			noEventsText: app.vtranslate('JS_NO_RECORDS'),
			buttonText: {
				today: '',
				year: app.vtranslate('JS_YEAR'),
				week: app.vtranslate('JS_WEEK'),
				month: app.vtranslate('JS_MONTH'),
				day: app.vtranslate('JS_DAY'),
				dayGridMonth: app.vtranslate('JS_MONTH'),
				dayGridWeek: app.vtranslate('JS_WEEK'),
				listWeek: app.vtranslate('JS_WEEK'),
				dayGridDay: app.vtranslate('JS_DAY'),
				timeGridDay: app.vtranslate('JS_DAY'),
				list: app.vtranslate('JS_CALENDAR_LIST')
			},
			buttonHints: {
				prev(buttonText) {
					return `${app.vtranslate('JS_PREV')} ${buttonText}`;
				},
				next(buttonText) {
					return `${app.vtranslate('JS_NEXT')} ${buttonText}`;
				},
				today(buttonText) {
					return `${app.vtranslate('JS_CURRENT')} ${buttonText}`;
				}
			},
			viewHint: '$0',
			navLinkHint: (_dateStr, zonedDate) => {
				return App.Fields.Date.dateToUserFormat(zonedDate);
			},
			dayHeaderContent: (arg) => {
				if (this.container.width() < 600) {
					return App.Fields.Date.daysTranslated[arg.date.getDay()];
				}
				return App.Fields.Date.fullDaysTranslated[arg.date.getDay()];
			}
		};
	}
	/**
	 * Set calendar's advanced options.
	 * @returns {object}
	 */
	setCalendarAdvancedOptions() {
		const self = this;
		return {
			editable: !this.readonly && this.eventEdit == 1,
			selectable: !this.readonly && this.eventCreate == 1,
			headerToolbar: {
				left: 'dayGridMonth,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
				center: 'title,today',
				right: 'prev,next'
			},
			allDaySlot: app.getMainParams('allDaySlot'),
			views: {
				basic: {
					dayMaxEvents: false
				},
				dayGridMonth: {
					titleFormat: (args) => {
						return this.formatDate(args.date, 'month');
					}
				},
				timeGridWeek: {
					titleFormat: (args) => {
						return this.formatDate(args.date, 'week');
					}
				},
				timeGridDay: {
					titleFormat: (args) => {
						return this.formatDate(args.date, 'day');
					}
				},
				listWeek: {
					titleFormat: (args) => {
						return this.formatDate(args.date, 'week');
					},
					dayHeaderContent: (arg) => {
						return {
							html: `<span class="fc-list-day-text">${App.Fields.Date.fullDaysTranslated[arg.date.getDay()]}</span>
							<span class="fc-list-day-side-text">${App.Fields.Date.dateToUserFormat(arg.date)}</span>`
						};
					}
				},
				basicDay: {
					type: 'timeGridDay'
				}
			},
			eventDrop: self.updateEvent,
			eventResize: self.updateEvent,
			datesSet: (dateInfo) => {
				app.event.trigger('Calendar.DatesSet', dateInfo, this);
				self.loadCalendarData();
			},
			eventContent: self.eventRenderer,
			height: this.setCalendarHeight()
		};
	}
	/**
	 * Set calendar module's options.
	 * @returns {object}
	 */
	setCalendarModuleOptions() {
		return {};
	}
	/**
	 * Invokes FullCalendar with options.
	 */
	renderCalendar() {
		this.fullCalendar = new FullCalendar.Calendar(this.calendarView.get(0), this.calendarOptions);
		this.fullCalendar.render();
		this.registerViewRenderEvents();
	}
	/**
	 * Get calendar container.
	 * @returns {(boolean|jQuery)}
	 */
	getCalendarView() {
		if (!this.calendarView) {
			this.calendarView = this.container.find('.js-calendar__container');
		}
		return this.calendarView;
	}
	/**
	 * Load calendar data
	 */
	loadCalendarData() {
		const defaultParams = this.getDefaultParams();
		this.fullCalendar.removeAllEvents();
		if (!defaultParams.emptyFilters) {
			const progressInstance = $.progressIndicator({ blockInfo: { enabled: true } });
			AppConnector.request(defaultParams).done((events) => {
				this.fullCalendar.addEventSource(events.result);
				progressInstance.progressIndicator({ mode: 'hide' });
			});
		}
	}
	/**
	 * Reload calendar data after changing search parameters
	 */
	reloadCalendarData() {
		this.loadCalendarData();
	}
	/**
	 * Default params
	 * @returns {{module: string, action: string, mode: string, start: string, end: string, user: *, cvid: int, emptyFilters: boolean}}
	 */
	getDefaultParams() {
		let users = app.moduleCacheGet('calendar-users') || CONFIG.userId,
			sideBar = this.getSidebarView(),
			filters = [],
			params = {
				module: this.module ? this.module : CONFIG.module,
				action: 'Calendar',
				mode: 'getEvents',
				start: App.Fields.Date.dateToUserFormat(this.fullCalendar.view.activeStart),
				end: App.Fields.Date.dateToUserFormat(this.fullCalendar.view.activeEnd),
				user: users,
				cvid: this.getCurrentCvId(),
				emptyFilters: users.length === 0
			};
		sideBar.find('.calendarFilters .filterField').each(function () {
			let element = $(this),
				name,
				value;
			if (element.attr('type') == 'checkbox') {
				name = element.val();
				value = element.prop('checked') ? 1 : 0;
			} else {
				name = element.attr('name');
				value = element.val();
			}
			filters.push({ name: name, value: value });
		});
		if (filters.length) {
			params.filters = filters;
		}
		sideBar.find('.js-sidebar-filter-container').each((_, e) => {
			let element = $(e);
			let name = element.data('name');
			let cacheName = element.data('cache');
			if (name && cacheName && app.moduleCacheGet(cacheName)) {
				params[name] = app.moduleCacheGet(cacheName);
				params.emptyFilters = !params.emptyFilters && params[name].length === 0;
			}
		});
		sideBar.find('.js-filter__container_checkbox_list').each((_, e) => {
			let filters = [];
			let element = $(e);
			let name = element.data('name');
			element.find('.js-filter__item__val:checked').each(function () {
				filters.push($(this).val());
			});
			if (name) {
				params[name] = filters;
			}
		});
		sideBar.find('.js-calendar__filter__select').each((_, e) => {
			let element = $(e);
			let name = element.attr('name');
			let cacheName = element.data('cache');
			if (name) {
				params[name] = cacheName && app.moduleCacheGet(cacheName) ? app.moduleCacheGet(cacheName) : element.val();
				params.emptyFilters = !params.emptyFilters && params[name].length === 0;
			}
		});
		return params;
	}
	/**
	 * Converts the date format.
	 * @param {object} date
	 * @param {string} type
	 * @returns {string}
	 */
	formatDate(date, type) {
		switch (type) {
			case 'month':
				return Calendar_Js.monthFormat[CONFIG.dateFormat]
					.replace('YYYY', date['year'])
					.replace('MMMM', App.Fields.Date.fullMonthsTranslated[date['month']]);
			case 'week':
				return CONFIG.dateFormat
					.replace('yyyy', date['year'])
					.replace('mm', App.Fields.Date.monthsTranslated[date['month']])
					.replace('dd', date['day'] + ' - ' + (date['day'] + 7));
			case 'day':
				return CONFIG.dateFormat
					.replace('yyyy', date['year'])
					.replace('mm', App.Fields.Date.monthsTranslated[date['month']])
					.replace('dd', date['day']);
		}
	}
	/**
	 * Update calendar's event.
	 * @param {Object} info
	 */
	updateEvent(info) {
		const progressInstance = jQuery.progressIndicator({ blockInfo: { enabled: true } });
		AppConnector.request({
			module: this.module ? this.module : CONFIG.module,
			action: 'Calendar',
			mode: 'updateEvent',
			id: info.event.id,
			start: App.Fields.DateTime.dateToUserFormat(info.event.start),
			end: App.Fields.DateTime.dateToUserFormat(info.event.end),
			allDay: info.event.allDay
		})
			.done(function (response) {
				progressInstance.progressIndicator({ mode: 'hide' });
				if (!response['result']) {
					app.showNotify({
						text: app.vtranslate('JS_NO_EDIT_PERMISSION'),
						type: 'error'
					});
					info.revert();
				} else {
					window.popoverCache = {};
				}
			})
			.fail(function () {
				progressInstance.progressIndicator({ mode: 'hide' });
				app.showNotify({
					text: app.vtranslate('JS_NO_EDIT_PERMISSION'),
					type: 'error'
				});
				info.revert();
			});
	}
	/**
	 * Render event.
	 * @param {Object} arg
	 * @returns {Object}
	 */
	eventRenderer(arg) {
		if (arg.event.display === 'background') {
			return {
				html: `<span class="${arg.event.extendedProps.icon} js-popover-icon mr-1"></span>${arg.event._def.title}`
			};
		}
	}
	/**
	 * Returns counted calendar height.
	 * @returns {(number|string)}
	 */
	setCalendarHeight() {
		let defaultHeightValue = 'auto';
		if ($(window).width() > 993) {
			let calendarPadding;
			if (this.container.hasClass('js-modal-container')) {
				calendarPadding = this.container.find('.js-modal-header').outerHeight(); // modal needs bigger padding to prevent modal's scrollbar
			} else {
				calendarPadding = this.container.find('.js-contents-div').css('margin-left').replace('px', ''); //equals calendar padding bottom to left margin
			}
			let setCalendarH = () => {
				return (
					$(window).height() -
					this.container.find('.js-calendar__container').offset().top -
					$('.js-footer').height() -
					calendarPadding
				);
			};
			defaultHeightValue = setCalendarH();
			new ResizeSensor(this.container.find('.contentsDiv'), () => {
				let currentHeight = setCalendarH();
				if (currentHeight !== defaultHeightValue) {
					this.fullCalendar.setOption('height', currentHeight);
				}
			});
		}
		return defaultHeightValue;
	}
	/**
	 * Set calendar options from browser history.
	 * @returns {object}
	 */
	setBrowserHistoryOptions() {
		const historyParams = app.getMainParams('historyParams', true);
		let options = {};
		if (historyParams && (historyParams.length || Object.keys(historyParams).length)) {
			let s = App.Fields.Date.getDateInstance(historyParams.start);
			let e = App.Fields.Date.getDateInstance(historyParams.end);
			this.browserHistoryOptions = {
				initialView: historyParams.viewType,
				initialDate: App.Fields.Date.dateToDbFormat(new Date(e - (e - s) / 2)),
				hiddenDays: historyParams.hiddenDays.split(',').map((x) => {
					let parsedValue = parseInt(x);
					return isNaN(parsedValue) ? '' : parsedValue;
				})
			};
			options = {
				start: historyParams.start,
				end: historyParams.end,
				time: historyParams.time,
				user: historyParams.user,
				cvid: historyParams.cvid
			};
			Object.keys(options).forEach((key) => options[key] === 'undefined' && delete options[key]);
			Object.keys(this.browserHistoryOptions).forEach(
				(key) => this.browserHistoryOptions[key] === 'undefined' && delete this.browserHistoryOptions[key]
			);
			app.moduleCacheSet('browserHistoryEvent', false);
			if (historyParams.cvid && historyParams.cvid !== 'undefined') {
				app.moduleCacheSet('CurrentCvId', historyParams.cvid);
			}
			app.setMainParams('showType', options.time);
			app.setMainParams('usersId', options.user);
			app.setMainParams('defaultView', this.browserHistoryOptions);
		}
		window.addEventListener(
			'popstate',
			function () {
				app.moduleCacheSet('browserHistoryEvent', true);
			},
			false
		);
		return options;
	}
	/**
	 * Register filters
	 */
	registerFilters() {
		const self = this;
		let sideBar = self.getSidebarView();
		if (!sideBar || sideBar.length <= 0) {
			return;
		}
		sideBar.find('.js-sidebar-filter-container').each((_, row) => {
			let formContainer = $(row);
			self.registerUsersChange(formContainer);
			App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
			app.showNewScrollbar(formContainer, {
				suppressScrollX: true
			});
			self.registerFilterForm(formContainer);
		});
		self.registerSelectAll(sideBar);
		if (app.moduleCacheGet('CurrentCvId') !== null) {
			this.container
				.find('.js-calendar__extended-filter-tab [data-cvid="' + app.moduleCacheGet('CurrentCvId') + '"] a')
				.addClass('active');
		}
	}
	/**
	 * Register filter for users and groups
	 * @param {jQuery} container
	 */
	registerFilterForm(container) {
		const self = this;
		if (container.find('.js-filter__search').length) {
			container.find('.js-filter__search').on('keyup', this.findElementOnList.bind(self));
		}
		container.find('.js-calendar__filter__select, .filterField').each((_, e) => {
			let element = $(e);
			let name = element.data('cache');
			let cachedValue = app.moduleCacheGet(name);
			if (element.length > 0 && cachedValue !== undefined) {
				if (element.prop('tagName') == 'SELECT') {
					element.val(cachedValue);
				}
			} else if (
				name &&
				element.length > 0 &&
				cachedValue === undefined &&
				!element.find(':selected').length &&
				element.data('selected') !== 0
			) {
				let allOptions = [];
				element.find('option').each((i, option) => {
					allOptions.push($(option).val());
				});
				element.val(allOptions);
				app.moduleCacheSet(name, cachedValue);
			}
			element.off('change');
			App.Fields.Picklist.showSelect2ElementView(element);
			element.on('change', (e) => {
				let item = $(e.currentTarget);
				let value = item.val();
				if (value == null) {
					value = '';
				}
				if (item.attr('type') == 'checkbox') {
					value = element.is(':checked');
				}
				app.moduleCacheSet(item.data('cache'), value);
				self.reloadCalendarData();
			});
		});
		container
			.find('.js-filter__container_checkbox_list .js-filter__item__val')
			.off('change')
			.on('change', (e) => {
				self.reloadCalendarData();
			});
	}
	/**
	 * Find element on list (user, group)
	 * @param {jQuery.Event} e
	 */
	findElementOnList(e) {
		const target = $(e.target),
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
	 * Register users change
	 * @param {jQuery} formContainer
	 */
	registerUsersChange(formContainer) {
		formContainer.find('.js-input-user-owner-id-ajax, .js-input-user-owner-id').on('change', () => {
			this.reloadCalendarData();
		});
	}
	/**
	 * Register change on select all checkbox
	 * @param {jQuery} formContainer
	 */
	registerSelectAll(formContainer) {
		formContainer.find('.js-select-all').on('change', (e) => {
			let checkboxSelectAll = $(e.currentTarget);
			let checkboxes = formContainer.find('.js-input-user-owner-id-ajax, .js-input-user-owner-id');
			if (checkboxSelectAll.is(':checked')) {
				checkboxes.prop('checked', true);
			} else {
				checkboxes.prop('checked', false);
				formContainer.find('#ownerId' + CONFIG.userId).prop('checked', true);
			}
			this.reloadCalendarData();
		});
	}
	/**
	 * Register sidebar events.
	 */
	registerSidebarEvents() {
		$('.bodyContents').on('Vtiger.Widget.Load.undefined', () => {
			this.registerSelect2Event();
		});
	}
	/**
	 * Get sidebar view panel
	 * @returns {jQuery}
	 */
	getSidebarView() {
		if (!this.sidebarView || !this.sidebarView.length) {
			this.sidebarView = this.container.find('.js-calendar-right-panel');
		}
		return this.sidebarView;
	}
	/**
	 * Get current cv id
	 * @returns {int}
	 */
	getCurrentCvId() {
		let tab = $('.js-calendar__container .js-calendar__extended-filter-tab');
		if (tab.length === 0) {
			tab = $('.js-calendar__header-buttons .js-calendar__extended-filter-tab');
		}
		return tab.find('.active').parent().data('cvid');
	}
	/**
	 * Register select2 event.
	 */
	registerSelect2Event() {
		const self = this;
		$('.siteBarRight .js-calendar__filter__select').each(function () {
			let element = $(this);
			let name = element.data('cache');
			let cachedValue = app.moduleCacheGet(name);
			if (element.length > 0 && cachedValue !== undefined) {
				if (element.prop('tagName') == 'SELECT') {
					element.val(cachedValue);
				}
			} else if (element.length > 0 && cachedValue === undefined && !element.find(':selected').length) {
				let allOptions = [];
				element.find('option').each((_i, option) => {
					allOptions.push($(option).val());
				});
				element.val(allOptions);
				app.moduleCacheSet(name, cachedValue);
			}
		});
		let selectsElements = $('.siteBarRight .select2, .siteBarRight .filterField');
		selectsElements.off('change');
		App.Fields.Picklist.showSelect2ElementView(selectsElements);
		selectsElements.on('change', function () {
			let element = $(this);
			let value = element.val();
			if (value == null) {
				value = '';
			}
			if (element.attr('type') == 'checkbox') {
				value = element.is(':checked');
			}
			app.moduleCacheSet(element.data('cache'), value);
			self.reloadCalendarData();
		});
	}
	/**
	 * Register button select all.
	 */
	registerButtonSelectAll() {
		$('.selectAllBtn').on('click', function () {
			const selectAllLabel = $(this).find('.selectAll'),
				deselectAllLabel = $(this).find('.deselectAll');
			if (selectAllLabel.hasClass('d-none')) {
				selectAllLabel.removeClass('d-none');
				deselectAllLabel.addClass('d-none');
				$(this).closest('.quickWidget').find('select option').prop('selected', false);
			} else {
				$(this).closest('.quickWidget').find('select option').prop('selected', true);
				deselectAllLabel.removeClass('d-none');
				selectAllLabel.addClass('d-none');
			}
			$(this).closest('.quickWidget').find('select').trigger('change');
		});
	}
	/**
	 * Register add button.
	 */
	registerAddButton() {
		$('.js-add').on('click', () => {
			this.getCalendarCreateView().done((data) => {
				App.Components.QuickCreate.showModal(data, {
					callbackFunction: () => {
						this.reloadCalendarData();
					}
				});
			});
		});
	}
	/**
	 * Get calendar create view.
	 * @returns {promise}
	 */
	getCalendarCreateView() {
		const self = this,
			aDeferred = jQuery.Deferred();
		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView);
			return aDeferred.promise();
		}
		let progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView()
			.done(function (data) {
				progressInstance.hide();
				self.calendarCreateView = data;
				aDeferred.resolve(data);
			})
			.fail(function () {
				progressInstance.hide();
			});
		return aDeferred.promise();
	}
	/**
	 * Load calendar create view.
	 * @returns {promise}
	 */
	loadCalendarCreateView() {
		const aDeferred = jQuery.Deferred(),
			moduleName = app.getModuleName();
		App.Components.QuickCreate.getForm('index.php?module=' + moduleName + '&view=QuickCreateAjax', moduleName)
			.done(function (data) {
				aDeferred.resolve(data);
			})
			.fail(function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			});
		return aDeferred.promise();
	}
	/**
	 * Function invokes by FullCalendar, sets selected days in form
	 * @param info
	 */
	selectDays(info) {
		this.getCalendarCreateView().done((data) => {
			App.Components.QuickCreate.showModal(data, {
				callbackFunction: () => {
					self.reloadCalendarData();
				},
				callbackPostShown: (modal) => {
					this.selectCallbackCreateModal(modal, info);
				}
			});
		});
	}
	/**
	 * Callback after shown create modal
	 * @param {jQuery} modal
	 */
	selectCallbackCreateModal(modal, info) {
		let startDate = info.start,
			endDate = info.end;
		if (info['allDay']) {
			endDate.setDate(endDate.getDate() - 1);
		}
		if (info['allDay']) {
			let startDateSplitted = this.startHour.split(':');
			let endDateSplitted = this.endHour.split(':');
			startDate.setHours(startDateSplitted[0], startDateSplitted[1]);
			endDate.setHours(endDateSplitted[0], endDateSplitted[1]);
			if (startDate.toDateString() === endDate.toDateString()) {
				let activityType = modal.find('[name="activitytype"]').val();
				let activityDurations = JSON.parse(modal.find('[name="defaultOtherEventDuration"]').val());
				let minutes = 60;
				for (let i in activityDurations) {
					if (activityDurations[i].activitytype === activityType) {
						minutes = parseInt(activityDurations[i].duration);
						break;
					}
				}
				if (minutes) {
					endDate.setMinutes(endDate.getMinutes() + minutes);
				}
			}
		}
		let dateFormat = CONFIG.dateFormat;
		let timeFormat = CONFIG.hourFormat;
		let dateField = modal.find('[name="date_start"]');
		if (dateField.length) {
			dateFormat = dateField.data('dateFormat');
		}
		let timeField = modal.find('[name="time_start"]');
		if (timeField.length) {
			timeFormat = timeField.data('format');
		}
		let defaultTimeFormat = '';
		if (timeFormat == 24) {
			defaultTimeFormat = 'HH:mm';
		} else {
			defaultTimeFormat = 'hh:mm A';
		}
		modal.find('[name="date_start"]').val(App.Fields.Date.dateToUserFormat(startDate, dateFormat));
		modal.find('[name="due_date"]').val(App.Fields.Date.dateToUserFormat(endDate, dateFormat));
		if (modal.find('.js-autofill').prop('checked') === true) {
			Calendar_Edit_Js.getInstance().getFreeTime(modal);
		} else {
			modal.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
			modal.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
		}
	}
	/**
	 * Register events.
	 */
	registerEvents() {
		this.registerFilters();
		this.registerSidebarEvents();
		this.renderCalendar();
		this.registerButtonSelectAll();
		this.registerAddButton();
	}
};
