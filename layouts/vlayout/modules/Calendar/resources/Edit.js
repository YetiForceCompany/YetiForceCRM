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
    relatedContactElement : false,

    getRelatedContactElement : function() {
        if(this.relatedContactElement == false) {
            this.relatedContactElement =  jQuery('#contact_id_display');
        }
        return this.relatedContactElement;
    },

    isEvents : function() {
        var form = this.getForm();
        var moduleName = form.find('[name="module"]').val();
        if(moduleName == 'Events') {
            return true;
        }
        return false;
    },

    getPopUpParams : function(container) {
        var params = this._super(container);
		var sourceFieldElement = jQuery('input[class="sourceField"]',container);

		if(sourceFieldElement.attr('name') == 'contact_id') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="parent_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('td');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			}
		}
        return params;
    },

	registerReminderFieldCheckBox : function() {
		this.getForm().find('input[name="set_reminder"]').on('change', function(e) {
			var element = jQuery(e.currentTarget);
			var closestDiv = element.closest('div').next();
			if(element.is(':checked')) {
				closestDiv.show();
			} else {
				closestDiv.hide();
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
				repeatUI.show();
			} else {
				repeatUI.hide();
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
				jQuery('#repeatMonthDayType').select2("enable");
				jQuery('#repeatMonthDay').select2("enable");
			} else {
				jQuery('#repeatMonthDate').removeAttr('disabled');
				jQuery('#repeatMonthDayType').select2("disable");
				jQuery('#repeatMonthDay').select2("disable");
			}
	},

    /**
     * Function which will fill the already saved contacts on load 
     */
    fillRelatedContacts : function() {
        var form = this.getForm();
        var relatedContactValue = form.find('[name="relatedContactInfo"]').data('value');
        for(var contactId in relatedContactValue) {
            var info = relatedContactValue[contactId];
            info.text = info.name;
            relatedContactValue[contactId] = info;
        }
        this.getRelatedContactElement().select2('data',relatedContactValue);
    },

    addNewContactToRelatedList : function(newContactInfo){
         var resultentData = new Array();
            var element =  jQuery('#contact_id_display');
            var selectContainer = jQuery(element.data('select2').containerSelector);
            var choices = selectContainer.find('.select2-search-choice');
            choices.each(function(index,element){
                resultentData.push(jQuery(element).data('select2-data'));
            });

            var select2FormatedResult = newContactInfo.data;
            for(var i=0 ; i < select2FormatedResult.length; i++) {
              var recordResult = select2FormatedResult[i];
              recordResult.text = recordResult.name;
              resultentData.push( recordResult );
            }
            jQuery('#contact_id_display').select2('data',resultentData);
    },

    referenceCreateHandler : function(container) {
        var thisInstance = this;
		var form = thisInstance.getForm();
		var mode = jQuery(form).find('[name="module"]').val();
        if(container.find('.sourceField').attr('name') != 'contact_id'){
            this._super(container);
			return;
        }
         var postQuickCreateSave  = function(data) {
            var params = {};
            params.name = data.result._recordLabel;
            params.id = data.result._recordId;
			if(mode == "Calendar"){
				thisInstance.setReferenceFieldValue(container, params);
				return;
			}
            thisInstance.addNewContactToRelatedList({'data':[params]});
        }

        var referenceModuleName = this.getReferencedModuleName(container);
        var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
        if(quickCreateNode.length <= 0) {
            Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'))
        }
        quickCreateNode.trigger('click',{'callbackFunction':postQuickCreateSave});
    },

    registerClearReferenceSelectionEvent : function(container) {
        var thisInstance = this;
        this._super(container);

        this.getRelatedContactElement().closest('td').find('.clearReferenceSelection').on('click',function(e){
            thisInstance.getRelatedContactElement().select2('data',[]);
        });
    },
	
	/**
	 * Function to change the end time based on default call duration
	 */
	registerTimeStartChangeEvent : function(container) {
		container.on('changeTime','input[name="time_start"]',function(e) {
			var strtTimeElement = jQuery(e.currentTarget);
			var endTimeElement = container.find('[name="time_end"]');
			var dateStartElement = container.find('[name="date_start"]');
            var endDateElement = container.find('[name="due_date"]');
			
            if(endDateElement.data('userChangedTime') == true) {
                return;
            }
            if(jQuery('[name="userChangedEndDateTime"]').val() == '1') {
                return;
            }
			
			var startDate = dateStartElement.val();
			var strtTime = strtTimeElement.val();
			
			var result = Vtiger_Time_Validator_Js.invokeValidation(strtTimeElement);
			if(result != true){
				return;
			}
			var dateTime = startDate+' '+strtTime;
			var dateFormat = container.find('[name="date_start"]').data('dateFormat');
			var timeFormat = endTimeElement.data('format');
			var date = Vtiger_Helper_Js.getDateInstance(dateTime,dateFormat);

			var endDateInstance = Date.parse(date);
			if(container.find('[name="activitytype"]').val() == 'Call'){
				var defaulCallDuration = container.find('[name="defaultCallDuration"]').val();
				endDateInstance.addMinutes(defaulCallDuration);
			} else {
				var defaultOtherEventDuration = container.find('[name="defaultOtherEventDuration"]').val();
				endDateInstance.addMinutes(defaultOtherEventDuration);
			}
			var endDateString = app.getDateInVtigerFormat(dateFormat,endDateInstance);
			if(timeFormat == 24){
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm tt';
			}
			var endTimeString = endDateInstance.toString(defaultTimeFormat);

			endDateElement.val(endDateString);
			endTimeElement.val(endTimeString);
		});
        
        container.find('[name="date_start"]').on('change',function(e) {
            var startDateElement = jQuery(e.currentTarget);
            var result = Vtiger_Date_Validator_Js.invokeValidation(startDateElement);
            if(result != true){
				return;
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
	 * Function to change the Other event Duration
	 */
	registerActivityTypeChangeEvent : function(container) {
		container.on('change','[name="activitytype"]',function(e) {
			container.find('input[name="time_start"]').trigger('changeTime');
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
                jQuery('<input type="hidden" name="contactidlist" /> ').appendTo(form).val(thisInstance.getRelatedContactElement().val().split(',').join(';'));
                form.find('[name="contact_id"]').attr('name','');
				var inviteeIdsList = jQuery('#selectedUsers').val();
				if(inviteeIdsList != null) {
					inviteeIdsList = jQuery('#selectedUsers').val().join(';')
				}
                jQuery('<input type="hidden" name="inviteesid" />').appendTo(form).val(inviteeIdsList);
            }
		})
	},

    registerRelatedContactSpecificEvents : function() {
        var thisInstance = this;
		var form = this.getForm();
		
		form.find('[name="contact_id"]').on(Vtiger_Edit_Js.preReferencePopUpOpenEvent,function(e){
            var form = thisInstance.getForm();
            var parentIdElement  = form.find('[name="parent_id"]');
            var container = parentIdElement.closest('td');
            var popupReferenceModule = jQuery('input[name="popupReferenceModule"]',container).val();
            
            if(popupReferenceModule == 'Leads' && parentIdElement.val().length > 0) {
                e.preventDefault();
                Vtiger_Helper_Js.showPnotify(app.vtranslate('LBL_CANT_SELECT_CONTACT_FROM_LEADS'));
            }
        })
        //If module is not events then we dont have to register events
        if(!this.isEvents()) {
            return;
        }
        this.getRelatedContactElement().select2({
             minimumInputLength: 3,
             ajax : {
                'url' : 'index.php?module=Contacts&action=BasicAjax&search_module=Contacts',
                'dataType' : 'json',
                'data' : function(term,page){
                     var data = {};
                     data['search_value'] = term;
                     var parentIdElement  = form.find('[name="parent_id"]');
                     if(parentIdElement.val().length > 0) {
                        var closestContainer = parentIdElement.closest('td');
                        data['parent_id'] = parentIdElement.val();
                        data['parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
                     }
                     return data;
                },
                'results' : function(data){
                    data.results = data.result;
                    for(var index in data.results ) {

                        var resultData = data.result[index];
                        resultData.text = resultData.label;
                    }
                    return data
                },
                 transport : function(params){
                    return jQuery.ajax(params);
                 }
             },
             multiple : true
        });
		
        //To add multiple selected contact from popup
        form.find('[name="contact_id"]').on(Vtiger_Edit_Js.refrenceMultiSelectionEvent,function(e,result){
            thisInstance.addNewContactToRelatedList(result);
        });
        
        this.fillRelatedContacts();
    },
	
	/**
	 * Function to get reference search params
	 */
	getReferenceSearchParams : function(element){
		var tdElement = jQuery(element).closest('td');
		var contactField = tdElement.find('[name="contact_id"]');
		var params = {};
		if(contactField.length > 0){
			var form = this.getForm();
			var parentIdElement  = form.find('[name="parent_id"]');
			if(parentIdElement.val().length > 0) {
			   var closestContainer = parentIdElement.closest('td');
			   params['parent_id'] = parentIdElement.val();
			   params['parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			}
		}
		var searchModule = this.getReferencedModuleName(tdElement);
		params.search_module = searchModule;
		return params;
	},

	registerBasicEvents : function(container) {
		this._super(container);
		this.registerActivityTypeChangeEvent(container);
		this.registerTimeStartChangeEvent(container);
        this.registerEndDateTimeChangeLogger(container);
        //Required to set the end time based on the default ActivityType selected
        container.find('[name="activitytype"]').trigger('change');
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
        this.registerRelatedContactSpecificEvents();
		this._super();
	}
});