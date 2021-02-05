/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 *  Class representing a modal calendar.
 * @extends Calendar_CalendarExtended_Js
 */
window.Calendar_CalendarModal_Js = class Calendar_CalendarModal_Js extends (
	Calendar_CalendarExtended_Js
) {
	constructor(container, readonly) {
		super(container, readonly);
		this.isSwitchAllDays = false;
		this.sidebarName = 'add'; //available: add, status, edit
		this.eventCreate = true;
		this.module = 'Calendar';
		this.renderCalendar();
		this.registerEvents();
	}
	/**
	 * Render calendar
	 */
	renderCalendar() {
		let self = this,
			basicOptions = this.setCalendarOptions(),
			options = {
				header: {
					left: 'month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
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
		options.eventClick = function (calEvent, jsEvent) {
			jsEvent.preventDefault();
		};
		this.calendar.fullCalendar(options);
	}
	addCommonMethodsToYearView() {}
	/**
	 * Function sets calendar moduls's options
	 * Overwrites Calendar_Calendar_Js
	 */
	setCalendarModuleOptions() {
		let options = super.setCalendarModuleOptions();
		options.hiddenDays = app.getMainParams('hiddenDays', true);
		options.header = {
			left: 'month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
			center: 'prevYear,prev,title,next,nextYear',
			right: 'today'
		};
		options.selectable = true;
		return options;
	}

	/**
	 * Function registers calendar events
	 * Overwrites Calendar_CalendarExtended_Js
	 */
	registerEvents() {
		this.registerSwitchEvents();
		this.registerUsersChange();
		this.registerAutofillTime();
		this.registerPopoverButtonsClickEvent();
	}

	/**
	 * Function registers calendar switch event
	 * Overwrites Calendar_CalendarExtended_Js
	 */
	registerSwitchEvents() {
		if (app.getMainParams('hiddenDays', true) !== false) {
			let calendarview = this.getCalendarView(),
				switchContainer = $(`<div class="js-calendar-switch-container"></div>`).insertAfter(
					calendarview.find('.fc-center')
				);
			$(this.switchTpl(app.vtranslate('JS_WORK_DAYS'), app.vtranslate('JS_ALL'), this.isSwitchAllDays))
				.prependTo(switchContainer)
				.on('change', 'input', (e) => {
					const currentTarget = $(e.currentTarget);
					let hiddenDays = [];
					if (typeof currentTarget.data('on-text') !== 'undefined') {
						hiddenDays = app.getMainParams('hiddenDays', true);
						this.isSwitchAllDays = false;
					} else {
						this.isSwitchAllDays = true;
					}
					this.getCalendarView().fullCalendar('option', 'hiddenDays', hiddenDays);
					if (this.getCalendarView().fullCalendar('getView').type === 'year') {
						this.registerViewRenderEvents(this.getCalendarView().fullCalendar('getView'));
					}
					this.registerSwitchEvents();
				});
		}
	}

	/**
	 * Function registers select's user change event
	 * Overwrites Calendar_CalendarExtended_Js
	 */
	registerUsersChange() {
		this.container.find('.assigned_user_id').on('change', () => {
			this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
	}

	/**
	 * Function return user's id
	 * Overwrites Calendar_CalendarExtended_Js
	 * @returns {int}
	 */
	getSelectedUsersCalendar() {
		return this.container.find('.assigned_user_id').val();
	}

	/**
	 * Function invokes by fullcalendar, sets selected days in form
	 * Overwrites Calendar_CalendarExtended_Js
	 * @param startDate
	 * @param endDate
	 */
	selectDays(startDate, endDate) {
		if (this.sidebarName === 'status') {
			this.sidebarName = 'add';
			this.getCalendarCreateView().done(() => {
				this.selectDays(startDate, endDate);
			});
			return;
		}
		let startHour = app.getMainParams('startHour'),
			endHour = app.getMainParams('endHour'),
			view = this.getCalendarView().fullCalendar('getView');
		if (endDate.hasTime() == false) {
			endDate.add(-1, 'days');
		}
		startDate = startDate.format();
		endDate = endDate.format();
		if (startHour == '') {
			startHour = '00';
		}
		if (endHour == '') {
			endHour = '00';
		}
		if (view.name != 'agendaDay' && view.name != 'agendaWeek') {
			startDate = startDate + 'T' + startHour + ':00';
			endDate = endDate + 'T' + endHour + ':00';
			if (startDate == endDate) {
				let activityType = this.container.find('[name="activitytype"]').val();
				let activityDurations = JSON.parse(this.container.find('[name="defaultOtherEventDuration"]').val());
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
		let dateFormat = this.container.find('[name="date_start"]').data('dateFormat').toUpperCase(),
			timeFormat = this.container.find('[name="time_start"]').data('format'),
			defaultTimeFormat = '';
		if (timeFormat == 24) {
			defaultTimeFormat = 'HH:mm';
		} else {
			defaultTimeFormat = 'hh:mm A';
		}
		this.container.find('[name="date_start"]').val(moment(startDate).format(dateFormat));
		this.container.find('[name="due_date"]').val(moment(endDate).format(dateFormat));
		if (this.container.find('.js-autofill').prop('checked') === true) {
			let calendarEditInstance = new Calendar_Edit_Js();
			calendarEditInstance.getFreeTime(this.container);
		} else {
			this.container.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
			this.container.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
		}
	}

	/** @inheritdoc */
	registerEditForm(sideBar) {
		let editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('[name="module"]').val()),
			params = [];
		let rightFormCreate = sideBar.find('form.js-form');
		editViewInstance.registerBasicEvents(rightFormCreate);
		rightFormCreate.validationEngine(app.validationEngineOptions);
		App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
		sideBar.find('.js-summary-close-edit').on('click', () => {
			this.getCalendarCreateView();
		});
		App.Components.QuickCreate.registerPostLoadEvents(rightFormCreate, params);
		App.Fields.Text.Editor.register(sideBar.find('.js-editor'), { height: '5em', toolbar: 'Min' });
	}

	/** @inheritdoc */
	updateSidebar(sidebar, data) {
		const modalTitleContainer = $('.js-modal-title__container'),
			modalTitles = modalTitleContainer.find('[class*="js-modal-title"]');
		data = $(data);

		modalTitles.addClass('d-none');
		if (data.hasClass('js-edit-form')) {
			let title = data.find('.js-sidebar-title ').data('title');
			modalTitles.filter(`.js-modal-title--${title}`).removeClass('d-none');
			this.sidebarName = title;
		} else if (data.hasClass('js-activity-state')) {
			modalTitles.filter('.js-modal-title--status').removeClass('d-none');
			this.sidebarName = 'status';
		}
		sidebar.find('.js-qc-form').html(data);
	}
};

jQuery.Class(
	'Calendar_QuickCreate_Js',
	{},
	{
		container: false,
		getContainer() {
			return this.container;
		},
		setContainer: function (container) {
			this.container = container;
		},
		registerExtendCalendar: function () {
			new Calendar_CalendarModal_Js($('.js-modal-container'), true);
			let container = this.getContainer();
			container.find('.js-activity-buttons button').on('click', function (e) {
				let form = container.find('form');
				let currentTarget = $(e.currentTarget);
				if (1 === currentTarget.data('type')) {
					form.append('<input type=hidden name="activitystatus" value="' + currentTarget.data('state') + '">');
					form.submit();
				} else {
					container.find('.js-activity-buttons').remove();
					form.find('[name="record"]').val('');
					form.append('<input type=hidden name="postponed" value="true">');
					form.append('<input type=hidden name="followup" value="' + currentTarget.data('id') + '">');
				}
			});
		},
		registerStandardCalendar: function () {
			const thisInstance = this;
			let container = this.getContainer();
			let data = container.find('form');
			let user = data.find('[name="assigned_user_id"]');
			let dateStartEl = data.find('[name="date_start"]');
			let dateEnd = data.find('[name="due_date"]');
			user.on('change', function (e) {
				var element = $(e.currentTarget);
				var data = element.closest('form');
				thisInstance.getNearCalendarEvent(data);
			});
			dateStartEl.on('change', function (e) {
				var element = $(e.currentTarget);
				var data = element.closest('form');
				thisInstance.getNearCalendarEvent(data);
			});
			data.find('ul li a').on('click', function (e) {
				var element = $(e.currentTarget);
				var data = element.closest('form');
				data.find('.addedNearCalendarEvent').remove();
				thisInstance.getNearCalendarEvent(data);
			});
			data.on('click', '.nextDayBtn', function () {
				var dateStartEl = data.find('[name="date_start"]');
				var startDay = dateStartEl.val();
				var dateStartFormat = dateStartEl.data('date-format');
				startDay = moment(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '+7', ' ')).format(
					dateStartFormat.toUpperCase()
				);
				dateStartEl.val(startDay);
				dateEnd.val(startDay);
				thisInstance.getNearCalendarEvent(data);
			});
			data.on('click', '.previousDayBtn', function () {
				var dateStartEl = data.find('[name="date_start"]');
				var startDay = dateStartEl.val();
				var dateStartFormat = dateStartEl.data('date-format');
				startDay = moment(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '-7', ' ')).format(
					dateStartFormat.toUpperCase()
				);
				dateStartEl.val(startDay);
				dateEnd.val(startDay);
				thisInstance.getNearCalendarEvent(data);
			});
			data.on('click', '.dateBtn', function (e) {
				var element = $(e.currentTarget);
				dateStartEl.val(element.data('date'));
				data.find('[name="due_date"]').val(element.data('date'));
				data.find('[name="date_start"]').trigger('change');
			});
			thisInstance.getNearCalendarEvent(data);
		},
		getNearCalendarEvent: function (container) {
			let dateStartVal = container.find('[name="date_start"]').val();
			if (typeof dateStartVal === 'undefined' || dateStartVal === '') {
				return;
			}
			let params = {
				module: 'Calendar',
				view: 'QuickCreateEvents',
				currentDate: dateStartVal,
				user: container.find('[name="assigned_user_id"]').val()
			};
			let progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true,
					elementToBlock: container.find('.eventsTable')
				}
			});
			AppConnector.request(params).done(function (events) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				container.find('.eventsTable').remove();
				container.append(events);
				app.showPopoverElementView(container.find('.js-help-info'));
			});
		},
		registerEvents: function (container) {
			let calendarType = container.closest('.js-modal-container').find('.js-calendar-type').val();
			this.setContainer(container);
			if (calendarType === 'Extended') {
				this.registerExtendCalendar();
			} else {
				this.registerStandardCalendar();
			}
		}
	}
);
