/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
jQuery.Class("Calendar_QuickCreate_Js", {}, {
	container: false,
	getContainer() {
		return this.container;
	},
	setContainer: function (container) {
		this.container = container;
	},
	registerExtendCalendar: function () {
		let instance = Calendar_Calendar_Js.getInstanceByView('CalendarExtended');
		instance.setContainer(this.getContainer());
		instance.calendarView = this.getContainer().find('#calendarview');
		instance.renderCalendar();
		instance.registerChangeView();
		instance.loadCalendarData();
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
		var thisInstance = this;
		var dateStartVal = container.find('[name="date_start"]').val();
		if (typeof dateStartVal === "undefined" || dateStartVal === '') {
			return;
		}
		var params = {
			module: 'Calendar',
			view: 'QuickCreateEvents',
			currentDate: dateStartVal,
			user: container.find('[name="assigned_user_id"]').val(),
		}
		var progressIndicatorElement = $.progressIndicator({
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

