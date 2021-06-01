/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Occurrences_Calendar_Js = class Occurrences_Calendar_Js extends (
	Vtiger_Calendar_Js
) {
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
			let dateElement = data.find('[name="date_start"]');
			let dateFormat = dateElement.data('dateFormat').toUpperCase(),
				timeFormat = dateElement.data('hour-format'),
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
			data.find('[name="date_start"]').val(startDateString + ' ' + startTimeString);
			data.find('[name="date_end"]').val(endDateString + ' ' + endTimeString);

			App.Components.QuickCreate.showModal(data, {
				callbackFunction(data) {
					self.loadCalendarData();
				}
			});
		});
	}
};
