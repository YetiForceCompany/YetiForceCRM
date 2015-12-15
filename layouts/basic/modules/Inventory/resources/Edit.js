/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Inventory_Edit_Js", {
	zeroDiscountType: 'zero',
	percentageDiscountType: 'percentage',
	directAmountDiscountType: 'amount',
	individualTaxType: 'individual',
	groupTaxType: 'group'
}, {
	//Container which stores the line item elements
	lineItemContentsContainer: false,
	//Container which stores line item result details
	lineItemResultContainer: false,
	//contains edit view form element
	editViewForm: false,
	//a variable which will be used to hold the sequence of the row
	rowSequenceHolder: false,
	//holds the element which has basic hidden row which we can clone to add rows
	basicRow: false,
	//will be having class which is used to identify the rows
	rowClass: 'lineItemRow',
	prevSelectedCurrencyConversionRate: false,
	/**
	 * Function that is used to get the line item container
	 * @return : jQuery object
	 */
	getLineItemContentsContainer: function () {
		if (this.lineItemContentsContainer == false) {
			this.setLineItemContainer(jQuery('#lineItemTab'));
		}
		return this.lineItemContentsContainer;
	},
	/**
	 * Function to set line item container
	 * @params : element - jQuery object which represents line item container
	 * @return : current instance ;
	 */
	setLineItemContainer: function (element) {
		this.lineItemContentsContainer = element;
		return this;
	},
	/**
	 * Function to get the line item result container
	 * @result : jQuery object which represent line item result container
	 */
	getLineItemResultContainer: function () {
		if (this.lineItemResultContainer == false) {
			this.setLinteItemResultContainer(jQuery('#lineItemResult'));
		}
		return this.lineItemResultContainer;
	},
	/**
	 * Function to set line item result container
	 * @param : element - jQuery object which represents line item result container
	 * @result : current instance
	 */
	setLinteItemResultContainer: function (element) {
		this.lineItemResultContainer = element;
		return this;
	},
	/**
	 * Function which will give the closest line item row element
	 * @return : jQuery object
	 */
	getClosestLineItemRow: function (element) {
		return element.closest('tr.' + this.rowClass);
	},
	getTaxTypeSelectElement: function () {
		return jQuery('#taxtype');
	},
	isIndividualTaxMode: function () {
		var taxTypeElement = this.getTaxTypeSelectElement();
		var selectedOption = taxTypeElement.find('option:selected');
		if (selectedOption.val() == Inventory_Edit_Js.individualTaxType) {
			return true;
		}
		return false;
	},
	isGroupTaxMode: function () {
		var taxTypeElement = this.getTaxTypeSelectElement();
		var selectedOption = taxTypeElement.find('option:selected');
		if (selectedOption.val() == Inventory_Edit_Js.groupTaxType) {
			return true;
		}
		return false;
	},
	/**
	 * Function which gives edit view form
	 * @return : jQuery object which represents the form element
	 */
	getForm: function () {
		if (this.editViewForm == false) {
			this.editViewForm = jQuery('#EditView');
		}
		return this.editViewForm;
	},
	/**
	 * Function which gives quantity value
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getQuantityValue: function (lineItemRow) {
		return parseFloat(jQuery('.qty', lineItemRow).val());
	},
	/**
	 * Function which will give me list price value
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getListPriceValue: function (lineItemRow) {
		return parseFloat(jQuery('.listPrice', lineItemRow).val());
	},
	setListPriceValue: function (lineItemRow, listPriceValue) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var listPrice = parseFloat(listPriceValue).toFixed(numberOfDecimal);
		lineItemRow.find('.listPrice').val(listPrice);
		return this;
	},
	/**
	 * Function which will set the line item total value excluding tax and discount
	 * @params : lineItemRow - row which represents the line item
	 *			 lineItemTotalValue - value which has line item total  (qty*listprice)
	 * @return : current instance;
	 */
	setLineItemTotal: function (lineItemRow, lineItemTotalValue) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var lineItemTotal = parseFloat(lineItemTotalValue).toFixed(numberOfDecimal);
		jQuery('.productTotal', lineItemRow).text(lineItemTotal);
		return this;
	},
	/**
	 * Function which will get the value of line item total (qty*listprice)
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemTotal: function (lineItemRow) {
		return parseFloat(this.getLineItemTotalElement(lineItemRow).text());
	},
	/**
	 * Function which will get the line item total element
	 * @params : lineItemRow - row which represents the line item
	 * @return : jQuery element
	 */
	getLineItemTotalElement: function (lineItemRow) {
		return jQuery('.productTotal', lineItemRow);
	},
	/**
	 * Function which will set the discount total value for line item
	 * @params : lineItemRow - row which represents the line item
	 *			 discountValue - discount value
	 * @return : current instance;
	 */
	setDiscountTotal: function (lineItemRow, discountValue) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var discountTotal = parseFloat(discountValue).toFixed(numberOfDecimal);
		jQuery('.discountTotal', lineItemRow).text(discountTotal);
		return this;
	},
	/**
	 * Function which will get the value of total discount
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getDiscountTotal: function (lineItemRow) {
		return parseFloat(jQuery('.discountTotal', lineItemRow).text());
	},
	/**
	 * Function which will set the total after discount value
	 * @params : lineItemRow - row which represents the line item
	 *			 totalAfterDiscountValue - total after discount value
	 * @return : current instance;
	 */
	setTotalAfterDiscount: function (lineItemRow, totalAfterDiscountValue) {
		jQuery('.totalAfterDiscount', lineItemRow).text(totalAfterDiscountValue);
		return this;
	},
	/**
	 * Function which will get the value of total after discount
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getTotalAfterDiscount: function (lineItemRow) {
		return parseFloat(jQuery('.totalAfterDiscount', lineItemRow).text());
	},
	getPurchase: function (lineItemRow) {
		var qty = this.getQuantityValue(lineItemRow);
		return parseFloat(jQuery('.purchase', lineItemRow).val()) * qty;
	},
	/**
	 * Function which will set the tax total
	 * @params : lineItemRow - row which represents the line item
	 *			 taxTotal -  tax total
	 * @return : current instance;
	 */
	setLineItemTaxTotal: function (lineItemRow, taxTotal) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var productTaxTotal = parseFloat(taxTotal).toFixed(numberOfDecimal);
		jQuery('.productTaxTotal', lineItemRow).text(productTaxTotal);
		return this;
	},
	/**
	 * Function which will get the value of total tax
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemTaxTotal: function (lineItemRow) {
		return parseFloat(jQuery('.productTaxTotal', lineItemRow).text());
	},
	/**
	 * Function which will set the line item net price
	 * @params : lineItemRow - row which represents the line item
	 *			 lineItemNetPriceValue -  line item net price value
	 * @return : current instance;
	 */
	setLineItemNetPrice: function (lineItemRow, lineItemNetPriceValue) {
		jQuery('.netPrice', lineItemRow).text(lineItemNetPriceValue);
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var lineItemNetPrice = parseFloat(lineItemNetPriceValue).toFixed(numberOfDecimal);
		jQuery('.netPrice', lineItemRow).text(lineItemNetPrice);
		return this;
	},
	/**
	 * Function which will get the value of net price
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemNetPrice: function (lineItemRow) {
		return parseFloat(jQuery('.netPrice', lineItemRow).text());
	},
	/**
	 * Function which will get the value of net price include tax type
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemNetPriceIncludeTaxType: function (lineItemRow) {
		var netPrice = 0;
		if (this.isIndividualTaxMode()) {
			netPrice = this.getTotalAfterDiscount(lineItemRow);
		} else {
			netPrice = jQuery('.netPrice', lineItemRow).text();
		}
		return parseFloat(netPrice);
	},
	setNetTotal: function (netTotalValue) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var netTotal = parseFloat(netTotalValue).toFixed(numberOfDecimal);
		jQuery('#netTotal').text(netTotal);
		return this;
	},
	getNetTotalIncludeTaxType: function () {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var thisInstance = this
		var lineItemTable = this.getLineItemContentsContainer();
		var netTotalValue = 0;
		lineItemTable.find('tr.' + this.rowClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			netTotalValue += thisInstance.getLineItemNetPrice(lineItemRow);
		});
		var netTotal = parseFloat(netTotalValue).toFixed(numberOfDecimal);
		return netTotal;
	},
	getNetTotal: function () {
		return parseFloat(jQuery('#netTotal').text());
	},
	/**
	 * Function to set the final discount total
	 */
	setFinalDiscountTotal: function (finalDiscountValue) {
		jQuery('#discountTotal_final').text(finalDiscountValue);
		return this;
	},
	getFinalDiscountTotal: function () {
		return parseFloat(jQuery('#discountTotal_final').text());
	},
	setGroupTaxTotal: function (groupTaxTotalValue) {
		jQuery('#tax_final').text(groupTaxTotalValue);
	},
	getGroupTaxTotal: function () {
		return parseFloat(jQuery('#tax_final').text());
	},
	setGrandTotal: function (grandTotalValue) {
		jQuery('#grandTotal').text(grandTotalValue);
		return this;
	},
	setTotalMargin: function (marginTotalValue) {
		jQuery('#total_margin').text(marginTotalValue);
		return this;
	},
	getGrandTotal: function () {
		return parseFloat(jQuery('#grandTotal').text());
	},
	/**
	 * Function which will get the value of margin
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemMargin: function (lineItemRow) {
		return parseFloat(jQuery('.margin', lineItemRow).val());
	},
	loadRowSequenceNumber: function () {
		if (this.rowSequenceHolder == false) {
			this.rowSequenceHolder = jQuery('.' + this.rowClass, this.getLineItemContentsContainer()).length;
		}
		return this;
	},
	getNextLineItemRowNumber: function () {
		if (this.rowSequenceHolder == false) {
			this.loadRowSequenceNumber();
		}
		return ++this.rowSequenceHolder;
	},
	/**
	 * Function which will return the basic row which can be used to add new rows
	 * @return jQuery object which you can use to
	 */
	getBasicRow: function () {
		if (this.basicRow == false) {
			var lineItemTable = this.getLineItemContentsContainer();
			this.basicRow = jQuery('.lineItemCloneCopy', lineItemTable)
		}
		var newRow = this.basicRow.clone(true, true);
		var individualTax = this.isIndividualTaxMode();
		if (individualTax) {
			newRow.find('.individualTaxContainer').removeClass('hide');
		}
		return newRow.removeClass('hide lineItemCloneCopy noValidate');
	},
	registerAddingNewProductsAndServices: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		jQuery('#addProduct').on('click', function () {
			var newRow = thisInstance.getBasicRow().addClass(thisInstance.rowClass);
			jQuery('.lineItemPopup[data-module-name="Services"]', newRow).closest('span.input-group-addon').remove();
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();
			newRow = newRow.appendTo(lineItemTable);
			thisInstance.checkLineItemRow();
			newRow.find('input.rowNumber').val(sequenceNumber);
			thisInstance.updateLineItemsElementWithSequenceNumber(newRow, sequenceNumber);
			newRow.find('input.productName').addClass('autoComplete');
			thisInstance.registerLineItemAutoComplete(newRow);
		});
		jQuery('#addService').on('click', function () {
			var newRow = thisInstance.getBasicRow().addClass(thisInstance.rowClass);
			jQuery('.lineItemPopup[data-module-name="Products"]', newRow).closest('span.input-group-addon').remove();
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();
			newRow = newRow.appendTo(lineItemTable);
			thisInstance.checkLineItemRow();
			newRow.find('input.rowNumber').val(sequenceNumber);
			thisInstance.updateLineItemsElementWithSequenceNumber(newRow, sequenceNumber);
			newRow.find('input.productName').addClass('autoComplete');
			thisInstance.registerLineItemAutoComplete(newRow);
		});
	},
	getTaxDiv: function (taxObj, parentRow) {
		var rowNumber = jQuery('input.rowNumber', parentRow).val();
		var loopIterator = 1;
		var taxDiv = '<div class="taxUI validCheck hide" id="tax_div' + rowNumber + '">' +
				'<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table' + rowNumber + '">' +
				'<tr>' +
				'<th colspan="2" id="tax_div_title' + rowNumber + '" align="left" ><strong>' + app.vtranslate('JS_SET_TAX_FOR') + '</strong></th>' +
				'<th colspan="2"><button aria-hidden="true" data-dismiss="modal" class="close closeDiv" type="button">x</button>' +
				'</th>' +
				'</tr>';
		if (!jQuery.isEmptyObject(taxObj)) {
			for (var taxName in taxObj) {
				var taxInfo = taxObj[taxName]
				taxDiv += '<tr>' +
						'<td>' +
						'<div class="input-group input-group-sm"><span class="input-group-addon"><input type="radio" name="tax_option' + rowNumber + '" class="tax_option" value="' + taxName + '" ></span>' +
						'<input type="text" name="' + taxName + '_percentage' + rowNumber + '" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" id="' + taxName + '_percentage' + rowNumber + '" value="' + taxInfo.percentage + '" class="smallInputBox taxPercentage form-control input-sm"><span class="input-group-addon">%</span></div>' +
						'</td>' +
						'<td><div class="textOverflowEllipsis">' + taxInfo.label + '</div></td> ' +
						'<td>' +
						'<input type="text" name="popup_tax_row' + rowNumber + '" class="cursorPointer smallInputBox taxTotal form-control input-sm" value="0.0" readonly>' +
						'</td>' +
						'</tr>';
				loopIterator++;
			}
		} else {
			taxDiv += '<tr>' +
					'<td>' + app.vtranslate("JS_LBL_NO_TAXES") + '</td>' +
					'</tr>';
		}
		taxDiv += '</table><div class="modal-footer lineItemPopupModalFooter modal-footer-padding backgroundColor">' +
				'<div class=" pull-right cancelLinkContainer">' +
				'<a class="cancelLink btn btn-warning" type="reset" data-dismiss="modal">' + app.vtranslate("JS_LBL_CANCEL") + '</a>' +
				'</div>' +
				'<button class="btn btn-success taxSave" type="button" name="lineItemActionSave"><strong>' + app.vtranslate("JS_LBL_SAVE") + '</strong></button>' +
				'</div></div>';
		return jQuery(taxDiv);
	},
	loadSubProducts: function (lineItemRow) {
		var recordId = jQuery('input.selectedModuleId', lineItemRow).val();
		var subProrductParams = {
			'module': "Products",
			'action': "SubProducts",
			'record': recordId
		}
		var progressInstace = jQuery.progressIndicator();
		AppConnector.request(subProrductParams).then(
				function (data) {

					var responseData = data.result;
					var subProductsContainer = jQuery('.subProductsContainer', lineItemRow);
					var subProductIdHolder = jQuery('.subProductIds', lineItemRow);

					var subProductHtml = '';
					for (var id in responseData) {
						subProductHtml += '<em>-' + responseData[id] + '</em><br>';
					}
					subProductIdHolder.val(Object.keys(responseData).join(':'));
					subProductsContainer.html(subProductHtml);
					progressInstace.hide();
				},
				function (error, err) {
					//TODO : handle the error case
				}
		);
	},
	mapResultsToFields: function (referenceModule, element, responseData) {
		var parentRow = jQuery(element).closest('tr.' + this.rowClass);
		var lineItemNameElment = jQuery('input.productName', parentRow);

		for (var id in responseData) {
			var recordId = id;
			var recordData = responseData[id];
			var selectedName = recordData.name;
			var unitPrice = recordData.listprice;
			var usageUnit = recordData.usageunit;
			var listPriceValues = recordData.listpricevalues;
			var taxes = recordData.taxes;
			if (referenceModule == 'Products') {
				parentRow.data('quantity-in-stock', recordData.quantityInStock);
			}
			var description = recordData.description;
			jQuery('input.selectedModuleId', parentRow).val(recordId);
			jQuery('input.lineItemType', parentRow).val(referenceModule);
			lineItemNameElment.val(selectedName);
			lineItemNameElment.attr('disabled', 'disabled');
			jQuery('input.listPrice', parentRow).val(unitPrice);
			jQuery('span.usageUnit', parentRow).text(usageUnit);
			var currencyId = jQuery("#currency_id").val();
			var listPriceValuesJson = JSON.stringify(listPriceValues);
			if (typeof listPriceValues[currencyId] != 'undefined') {
				this.setListPriceValue(parentRow, listPriceValues[currencyId]);
				this.lineItemRowCalculations(parentRow);
			}
			jQuery('input.listPrice', parentRow).attr('list-info', listPriceValuesJson);
			jQuery('textarea.lineItemCommentBox', parentRow).val(description);
			var taxUI = this.getTaxDiv(taxes, parentRow);
			jQuery('.taxDivContainer', parentRow).html(taxUI);
			if (this.isIndividualTaxMode()) {
				parentRow.find('.productTaxTotal').removeClass('hide')
			} else {
				parentRow.find('.productTaxTotal').addClass('hide')
			}
		}
		if (referenceModule == 'Products') {
			this.loadSubProducts(parentRow);
		}

		jQuery('.qty', parentRow).trigger('focusout');
	},
	showPopup: function (params) {
		var aDeferred = jQuery.Deferred();
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(params, function (data) {
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	/*
	 * Function which is reposible to handle the line item popups
	 * @params : popupImageElement - popup image element
	 */
	lineItemPopupEventHandler: function (popupImageElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var referenceModule = popupImageElement.data('moduleName');
		var moduleName = app.getModuleName();

		var params = {};
		params.view = popupImageElement.data('popup');
		params.module = moduleName;

		// narrow popup products list to those related to chosen potential
		if (moduleName == 'Quotes' || moduleName == 'Invoice') {

			var potential_id = jQuery('input[name="potential_id"]').val();
			if (typeof potential_id == 'undefined') { // if potential field has a bit diffrent name
				potential_id = jQuery('input[name="potentialid"]').val();
			}
			if (potential_id) {
				params.potentialid = potential_id;
			}
		}
		params.currency_id = jQuery('#currency_id option:selected').val();

		this.showPopup(params).then(function (data) {
			var responseData = JSON.parse(data);
			var len = Object.keys(responseData).length;
			if (len > 1) {
				for (var i = 0; i < len; i++) {
					if (i == 0) {
						thisInstance.mapResultsToFields(referenceModule, popupImageElement, responseData[i]);
					} else if (i >= 1 && (referenceModule == 'Products' || referenceModule == 'Services')) {
						if (referenceModule == 'Products') {
							var row = jQuery('#addProduct').trigger('click');
						} else if (referenceModule == 'Services') {
							var row1 = jQuery('#addService').trigger('click');
						}
						//TODO : CLEAN :  we might synchronus invocation since following elements needs to executed once new row is created
						var newRow = jQuery('#lineItemTab > tbody > tr:last');
						var targetElem = jQuery('.lineItemPopup', newRow);
						thisInstance.mapResultsToFields(referenceModule, targetElem, responseData[i]);
						aDeferred.resolve();
					}
				}
			} else {
				thisInstance.mapResultsToFields(referenceModule, popupImageElement, responseData);
				aDeferred.resolve();
			}
		})
		return aDeferred.promise();
	},
	/**
	 * Function which will be used to handle price book popup
	 * @params :  popupImageElement - popup image element
	 */
	pricebooksPopupHandler: function (popupImageElement) {
		var thisInstance = this;
		var lineItemRow = popupImageElement.closest('tr.' + this.rowClass);
		var lineItemProductOrServiceElement = lineItemRow.find('input.productName').closest('td');
		var params = {};
		params.module = 'PriceBooks';
		params.src_module = jQuery('img.lineItemPopup', lineItemProductOrServiceElement).data('moduleName');
		params.src_field = jQuery('img.lineItemPopup', lineItemProductOrServiceElement).data('fieldName');
		params.src_record = jQuery('input.selectedModuleId', lineItemProductOrServiceElement).val();
		params.get_url = 'getProductListPriceURL';
		params.currency_id = jQuery('#currency_id option:selected').val();
		this.showPopup(params).then(function (data) {
			var responseData = JSON.parse(data);
			for (var id in responseData) {
				thisInstance.setListPriceValue(lineItemRow, responseData[id]);
			}
			thisInstance.quantityChangeActions(lineItemRow);
		});
	},
	/**
	 * Function which will calculate line item total excluding discount and tax
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateLineItemTotal: function (lineItemRow) {
		var quantity = this.getQuantityValue(lineItemRow);
		var listPrice = this.getListPriceValue(lineItemRow);
		var lineItemTotal = parseFloat(quantity) * parseFloat(listPrice);
		this.setLineItemTotal(lineItemRow, lineItemTotal);
	},
	/**
	 * Function which will calculate discount for the line item
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateDiscountForLineItem: function (lineItemRow) {
		var discountContianer = lineItemRow.find('div.discountUI');
		var element = discountContianer.find('input.discounts').filter(':checked');
		var discountType = element.data('discountType');
		var discountRow = element.closest('tr');

		jQuery('input.discount_type', discountContianer).val(discountType);
		var rowPercentageField = jQuery('input.discount_percentage', discountContianer).closest('div.input-group');
		var rowAmountField = jQuery('input.discount_amount', discountContianer);

		//intially making percentage and amount discount fields as hidden
		rowPercentageField.addClass('hide');
		rowAmountField.addClass('hide');

		var discountValue = discountRow.find('.discountVal').val();
		if (discountValue == "") {
			discountValue = 0;
		}
		if (isNaN(discountValue) || discountValue < 0) {
			discountValue = 0;
		}
		if (discountType == Inventory_Edit_Js.percentageDiscountType) {
			rowPercentageField.removeClass('hide').focus();
			//since it is percentage
			var productTotal = this.getLineItemTotal(lineItemRow);
			discountValue = (productTotal * discountValue) / 100;
		} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
			rowAmountField.removeClass('hide').focus();
		}
		this.setDiscountTotal(lineItemRow, discountValue)
				.calculateTotalAfterDiscount(lineItemRow);
	},
	/**
	 * Function which will calculate line item total after discount
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateTotalAfterDiscount: function (lineItemRow) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var productTotal = this.getLineItemTotal(lineItemRow);
		var discountTotal = this.getDiscountTotal(lineItemRow);
		var totalAfterDiscount = productTotal - discountTotal;
		totalAfterDiscount = totalAfterDiscount.toFixed(numberOfDecimal);
		this.setTotalAfterDiscount(lineItemRow, totalAfterDiscount);
	},
	/**
	 * Function which will calculate tax for the line item total after discount
	 */
	calculateTaxForLineItem: function (lineItemRow) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var taxPercentages = jQuery('.taxPercentage', lineItemRow);
		//intially make the tax as zero
		var taxTotal = 0;
		jQuery.each(taxPercentages, function (index, domElement) {
			var taxPercentage = jQuery(domElement);
			var individualTaxRow = taxPercentage.closest('tr');
			if (individualTaxRow.find('.tax_option').is(":checked")) {
				var individualTaxPercentage = taxPercentage.val();
				if (individualTaxPercentage == "") {
					individualTaxPercentage = "0";
				}
				if (isNaN(individualTaxPercentage)) {
					var individualTaxTotal = "0";
				} else {
					var individualTaxPercentage = parseFloat(individualTaxPercentage);
					var individualTaxTotal = Math.abs(individualTaxPercentage * totalAfterDiscount) / 100;
					individualTaxTotal = individualTaxTotal.toFixed(numberOfDecimal);
				}
				jQuery('.taxTotal', individualTaxRow).val(individualTaxTotal);
				taxTotal += parseFloat(individualTaxTotal);
			}
		});
		taxTotal = parseFloat(taxTotal.toFixed(numberOfDecimal));
		this.setLineItemTaxTotal(lineItemRow, taxTotal);
	},
	/**
	 * Function which will calculate net price for the line item
	 */
	calculateLineItemNetPrice: function (lineItemRow) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var netPrice = parseFloat(totalAfterDiscount);
		if (this.isIndividualTaxMode()) {
			var productTaxTotal = this.getLineItemTaxTotal(lineItemRow);
			netPrice += parseFloat(productTaxTotal)
		}
		netPrice = netPrice.toFixed(numberOfDecimal);
		this.setLineItemNetPrice(lineItemRow, netPrice);
	},
	/**
	 * Function which will caliculate the total net price for all the line items
	 */
	calculateNetTotal: function () {
		var thisInstance = this
		var lineItemTable = this.getLineItemContentsContainer();
		var netTotalValue = 0;
		lineItemTable.find('tr.' + this.rowClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			netTotalValue += thisInstance.getLineItemNetPriceIncludeTaxType(lineItemRow);
		});
		this.setNetTotal(netTotalValue);
	},
	calculateFinalDiscount: function () {
		var thisInstance = this;
		var discountContainer = jQuery('#finalDiscountUI');
		var element = discountContainer.find('input.finalDiscounts').filter(':checked');
		var discountType = element.data('discountType');
		var discountRow = element.closest('tr');
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());

		jQuery('#discount_type_final').val(discountType);
		var rowPercentageField = discountContainer.find('input.discount_percentage_final').closest('div.input-group');
		var rowAmountField = discountContainer.find('input.discount_amount_final');

		//intially making percentage and amount discount fields as hidden
		rowPercentageField.addClass('hide');
		rowAmountField.addClass('hide');

		var discountValue = discountRow.find('.discountVal').val();
		if (discountValue == "") {
			discountValue = 0;
		}
		if (isNaN(discountValue) || discountValue < 0) {
			discountValue = 0;
		}
		if (discountType == Inventory_Edit_Js.percentageDiscountType) {
			rowPercentageField.removeClass('hide').focus();
			//since it is percentage
			var productTotal = this.getNetTotalIncludeTaxType();
			discountValue = (productTotal * discountValue) / 100;
		} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
			if (thisInstance.prevSelectedCurrencyConversionRate) {
				var conversionRate = jQuery('#conversion_rate').val();
				conversionRate = conversionRate / thisInstance.prevSelectedCurrencyConversionRate;
				discountValue = discountValue * conversionRate;
				discountRow.find('.discountVal').val(discountValue);
			}
			rowAmountField.removeClass('hide').focus();
		}
		discountValue = parseFloat(discountValue).toFixed(numberOfDecimal);
		this.setFinalDiscountTotal(discountValue);
		this.calculatePreTaxTotal();
	},
	calculateGroupTax: function () {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var netTotal = this.getNetTotalIncludeTaxType();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var amount = netTotal - finalDiscountValue;
		amount = parseFloat(amount).toFixed(numberOfDecimal);
		var groupTaxContainer = jQuery('#group_tax_div');
		var groupTaxTotal = 0;
		groupTaxContainer.find('.groupTaxPercentage').each(function (index, domElement) {
			var groupTaxPercentageElement = jQuery(domElement);
			var groupTaxRow = groupTaxPercentageElement.closest('tr');
			if (groupTaxRow.find('.group_tax_option').is(":checked")) {
				if (isNaN(groupTaxPercentageElement.val())) {
					var groupTaxValue = "0";
				} else {
					var groupTaxValue = Math.abs(amount * groupTaxPercentageElement.val()) / 100;
				}
				groupTaxValue = parseFloat(groupTaxValue).toFixed(numberOfDecimal);
				groupTaxRow.find('.groupTaxTotal').val(groupTaxValue);
				groupTaxTotal += parseFloat(groupTaxValue);
			}
		});
		this.setGroupTaxTotal(groupTaxTotal);
	},
	calculateGrandTotal: function () {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var netTotal = this.getNetTotalIncludeTaxType();
		var discountTotal = this.getFinalDiscountTotal();
		var grandTotal = parseFloat(netTotal) - parseFloat(discountTotal);

		if (this.isGroupTaxMode()) {
			grandTotal += this.getGroupTaxTotal();
		}

		grandTotal = grandTotal.toFixed(numberOfDecimal);
		this.setGrandTotal(grandTotal);
	},
	calculateTotalMargin: function () {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var thisInstance = this
		var lineItemTable = this.getLineItemContentsContainer();
		var totalMarginValue = 0;
		lineItemTable.find('tr.' + this.rowClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			totalMarginValue += thisInstance.getLineItemMargin(lineItemRow);
		});

		var totalMargin = parseFloat(totalMarginValue).toFixed(numberOfDecimal);
		this.setTotalMargin(totalMargin);
	},
	registerFinalDiscountShowEvent: function () {
		var thisInstance = this;
		jQuery('#finalDiscount').on('click', function (e) {
			var finalDiscountUI = jQuery('#finalDiscountUI');
			thisInstance.hideLineItemPopup();
			finalDiscountUI.removeClass('hide');
		});
	},
	registerFinalDiscountChangeEvent: function () {
		var lineItemResultTab = this.getLineItemResultContainer();
		var thisInstance = this;

		lineItemResultTab.on('change', '.finalDiscounts', function (e) {
			thisInstance.finalDiscountChangeActions();
		});
	},
	registerFinalDiscountValueChangeEvent: function () {
		var thisInstance = this;
		jQuery('.finalDiscountSave').on('click', function (e) {
			thisInstance.finalDiscountChangeActions();
		});
	},
	registerLineItemActionSaveEvent: function () {
		var editForm = this.getForm();
		var thisInstance = this;
		editForm.on('click', 'button[name="lineItemActionSave"]', function () {
			var match = true;
			var formError = jQuery('#EditView').data('jqv').InvalidFields;
			var closestDiv = jQuery('button[name="lineItemActionSave"]').closest('.validCheck').find('input[data-validation-engine]').not('.hide');
			jQuery(closestDiv).each(function (key, value) {
				if (jQuery.inArray(value, formError) != -1) {
					match = false;
				}
			});
			if (!match) {
				editForm.removeData('submit');
				return false;
			} else {
				jQuery('.closeDiv').trigger('click');
			}
			thisInstance.calculateGroupTax();
			thisInstance.calculateGrandTotal();
		});
	},
	registerGroupTaxShowEvent: function () {
		var thisInstance = this;
		jQuery('#finalTax').on('click', function (e) {
			var groupTaxContainer = jQuery('#group_tax_row');
			thisInstance.hideLineItemPopup();
			groupTaxContainer.find('.finalTaxUI').removeClass('hide');
		});
	},
	registerGroupTaxChangeEvent: function () {
		var thisInstance = this;
		var groupTaxContainer = jQuery('#group_tax_row');

		groupTaxContainer.on('focusout', '.groupTaxPercentage', function (e) {
			thisInstance.calculateGroupTax();
			thisInstance.calculateGrandTotal();
		});
	},
	registerRecalculateMargin: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('focusout', '.listPrice', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.' + thisInstance.rowClass);
			thisInstance.recalculateMargin(lineItemRow);
			thisInstance.recalculateAllMargin();
		});
		lineItemTable.on('focusout', '.purchase', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.' + thisInstance.rowClass);
			thisInstance.recalculateMargin(lineItemRow);
			thisInstance.recalculateAllMargin();
		});
		lineItemTable.on('focusout', '.listPrice', function (e) {
			thisInstance.recalculateAllMargin();
		});
		lineItemTable.on('focusout', '.qty', function (e) {
			thisInstance.recalculateAllMargin();
		});
	},
	recalculateMargin: function (lineItemRow) {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var qty = this.getQuantityValue(lineItemRow);
		var purchase = this.getPurchase(lineItemRow);
		var margin = totalAfterDiscount - purchase;
		margin = margin.toFixed(numberOfDecimal);
		var marginp = '0';
		lineItemRow.find('.margin').val(margin);
		if (purchase != 0) {
			marginp = (margin / purchase) * 100;
			marginp = marginp.toFixed(numberOfDecimal)
		}
		lineItemRow.find('.marginp').val(marginp);
	},
	recalculateAllMargin: function () {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var thisInstance = this
		var lineItemTable = this.getLineItemContentsContainer();
		var totalAfterDiscount = 0;
		var purchase = 0;
		var margin = 0;
		var marginp = 0;
		lineItemTable.find('tr.' + this.rowClass).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			var qty = thisInstance.getQuantityValue(lineItemRow);
			totalAfterDiscount += thisInstance.getTotalAfterDiscount(lineItemRow)
			purchase += thisInstance.getPurchase(lineItemRow);
		});
		margin = totalAfterDiscount - purchase;
		if (purchase != 0) {
			marginp = (margin / purchase) * 100;
		}
		purchase = purchase.toFixed(numberOfDecimal);
		margin = margin.toFixed(numberOfDecimal);
		marginp = marginp.toFixed(numberOfDecimal);
		jQuery('.total_purchase').text(purchase);
		jQuery('.total_margin').text(margin);
		jQuery('.total_marginp').text(marginp);
	},
	registerLineItemsPopUpCancelClickEvent: function () {
		var editForm = this.getForm();
		editForm.on('click', '.cancelLink', function () {
			jQuery('.closeDiv').trigger('click')
		})
	},
	lineItemResultActions: function () {
		var thisInstance = this;
		var lineItemResultTab = this.getLineItemResultContainer();

		this.registerFinalDiscountShowEvent();
		this.registerFinalDiscountValueChangeEvent();
		this.registerFinalDiscountChangeEvent();

		this.registerLineItemActionSaveEvent();
		this.registerLineItemsPopUpCancelClickEvent();

		this.registerGroupTaxShowEvent();
		this.registerGroupTaxChangeEvent();

		lineItemResultTab.on('click', '.closeDiv', function (e) {
			jQuery(e.target).closest('div').addClass('hide');
		});
	},
	lineItemRowCalculations: function (lineItemRow) {
		this.calculateLineItemTotal(lineItemRow);
		this.calculateDiscountForLineItem(lineItemRow);
		this.calculateTaxForLineItem(lineItemRow);
		this.calculateLineItemNetPrice(lineItemRow);
	},
	lineItemToTalResultCalculations: function () {
		this.calculateNetTotal();
		this.calculateFinalDiscount();
		if (this.isGroupTaxMode()) {
			this.calculateGroupTax();
		}
		this.calculateGrandTotal();
		this.calculateTotalMargin();
	},
	/**
	 * Function which will handle the actions that need to be preformed once the qty is changed like below
	 *  - calculate line item total -> discount and tax -> net price of line item -> grand total
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	quantityChangeActions: function (lineItemRow) {
		this.lineItemRowCalculations(lineItemRow);
		this.lineItemToTalResultCalculations();
	},
	lineItemDiscountChangeActions: function (lineItemRow) {
		this.calculateDiscountForLineItem(lineItemRow);
		this.calculateTaxForLineItem(lineItemRow);
		this.calculateLineItemNetPrice(lineItemRow);
		this.lineItemToTalResultCalculations();
		this.recalculateMargin(lineItemRow);
		this.recalculateAllMargin();
	},
	/**
	 * Function which will handle the actions that need to be performed once the tax percentage is change for a line item
	 * @params : lineItemRow - element which will represent lineItemRow
	 */

	taxPercentageChangeActions: function (lineItemRow) {
		this.calculateLineItemNetPrice(lineItemRow);
		this.calculateNetTotal();
		this.calculateFinalDiscount();
		if (this.isGroupTaxMode()) {
			this.calculateGroupTax();
		}
		this.calculateGrandTotal();
	},
	lineItemDeleteActions: function () {
		this.lineItemToTalResultCalculations();
	},
	finalDiscountChangeActions: function () {
		this.calculateFinalDiscount();
		if (this.isGroupTaxMode()) {
			this.calculateGroupTax();
		}
		this.calculateGrandTotal();
	},
	/**
	 * Function which will register change event for discounts radio buttons
	 */
	registerDisountChangeEvent: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('change', '.discounts', function (e) {
			var lineItemRow = jQuery(e.currentTarget).closest('tr.' + thisInstance.rowClass);
			thisInstance.lineItemDiscountChangeActions(lineItemRow);
		});
	},
	/**
	 * Function which will register event for focusout of discount input fields like percentage and amount
	 */
	registerDisountValueChange: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('click', '.discountSave', function (e) {
			var element = jQuery(e.currentTarget);
			//if the element is not hidden then we need to handle the focus out
			if (!app.isHidden(element)) {
				var lineItemRow = jQuery(e.currentTarget).closest('tr.' + thisInstance.rowClass);
				thisInstance.lineItemDiscountChangeActions(lineItemRow);
			}

		});
	},
	hideLineItemPopup: function () {
		var editForm = this.getForm();
		var popUpElementContainer = jQuery('.popupTable', editForm).closest('div');
		if (popUpElementContainer.length > 0) {
			popUpElementContainer.addClass('hide');
		}
	},
	registerLineItemDiscountShowEvent: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('click', '.individualDiscount', function (e) {
			var element = jQuery(e.currentTarget);
			var response = thisInstance.isProductSelected(element);
			if (response == true) {
				return;
			}
			var parentElem = jQuery(e.currentTarget).closest('td');
			thisInstance.hideLineItemPopup();
			parentElem.find('div.discountUI').removeClass('hide');
		});
	},
	/**
	 * Function which will regiser events for product and service popup
	 */
	registerProductAndServicePopup: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('click', 'img.lineItemPopup', function (e) {
			var element = jQuery(e.currentTarget);
			thisInstance.lineItemPopupEventHandler(element).then(function (data) {
				var parent = element.closest('tr');
				var deletedItemInfo = parent.find('.deletedItem');
				if (deletedItemInfo.length > 0) {
					deletedItemInfo.remove();
				}
			})
		});
	},
	/**
	 * Function which will regisrer price book popup
	 */
	registerPriceBookPopUp: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('click', '.priceBookPopup', function (e) {
			var element = jQuery(e.currentTarget);
			var response = thisInstance.isProductSelected(element);
			if (response == true) {
				return;
			}
			thisInstance.pricebooksPopupHandler(element);
		});
	},
	/*
	 * Function which will register event for quantity change (focusout event)
	 */
	registerQuantityChangeEventHandler: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('focusout', '.qty', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.' + thisInstance.rowClass);
			var quantityInStock = lineItemRow.data('quantityInStock');
			if (typeof quantityInStock != 'undefined') {
				if (parseFloat(element.val()) > parseFloat(quantityInStock)) {
					lineItemRow.find('.stockAlert').removeClass('hide').find('.maxQuantity').text(quantityInStock);
				} else {
					lineItemRow.find('.stockAlert').addClass('hide');
				}
			}
			thisInstance.quantityChangeActions(lineItemRow);
		});
	},
	/**
	 * Function which will register event for list price event change
	 */
	registerListPriceChangeEvent: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('focusout', 'input.listPrice', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = thisInstance.getClosestLineItemRow(element);
			thisInstance.quantityChangeActions(lineItemRow);
		});
	},
	registerTaxPercentageChange: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('focusout', '.taxPercentage', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = thisInstance.getClosestLineItemRow(element);
			thisInstance.calculateTaxForLineItem(lineItemRow);
		});
		lineItemTable.on('change', '.tax_option', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = thisInstance.getClosestLineItemRow(element);
			thisInstance.calculateTaxForLineItem(lineItemRow);
		});
		lineItemTable.on('click', '.taxSave', function (e) {
			var element = jQuery(e.currentTarget);
			var lineItemRow = thisInstance.getClosestLineItemRow(element);
			thisInstance.taxPercentageChangeActions(lineItemRow);
		});
	},
	isProductSelected: function (element) {
		var parentRow = element.closest('tr');
		var productField = parentRow.find('.productName');
		var response = productField.validationEngine('validate');
		return response;
	},
	registerLineItemTaxShowEvent: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('click', '.individualTax', function (e) {
			var element = jQuery(e.currentTarget);
			var response = thisInstance.isProductSelected(element);
			if (response == true) {
				return;
			}
			var parentElem = jQuery(e.currentTarget).closest('td');
			thisInstance.hideLineItemPopup()
			parentElem.find('.taxUI').removeClass('hide');
		});
	},
	registerDeleteLineItemEvent: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('click', '.deleteRow', function (e) {
			var element = jQuery(e.currentTarget);
			//removing the row
			element.closest('tr.' + thisInstance.rowClass).remove();
			thisInstance.checkLineItemRow();
			thisInstance.lineItemDeleteActions();
		});
	},
	registerTaxTypeChange: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		this.getTaxTypeSelectElement().on('change', function (e) {
			if (thisInstance.isIndividualTaxMode()) {
				jQuery('#group_tax_row').addClass('hide');
				lineItemTable.find('tr.' + thisInstance.rowClass).each(function (index, domElement) {
					var lineItemRow = jQuery(domElement);
					lineItemRow.find('.individualTaxContainer,.productTaxTotal').removeClass('hide');
					thisInstance.lineItemRowCalculations(lineItemRow);
				});
			} else {
				jQuery('#group_tax_row').removeClass('hide');
				lineItemTable.find('tr.' + thisInstance.rowClass).each(function (index, domElement) {
					var lineItemRow = jQuery(domElement);
					lineItemRow.find('.individualTaxContainer,.productTaxTotal').addClass('hide');
					thisInstance.calculateLineItemNetPrice(lineItemRow);
				});
			}
			thisInstance.lineItemToTalResultCalculations();
		});
	},
	registerCurrencyChangeEvent: function () {
		var thisInstance = this;
		jQuery('#currency_id').change(function (e) {
			var element = jQuery(e.currentTarget);
			var currencyId = element.val();
			var conversionRateElem = jQuery('#conversion_rate');
			var prevSelectedCurrencyConversionRate = conversionRateElem.val();
			thisInstance.prevSelectedCurrencyConversionRate = prevSelectedCurrencyConversionRate;
			var optionsSelected = element.find('option:selected');
			var conversionRate = optionsSelected.data('conversionRate');
			conversionRateElem.val(conversionRate);
			conversionRate = parseFloat(conversionRate) / parseFloat(prevSelectedCurrencyConversionRate);
			thisInstance.LineItemDirectDiscountCal(conversionRate);
			var lineItemTable = thisInstance.getLineItemContentsContainer();
			lineItemTable.find('tr.' + thisInstance.rowClass).each(function (index, domElement) {
				var lineItemRow = jQuery(domElement);
				var listPriceElement = jQuery(lineItemRow).find('[name^=listPrice]');
				var listPriceValues = JSON.parse(listPriceElement.attr('list-info'));
				if (typeof listPriceValues[currencyId] != 'undefined') {
					thisInstance.setListPriceValue(lineItemRow, listPriceValues[currencyId]);
					thisInstance.lineItemRowCalculations(lineItemRow);
				} else {
					var listPriceVal = thisInstance.getListPriceValue(lineItemRow);
					var convertedListPrice = listPriceVal * conversionRate;
					thisInstance.setListPriceValue(lineItemRow, convertedListPrice);
					thisInstance.lineItemRowCalculations(lineItemRow);
				}

			});
			thisInstance.lineItemToTalResultCalculations();
			jQuery('#prev_selected_currency_id').val(optionsSelected.val())
		});
	},
	LineItemDirectDiscountCal: function (conversionRate) {
		//LineItems Discount Calculations for direct Price reduction
		var thisInstance = this;

		var lineItemRows = jQuery('.lineItemRow');
		jQuery(lineItemRows).each(function (index) {
			var lineItemRow = jQuery(lineItemRows[index]);
			var discountContianer = lineItemRow.find('div.discountUI');
			var element = discountContianer.find('input.discounts').filter(':checked');
			var discountRow = element.closest('tr');
			var discountType = element.data('discountType');
			var discountValue = discountRow.find('.discountVal').val();
			if ((discountType == Inventory_Edit_Js.directAmountDiscountType)) {
				var newdiscountValue = conversionRate * discountValue;
				discountRow.find('.discountVal').val(newdiscountValue);
				jQuery(element).closest('tr').find('.discountVal').val(newdiscountValue);
				thisInstance.setDiscountTotal(lineItemRow, newdiscountValue);
			}
		});
	},
	lineItemActions: function () {
		var lineItemTable = this.getLineItemContentsContainer();

		this.registerDisountChangeEvent();
		this.registerDisountValueChange();
		this.registerLineItemDiscountShowEvent();

		this.registerLineItemAutoComplete();
		this.registerClearLineItemSelection();

		this.registerProductAndServicePopup();
		this.registerPriceBookPopUp();

		this.registerQuantityChangeEventHandler();
		this.registerListPriceChangeEvent();

		this.registerTaxPercentageChange();
		this.registerLineItemTaxShowEvent();

		this.registerDeleteLineItemEvent();
		this.registerTaxTypeChange();
		this.registerCurrencyChangeEvent();

		this.registerRecalculateMargin();

		lineItemTable.on('click', '.closeDiv', function (e) {
			jQuery(e.currentTarget).closest('div').addClass('hide');
		});

		lineItemTable.on('click', '.clearComment', function (e) {
			var elem = jQuery(e.currentTarget);
			var parentElem = elem.closest('div');
			var comment = jQuery('.lineItemCommentBox', parentElem).val('');
		});

	},
	/***
	 * Function which will update the line item row elements with the sequence number
	 * @params : lineItemRow - tr line item row for which the sequence need to be updated
	 *			 currentSequenceNUmber - existing sequence number that the elments is having
	 *			 expectedSequenceNumber - sequence number to which it has to update
	 *
	 * @return : row element after changes
	 */
	updateLineItemsElementWithSequenceNumber: function (lineItemRow, expectedSequenceNumber, currentSequenceNumber) {
		if (typeof currentSequenceNumber == 'undefined') {
			//by default there will zero current sequence number
			currentSequenceNumber = 0;
		}

		var idFields = new Array('productName', 'subproduct_ids', 'hdnProductId',
				'comment', 'qty', 'listPrice', 'discount_type', 'discount_percentage',
				'discount_amount', 'lineItemType', 'searchIcon', 'netPrice', 'subprod_names',
				'productTotal', 'discountTotal', 'totalAfterDiscount', 'taxTotal', 'purchase');

		var nameFields = new Array('discount');
		var classFields = new Array('taxPercentage');
		//To handle variable tax ids
		for (var classIndex in classFields) {
			var className = classFields[classIndex];
			jQuery('.' + className, lineItemRow).each(function (index, domElement) {
				var idString = domElement.id
				//remove last character which will be the row number
				idFields.push(idString.slice(0, (idString.length - 1)));
			});
		}

		var expectedRowId = 'row' + expectedSequenceNumber;
		for (var idIndex in idFields) {
			var elementId = idFields[idIndex];
			var actualElementId = elementId + currentSequenceNumber;
			var expectedElementId = elementId + expectedSequenceNumber;
			lineItemRow.find('#' + actualElementId).attr('id', expectedElementId)
					.filter('[name="' + actualElementId + '"]').attr('name', expectedElementId);
		}

		for (var nameIndex in nameFields) {
			var elementName = nameFields[nameIndex];
			var actualElementName = elementName + currentSequenceNumber;
			var expectedElementName = elementName + expectedSequenceNumber;
			lineItemRow.find('[name="' + actualElementName + '"]').attr('name', expectedElementName);
		}


		return lineItemRow.attr('id', expectedRowId);
	},
	updateLineItemElementByOrder: function () {
		var lineItemContentsContainer = this.getLineItemContentsContainer();
		var thisInstance = this;
		jQuery('tr.' + this.rowClass, lineItemContentsContainer).each(function (index, domElement) {
			var lineItemRow = jQuery(domElement);
			var expectedRowIndex = (index + 1);
			var expectedRowId = 'row' + expectedRowIndex;
			var actualRowId = lineItemRow.attr('id');
			if (expectedRowId != actualRowId) {
				var actualIdComponents = actualRowId.split('row');
				thisInstance.updateLineItemsElementWithSequenceNumber(lineItemRow, expectedRowIndex, actualIdComponents[1]);
			}
		});
	},
	saveProductCount: function () {
		jQuery('#totalProductCount').val(jQuery('tr.' + this.rowClass, this.getLineItemContentsContainer()).length);
	},
	saveSubTotalValue: function () {
		jQuery('#subtotal').val(this.getNetTotal());
	},
	saveTotalValue: function () {
		jQuery('#total').val(this.getGrandTotal());
	},
	makeLineItemsSortable: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.sortable({
			'containment': lineItemTable,
			'handle': '.dragHandle',
			'items': 'tr.' + this.rowClass,
			'revert': true,
			'tolerance': 'pointer',
			'helper': function (e, ui) {
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function (index, element) {
					element = jQuery(element);
					element.width(element.width());
				})
				return ui;
			},
			start: function (event, ui) {
				var textareaId = ui.item.find('textarea').attr('id');
				if (typeof textareaId != 'undefined') {
					var editorInstance = CKEDITOR.instances[textareaId];
					editorInstance.destroy();
				}
			},
			stop: function (event, ui) {
				var customConfig = {};
				var textarea = ui.item.find('textarea');
				if (typeof textarea.attr('id') != 'undefined') {
					thisInstance.loadCkEditorElement(textarea);
				}
			}
		}).mousedown(function (event) {
			//TODO : work around for issue of mouse down even hijack in sortable plugin
			thisInstance.getClosestLineItemRow(jQuery(event.target)).find('input:focus').trigger('focusout');
		});
	},
	registerSubmitEvent: function () {
		var thisInstance = this;
		var editViewForm = this.getForm();
		this._super();
		editViewForm.submit(function (e) {
			thisInstance.updateLineItemElementByOrder();
			var lineItemTable = thisInstance.getLineItemContentsContainer();
			jQuery('.discountSave', lineItemTable).trigger('click');
			thisInstance.lineItemToTalResultCalculations();
			thisInstance.saveProductCount();
			thisInstance.saveSubTotalValue();
			thisInstance.saveTotalValue();
			thisInstance.savePreTaxTotalValue();
		})
	},
	registerLineItemAutoComplete: function (container) {
		var thisInstance = this;
		if (typeof container == 'undefined') {
			container = thisInstance.getLineItemContentsContainer();
		}
		container.find('input.autoComplete').autocomplete({
			'delay': '600',
			'minLength': '3',
			'source': function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = tdElement.find('.lineItemPopup').data('moduleName');
				params.search_module = searchModule
				params.search_value = searchValue;
				thisInstance.searchModuleNames(params).then(function (data) {
					var reponseDataList = new Array();
					var serverDataFormat = data.result
					if (serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type': 'no results'
						});
					}
					for (var id in serverDataFormat) {
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select': function (event, ui) {
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
					return false;
				}

				var element = jQuery(this);
				element.attr('disabled', 'disabled');
				var tdElement = element.closest('td');
				var selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
				var popupElement = tdElement.find('.lineItemPopup');
				var dataUrl = "index.php?module=Inventory&action=GetTaxes&record=" + selectedItemData.id + "&currency_id=" + jQuery('#currency_id option:selected').val();
				AppConnector.request(dataUrl).then(
						function (data) {
							for (var id in data) {
								if (typeof data[id] == "object") {
									var recordData = data[id];
									thisInstance.mapResultsToFields(selectedModule, popupElement, recordData);
								}
							}
						},
						function (error, err) {

						}
				);
			},
			'change': function (event, ui) {
				var element = jQuery(this);
				//if you dont have disabled attribute means the user didnt select the item
				if (element.attr('disabled') == undefined) {

					element.closest('td').find('.clearLineItem').trigger('click');

				}

			}
		});
	},
	registerClearLineItemSelection: function () {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('click', '.clearLineItem', function (e) {
			var elem = jQuery(e.currentTarget);
			var parentElem = elem.closest('td');
			thisInstance.clearLineItemDetails(parentElem);
			parentElem.find('input.productName').removeAttr('disabled').val('');
			e.preventDefault();
		});
	},
	clearLineItemDetails: function (parentElem) {
		var thisInstance = this;
		var lineItemRow = parentElem.closest('tr.' + thisInstance.rowClass);
		jQuery('input.selectedModuleId', lineItemRow).val('');
		jQuery('input.listPrice', lineItemRow).val('0');
		jQuery('.lineItemCommentBox', lineItemRow).val('');
		jQuery('.usageUnit', lineItemRow).text('');
		jQuery('.margin', lineItemRow).val('0');

		thisInstance.quantityChangeActions(lineItemRow);
	},
	checkLineItemRow: function () {
		var lineItemTable = this.getLineItemContentsContainer();
		var noRow = lineItemTable.find('.lineItemRow').length;
		if (noRow > 1) {
			this.showLineItemsDeleteIcon();
		} else {
			this.hideLineItemsDeleteIcon();
		}
	},
	showLineItemsDeleteIcon: function () {
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.find('.deleteRow').show();
	},
	hideLineItemsDeleteIcon: function () {
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.find('.deleteRow').hide();
	},
	/**
	 * Function to swap array
	 * @param Array that need to be swapped
	 */
	swapObject: function (objectToSwap) {
		var swappedArray = {};
		var newKey, newValue;
		for (var key in objectToSwap) {
			newKey = objectToSwap[key];
			newValue = key;
			swappedArray[newKey] = newValue;
		}
		return swappedArray;
	},
	/**
	 * Function to check for relation operation
	 * if relation exist calculation should happen by default
	 */
	registerForRealtionOperation: function () {
		var form = this.getForm();
		var relationExist = form.find('[name="relationOperation"]').val();
		if (relationExist) {
			jQuery('.qty').trigger('focusout');
		}
	},
	//Related to preTaxTotal Field

	/**
	 * Function to set the pre tax total
	 */
	setPreTaxTotal: function (preTaxTotalValue) {
		jQuery('#preTaxTotal').text(preTaxTotalValue);
		return this;
	},
	/**
	 * Function to get the pre tax total
	 */
	getPreTaxTotal: function () {
		return parseFloat(jQuery('#preTaxTotal').text());
	},
	/**
	 * Function to calculate the preTaxTotal value
	 */
	calculatePreTaxTotal: function () {
		var numberOfDecimal = parseInt(jQuery('.numberOfCurrencyDecimal').val());
		var netTotal = this.getNetTotalIncludeTaxType();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var preTaxTotal = netTotal - finalDiscountValue;
		var preTaxTotalValue = parseFloat(preTaxTotal).toFixed(numberOfDecimal);
		this.setPreTaxTotal(preTaxTotalValue);
	},
	/**
	 * Function to save the pre tax total value
	 */
	savePreTaxTotalValue: function () {
		jQuery('#pre_tax_total').val(this.getPreTaxTotal());
	},
	/**
	 * Function which will register all the events
	 */
	registerBasicEvents: function (container) {
		this._super(container);
	},
	registerEvents: function () {
		this._super();
		this.registerAddingNewProductsAndServices();
		this.lineItemActions();
		this.lineItemResultActions();
		//TODO : this might be costier operation. This we added to calculate tax for each line item
		this.makeLineItemsSortable();
		this.checkLineItemRow();
		this.registerForRealtionOperation();
	}
});
