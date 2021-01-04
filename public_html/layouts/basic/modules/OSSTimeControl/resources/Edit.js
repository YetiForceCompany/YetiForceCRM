/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'OSSTimeControl_Edit_Js',
	{},
	{
		registerGenerateTCFieldTimeAndCost: function () {
			var thisInstance = this;
			$('input[name="sum_time"]').attr('readonly', 'readonly').css('width', '80px');
			var form = thisInstance.getForm();
			var sumeTime = thisInstance.differenceDays(form);
			var hours = (Math.ceil((sumeTime / 3600000) * 100) / 100).toFixed(2);

			jQuery('input[name="sum_time"]').val(hours);
			jQuery('.dateField').on('change', function () {
				sumeTime = thisInstance.differenceDays(form);
				if (sumeTime == 'Error') {
					return false;
				}
				hours = (Math.ceil((sumeTime / 3600000) * 100) / 100).toFixed(2);
				jQuery('input[name="sum_time"]').val(hours);
			});
			jQuery('.clockPicker').on('change', function () {
				sumeTime = thisInstance.differenceDays(form);
				if (sumeTime == 'Error') {
					return false;
				}
				hours = (Math.round((sumeTime / 3600000) * 100) / 100).toFixed(2);
				jQuery('input[name="sum_time"]').val(hours);
			});
		},

		differenceDays: function (container) {
			let firstDate = container.find('input[name="date_start"]');
			let firstDateFormat = firstDate.data('date-format');
			let firstDateValue = firstDate.val();
			let secondDate = container.find('input[name="due_date"]');
			let secondDateFormat = secondDate.data('date-format');
			let secondDateValue = secondDate.val();
			let firstTime = container.find('input[name="time_start"]');
			let secondTime = container.find('input[name="time_end"]');
			if (firstTime.length) {
				firstDateValue = firstDateValue + ' ' + firstTime.val();
			}
			if (secondTime.length) {
				secondDateValue = secondDateValue + ' ' + secondTime.val();
			}
			try {
				let firstDateInstance = Vtiger_Helper_Js.getDateInstance(firstDateValue, firstDateFormat);
				let secondDateInstance = Vtiger_Helper_Js.getDateInstance(secondDateValue, secondDateFormat);
				let timeBetweenDates = secondDateInstance - firstDateInstance;
				if (timeBetweenDates >= 0) {
					return timeBetweenDates;
				}
			} catch (err) {
				return 'Error';
			}
			return 'Error';
		},

		/**
		 * Function to register recordpresave event
		 */
		registerRecordPreSaveEvent: function (container) {
			var thisInstance = this;
			var form = $('form.js-form');
			if (form.length == 0) {
				form = this.getForm();
			}
			form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
				var sumeTime2 = thisInstance.differenceDays(form);
				if (sumeTime2 == 'Error') {
					var parametres = {
						text: app.vtranslate('JS_DATE_SHOULD_BE_GREATER_THAN'),
						type: 'error'
					};
					app.showNotify(parametres);
					return false;
				}
			});
		},
		escapeRegExp: function (string) {
			return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1');
		},
		replaceAll: function (string, find, replace) {
			var thisInstance = this;
			string = string.replace(new RegExp(thisInstance.escapeRegExp('&Oacute;'), 'g'), 'Ó');
			return string.replace(new RegExp(thisInstance.escapeRegExp(find), 'g'), replace);
		},
		registerBasicEvents: function (container) {
			this._super(container);
			this.registerRecordPreSaveEvent(container);
		},
		registerEvents: function () {
			this._super();
			this.registerGenerateTCFieldTimeAndCost();
		}
	}
);
