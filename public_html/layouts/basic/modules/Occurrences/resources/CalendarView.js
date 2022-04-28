/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Occurrences_Calendar_Js = class Occurrences_Calendar_Js extends Vtiger_Calendar_Js {
	/**
	 * Callback after shown create modal
	 * @param {jQuery} modal
	 * @param {object} info
	 */
	selectCallbackCreateModal(modal, info) {
		let dateFormat = modal.find('[name="date_start"]').data('dateFormat'),
			timeFormat = modal.find('[name="date_start"]').data('hourFormat'),
			userFormat = App.Fields.Date.dateToUserFormat(info.date, dateFormat),
			defaultTimeFormat = 'hh:mm A',
			endTimeString;
		if (timeFormat == 24) {
			defaultTimeFormat = 'HH:mm';
		}
		let startTimeString = moment(info.date).format(defaultTimeFormat);
		if (info['allDay']) {
			startTimeString = moment().format(defaultTimeFormat);
			endTimeString = moment().add(30, 'minutes').format(defaultTimeFormat);
		} else {
			endTimeString = moment(info.date).add(30, 'minutes').format(defaultTimeFormat);
		}
		modal.find('[name="date_start"]').val(userFormat + ' ' + startTimeString);
		modal.find('[name="date_end"]').val(userFormat + ' ' + endTimeString);
	}
};
