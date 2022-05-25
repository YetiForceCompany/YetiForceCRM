/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 *  Class representing a modal calendar.
 * @extends Vtiger_Calendar_Js
 */
window.Reservations_CalendarModal_Js = class Reservations_CalendarModal_Js extends Vtiger_Calendar_Js {
	constructor(container, readonly) {
		super(container, readonly);
		this.isSwitchAllDays = false;
		this.sidebarName = 'add'; //available: add, status, edit
		this.eventCreate = false;
		this.module = container.find('[name="module"]').val();
		this.renderCalendar();
		this.registerEvents();
	}
	/**
	 * Function sets calendar module's options
	 * @returns {{eventClick: function, headerToolbar: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		let options = super.setCalendarModuleOptions();
		options.selectable = true;
		options.eventClick = function (info) {
			info.jsEvent.preventDefault();
		};
		let date = this.container.find('.js-selected-date').val();
		if (date) {
			options.initialDate = date;
		}
		return options;
	}
	/**
	 * Function registers calendar events
	 */
	registerEvents() {
		const calendarView = this.getCalendarView();
		this.switchContainer = $(`<div class="js-calendar-switch-container"></div>`).insertAfter(
			calendarView.find('.fc-center')
		);
		this.registerSwitchEvents();
		this.registerUsersChange();
	}
	/**
	 * Function registers calendar switch event
	 */
	registerSwitchEvents() {
		if (app.getMainParams('hiddenDays', true) !== false) {
			this.switchContainer.html(this.createSwitch());
			this.switchContainer.find('input').on('change', (e) => {
				const currentTarget = $(e.currentTarget);
				let hiddenDays = [];
				if (typeof currentTarget.data('on-text') !== 'undefined') {
					hiddenDays = app.getMainParams('hiddenDays', true);
					this.isSwitchAllDays = false;
				} else {
					this.isSwitchAllDays = true;
				}
				this.fullCalendar.setOption('hiddenDays', hiddenDays);
				this.registerSwitchEvents();
			});
		}
	}
	/**
	 * Generate filter buttons
	 * @returns {string}
	 */
	createSwitch() {
		let on = app.vtranslate('JS_WORK_DAYS'),
			off = app.vtranslate('JS_ALL'),
			state = this.isSwitchAllDays;
		return `<div class="btn-group btn-group-toggle js-switch c-calendar-switch" data-toggle="buttons">
					<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on ${state ? '' : 'active'}">
						<input type="radio" name="options" data-on-text="${on}" autocomplete="off" ${state ? '' : 'checked'}>${on}
					</label>
					<label class="btn btn-outline-primary c-calendar-switch__button ${state ? 'active' : ''}">
						<input type="radio" name="options" data-off-text="${off}" autocomplete="off" ${state ? 'checked' : ''}>${off}
					</label>
				</div>`;
	}
	/**
	 * Function registers select's user change event
	 */
	registerUsersChange() {
		this.container.find('.assigned_user_id').on('change', () => {
			this.reloadCalendarData();
		});
	}
	/**
	 * Get selected users
	 * @returns {{ selectedIds: array, excludedIds: array }}
	 */
	getSelectedUsersCalendar() {
		return { selectedIds: [this.container.find('.assigned_user_id').val()], excludedIds: [] };
	}
	/**
	 * Function invokes by FullCalendar, sets selected days in form
	 * @param {Object} info
	 */
	selectDays(info) {
		if (this.sidebarName === 'status') {
			this.sidebarName = 'add';
			this.getCalendarCreateView().done(() => {
				this.selectDays(info);
			});
			return;
		}
		let startDate = info.start,
			endDate = info.end;
		if (info['allDay']) {
			endDate.setDate(endDate.getDate() - 1);
			const d = new Date();
			startDate.setHours(d.getHours(), d.getMinutes());
			endDate.setHours(d.getHours(), d.getMinutes() + 30);
		}
		this.container.find('[name="date_start"]').val(App.Fields.Date.dateToUserFormat(startDate));
		this.container.find('[name="due_date"]').val(App.Fields.Date.dateToUserFormat(endDate));
		this.container.find('[name="time_start"]').val(App.Fields.Time.dateToUserFormat(startDate));
		this.container.find('[name="time_end"]').val(App.Fields.Time.dateToUserFormat(endDate));
	}
};

$.Class(
	'Reservations_QuickCreate_Js',
	{},
	{
		registerEvents: function (container) {
			let className = container.find('[name="module"]').val() + '_CalendarModal_Js';
			this.calendarView = new window[className](container.closest('.js-modal-container'), true);
		}
	}
);
