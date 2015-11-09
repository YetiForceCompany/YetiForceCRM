/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Edit_Js("SalesOrder_Edit_Js", {}, {
	addressFieldsMappingBlockID: {
		'LBL_ADDRESS_INFORMATION': 'a',
		'LBL_ADDRESS_DELIVERY_INFORMATION': 'b'
	},

	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		this._super(container);
		var thisInstance = this;
		
		jQuery('input[name="account_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
//			thisInstance.referenceSelectionEventHandler(data, container);
		});
	},

	/**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);

		if(sourceFieldElement.attr('name') == 'contact_id' || sourceFieldElement.attr('name') == 'potential_id') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="account_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				var closestContainer = parentIdElement.closest('.fieldValue');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			} else if(sourceFieldElement.attr('name') == 'potential_id') {
				parentIdElement  = form.find('[name="contact_id"]');
				if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
					closestContainer = parentIdElement.closest('.fieldValue');
					params['related_parent_id'] = parentIdElement.val();
					params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
        }
        return params;
    },

	/**
	 * Function to search module names
	 */
	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}
		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if (params.search_module == 'Contacts' || params.search_module == 'Potentials') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="account_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('.fieldValue');
				params.parent_id = parentIdElement.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			} else if(params.search_module == 'Potentials') {
				parentIdElement  = form.find('[name="contact_id"]');
				if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
					closestContainer = parentIdElement.closest('.fieldValue');
					params.parent_id = parentIdElement.val();
					params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
		}
		else if ( params.search_module == 'Products' || params.search_module == 'Services' ) {
			params.potentialid = jQuery('[name="potential_id"]').val();
		}

		AppConnector.request(params).then(
			function(data){
				aDeferred.resolve(data);
			},
			function(error){
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
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
			
			//If validation need to be added for new elements,then we need to detach and attach validation
			//to form
			if(addValidation){
				form.validationEngine('detach');
				thisInstance.AddOrRemoveRequiredValidation(fieldsForValidation,addValidation);
				//For attaching validation back we are using not using attach,because chosen select validation will be missed
				form.validationEngine(app.validationEngineOptionsForRecord);
				//As detach is used on form for detaching validationEngine,it will remove any actions on form submit,
				//so events that are registered on form submit,need to be registered again after validationengine detach and attach
				thisInstance.registerSubmitEvent();
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
					relatedField.attr('disabled',false).trigger("chosen:updated");
				}else{
					relatedField.removeAttr('disabled');
				}
			}else if(!addValidation){
				if(relatedField.is("select")){
					relatedField.attr('disabled',true).trigger("chosen:updated");
				}else{
					relatedField.attr('disabled',"disabled");
				}
				relatedField.validationEngine('hide');
				if(relatedField.is('select') && relatedField.hasClass('chzn-select')){
					var parentTd = relatedField.closest('.fieldValue');
					parentTd.find('.chzn-container').validationEngine('hide');
				}
			}
		})
	},
    mapResultsToFields: function(referenceModule,element,responseData){
		var parentRow = jQuery(element).closest('tr.'+this.rowClass);
		if(referenceModule == 'Calculations'){
			var lineItemNameElment = jQuery('input.calculation',parentRow);
		}else{
			var lineItemNameElment = jQuery('input.productName',parentRow);
		}
		for(var id in responseData){
			var recordId = id;
			var recordData = responseData[id];
			var selectedName = recordData.name;
			var unitPrice = recordData.listprice;
			var usageUnit = recordData.usageunit;
			var listPriceValues = recordData.listpricevalues;
			var taxes = recordData.taxes;
			if(referenceModule == 'Products') {
				parentRow.data('quantity-in-stock',recordData.quantityInStock);
			}
			var description = recordData.description;

			lineItemNameElment.val(selectedName);
			lineItemNameElment.attr('disabled', 'disabled');
			if(referenceModule == 'Calculations'){
				jQuery('input.selectedModuleIdC',parentRow).val(recordId);
				jQuery('input.lineItemTypeC',parentRow).val(referenceModule);
			}else{
				jQuery('input.selectedModuleId',parentRow).val(recordId);
				jQuery('input.lineItemType',parentRow).val(referenceModule);
				jQuery('input.listPrice',parentRow).val(unitPrice);
				jQuery('span.usageUnit',parentRow).text(usageUnit);
				var currencyId = jQuery("#currency_id").val();
				var listPriceValuesJson  = JSON.stringify(listPriceValues);
				if(typeof listPriceValues[currencyId]!= 'undefined') {
					this.setListPriceValue(parentRow, listPriceValues[currencyId]);
					this.lineItemRowCalculations(parentRow);
				}
				jQuery('input.listPrice',parentRow).attr('list-info',listPriceValuesJson);
				jQuery('textarea.lineItemCommentBox',parentRow).val(description);
				var taxUI = this.getTaxDiv(taxes,parentRow);
				jQuery('.taxDivContainer',parentRow).html(taxUI);
				if(this.isIndividualTaxMode()) {
					parentRow.find('.productTaxTotal').removeClass('hide')
				}else{
					parentRow.find('.productTaxTotal').addClass('hide')
				}
			}
		}
		if(referenceModule == 'Products'){
			this.loadSubProducts(parentRow);
		}
		if(referenceModule != 'Calculations'){
			jQuery('.qty',parentRow).trigger('focusout');
		}
    },
	registerClearLineItemSelection : function() {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('click','.clearLineItem',function(e){
			var elem = jQuery(e.currentTarget);
			var parentElem = elem.closest('.fieldValue');
			var selectedModuleIdC = parentElem.find('.selectedModuleIdC');
			if(selectedModuleIdC.length == 1 ){
				parentElem.find('input.calculation').removeAttr('disabled').val('');
				parentElem.find('input.selectedModuleIdC').val('');
			}else{
				thisInstance.clearLineItemDetails(parentElem);
				parentElem.find('input.productName').removeAttr('disabled').val('');
			}
			e.preventDefault();
		});
	},

	registerEvents: function(){
		this._super();
		this.registerEventForEnablingRecurrence();
	}
});


