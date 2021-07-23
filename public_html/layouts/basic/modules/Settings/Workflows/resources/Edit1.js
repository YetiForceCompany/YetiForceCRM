/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Settings_Workflows_Edit_Js(
	'Settings_Workflows_Edit1_Js',
	{},
	{
		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.step1Container;
		},

		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step1Container = element;
			return this;
		},

		/**
		 * Function  to intialize the reports step1
		 */
		initialize: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('#workflow_step1');
			}
			if (container.is('#workflow_step1')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('#workflow_step1'));
			}
		},

		submit: function () {
			var aDeferred = jQuery.Deferred();
			var form = this.getContainer();
			var formData = form.serializeFormData();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(formData).done(function (data) {
				form.hide();
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
				aDeferred.resolve(data);
			});
			return aDeferred.promise();
		},

		/**
		 * Function to register event for scheduled workflows UI
		 */
		registerEventForScheduledWorkflow: function () {
			let container = $('.js-wf-executions-container');
			$('input[name="execution_condition"]').on('click', function (e) {
				let element = $(e.currentTarget),
					itemBox = element.closest('.js-wf-execution-container').find('.js-wf-execution-item');
				container.find('.js-wf-execution-item').addClass('d-none');
				if (itemBox.length && element.prop('checked')) {
					itemBox.removeClass('d-none');
				}
			});

			app.registerEventForClockPicker($('.clockPicker'));
			App.Fields.Date.register('#scheduleByDate', true);
			App.Fields.DateTime.register($('#scheduleByDate'));
			let newElement = App.Fields.Date.register('#annualDates', false, {
				maxViewMode: 1,
				multidate: true,
				autoclose: false
			}).on('changeDate', function (e) {
				let values = [];
				for (var index in e.dates) {
					let date = e.dates[index];
					let formated = moment(date).format(CONFIG.dateFormat.toUpperCase());
					values.push(formated);
				}
				container.find('#annualDates').val(values.join(','));
			});
			newElement
				.closest('.date')
				.find('.js-date__btn')
				.on('click', (e) => {
					newElement.trigger('click');
				});
			App.Fields.Picklist.showSelect2ElementView($('#schdayofweek'));
			App.Fields.Picklist.showSelect2ElementView($('#schdayofmonth'));
		},

		registerEventForChangeInScheduledType: function () {
			var thisInstance = this;
			jQuery('#schtypeid').on('change', function (e) {
				var element = jQuery(e.currentTarget);
				var value = element.val();

				thisInstance.hideScheduledTime();
				thisInstance.hideScheduledWeekList();
				thisInstance.hideScheduledMonthByDateList();
				thisInstance.hideScheduledSpecificDate();
				thisInstance.hideScheduledAnually();
				if ($.inArray(value, ['2', '11', '12', '13']) != -1) {
					//hourly
					thisInstance.showScheduledTime();
				} else if (value == '3') {
					//weekly
					thisInstance.showScheduledWeekList();
				} else if (value == '4') {
					//specific date
					thisInstance.showScheduledSpecificDate();
				} else if (value == '5') {
					//monthly by day
					thisInstance.showScheduledMonthByDateList();
				} else if (value == '7') {
					thisInstance.showScheduledAnually();
				}
			});
		},

		hideScheduledTime: function () {
			jQuery('#scheduledTime').addClass('d-none');
		},

		showScheduledTime: function () {
			jQuery('#scheduledTime').removeClass('d-none');
		},

		hideScheduledWeekList: function () {
			jQuery('#scheduledWeekDay').addClass('d-none');
		},

		showScheduledWeekList: function () {
			jQuery('#scheduledWeekDay').removeClass('d-none');
		},

		hideScheduledMonthByDateList: function () {
			jQuery('#scheduleMonthByDates').addClass('d-none');
		},

		showScheduledMonthByDateList: function () {
			jQuery('#scheduleMonthByDates').removeClass('d-none');
		},

		hideScheduledSpecificDate: function () {
			jQuery('#scheduleByDate').addClass('d-none');
		},

		showScheduledSpecificDate: function () {
			jQuery('#scheduleByDate').removeClass('d-none');
		},

		hideScheduledAnually: function () {
			jQuery('#scheduleAnually').addClass('d-none');
		},

		showScheduledAnually: function () {
			jQuery('#scheduleAnually').removeClass('d-none');
		},

		registerEvents: function () {
			var container = this.getContainer();

			//After loading 1st step only, we will enable the Next button
			container.find('[type="submit"]').removeAttr('disabled');

			var opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'bottomRight';
			container.validationEngine(opts);

			this.registerEventForScheduledWorkflow();
			this.registerEventForChangeInScheduledType();
		}
	}
);
