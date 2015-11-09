/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

Inventory_Edit_Js("Calculations_Edit_Js",{},{
	recalculateMargin : function(lineItemRow) {
		var listPrice = lineItemRow.find('.listPrice').val();
		var purchase = lineItemRow.find('.purchase').val();
		var margin = listPrice-purchase;
		var marginp = '0';
		lineItemRow.find('.margin').val(margin);
		if(purchase != 0){
			marginp = (margin/purchase)*100;
		}
		lineItemRow.find('.marginp').val(marginp);
	},
	recalculateAllMargin : function() {
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var thisInstance = this
		var lineItemTable = this.getLineItemContentsContainer();
		var grandTotal = 0;
		var purchase = 0;
		var margin = 0;
		lineItemTable.find('tr.'+this.rowClass).each(function(index,domElement){
			var lineItemRow = jQuery(domElement);
			var qty = parseFloat(lineItemRow.find('.qty').val());
			grandTotal += parseFloat(lineItemRow.find('.productTotal').val())*qty;
			purchase += parseFloat(lineItemRow.find('.purchase').val())*qty;
			margin += parseFloat(lineItemRow.find('.margin').val())*qty;
		});
		purchase = purchase.toFixed(numberOfDecimal);
		jQuery('.total_purchase').text(purchase);
		margin = margin.toFixed(numberOfDecimal);
		jQuery('.total_margin').text(margin);
		var marginp = 0;
		if(purchase != 0){
			marginp = (margin/purchase)*100;
		}
		marginp = marginp.toFixed(numberOfDecimal);
		jQuery('.total_marginp').text(marginp);
	},
	registerRecalculateMargin : function() {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('focusout','.listPrice',function(e){
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.'+thisInstance.rowClass);
			thisInstance.recalculateMargin(lineItemRow);
			thisInstance.recalculateAllMargin();
		});
		lineItemTable.on('focusout','.purchase',function(e){
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.'+thisInstance.rowClass);
			thisInstance.recalculateMargin(lineItemRow);
			thisInstance.recalculateAllMargin();
		});	
		lineItemTable.on('focusout','.listPrice',function(e){
			thisInstance.recalculateAllMargin();
		});
		lineItemTable.on('focusout','.qty',function(e){
			thisInstance.recalculateAllMargin();
		});	
    },
	/**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		if(sourceFieldElement.attr('name') == 'ticketid' || sourceFieldElement.attr('name') == 'potentialid' || sourceFieldElement.attr('name') == 'projectid') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="relatedid"]');
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
			var parentIdElement  = form.find('[name="relatedid"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('.fieldValue');
				params.parent_id = parentIdElement.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			}
		}
		else if ( params.search_module == 'Products' || params.search_module == 'Services' ) {
			params.potentialid = jQuery('[name="potentialid"]').val();
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
	
	registerQuantityChangeEventHandler : function() {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('focusout','.qty',function(e){
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.'+thisInstance.rowClass);
			thisInstance.quantityChangeActions(lineItemRow);
		});
	 },
	calculateGrandTotal : function(){
        var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var thisInstance = this
		var lineItemTable = this.getLineItemContentsContainer();
		var grandTotal = 0;
		lineItemTable.find('tr.'+this.rowClass).each(function(index,domElement){
			var lineItemRow = jQuery(domElement);
			grandTotal += parseFloat(lineItemRow.find('.productTotal').text());
			
		});
		grandTotal = grandTotal.toFixed(numberOfDecimal);
		jQuery('input[name="total"]').val(grandTotal);
		this.setGrandTotal(grandTotal);
	},
    registerAddingNewProductsAndServices: function(){
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		jQuery('#addProduct').on('click',function(){
			var newRow = thisInstance.getBasicRow().addClass(thisInstance.rowClass);
			jQuery('.lineItemPopup[data-module-name="Services"]',newRow).closest('span.input-group-addon').remove();
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();
			newRow = newRow.appendTo(lineItemTable);
			thisInstance.checkLineItemRow();
			newRow.find('input.rowNumber').val(sequenceNumber);
			thisInstance.updateLineItemsElementWithSequenceNumber(newRow,sequenceNumber);
			newRow.find('input.productName').addClass('autoComplete');
			thisInstance.registerLineItemAutoComplete(newRow);
			newRow.find('textarea.lineItemCommentBox').addClass('ckEditorSource');
			thisInstance.registerEventForCkEditor();
		});
		jQuery('#addService').on('click',function(){
			var newRow = thisInstance.getBasicRow().addClass(thisInstance.rowClass);
			jQuery('.lineItemPopup[data-module-name="Products"]',newRow).closest('span.input-group-addon').remove();
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();
			newRow = newRow.appendTo(lineItemTable);
			thisInstance.checkLineItemRow();
			newRow.find('input.rowNumber').val(sequenceNumber);
			thisInstance.updateLineItemsElementWithSequenceNumber(newRow,sequenceNumber);
			newRow.find('input.productName').addClass('autoComplete');
			thisInstance.registerLineItemAutoComplete(newRow);
			newRow.find('textarea.lineItemCommentBox').addClass('ckEditorSource');
			thisInstance.registerEventForCkEditor();
		});
    },
	registerSubmitEvent : function () {
		var thisInstance = this;
		var editViewForm = this.getForm();
		this._super();
		editViewForm.submit(function(e){
			thisInstance.updateLineItemElementByOrder();
			var lineItemTable = thisInstance.getLineItemContentsContainer();
			jQuery('.discountSave',lineItemTable).trigger('click');
			thisInstance.lineItemToTalResultCalculations();
			thisInstance.saveProductCount();
			thisInstance.saveSubTotalValue();
			thisInstance.saveTotalValue();
			thisInstance.savePreTaxTotalValue();
		})
	},
	registerEvents: function(){
		this._super();
		this.registerQuantityChangeEventHandler();
		this.registerEventForCopyAddress();
		this.registerRecalculateMargin();
	}
});


