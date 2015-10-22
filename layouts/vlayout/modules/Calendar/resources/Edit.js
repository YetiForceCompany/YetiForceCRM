/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Calendar_Edit_Js",{

},{
    isEvents : function() {
        var form = this.getForm();
        var moduleName = form.find('[name="module"]').val();
        if(moduleName == 'Events') {
            return true;
        }
        return false;
    },

	registerReminderFieldCheckBox : function() {
		this.getForm().find('input[name="set_reminder"]').on('change', function(e) {
			var element = jQuery(e.currentTarget);
			var closestDiv = element.closest('div').next();
			if(element.is(':checked')) {
				closestDiv.removeClass('hide');
			} else {
				closestDiv.addClass('hide');
			}
		})
	},
	
	/**
	 * Function which will register change event on recurrence field checkbox
	 */
	registerRecurrenceFieldCheckBox : function() {
		var thisInstance = this;
		thisInstance.getForm().find('input[name="recurringcheck"]').on('change', function(e) {
			var element = jQuery(e.currentTarget);
			var repeatUI = jQuery('#repeatUI');
			if(element.is(':checked')) {
				repeatUI.removeClass('hide');
			} else {
				repeatUI.addClass('hide');
			}
		});
	},
	
	/**
	 * Function which will register the change event for recurring type
	 */
	registerRecurringTypeChangeEvent : function() {
		var thisInstance = this;
		jQuery('#recurringType').on('change', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var recurringType = currentTarget.val();
			thisInstance.changeRecurringTypesUIStyles(recurringType);
			
		});
	},
	
	/**
	 * Function which will register the change event for repeatMonth radio buttons
	 */
	registerRepeatMonthActions : function() {
		var thisInstance = this;
		thisInstance.getForm().find('input[name="repeatMonth"]').on('change', function(e) {
			//If repeatDay radio button is checked then only select2 elements will be enable
			thisInstance.repeatMonthOptionsChangeHandling();
		});
	},
	
	
	/**
	 * Function which will change the UI styles based on recurring type
	 * @params - recurringType - which recurringtype is selected
	 */
	changeRecurringTypesUIStyles : function(recurringType) {
		var thisInstance = this;
		if(recurringType == 'Daily' || recurringType == 'Yearly') {
			jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
			jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
		} else if(recurringType == 'Weekly') {
			jQuery('#repeatWeekUI').removeClass('hide').addClass('show');
			jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
		} else if(recurringType == 'Monthly') {
			jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
			jQuery('#repeatMonthUI').removeClass('hide').addClass('show');
		}
	},
	
	/**
	 * This function will handle the change event for RepeatMonthOptions
	 */
	repeatMonthOptionsChangeHandling : function() {
		//If repeatDay radio button is checked then only select2 elements will be enable
			if(jQuery('#repeatDay').is(':checked')) {
				jQuery('#repeatMonthDate').attr('disabled', true);
				jQuery('#repeatMonthDayType').prop("disabled", false);
				jQuery('#repeatMonthDay').prop("disabled", false);
			} else {
				jQuery('#repeatMonthDate').removeAttr('disabled');
				jQuery('#repeatMonthDayType').prop("disabled", true);
				jQuery('#repeatMonthDay').prop("disabled", true);
			}
	},
	
	/**
	 * Function to change the end time based on default call duration
	 */
	registerTimeStartChangeEvent: function (container) {
		var thisInstance = this;
		container.on('changeTime', 'input[name="time_start"]', function (e) {
			var strtTimeElement = jQuery(e.currentTarget);
			var endTimeElement = container.find('[name="time_end"]');
			var dateStartElement = container.find('[name="date_start"]');
			var endDateElement = container.find('[name="due_date"]');

			if (endDateElement.data('userChangedTime') == true) {
				return;
			}
			if (jQuery('[name="userChangedEndDateTime"]').val() == '1') {
				return;
			}

			var endDate = endDateElement.val();
			var endTime = endTimeElement.val();

			var result = Vtiger_Time_Validator_Js.invokeValidation(strtTimeElement);
			if (result != true) {
				return;
			}
			var dateTime = endDate + ' ' + endTime;
			var dateFormat = container.find('[name="due_date"]').data('dateFormat');
			var timeFormat = endTimeElement.data('format');
			var date = Vtiger_Helper_Js.getDateInstance(dateTime, dateFormat);
			var endDateInstance = Date.parse(date);


			if (container.find('[name="activitytype"]').val() == 'Call') {
				var defaulCallDuration = container.find('[name="defaultCallDuration"]').val();
				endDateInstance.addMinutes(defaulCallDuration);
			} else {
				var defaultOtherEventDuration = container.find('[name="defaultOtherEventDuration"]').val();
				endDateInstance.addMinutes(defaultOtherEventDuration);
			}
			var endDateString = app.getDateInVtigerFormat(dateFormat, endDateInstance);
			if(timeFormat == 24){
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm tt';
			}
			var endTimeString = endDateInstance.toString(defaultTimeFormat);
			var selectedEndTimeString = container.find('[name="selectedTimeStart"]');
			if(selectedEndTimeString.length > 0)
				endTimeString = selectedEndTimeString.val();

			endDateElement.val(endDateString);
			endTimeElement.val(endTimeString);
		});
        
        container.find('[name="date_start"]').on('change',function(e) {
			var startDateElement = jQuery(e.currentTarget);
			var endDateElement = container.find('[name="due_date"]');
			
			var start = thisInstance.getDateInstance(container, 'start');
			var end = thisInstance.getDateInstance(container, 'end');
			var dateFormat = $('#userDateFormat').val();
			var timeFormat = $('#userTimeFormat').val();
			if(start > end){
				end = start;
				var endDateString = app.getDateInVtigerFormat(dateFormat, end);
				endDateElement.val(endDateString);
				app.registerEventForDatePickerFields(container);
			}
            var timeStartElement = startDateElement.closest('td.fieldValue').find('[name="time_start"]');
            timeStartElement.trigger('changeTime');
        });
		
		container.find('input[name="time_start"]').on('focus',function(e){
			var element = jQuery(e.currentTarget);
			element.data('prevValue',element.val());
		})
		
		container.find('input[name="time_start"]').on('blur', function(e,data){
            if(typeof data =='undefined'){
                data = {};
            }
            
            if(typeof data.forceChange == 'undefined') {
                data.forceChange = false;
            }
			var element = jQuery(e.currentTarget);
			var currentValue = element.val();
			var prevValue = element.data('prevValue');
			if(currentValue != prevValue || data.forceChange) {
				var list = element.data('timepicker-list');
                if(!list) {
                    //To generate the list 
                    element.timepicker('show');
                    element.timepicker('hide');
                    list = element.data('timepicker-list');
                }
				list.show();
				e = jQuery.Event("keydown");
				e.which = 13;
				e.keyCode = 13;
				element.trigger(e)
				list.hide();
			}
		});
	},
    
    registerEndDateTimeChangeLogger : function(container) {
        container.find('[name="time_end"]').on('changeTime',function(e) {
            var timeElement = jQuery(e.currentTarget);
            var result = Vtiger_Time_Validator_Js.invokeValidation(timeElement);
			if(result != true){
				return;
			}
            var timeDateElement = timeElement.closest('td.fieldValue').find('[name="due_date"]');
            jQuery('[name="userChangedEndDateTime"]').val('1');
            timeDateElement.data('userChangedTime',true);
        });
        
        container.find('[name="due_date"]').on('change',function(e) {
            var dueDateElement = jQuery(e.currentTarget);
            var result = Vtiger_Date_Validator_Js.invokeValidation(dueDateElement);
            if(result != true){
				return;
			}
            jQuery('[name="userChangedEndDateTime"]').val('1');
            dueDateElement.data('userChangedTime',true);
        });
    },
	
	/**
	 * This function will register the submit event on form
	 */
	registerFormSubmitEvent : function() {
        var thisInstance = this;
		var form = this.getForm();
		form.on('submit', function(e) {
			var recurringCheck = form.find('input[name="recurringcheck"]').is(':checked');
			
			//If the recurring check is not enabled then recurring type should be --None--
			if(recurringCheck == false) {
				jQuery('#recurringType').append(jQuery('<option value="--None--">None</option>')).val('--None--');
			}
            if(thisInstance.isEvents()) {
				var inviteeIdsList = jQuery('#selectedUsers').val();
				if(inviteeIdsList != null) {
					inviteeIdsList = jQuery('#selectedUsers').val().join(';')
				}
                jQuery('<input type="hidden" name="inviteesid" />').appendTo(form).val(inviteeIdsList);
            }
		})
	},

	registerBasicEvents : function(container) {
		this._super(container);
		this.toggleTimesInputs(container);
		this.registerTimesInputs(container);
		this.registerTimeStartChangeEvent(container);
        this.registerEndDateTimeChangeLogger(container);
        //Required to set the end time based on the default ActivityType selected
        container.find('[name="activitytype"]').trigger('change');
	},

	toggleTimesInputs: function(container){		
		container.find(':checkbox').change(function() {
			var checkboxName =  $(this).attr('name');
			if('allday' == checkboxName){
				var checkboxIsChecked = $(this).is(':checked');
				if (!container.find('#quickCreate').length){
					if(checkboxIsChecked){
						container.find('.time').hide();
					}else{
						container.find('.time').show();
					}
				}
			}
		});
	},
	
	registerTimesInputs: function(container){		
		var allday = container.find('[name="allday"]:checkbox');
		if(allday.prop('checked')){
			container.find('.time').hide();
		};
	},
	
	getDateInstance: function(container, type){
		var startDateElement = container.find('[name="date_start"]');
		var endDateElement = container.find('[name="due_date"]');
		var endTimeElement = container.find('[name="time_end"]');
		var startTimeElement = container.find('[name="time_start"]');
		var startDate = startDateElement.val();
		var startTime = startTimeElement.val();
		var endTime = endTimeElement.val();
		var endDate = endDateElement.val();
		var dateFormat = $('#userDateFormat').val();
		var timeFormat = $('#userTimeFormat').val();
		if(type == 'start'){
			var dateInstance = Vtiger_Helper_Js.getDateInstance(startDate + ' ' + startTime, dateFormat);
		}
		if(type == 'end'){
			var dateInstance = Vtiger_Helper_Js.getDateInstance(endDate + ' ' + endTime, dateFormat);
		}
		return dateInstance;
	},
	
	registerEvents : function(){
		var statusToProceed = this.proceedRegisterEvents();
		if(!statusToProceed){
			return;
		}
		this.registerReminderFieldCheckBox();
		this.registerRecurrenceFieldCheckBox();
		this.registerFormSubmitEvent();
		this.repeatMonthOptionsChangeHandling();
		this.registerRecurringTypeChangeEvent();
		this.registerRepeatMonthActions();
		this._super();
	}
});

