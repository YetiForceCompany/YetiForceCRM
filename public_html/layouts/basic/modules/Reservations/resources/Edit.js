/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery(document).ready(function ($) {
	// enable/disable confirm button
	$('#status').on('change', function () {
		$('#confirm').attr('disabled', !this.checked);
	});
});
Vtiger_Edit_Js("Reservations_Edit_Js", {}, {

	differenceDays: function () {
		var firstDate = jQuery('input[name="date_start"]');
		var firstDateFormat = firstDate.data('date-format');
		var firstDateValue = firstDate.val();
		var secondDate = jQuery('input[name="due_date"]');
		var secondDateFormat = secondDate.data('date-format');
		var secondDateValue = secondDate.val();
		var firstTime = jQuery('input[name="time_start"]');
		var secondTime = jQuery('input[name="time_end"]');
		var firstTimeValue = firstTime.val();
		var secondTimeValue = secondTime.val();
		var firstDateTimeValue = firstDateValue + ' ' + firstTimeValue;
		var secondDateTimeValue = secondDateValue + ' ' + secondTimeValue;

		var firstDateInstance = Vtiger_Helper_Js.getDateInstance(firstDateTimeValue, firstDateFormat);
		var secondDateInstance = Vtiger_Helper_Js.getDateInstance(secondDateTimeValue, secondDateFormat);

		var timeBetweenDates = secondDateInstance - firstDateInstance;
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
				Vtiger_Helper_Js.showPnotify(parametry);
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
});
