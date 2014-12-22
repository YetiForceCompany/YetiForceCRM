/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Detail_Js("SalesOrder_Detail_Js",{},{
	
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent : function(form){
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		var fieldsForRecurrence = new Array("start_period","end_period","recurring_frequency","payment_duration","invoicestatus");
		form.on(this.fieldPreSave,'[name="enable_recurring"]', function(e) {
			var currentElement = jQuery(e.currentTarget);
			jQuery(fieldsForRecurrence).each(function(key,value){
				var relatedFieldSavedValue = form.find('[value="'+value+'"]').data('prevValue');
				if((currentElement.is(':checked')) && (relatedFieldSavedValue == '')){
				form.removeData('submit');
				e.preventDefault();
			}
			});
		})
	},
	
	/**
	 * Function to register event for enabling recurrence
	 * When recurrence is enabled some of the fields need
	 * to be check for mandatory validation
	 */
	registerEventForEnablingRecurrence : function(){
		var thisInstance = this;
		var form = this.getForm();
		var enableRecurrenceField = form.find('[name="enable_recurring"]');
		var fieldsForValidation = new Array('recurring_frequency','start_period','end_period','payment_duration','invoicestatus');
		enableRecurrenceField.on('change',function(e){
			var element = jQuery(e.currentTarget);
			var addValidation;
			if(element.is(':checked')){
				addValidation = true;
			}else{
				addValidation = false;
			}
			if(addValidation){
				form.validationEngine('detach');
				thisInstance.AddOrRemoveRequiredValidation(fieldsForValidation,addValidation);
				form.validationEngine(app.validationEngineOptions);
				jQuery(fieldsForValidation).each(function(key,value){
					form.find('[name="'+value+'"]').trigger('click');
				});
			}else{
				thisInstance.AddOrRemoveRequiredValidation(fieldsForValidation,addValidation);
			}
		})
		if(!enableRecurrenceField.is(":checked")){
			thisInstance.AddOrRemoveRequiredValidation(fieldsForValidation,false);
		}else if(enableRecurrenceField.is(":checked")){
			thisInstance.AddOrRemoveRequiredValidation(fieldsForValidation,true);
		}
	},
	
	/**
	 * Function to add or remove required validation for dependent fields
	 */
	AddOrRemoveRequiredValidation : function(dependentFieldsForValidation,addValidation){
		var form = this.getForm();
		jQuery(dependentFieldsForValidation).each(function(key,value){
			var relatedField = form.find('[name="'+value+'"]');
			if(addValidation){
				var validationValue = relatedField.attr('data-validation-engine');
				if(validationValue.indexOf('[f') > 0){
					relatedField.attr('data-validation-engine','validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
				}
				if(relatedField.is("select")){
					relatedField.attr('disabled',false).trigger("liszt:updated");
				}else{
					relatedField.removeAttr('disabled');
				}
			}else if(!addValidation){
				if(relatedField.is("select")){
					relatedField.attr('disabled',true).trigger("liszt:updated");
				}else{
					relatedField.attr('disabled','disabled');
				}
				relatedField.closest('td').find('.edit').addClass('hide');
			}
		})
	},
	
	getCustomFieldNameValueMap : function(fieldNameValueMap){
		var fieldName = fieldNameValueMap['field'];
		if(fieldName == "enable_recurring"){
			var fieldsForValidation = new Array('recurring_frequency','start_period','end_period','payment_duration','invoicestatus');
			var form = this.getForm();
			jQuery(fieldsForValidation).each(function(key,value){
				var relatedFieldSavedValue = jQuery('[value="'+value+'"]',form).data('prevValue');
				fieldNameValueMap[value] = relatedFieldSavedValue;
			})
		}
		return fieldNameValueMap;
	},
	
	/**
    * Function which will regiter all events for this page
    */
    registerEvents : function(){
		this._super();
		var form = this.getForm();
		this.registerRecordPreSaveEvent(form);
		this.registerEventForEnablingRecurrence();
    }
});