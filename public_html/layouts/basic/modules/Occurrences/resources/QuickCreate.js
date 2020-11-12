/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 *  Class representing a modal calendar.
 * @extends Vtiger_Calendar_Js
 */
window.Occurrences_CalendarModal_Js = class Occurrences_CalendarModal_Js extends Vtiger_Calendar_Js {
	constructor(container, readonly) {
		super(container, readonly);
		this.isSwitchAllDays = false;
		this.sidebarName = 'add'; //available: add, status, edit
		this.eventCreate = false;
		this.module = container.find('[name="module"]').val();
		this.renderCalendar();
		this.registerEvents();
	}

	addCommonMethodsToYearView() {}
	/**
	 * Function sets calendar module's options
	 */
	setCalendarModuleOptions() {
		let options = super.setCalendarModuleOptions();
		options.selectable = true;
		options.hiddenDays = app.getMainParams('hiddenDays', true);
		options.header = {
			left: 'month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
			center: 'prevYear,prev,title,next,nextYear',
			right: 'today'
		};
		options.eventClick = function (calEvent, jsEvent) {
			jsEvent.preventDefault();
		};
		return options;
	}

	/**
	 * Function registers calendar events
	 */
	registerEvents() {
		this.registerSwitchEvents();
		this.registerUsersChange();
	}

	/**
	 * Function registers calendar switch event
	 */
	registerSwitchEvents() {
		if (app.getMainParams('hiddenDays', true) !== false) {
			let calendarView = this.getCalendarView(),
				switchContainer = $(`<div class="js-calendar-switch-container"></div>`).insertAfter(
					calendarView.find('.fc-center')
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
	switchTpl(on, off, state) {
		return `<div class="btn-group btn-group-toggle js-switch c-calendar-switch" data-toggle="buttons">
					<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on ${state ? '' : 'active'}">
						<input type="radio" name="options" data-on-text="${on}" autocomplete="off" ${state ? '' : 'checked'}>
						${on}
					</label>
					<label class="btn btn-outline-primary c-calendar-switch__button ${state ? 'active' : ''}">
						<input type="radio" name="options" data-off-text="${off}" autocomplete="off" ${state ? 'checked' : ''}>
						${off}
					</label>
				</div>`;
	}
	/**
	 * Function registers select's user change event
	 */
	registerUsersChange() {
		this.container.find('.assigned_user_id').on('change', () => {
			this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
	}

	/**
	 * Function return user's id
	 * @returns {int}
	 */
	getSelectedUsersCalendar() {
		return this.container.find('.assigned_user_id').val();
	}

	/**
	 * Function invokes by fullcalendar, sets selected days in form
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
		}
		let dateFormat = CONFIG.dateFormat.toUpperCase(),
			timeFormat = CONFIG.hourFormat.toUpperCase(),
			defaultTimeFormat = '';
		if (timeFormat == 24) {
			timeFormat = 'HH:mm';
		} else {
			timeFormat = 'hh:mm A';
		}
		defaultTimeFormat = dateFormat + ' ' + timeFormat;
		this.container.find('[name="date_start"]').val(moment(startDate).format(defaultTimeFormat));
		this.container.find('[name="date_end"]').val(moment(endDate).format(defaultTimeFormat));
	}
};

$.Class(
	'Occurrences_QuickCreate_Js',
	{},
	{
		container: false,
		module: false,
		calendar: false,
		getContainer() {
			return this.container;
		},
		setContainer(container) {
			this.container = container;
		},
		setModule() {
			this.module = this.getContainer().find('[name="module"]').val();
		},
		initCalendar() {
			let className = this.module + '_CalendarModal_Js';
			this.calendar = new window[className](this.getContainer().closest('.js-modal-container'), true);
		},
		registerEvents: function (container) {
			this.setContainer(container);
			this.setModule();
			this.initCalendar();
		}
	}
);
