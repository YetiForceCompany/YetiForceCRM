/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Calendar_CalendarModal_Js = class Calendar_CalendarModal_Js extends Calendar_CalendarExtended_Js {

	constructor(container, readonly) {
		super(container, readonly);
		this.switchAllDays = app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all' ? true : false;
		this.user = this.container.find('.assigned_user_id');
		this.user.on('change', () => {
			this.loadCalendarData();
		});
	}

	renderCalendar() {
		super.renderCalendar();
		this.registerSwitchEvents();
	}

	selectDays(startDate, endDate) {
		let start_hour = $('#start_hour').val(),
			end_hour = $('#end_hour').val(),
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
		if (view.name != 'agendaDay' && view.name != 'agendaWeek') {
			startDate = startDate + 'T' + start_hour + ':00';
			endDate = endDate + 'T' + end_hour + ':00';
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
			Calendar_Edit_Js.getInstance().getFreeTime(this.container);
		} else {
			this.container.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
			this.container.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
		}
	}

	registerSwitchEvents() {
		if (app.getMainParams('hiddenDays', true) !== false) {
			let calendarview = this.getCalendarView(),
				switchContainer = $(`<div class="js-calendar-switch-container"></div>`).insertAfter(calendarview.find('.fc-center'));
			$(this.switchTpl(app.vtranslate('JS_WORK_DAYS'), app.vtranslate('JS_ALL'), this.switchAllDays))
				.prependTo(switchContainer)
				.on('change', 'input', (e) => {
					const currentTarget = $(e.currentTarget);
					let hiddenDays = [];
					if (typeof currentTarget.data('on-text') !== 'undefined') {
						hiddenDays = app.getMainParams('hiddenDays', true);
						this.switchAllDays = false;
					} else {
						this.switchAllDays = true;
					}
					this.getCalendarView().fullCalendar('option', 'hiddenDays', hiddenDays);
					//calendarView.fullCalendar('option', 'height', this.setCalendarHeight());
					if (this.getCalendarView().fullCalendar('getView').type === 'year') {
						this.registerViewRenderEvents(calendarView.fullCalendar('getView'));
					}
					this.registerSwitchEvents();
				});
		}
	}

	getSelectedUsersCalendar() {
		return this.user.val();
	}
}

jQuery.Class("Calendar_QuickCreate_Js", {}, {
	container: false,
	getContainer() {
		return this.container;
	},
	setContainer: function (container) {
		this.container = container;
	},
	registerExtendCalendar: function () {
		(new Calendar_CalendarModal_Js($('.js-modal-container'), true)).renderCalendar();
	},
	registerStandardCalendar: function () {
		const thisInstance = this;
		let container = this.getContainer();
		let data = container.find('form')
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
			var dateStartEl = data.find('[name="date_start"]')
			var startDay = dateStartEl.val();
			var dateStartFormat = dateStartEl.data('date-format');
			startDay = moment(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '+7', ' ')).format(dateStartFormat.toUpperCase());
			dateStartEl.val(startDay);
			dateEnd.val(startDay);
			thisInstance.getNearCalendarEvent(data);
		});
		data.on('click', '.previousDayBtn', function () {
			var dateStartEl = data.find('[name="date_start"]')
			var startDay = dateStartEl.val();
			var dateStartFormat = dateStartEl.data('date-format');
			startDay = moment(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '-7', ' ')).format(dateStartFormat.toUpperCase());
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
		if (typeof dateStartVal === "undefined" || dateStartVal === '') {
			return;
		}
		let params = {
			module: 'Calendar',
			view: 'QuickCreateEvents',
			currentDate: dateStartVal,
			user: container.find('[name="assigned_user_id"]').val(),
		}
		let progressIndicatorElement = $.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true,
				elementToBlock: container.find('.eventsTable')
			}
		});
		AppConnector.request(params).done(function (events) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			container.find('.eventsTable').remove();
			container.append(events);
			Vtiger_Header_Js.getInstance().registerHelpInfo(container);
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
});

