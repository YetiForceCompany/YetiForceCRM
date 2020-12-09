/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery(document).ready(function ($) {
	// enable/disable confirm button
	$('#status').on('change', function () {
		$('#confirm').attr('disabled', !this.checked);
	});
});
Vtiger_Edit_Js(
	'Reservations_Edit_Js',
	{},
	{
		differenceDays: function () {
			let firstDate = jQuery('input[name="date_start"]');
			let firstDateFormat = firstDate.data('date-format');
			let firstDateValue = firstDate.val();
			let secondDate = jQuery('input[name="due_date"]');
			let secondDateFormat = secondDate.data('date-format');
			let secondDateValue = secondDate.val();
			let firstTime = jQuery('input[name="time_start"]');
			let secondTime = jQuery('input[name="time_end"]');
			if (firstTime.length) {
				firstDateValue = firstDateValue + ' ' + firstTime.val();
			}
			if (secondTime.length) {
				secondDateValue = secondDateValue + ' ' + secondTime.val();
			}
			let firstDateInstance = Vtiger_Helper_Js.getDateInstance(firstDateValue, firstDateFormat);
			let secondDateInstance = Vtiger_Helper_Js.getDateInstance(secondDateValue, secondDateFormat);

			let timeBetweenDates = secondDateInstance - firstDateInstance;
			if (timeBetweenDates >= 0) {
				return timeBetweenDates;
			}
			return 'Error';
		},

		/**
		 * Function to register recordpresave event
		 */
		registerRecordPreSaveEvent: function () {
			var thisInstance = this;
			var form = this.getForm();

			form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
				var sumeTime2 = thisInstance.differenceDays();
				if (sumeTime2 == 'Error') {
					var parametry = {
						text: app.vtranslate('JS_DATE_SHOULD_BE_GREATER_THAN'),
						type: 'error'
					};
					app.showNotify(parametry);
					return false;
				} else {
					form.submit();
				}
			});
		},
		registerEvents: function () {
			this._super();
			this.registerRecordPreSaveEvent();
		}
	}
);
