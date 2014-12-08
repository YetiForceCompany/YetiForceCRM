/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Settings_Workflows_Edit_Js("Settings_Workflows_Edit1_Js",{},{

	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step1Container;
	},

	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step1Container = element;
		return this;
	},

	/**
	 * Function  to intialize the reports step1
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#workflow_step1');
		}
		if(container.is('#workflow_step1')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#workflow_step1'));
		}
	},

	submit : function(){
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(formData).then(
			function(data) {
				form.hide();
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
				aDeferred.resolve(data);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to register event for scheduled workflows UI
	 */
	registerEventForScheduledWorkflow : function() {
		var thisInstance = this;
		jQuery('input[name="execution_condition"]').on('click', function(e) {
			var element = jQuery(e.currentTarget);
			var scheduleBoxContainer = jQuery('#scheduleBox');
			if(element.is(':checked') && element.val() == '6') {	//for scheduled workflows
				scheduleBoxContainer.removeClass('hide');
			} else {
				scheduleBoxContainer.addClass('hide');
			}
		});
		app.registerEventForTimeFields('#schtime', true);
		app.registerEventForDatePickerFields('#scheduleByDate', true);

		jQuery('#annualDates').chosen();
		jQuery('#schdayofweek').chosen();
		jQuery('#schdayofmonth').chosen();

		var currentYear = new Date().getFullYear();
		jQuery('#annualDatePicker').datepick({autoSize: true, multiSelect:100,monthsToShow: [1,2],
				minDate: '01/01/'+currentYear, maxDate: '12/31/'+currentYear,
				yearRange: currentYear+':'+currentYear,
				onShow : function() {
					//Hack to remove the year
					thisInstance.removeYearInAnnualWorkflow();
				},
				onSelect : function(dates) {
					var datesInfo = [];
					var values = [];
					var html='';
					// reset the annual dates
					var annualDatesEle = jQuery('#annualDates');
					thisInstance.updateAnnualDates(annualDatesEle);
					for(index in dates) {
						var date = dates[index];
						datesInfo.push({
								id:thisInstance.DateToYMD(date),
								text:thisInstance.DateToYMD(date)
							});
						values.push(thisInstance.DateToYMD(date));
						html += '<option selected value='+thisInstance.DateToYMD(date)+'>'+thisInstance.DateToYMD(date)+'</option>';
					}
					annualDatesEle.append(html);
					annualDatesEle.trigger("liszt:updated");
				}
			});
			var annualDatesEle = jQuery('#annualDates');
			thisInstance.updateAnnualDates(annualDatesEle);
			annualDatesEle.trigger("liszt:updated");
	},

	removeYearInAnnualWorkflow : function() {
		setTimeout(function() {
			var year = jQuery('.datepick-month.first').find('.datepick-month-year').get(1);
			jQuery(year).hide();
			var monthHeaders = $('.datepick-month-header');
			jQuery.each(monthHeaders, function( key, ele ) {
				var header = jQuery(ele);
				var str = header.html().replace(/[\d]+/, '');
				header.html(str);
			});
		},100);
	},

	updateAnnualDates : function(annualDatesEle) {
		annualDatesEle.html('');
		var annualDatesJSON = jQuery('#hiddenAnnualDates').val();
		if(annualDatesJSON) {
			var hiddenDates = '';
			var annualDates = JSON.parse(annualDatesJSON);
			for(j in annualDates) {
				hiddenDates += '<option selected value='+annualDates[j]+'>'+annualDates[j]+'</option>';
			}
			annualDatesEle.html(hiddenDates);
		}
	},

	DateToYMD : function (date) {
        var year, month, day;
        year = String(date.getFullYear());
        month = String(date.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(date.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        return year + "-" + month + "-" + day;
    },

	registerEventForChangeInScheduledType : function() {
		var thisInstance = this;
		jQuery('#schtypeid').on('change', function(e){
			var element = jQuery(e.currentTarget);
			var value = element.val();

			thisInstance.showScheduledTime();
			thisInstance.hideScheduledWeekList();
			thisInstance.hideScheduledMonthByDateList();
			thisInstance.hideScheduledSpecificDate();
			thisInstance.hideScheduledAnually();

			if(value == '1') {	//hourly
				thisInstance.hideScheduledTime();
			} else if(value == '3') {	//weekly
				thisInstance.showScheduledWeekList();
			} else if(value == '4') {	//specific date
				thisInstance.showScheduledSpecificDate();
			} else if(value == '5') {	//monthly by day
				thisInstance.showScheduledMonthByDateList();
			} else if(value == '7') {
				thisInstance.showScheduledAnually();
			}
		});
	},

	hideScheduledTime : function() {
		jQuery('#scheduledTime').addClass('hide');
	},

	showScheduledTime : function() {
		jQuery('#scheduledTime').removeClass('hide');
	},

	hideScheduledWeekList : function() {
		jQuery('#scheduledWeekDay').addClass('hide');
	},

	showScheduledWeekList : function() {
		jQuery('#scheduledWeekDay').removeClass('hide');
	},

	hideScheduledMonthByDateList : function() {
		jQuery('#scheduleMonthByDates').addClass('hide');
	},

	showScheduledMonthByDateList : function() {
		jQuery('#scheduleMonthByDates').removeClass('hide');
	},

	hideScheduledSpecificDate : function() {
		jQuery('#scheduleByDate').addClass('hide');
	},

	showScheduledSpecificDate : function() {
		jQuery('#scheduleByDate').removeClass('hide');
	},

	hideScheduledAnually : function() {
		jQuery('#scheduleAnually').addClass('hide');
	},

	showScheduledAnually : function() {
		jQuery('#scheduleAnually').removeClass('hide');
	},
	
	registerEvents : function(){
		var container = this.getContainer();
		
		//After loading 1st step only, we will enable the Next button
		container.find('[type="submit"]').removeAttr('disabled');
		
		var opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function(form,valid) {
            //returns the valid status
            return valid;
        };
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		
		this.registerEventForScheduledWorkflow();
		this.registerEventForChangeInScheduledType();
		

	}
});