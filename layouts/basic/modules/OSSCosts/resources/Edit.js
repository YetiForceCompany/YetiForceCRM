/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery(document).ready(function ($) {    
    // modal is greyed out if z-index is low
    $("#myModal").css("z-index", "999999999");
    
    // Hide modal if "Okay" is pressed
    $('#myModal .okay-button').click(function() {
        var disabled = $('#confirm').attr('disabled');
        if(typeof disabled == 'undefined') {
            $('#myModal').modal('hide');
            $('#uninstall #EditView').submit();
        }
    });
    
    // enable/disable confirm button
    $('#status').change(function() {
        $('#confirm').attr('disabled', !this.checked);
    });
});
Inventory_Edit_Js("OSSCosts_Edit_Js",{},{

	/**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		if(sourceFieldElement.attr('name') == 'ticketid' || sourceFieldElement.attr('name') == 'potentialid' || sourceFieldElement.attr('name') == 'projectid') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="relategid"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('.fieldValue');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
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

		if (params.search_module == 'HelpDesk' || params.search_module == 'Potentials' || params.search_module == 'Project') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="relategid"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('.fieldValue');
				params.parent_id = parentIdElement.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			}
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
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		this._super(container);
		var thisInstance = this;
		
		jQuery('input[name="relategid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
//			thisInstance.referenceSelectionEventHandler(data, container);
		});
	},
	registerQuantityChangeEventHandler : function() {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('focusout','.qty',function(e){
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.'+thisInstance.rowClass);
			var quantityInStock = lineItemRow.data('quantityInStock');
			if(typeof quantityInStock  != 'undefined') {
				lineItemRow.find('.stockAlert').removeClass('hide').find('.maxQuantity').text(quantityInStock);
			}
			thisInstance.quantityChangeActions(lineItemRow);
		});
	 },

	addressCostsFieldsMapping : [
							'buildingnumber',
							'localnumber',
							'country',
							'state',
							'addresslevel3',
							'addresslevel4',
							'city',
							'addresslevel6',
							'code',
							'street',
							'pobox'
							],

	/**
	* Function to copy address between fields
	* @param strings which accepts value as either odd or even
	*/
	copyAddress : function(fromLabel, toLabel, reletedRecord,sourceModule){
		var status = false;
		var thisInstance = this;
		var formElement = this.getForm();
		var addressMapping = this.addressFieldsMapping;
		var addressCostMapping = this.addressCostsFieldsMapping;
		var BlockIds = this.addressFieldsMappingBlockID;
	
		from = BlockIds[fromLabel];
		if(reletedRecord === false || sourceModule === false)
			from = BlockIds[fromLabel];
		to = BlockIds[toLabel];
		for(var key in addressMapping) {
			var nameElementFrom = addressMapping[key]+from;
			var nameElementTo = addressCostMapping[key];
			if(reletedRecord){
				var fromElement = thisInstance.addressFieldsData[nameElementFrom];
				var fromElementLable = thisInstance.addressFieldsData[nameElementFrom+'_label'];
			}else{
				var fromElement = formElement.find('[name="'+nameElementFrom+'"]').val();
				var fromElementLable = formElement.find('[name="'+nameElementFrom+'_display"]').val();
			}			
			var toElement = formElement.find('[name="'+nameElementTo+'"]');
			var toElementLable = formElement.find('[name="'+nameElementTo+'_display"]');
			if(fromElement != '' && fromElement != '0' && fromElement != undefined){
				if(toElementLable.length > 0)
					toElementLable.attr('readonly',true);
				status = true;
				toElement.val(fromElement);
				toElementLable.val(fromElementLable);
			}else{
				toElement.attr('readonly',false);
			}
		}
		if(status == false){
			if(sourceModule == "Accounts"){
				errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
			} else if(sourceModule == "Contacts"){
				errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
			} else {
				errorMsg = 'JS_DOES_NOT_HAVE_AN_ADDRESS';
			}
			Vtiger_Helper_Js.showPnotify(app.vtranslate(errorMsg));
		}
	}, 
	
	registerEvents: function(){
		this._super();
		this.registerQuantityChangeEventHandler();
	}
});
