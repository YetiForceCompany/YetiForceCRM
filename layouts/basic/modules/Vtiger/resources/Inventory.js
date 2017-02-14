/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Vtiger_Inventory_Js", {}, {
	inventoryContainer: false,
	inventoryHeadContainer: false,
	summaryTaxesContainer: false,
	summaryDiscountContainer: false,
	summaryCurrenciesContainer: false,
	rowClass: 'tr.inventoryRow',
	discountModalFields: ['aggregationType', 'globalDiscount', 'groupCheckbox', 'groupDiscount', 'individualDiscount', 'individualDiscountType'],
	taxModalFields: ['aggregationType', 'globalTax', 'groupCheckbox', 'groupTax', 'individualTax'],
	/**
	 * Function that is used to get the line item container
	 * @return : jQuery object
	 */
	getInventoryItemsContainer: function () {
		if (this.inventoryContainer === false) {
			this.inventoryContainer = $('.inventoryItems');
		}
		return this.inventoryContainer;
	},
	getInventoryHeadContainer: function () {
		if (this.inventoryHeadContainer === false) {
			this.inventoryHeadContainer = $('.inventoryHeader');
		}
		return this.inventoryHeadContainer;
	},
	getInventorySummaryDiscountContainer: function () {
		if (this.summaryDiscountContainer === false) {
			this.summaryDiscountContainer = $('.inventorySummaryDiscounts');
		}
		return this.summaryDiscountContainer;
	},
	getInventorySummaryTaxesContainer: function () {
		if (this.summaryTaxesContainer === false) {
			this.summaryTaxesContainer = $('.inventorySummaryTaxes');
		}
		return this.summaryTaxesContainer;
	},
	getInventorySummaryCurrenciesContainer: function () {
		if (this.summaryCurrenciesContainer === false) {
			this.summaryCurrenciesContainer = $('.inventorySummaryCurrencies');
		}
		return this.summaryCurrenciesContainer;
	},
	getNextLineItemRowNumber: function () {
		var $inventoryItemsNo = $('#inventoryItemsNo');
		var rowNumber = parseInt($inventoryItemsNo.val());
		$inventoryItemsNo.val(rowNumber + 1);
		return ++rowNumber;
	},
	getAccountId: function () {
		var accountReferenceField = $('#accountReferenceField').val();
		if (accountReferenceField != '') {
			return $('[name="' + accountReferenceField + '"]').val();
		}
		return '';
	},
	checkDeleteIcon: function () {
		var items = this.getInventoryItemsContainer();
		if (items.find(this.rowClass).length > 1) {
			this.showLineItemsDeleteIcon();
		} else {
			this.hideLineItemsDeleteIcon();
		}
	},
	showLineItemsDeleteIcon: function () {
		this.getInventoryItemsContainer().find('.deleteRow').removeClass('hide');
	},
	hideLineItemsDeleteIcon: function () {
		this.getInventoryItemsContainer().find('.deleteRow').addClass('hide');
	},
	getClosestRow: function (element) {
		return element.closest(this.rowClass);
	},
	/**
	 * Function which will return the basic row which can be used to add new rows
	 * @return jQuery object which you can use to
	 */
	getBasicRow: function () {
		return $('#blackIthemTable').find('tbody').clone(true, true);
	},
	isRecordSelected: function (element) {
		var parentRow = element.closest('tr');
		var productField = parentRow.find('.recordLabel');
		return productField.validationEngine('validate');
	},
	getTaxModeSelectElement: function (row) {
		var items = this.getInventoryHeadContainer();
		if (items.find('thead .taxMode').length > 0) {
			return $('.taxMode');
		}
		if (row) {
			return row.find('.taxMode');
		} else {
			return false;
		}
	},
	isIndividualTaxMode: function (row) {
		var taxModeElement = this.getTaxModeSelectElement(row);
		var selectedOption = taxModeElement.find('option:selected');
		return selectedOption.val() == '1';

	},
	isGroupTaxMode: function () {
		var taxTypeElement = this.getTaxModeSelectElement();
		if (taxTypeElement) {
			var selectedOption = taxTypeElement.find('option:selected');
			if (selectedOption.val() == '0') {
				return true;
			}
		}
		return false;
	},
	showIndividualTax: function (row) {
		var thisInstance = this;
		var groupTax = thisInstance.getInventorySummaryTaxesContainer().find('.groupTax');
		var items = thisInstance.getInventoryItemsContainer();
		var newRow = $('#blackIthemTable').find('tbody');
		if (thisInstance.isIndividualTaxMode()) {
			groupTax.addClass('hide');
			items.find('.changeTax').removeClass('hide');
			newRow.find('.changeTax').removeClass('hide');
		} else {
			groupTax.removeClass('hide');
			items.find('.changeTax').addClass('hide');
			newRow.find('.changeTax').addClass('hide');
		}
		thisInstance.setTax(items, 0);
		thisInstance.setTaxParam(items, []);
		thisInstance.rowsCalculations();
	},
	getDiscountModeSelectElement: function (row) {
		var items = this.getInventoryHeadContainer();
		if (items.find('thead .discountMode').length > 0) {
			return $('.discountMode');
		}
		return row.find('.discountMode');
	},
	isIndividualDiscountMode: function (row) {
		var discountModeElement = this.getDiscountModeSelectElement(row);
		var selectedOption = discountModeElement.find('option:selected');
		return selectedOption.val() == '1';
	},
	showIndividualDiscount: function (row) {
		var thisInstance = this;
		var groupDiscount = thisInstance.getInventorySummaryDiscountContainer().find('.groupDiscount');
		var items = thisInstance.getInventoryItemsContainer();
		var newRow = $('#blackIthemTable').find('tbody');
		if (thisInstance.isIndividualDiscountMode()) {
			groupDiscount.addClass('hide');
			items.find('.changeDiscount').removeClass('hide');
			newRow.find('.changeDiscount').removeClass('hide');
		} else {
			groupDiscount.removeClass('hide');
			items.find('.changeDiscount').addClass('hide');
			items.find('.changeDiscount').addClass('hide');
		}
		thisInstance.setDiscount(items, 0);
		thisInstance.setDiscountParam(items, []);
		thisInstance.rowsCalculations();
	},
	getCurrency: function () {
		var currency = $('[name="currency"]', this.getInventoryHeadContainer());
		return currency.find('option:selected').val();
	},
	getTax: function (row) {
		return $('.tax', row).getNumberFromValue();
	},
	getTaxParams: function (row) {
		var taxParams = row.find('.taxParam').val();
		if (taxParams == '' || taxParams == '[]' || taxParams == undefined)
			return false;
		return JSON.parse(taxParams);
	},
	getQuantityValue: function (row) {
		return $('.qty', row).getNumberFromValue();
	},
	getUnitPriceValue: function (row) {
		return $('.unitPrice', row).getNumberFromValue();
	},
	getDiscount: function (row) {
		return $('.discount', row).getNumberFromValue();
	},
	getNetPrice: function (row) {
		return $('.netPrice', row).getNumberFromValue();
	},
	getTotalPrice: function (row) {
		if ($('.totalPrice', row).length != 0) {
			return $('.totalPrice', row).getNumberFromValue();
		} else {
			return 0;
		}
	},
	getGrossPrice: function (row) {
		return $('.grossPrice', row).getNumberFromValue();
	},
	getPurchase: function (row) {
		var qty = this.getQuantityValue(row);
		var element = $('.purchase', row);
		var purchase = 0;
		if (element.length > 0) {
			purchase = app.parseNumberToFloat(element.val());
		}
		return purchase * qty;
	},
	getSummaryGrossPrice: function () {
		var thisInstance = this;
		var price = 0;
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			price += thisInstance.getGrossPrice($(this));
		});
		return app.parseNumberToFloat(price);
	},
	setUnitPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		row.find('.unitPrice').val(val).attr('title', val);
		return this;
	},
	setNetPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.netPriceText', row).text(val);
		$('.netPrice', row).val(val);
	},
	setGrossPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.grossPriceText', row).text(val);
		$('.grossPrice', row).val(val);
	},
	setTotalPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.totalPriceText', row).text(val);
		$('.totalPrice', row).val(val);
	},
	setMargin: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.margin', row).val(val);
	},
	setMarginP: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.marginp', row).val(val);
	},
	setDiscount: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.discount', row).val(val);
	},
	setDiscountParam: function (row, val) {
		$('.discountParam', row).val(JSON.stringify(val));
	},
	setTax: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.tax', row).val(val);
	},
	setTaxParam: function (row, val) {
		$('.taxParam', row).val(JSON.stringify(val));
	},
	quantityChangeActions: function (row) {
		this.rowCalculations(row);
		this.summaryCalculations();
	},
	rowCalculations: function (row) {
		this.calculateTotalPrice(row);
		this.calculateDiscounts(row);
		this.calculateNetPrice(row);
		this.calculateTaxes(row);
		this.calculateGrossPrice(row);
		this.calculateMargin(row);
	},
	rowsCalculations: function () {
		var thisInstance = this;
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			thisInstance.quantityChangeActions($(this));
		});
		thisInstance.calculateItemNumbers();
	},
	calculateDiscounts: function (row) {
		var discountParams = row.find('.discountParam').val();
		var aggregationType = $('.aggregationTypeDiscount').val();
		if (discountParams == '' || discountParams == '[]' || discountParams == undefined)
			return 0;
		discountParams = JSON.parse(discountParams);
		var valuePrices = this.getTotalPrice(row);
		var discountRate = 0;

		var types = discountParams.aggregationType;
		if (typeof types == 'string') {
			types = [types];
		}
		if (types)
			types.forEach(function (entry) {
				var discountValue;
				if (entry == 'individual') {
					discountValue = discountParams.individualDiscount;
					var discountType = discountParams.individualDiscountType;
					if (discountType == 'percentage') {
						discountRate += valuePrices * (discountValue / 100);
					} else {
						discountRate += app.parseNumberToFloat(discountValue);
					}
				}
				if (entry == 'global') {
					discountValue = discountParams.globalDiscount;
					discountRate += valuePrices * (discountValue / 100);
				}
				if (entry == 'group') {
					discountValue = discountParams.groupDiscount;
					discountRate += valuePrices * (discountValue / 100);
				}
				if (aggregationType == '2') {
					valuePrices = valuePrices - discountRate;
				}
			});
		this.setDiscount(row, discountRate);
	},
	calculateTaxes: function (row) {
		var taxParams = row.find('.taxParam').val();
		if (taxParams == '' || taxParams == '[]' || taxParams == undefined)
			return 0;
		taxParams = JSON.parse(taxParams);
		var aggregationType = $('.aggregationTypeTax').val();
		var valuePrices = this.getNetPrice(row);
		var taxRate = 0;
		var types = taxParams.aggregationType;
		if (typeof types == 'string') {
			types = [types];
		}
		if (types)
			types.forEach(function (entry) {
				var taxValue = 0;
				switch (entry) {
					case 'individual':
						taxValue = taxParams.individualTax;
						break;
					case 'global':
						taxValue = taxParams.globalTax;
						break;
					case 'group':
						taxValue = taxParams.groupTax;
						break;
					case 'regional':
						taxValue = taxParams.regionalTax;
						break;
				}
				taxRate += valuePrices * (app.parseNumberToFloat(taxValue) / 100);
				if (aggregationType == '2') {
					valuePrices = valuePrices + taxRate;
				}
			});
		this.setTax(row, taxRate);
	},
	summaryCalculations: function () {
		var thisInstance = this;
		thisInstance.getInventoryItemsContainer().find('tfoot .wisableTd').each(function (index) {
			thisInstance.calculatSummary($(this), $(this).data('sumfield'));
		});
		thisInstance.calculatDiscountSummary();
		thisInstance.calculatTaxSummary();
		thisInstance.calculatCurrenciesSummary();
		thisInstance.calculatMarginPSummary();
	},
	calculatSummary: function (element, field) {
		var thisInstance = this;
		var sum = 0;
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			var element = $(this).find('.' + field);
			if (element.length > 0) {
				sum += app.parseNumberToFloat(element.val());
			}
		});
		element.text(app.parseNumberToShow(sum));
	},
	calculatMarginPSummary: function () {
		var thisInstance = this;
		var sumRow = thisInstance.getInventoryItemsContainer().find('tfoot');
		var purchase = app.parseNumberToFloat(sumRow.find('[data-sumfield="purchase"]').text());
		var margin = app.parseNumberToFloat(sumRow.find('[data-sumfield="margin"]').text());
		margin = app.parseNumberToFloat(margin);
		var marginp = '0';
		if (purchase !== 0) {
			marginp = (margin / purchase) * 100;
		}
		sumRow.find('[data-sumfield="marginP"]').text(app.parseNumberToShow(marginp))
	},
	calculatDiscountSummary: function () {
		var thisInstance = this;
		var discount = thisInstance.getAllDiscount();
		var container = thisInstance.getInventorySummaryDiscountContainer();
		container.find('input').val(app.parseNumberToShow(discount));
	},
	getAllDiscount: function () {
		var thisInstance = this;
		var discount = 0;
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			var row = $(this);
			discount += thisInstance.getDiscount(row);
		});
		return discount;
	},
	calculatCurrenciesSummary: function () {
		var thisInstance = this;
		var container = thisInstance.getInventorySummaryCurrenciesContainer();
		var selected = $('[name="currency"] option:selected', thisInstance.getInventoryHeadContainer());
		var base = $('[name="currency"] option[data-base-currency="1"]', thisInstance.getInventoryHeadContainer());
		var conversionRate = selected.data('conversionRate');
		var baseConversionRate = base.data('conversionRate');
		if (conversionRate == baseConversionRate) {
			container.addClass('hide');
			return;
		}
		conversionRate = parseFloat(baseConversionRate) / parseFloat(conversionRate);
		container.removeClass('hide');
		var taxs = thisInstance.getAllTaxs();
		var sum = 0;
		container.find('.panel-body').html('');
		$.each(taxs, function (index, value) {
			if (value != undefined) {
				value = value * conversionRate;
				var row = container.find('.hide .form-group').clone();
				row.find('.percent').text(index + '%');
				row.find('input').val(app.parseNumberToShow(value));
				row.appendTo(container.find('.panel-body'));
				sum += value;
			}
		});
		container.find('.panel-footer input').val(app.parseNumberToShow(sum));
	},
	calculatTaxSummary: function () {
		var thisInstance = this;
		var taxs = thisInstance.getAllTaxs();
		var container = thisInstance.getInventorySummaryTaxesContainer();
		container.find('.panel-body').html('');
		var sum = 0;
		for (var index in taxs) {
			var row = container.find('.hide .form-group').clone();
			row.find('.percent').text(app.parseNumberToShow(app.parseNumberToFloat(index)) + '%');
			row.find('input').val(app.parseNumberToShow(taxs[index]));
			row.appendTo(container.find('.panel-body'));
			sum += taxs[index];
		}
		container.find('.panel-footer input').val(app.parseNumberToShow(sum));
	},
	getAllTaxs: function () {
		var thisInstance = this;
		var tax = [];
		var typeSummary = $('.aggregationTypeTax').val();
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			var row = $(this);
			var netPrice = thisInstance.getNetPrice(row);
			var params = row.find('.taxParam').val();
			if (params != '' && params != '[]' && params != undefined) {
				var param = $.parseJSON(params);
				if (typeof param.aggregationType == 'string') {
					param.aggregationType = [param.aggregationType];
				}
				if (param.aggregationType)
					$.each(param.aggregationType, function (index, name) {
						name = name + 'Tax';
						var precent = param[name];
						var old = 0;
						if (tax[precent] != undefined) {
							old = parseFloat(tax[precent]);
						}
						var taxRate = netPrice * (app.parseNumberToFloat(precent) / 100);
						tax[precent] = old + taxRate;
						if (typeSummary == '2') {
							netPrice += taxRate;
						}
					});
			}
		});

		return tax;
	},
	calculateNetPrice: function (row) {
		var netPrice = this.getTotalPrice(row) - this.getDiscount(row);
		this.setNetPrice(row, netPrice);
	},
	calculateGrossPrice: function (row) {
		var netPrice = this.getNetPrice(row);
		if (this.isIndividualTaxMode(row) || this.isGroupTaxMode(row)) {
			netPrice += this.getTax(row);
		}
		this.setGrossPrice(row, netPrice);
	},
	calculateTotalPrice: function (row) {
		var netPriceBeforeDiscount = this.getQuantityValue(row) * this.getUnitPriceValue(row);
		this.setTotalPrice(row, netPriceBeforeDiscount);
	},
	calculateMargin: function (row) {
		var netPrice;
		if (jQuery('.netPrice', row).length) {
			netPrice = this.getNetPrice(row);
		} else {
			netPrice = this.getTotalPrice(row);
		}
		var purchase = this.getPurchase(row);
		var margin = netPrice - purchase;
		this.setMargin(row, margin);
		var marginp = '0';
		if (purchase !== 0) {
			marginp = (margin / purchase) * 100;
		}
		this.setMarginP(row, marginp);
	},
	calculateDiscount: function (row, modal) {
		var netPriceBeforeDiscount = app.parseNumberToFloat(modal.find('.valueTotalPrice').text()),
				valuePrices = netPriceBeforeDiscount,
				globalDiscount = 0,
				groupDiscount = 0,
				individualDiscount = 0,
				valueDiscount = 0;

		var discountsType = modal.find('.discountsType').val();

		if (discountsType == '0' || discountsType == '1') {
			if (modal.find('.activepanel .globalDiscount').length > 0) {
				globalDiscount = app.parseNumberToFloat(modal.find('.activepanel .globalDiscount').val());
			}
			if (modal.find('.activepanel .individualDiscountType').length > 0) {
				var individualTypeDiscount = modal.find('.activepanel .individualDiscountType:checked').val();
				var value = app.parseNumberToFloat(modal.find('.activepanel .individualDiscountValue').val());
				if (individualTypeDiscount == 'percentage') {
					individualDiscount = netPriceBeforeDiscount * (value / 100);
				} else {
					individualDiscount = value;
				}
			}
			if (modal.find('.activepanel .groupCheckbox').length > 0 && modal.find('.activepanel .groupCheckbox').prop("checked") == true) {
				groupDiscount = app.parseNumberToFloat(modal.find('.groupValue').val());
				groupDiscount = netPriceBeforeDiscount * (groupDiscount / 100);
			}

			valuePrices = valuePrices * ((100 - globalDiscount) / 100);
			valuePrices = valuePrices - individualDiscount;
			valuePrices = valuePrices - groupDiscount;
		} else if (discountsType == '2') {
			modal.find('.activepanel').each(function (index) {
				var panel = $(this);
				if (panel.find('.globalDiscount').length > 0) {
					var globalDiscount = app.parseNumberToFloat(panel.find('.globalDiscount').val());
					valuePrices = valuePrices * ((100 - globalDiscount) / 100);
				} else if (panel.find('.groupCheckbox').length > 0 && panel.find('.groupCheckbox').prop("checked") == true) {
					var groupDiscount = app.parseNumberToFloat(panel.find('.groupValue').val());
					valuePrices = valuePrices * ((100 - groupDiscount) / 100);
				} else if (panel.find('.individualDiscountType').length > 0) {
					var value = app.parseNumberToFloat(panel.find('.individualDiscountValue').val());
					if (panel.find('.individualDiscountType[name="individual"]:checked').val() == 'percentage') {
						valuePrices = valuePrices * ((100 - value) / 100);
					} else {
						valuePrices = valuePrices - value;
					}
				}
			});
		}

		modal.find('.valuePrices').text(app.parseNumberToShow(valuePrices));
		modal.find('.valueDiscount').text(app.parseNumberToShow(netPriceBeforeDiscount - valuePrices));
	},
	calculateTax: function (row, modal) {
		var netPriceWithoutTax = app.parseNumberToFloat(modal.find('.valueNetPrice').text()),
				valuePrices = netPriceWithoutTax,
				globalTax = 0,
				groupTax = 0,
				regionalTax = 0,
				individualTax = 0;

		var taxType = modal.find('.taxsType').val();
		if (taxType == '0' || taxType == '1') {
			if (modal.find('.activepanel .globalTax').length > 0) {
				globalTax = app.parseNumberToFloat(modal.find('.activepanel .globalTax').val());
			}
			if (modal.find('.activepanel .individualTaxValue').length > 0) {
				var value = app.parseNumberToFloat(modal.find('.activepanel .individualTaxValue').val());
				individualTax = (value / 100) * valuePrices;
			}
			if (modal.find('.activepanel .groupTax').length > 0) {
				groupTax = app.parseNumberToFloat(modal.find('.groupTax').val());
				groupTax = netPriceWithoutTax * (groupTax / 100);
			}
			if (modal.find('.activepanel .regionalTax').length > 0) {
				regionalTax = app.parseNumberToFloat(modal.find('.regionalTax').val());
				regionalTax = netPriceWithoutTax * (regionalTax / 100);
			}

			valuePrices = valuePrices * ((100 + globalTax) / 100);
			valuePrices = valuePrices + individualTax;
			valuePrices = valuePrices + groupTax;
			valuePrices = valuePrices + regionalTax;
		} else if (taxType == '2') {
			modal.find('.activepanel').each(function (index) {
				var panel = $(this);
				if (panel.find('.globalTax').length > 0) {
					var globalTax = app.parseNumberToFloat(panel.find('.globalTax').val());
					valuePrices = valuePrices * ((100 + globalTax) / 100);
				} else if (panel.find('.groupTax').length > 0) {
					var groupTax = app.parseNumberToFloat(panel.find('.groupTax').val());
					valuePrices = valuePrices * ((100 + groupTax) / 100);
				} else if (panel.find('.regionalTax').length > 0) {
					var regionalTax = app.parseNumberToFloat(panel.find('.regionalTax').val());
					valuePrices = valuePrices * ((100 + regionalTax) / 100);
				} else if (panel.find('.individualTaxValue').length > 0) {
					var value = app.parseNumberToFloat(panel.find('.individualTaxValue').val());
					valuePrices = ((value + 100) / 100) * valuePrices;
				}
			});
		}
		modal.find('.valuePrices').text(app.parseNumberToShow(valuePrices));
		modal.find('.valueTax').text(app.parseNumberToShow(valuePrices - netPriceWithoutTax));
	},
	updateRowSequence: function () {
		var items = this.getInventoryItemsContainer();
		items.find(this.rowClass).each(function (index) {
			$(this).find('.sequence').val(index + 1);
		});
	},
	registerInventorySaveData: function (container) {
		var thisInstance = this;
		container.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			if (!thisInstance.checkLimits(container)) {
				return false;
			}
			var table = container.find('#blackIthemTable');
			table.find('[name]').removeAttr('name');
		});
	},
	/**
	 * Function which will be used to handle price book popup
	 * @params :  popupImageElement - popup image element
	 */
	pricebooksPopupHandler: function (popupImageElement) {
		var thisInstance = this;
		var lineItemRow = popupImageElement.closest(this.rowClass);
		var rowName = lineItemRow.find('.rowName');
		var params = {};
		params.module = 'PriceBooks';
		params.src_module = $('[name="popupReferenceModule"]', rowName).val();
		params.src_record = $('.sourceField', rowName).val();
		params.src_field = $('[name="popupReferenceModule"]', rowName).data('field');
		params.get_url = 'getProductUnitPriceURL';
		params.currency_id = thisInstance.getCurrency();
		this.showPopup(params).then(function (data) {
			var responseData = JSON.parse(data);
			for (var id in responseData) {
				if (responseData[id]) {
					thisInstance.setUnitPrice(lineItemRow, responseData[id]);
				} else {
					app.errorLog('Incorrect data', responseData);
				}
			}
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(rowName));
		});
	},
	showPopup: function (params) {
		var aDeferred = $.Deferred();
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(params, function (data) {
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	subProductsCashe: [],
	loadSubProducts: function (parentRow, indicator) {
		var thisInstance = this;
		var recordId = jQuery('input.sourceField', parentRow).val();
		var recordModule = parentRow.find('.rowName input[name="popupReferenceModule"]').val();
		thisInstance.removeSubProducts(parentRow);
		if (recordId == '0' || recordId == '' || $.inArray(recordModule, ['Products', 'Services']) < 0) {
			return false;
		}
		if (thisInstance.subProductsCashe[recordId]) {
			thisInstance.addSubProducts(parentRow, thisInstance.subProductsCashe[recordId]);
			return false;
		}
		var subProrductParams = {
			module: "Products",
			action: "SubProducts",
			record: recordId
		};
		if (indicator) {
			var progressInstace = jQuery.progressIndicator();
		}
		AppConnector.request(subProrductParams).then(
				function (data) {
					var responseData = data.result;
					thisInstance.subProductsCashe[recordId] = responseData;
					thisInstance.addSubProducts(parentRow, responseData);
					if (progressInstace) {
						progressInstace.hide();
					}
				},
				function (error, err) {
					if (progressInstace) {
						progressInstace.hide();
					}
					console.error(error, err);
				}
		);
	},
	removeSubProducts: function (parentRow) {
		var subProductsContainer = $('.subProductsContainer ul', parentRow);
		subProductsContainer.find("li").remove();
	},
	addSubProducts: function (parentRow, responseData) {
		var subProductsContainer = $('.subProductsContainer ul', parentRow);
		for (var id in responseData) {
			var priductText = $("<li>").text(responseData[id]);
			subProductsContainer.append(priductText);
		}
	},
	mapResultsToFields: function (referenceModule, parentRow, responseData) {
		var validationEngine, unitPrice, taxParam = [];
		var thisInstance = this;
		var isGroupTax = thisInstance.isGroupTaxMode();
		for (var id in responseData) {
			var recordData = responseData[id];
			var description = recordData.description;
			var unitPriceValues = recordData.unitPriceValues;
			var unitPriceValuesJson = JSON.stringify(unitPriceValues);
			// Load taxses detail
			if (isGroupTax) {
				var parameters  = parentRow.closest('.inventoryItems').data('taxParam');
				if(parameters){
					taxParam = JSON.parse(parameters);
				}
			} else if (recordData['taxes']) {
				taxParam = {aggregationType: recordData.taxes.type};
				taxParam[recordData.taxes.type + 'Tax'] = recordData.taxes.value;
			}
			thisInstance.setTaxParam(parentRow, taxParam);
			thisInstance.setTax(parentRow, 0);
			// Load auto fields
			for (var field in recordData['autoFields']) {
				parentRow.find('input.' + field).val(recordData['autoFields'][field]);
				if (recordData['autoFields'][field + 'Text']) {
					parentRow.find('.' + field + 'Text').text(recordData['autoFields'][field + 'Text']);
				}
			}
			
			var currencyId = thisInstance.getCurrency();
			if (typeof unitPriceValues[currencyId] !== 'undefined') {
				unitPrice = unitPriceValues[currencyId];
			} else {
				unitPrice = recordData.price;
			}
			thisInstance.setUnitPrice(parentRow, app.parseNumberToFloat(unitPrice));

			$('input.unitPrice', parentRow).attr('list-info', unitPriceValuesJson);
			$('textarea.commentTextarea', parentRow.next()).html(description);

			if (typeof recordData['autoFields']['unit'] !== 'undefined') {
				$('input.qtyparam', parentRow).prop('checked', false);
				switch (recordData['autoFields']['unit']) {
					default:
						$('.qtyparamButton', parentRow).addClass('hidden');
						validationEngine = 'validate[required,funcCall[Vtiger_NumberUserFormat_Validator_Js.invokeValidation]]';
						$('input.qty', parentRow).attr('data-validation-engine', validationEngine);
						break;
					case 'pack':
						$('.qtyparamButton', parentRow).removeClass('hidden');
						$('.qtyparamButton', parentRow).removeClass('active');
						validationEngine = 'validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]';
						$('input.qty', parentRow).attr('data-validation-engine', validationEngine);
						break;
					case 'pcs':
						$('.qtyparamButton', parentRow).addClass('hidden');
						validationEngine = 'validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]';
						$('input.qty', parentRow).attr('data-validation-engine', validationEngine);
						break;
				}
			}
		}
		if (referenceModule === 'Products') {
			thisInstance.loadSubProducts(parentRow, true);
		}
		thisInstance.quantityChangeActions(parentRow);
	},
	saveDiscountsParameters: function (parentRow, modal) {
		var thisInstance = this;
		var info = {};
		var extend = ['aggregationType', 'groupCheckbox', 'individualDiscountType'];
		$.each(thisInstance.discountModalFields, function (index, param) {
			if ($.inArray(param, extend) >= 0) {
				if (modal.find('[name="' + param + '"]:checked').length > 1) {
					info[param] = [];
					modal.find('[name="' + param + '"]:checked').each(function (index) {
						info[param].push($(this).val());
					});
				} else {
					info[param] = modal.find('[name="' + param + '"]:checked').val();
				}
			} else {
				info[param] = modal.find('[name="' + param + '"]').val();
			}
		});
		thisInstance.setDiscountParam($('#blackIthemTable'), info);
		thisInstance.setDiscountParam(parentRow, info);
	},
	saveTaxsParameters: function (parentRow, modal) {
		var thisInstance = this;
		var info = {};
		var extend = ['aggregationType', 'groupCheckbox', 'individualTaxType'];
		$.each(thisInstance.taxModalFields, function (index, param) {
			if ($.inArray(param, extend) >= 0) {
				if (modal.find('[name="' + param + '"]:checked').length > 1) {
					info[param] = [];
					modal.find('[name="' + param + '"]:checked').each(function (index) {
						info[param].push($(this).val());
					});
				} else {
					info[param] = modal.find('[name="' + param + '"]:checked').val();
				}
			} else {
				info[param] = modal.find('[name="' + param + '"]').val();
			}
		});
		parentRow.data('taxParam',JSON.stringify(info));
		thisInstance.setTaxParam(parentRow, info);
		thisInstance.setTaxParam($('#blackIthemTable'), info);
	},
	showExpandedRow: function (row) {
		var thisInstance = this;
		var items = thisInstance.getInventoryItemsContainer();
		var inventoryRowExpanded = items.find('[numrowex="' + row.attr('numrow') + '"]');
		var element = row.find('.toggleVisibility');
		element.data('status', '1');
		element.find('.glyphicon').removeClass('glyphicon-menu-down');
		element.find('.glyphicon').addClass('glyphicon-menu-up');
		inventoryRowExpanded.removeClass('hide');

		var listInstance = Vtiger_Edit_Js.getInstance();
		$.each(inventoryRowExpanded.find('.ckEditorSource'), function (key, data) {
			listInstance.loadCkEditorElement(jQuery(data));
		});
	},
	hideExpandedRow: function (row) {
		var thisInstance = this;
		var items = thisInstance.getInventoryItemsContainer();
		var inventoryRowExpanded = items.find('[numrowex="' + row.attr('numrow') + '"]');
		var element = row.find('.toggleVisibility');
		element.data('status', '0');
		element.find('.glyphicon').removeClass('glyphicon-menu-up');
		element.find('.glyphicon').addClass('glyphicon-menu-down');
		inventoryRowExpanded.addClass('hide');
		$.each(inventoryRowExpanded.find('.ckEditorSource'), function (key, data) {
			var editorInstance = CKEDITOR.instances[jQuery(data).attr('id')];
			if (editorInstance) {
				editorInstance.destroy();
			}
		});
	},
	initDiscountsParameters: function (parentRow, modal) {
		var thisInstance = this;
		var parameters = parentRow.find('.discountParam').val();
		if (parameters == '' || parameters == undefined) {
			return;
		}
		parameters = JSON.parse(parameters);
		$.each(thisInstance.discountModalFields, function (index, param) {
			var parameter = parameters[param];
			var field = modal.find('[name="' + param + '"]');

			if (field.attr('type') == 'checkbox' || field.attr('type') == 'radio') {
				var array = parameter;
				if (!$.isArray(array)) {
					array = [array];
				}
				$.each(array, function (index, arrayValue) {
					var value = field.filter('[value="' + arrayValue + '"]').prop('checked', true);
					if (param == 'aggregationType') {
						value.closest('.panel').find('.panel-body').show();
						value.closest('.panel').addClass('activepanel');
					}
				});
			} else if (field.prop("tagName") == 'SELECT') {
				field.find('option[value="' + parameter + '"]').prop('selected', 'selected').change();
			} else {
				modal.find('[name="' + param + '"]').val(parameter);
			}
		});

		thisInstance.calculateDiscount(parentRow, modal);
	},
	initTaxParameters: function (parentRow, modal) {
		var thisInstance = this;
		if (parentRow.data('taxParam')) {
			var parameters = parentRow.data('taxParam');
		} else {
			var parameters = parentRow.find('.taxParam').val();
		}
		if (!parameters) {
			return;
		}
		parameters = JSON.parse(parameters);
		$.each(thisInstance.taxModalFields, function (index, param) {
			var parameter = parameters[param];
			var field = modal.find('[name="' + param + '"]');

			if (field.attr('type') == 'checkbox' || field.attr('type') == 'radio') {
				var array = parameter;
				if (!$.isArray(array)) {
					array = [array];
				}
				$.each(array, function (index, arrayValue) {
					var value = field.filter('[value="' + arrayValue + '"]').prop('checked', true);
					if (param == 'aggregationType') {
						value.closest('.panel').find('.panel-body').show();
						value.closest('.panel').addClass('activepanel');
					}
				});
			} else if (field.prop("tagName") == 'SELECT') {
				field.find('option[value="' + parameter + '"]').prop('selected', 'selected').change();
			} else {
				modal.find('[name="' + param + '"]').val(parameter);
			}
		});
		thisInstance.calculateTax(parentRow, modal);
	},
	limitEnableSave: false,
	checkLimits: function () {
		var thisInstance = this;
		var account = thisInstance.getAccountId();
		var limit = parseInt(app.getMainParams('inventoryLimit'));
		var response = true;

		if (account == '' || thisInstance.limitEnableSave || !limit) {
			return response;
		}

		var params = {};
		params.data = {
			module: app.getModuleName(),
			action: 'Inventory',
			mode: 'checkLimits',
			record: account,
			currency: thisInstance.getCurrency(),
			price: thisInstance.getSummaryGrossPrice()
		};
		params.async = false;
		params.dataType = 'json';
		var progressInstace = jQuery.progressIndicator();
		AppConnector.request(params).then(
				function (data) {
					progressInstace.hide();
					var editViewForm = Vtiger_Edit_Js.getInstance().getForm();
					if (data.result.status == false) {
						app.showModalWindow(data.result.html, function (data) {});
						response = false;
					}
				},
				function (error, err) {
					progressInstace.hide();
					console.error(error, err);
				}
		);
		return response;
	},
	currencyChangeActions: function (select, option) {
		var thisInstance = this;
		if (option.data('baseCurrency') == 0) {
			thisInstance.showCurrencyChangeModal(select, option);
		} else {
			thisInstance.currencyConvertValues(select, option);
			select.data('oldValue', select.val());
		}
	},
	showCurrencyChangeModal: function (select, option) {
		var thisInstance = this;
		if (thisInstance.lockCurrencyChange == true) {
			return;
		}
		thisInstance.lockCurrencyChange = true;
		var block = select.closest('th');
		var modal = block.find('.modelContainer').clone();
		app.showModalWindow(modal, function (data) {
			var modal = $(data);
			var currencyParam = JSON.parse(block.find('.currencyparam').val());

			if (currencyParam != false) {
				if (typeof currencyParam[option.val()] == 'undefined') {
					var defaultCurrencyParams = [];
					defaultCurrencyParams['value'] = 1;
					defaultCurrencyParams['date'] = '';
					currencyParam[option.val()] = defaultCurrencyParams;
				}
				modal.find('.currencyName').text(option.text());
				modal.find('.currencyRate').val(currencyParam[option.val()]['value']);
				modal.find('.currencyDate').text(currencyParam[option.val()]['date']);
			}
			modal.on('click', 'button[type="submit"]', function (e) {
				var rate = modal.find('.currencyRate').val();
				var value = app.parseNumberToFloat(rate);
				var conversionRate = 1 / app.parseNumberToFloat(rate);

				option.data('conversionRate', conversionRate);
				currencyParam[option.val()] = {
					value: value.toString(),
					conversion: conversionRate.toString()
				};
				block.find('.currencyparam').val(JSON.stringify(currencyParam));

				thisInstance.currencyConvertValues(select, option);
				select.data('oldValue', select.val());
				app.hideModalWindow();
				thisInstance.lockCurrencyChange = false;
			});
			modal.on('click', 'button[type="reset"]', function (e) {
				select.val(select.data('oldValue')).change();
				thisInstance.lockCurrencyChange = false;
			});
		});
	},
	currencyConvertValues: function (select, selected) {
		var thisInstance = this;

		var previous = select.find('option[value="' + select.data('oldValue') + '"]');
		var conversionRate = selected.data('conversionRate');
		var prevConversionRate = previous.data('conversionRate');
		conversionRate = parseFloat(conversionRate) / parseFloat(prevConversionRate);

		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			var row = $(this);

			thisInstance.setUnitPrice(row, app.parseNumberToFloat(thisInstance.getUnitPriceValue(row) * conversionRate));
			thisInstance.setDiscount(row, app.parseNumberToFloat(thisInstance.getDiscount(row) * conversionRate));
			thisInstance.setTax(row, app.parseNumberToFloat(thisInstance.getTax(row) * conversionRate));
			thisInstance.quantityChangeActions(row);
		});
	},
	registerAddItem: function (container) {
		var thisInstance = this;
		var items = this.getInventoryItemsContainer();
		container.find('.btn-toolbar .addItem').on('click', function (e, data) {
			var table = container.find('#blackIthemTable');
			var newRow = thisInstance.getBasicRow();
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();
			var module = $(e.currentTarget).data('module');
			var field = $(e.currentTarget).data('field');
			var wysiwyg = $(e.currentTarget).data('wysiwyg');

			var replaced = newRow.html().replace(/_NUM_/g, sequenceNumber);
			newRow.html(replaced);
			newRow = newRow.find('tr').appendTo(items.find('tbody'));

			newRow.find('.rowName input[name="popupReferenceModule"]').val(module).data('field', field);
			newRow.find('select').each(function (index, select) {
				select = $(select);
				select.find('option').each(function (index, option) {
					option = $(option);
					if (option.data('module') != module) {
						option.remove();
					}
				});
				app.showSelect2ElementView(select);
			});
			thisInstance.initItem(newRow);
			Vtiger_Edit_Js.getInstance().registerAutoCompleteFields(newRow);
		});
	},
	registerSortableItems: function () {
		var thisInstance = this;
		var items = thisInstance.getInventoryItemsContainer();
		items.sortable({
			handle: '.dragHandle',
			items: thisInstance.rowClass,
			revert: true,
			tolerance: 'pointer',
			placeholder: "ui-state-highlight",
			helper: function (e, ui) {
				ui.children().each(function (index, element) {
					element = $(element);
					element.width(element.width());
				});
				return ui;
			},
			start: function (event, ui) {
				items.find(thisInstance.rowClass).each(function (index, element) {
					var row = $(element);
					thisInstance.hideExpandedRow(row);
				});
				ui.item.startPos = ui.item.index();
			},
			stop: function (event, ui) {
				var numrow = $(ui.item.context).attr('numrow');
				var child = items.find('.numRow' + numrow).remove().clone();
				items.find('[numrow="' + numrow + '"]').after(child);
				if (ui.item.startPos < ui.item.index()) {
					child = items.find('.numRow' + numrow).next().remove().clone();
					items.find('[numrow="' + numrow + '"]').before(child);
				}
				thisInstance.updateRowSequence();
			}
		});
	},
	registerShowHideExpanded: function (container) {
		var thisInstance = this;
		container.on('click', '.toggleVisibility', function (e) {
			var element = $(e.currentTarget);
			var row = thisInstance.getClosestRow(element);
			if (element.data('status') == '0') {
				thisInstance.showExpandedRow(row);
			} else {
				thisInstance.hideExpandedRow(row);
			}
		});
	},
	registerPriceBookPopUp: function (container) {
		var thisInstance = this;
		container.on('click', '.priceBookPopup', function (e) {
			var element = $(e.currentTarget);
			var response = thisInstance.isRecordSelected(element);
			if (response == true) {
				return;
			}
			thisInstance.pricebooksPopupHandler(element);
		});
	},
	registerRowChangeEvent: function (container) {
		var thisInstance = this;
		container.on('focusout', '.qty', function (e) {
			var element = $(e.currentTarget);
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(element));
		});
		container.on('focusout', '.unitPrice', function (e) {
			var element = $(e.currentTarget);
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(element));
		});
		container.on('focusout', '.purchase', function (e) {
			var element = $(e.currentTarget);
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(element));
		});
		var headContainer = thisInstance.getInventoryHeadContainer();
		headContainer.on('change', '.taxMode', function (e) {
			var element = $(e.currentTarget);
			thisInstance.showIndividualTax(thisInstance.getClosestRow(element));
			thisInstance.rowsCalculations();
		});
		headContainer.on('change', '.discountMode', function (e) {
			var element = $(e.currentTarget);
			thisInstance.showIndividualDiscount(thisInstance.getClosestRow(element));
			thisInstance.rowsCalculations();
		});
	},
	registerSubProducts: function (container) {
		var thisInstance = this;
		container.find('.inventoryItems ' + thisInstance.rowClass).each(function (index) {
			thisInstance.loadSubProducts($(this), false);
		});
	},
	registerClearReferenceSelection: function (container) {
		var thisInstance = this;
		container.on('click', '.clearReferenceSelection', function (e) {
			var element = $(e.currentTarget);
			var row = thisInstance.getClosestRow(element);
			thisInstance.removeSubProducts(row);
			row.find('.unitPrice,.tax,.discount,.margin,.purchase').val(app.parseNumberToShow(0));
			row.find('textarea,.valueVal').val('');
			row.find('.valueText').text('');
			row.find('.qtyparamButton').addClass('hidden');
			row.find('input.qtyparam').prop('checked', false);
			if(!thisInstance.isGroupTaxMode()){
				thisInstance.setTaxParam(row,[]);
			}
			thisInstance.quantityChangeActions(row);
		});
	},
	registerDeleteLineItemEvent: function (container) {
		var thisInstance = this;
		container.on('click', '.deleteRow', function (e) {
			var element = $(e.currentTarget);
			thisInstance.getClosestRow(element).remove();
			thisInstance.checkDeleteIcon();
			thisInstance.rowsCalculations();
		});
	},
	registerChangeDiscount: function (container) {
		var thisInstance = this;
		container.on('click', '.changeDiscount', function (e) {
			var parentRow;
			var element = $(e.currentTarget);
			var params = {
				module: app.getModuleName(),
				view: 'Inventory',
				mode: 'showDiscounts',
				currency: thisInstance.getCurrency(),
				relatedRecord: thisInstance.getAccountId()
			};
			if (element.hasClass('groupDiscount')) {
				parentRow = thisInstance.getInventoryItemsContainer();
				if (parentRow.find('tfoot .colTotalPrice').length != 0) {
					params.totalPrice = app.parseNumberToFloat(parentRow.find('tfoot .colTotalPrice').text());
				} else {
					params.totalPrice = 0;
				}
				params.discountType = 1;
			} else {
				parentRow = element.closest(thisInstance.rowClass);
				params.totalPrice = thisInstance.getTotalPrice(parentRow);
				params.discountType = 0;
			}

			var progressInstace = jQuery.progressIndicator();
			AppConnector.request(params).then(
					function (data) {
						app.showModalWindow(data, function (data) {
							thisInstance.initDiscountsParameters(parentRow, $(data));
							thisInstance.registerChangeDiscountModal(data, parentRow, params);
						});
						progressInstace.hide();
					},
					function (error, err) {
						progressInstace.hide();
						console.error(error, err);
					}
			);
		});
	},
	registerChangeDiscountModal: function (modal, parentRow, params) {
		var thisInstance = this;
		modal.on('change', '.individualDiscountType', function (e) {
			var element = $(e.currentTarget);
			modal.find('.individualDiscountContainer .input-group-addon').text(element.data('symbol'));
		});
		modal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
			var element = $(e.currentTarget);

			if (element.attr('type') == 'checkbox' && this.checked) {
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else if (element.attr('type') == 'radio') {
				modal.find('.panel').removeClass('activepanel');
				modal.find('.panel .panel-body').hide();
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else {
				element.closest('.panel').find('.panel-body').hide();
				element.closest('.panel').removeClass('activepanel');
			}
		});
		modal.on('change', '.activeCheckbox, .globalDiscount,.individualDiscountValue,.individualDiscountType,.groupCheckbox', function (e) {
			var element = $(e.currentTarget);
			thisInstance.calculateDiscount(parentRow, modal);
		});
		modal.on('click', '.saveDiscount', function (e) {
			thisInstance.saveDiscountsParameters(parentRow, modal);
			if (params.discountType == 0) {
				thisInstance.setDiscount(parentRow, app.parseNumberToFloat(modal.find('.valueDiscount').text()));
				thisInstance.quantityChangeActions(parentRow);
			} else {
				var rate = app.parseNumberToFloat(modal.find('.valueDiscount').text()) / app.parseNumberToFloat(modal.find('.valueTotalPrice').text());
				parentRow.find(thisInstance.rowClass).each(function (index) {
					thisInstance.setDiscount($(this), thisInstance.getTotalPrice($(this)) * rate);
					thisInstance.quantityChangeActions($(this));
				});
			}
			app.hideModalWindow();
		});
	},
	registerChangeTax: function (container) {
		var thisInstance = this;
		container.on('click', '.changeTax', function (e) {
			var parentRow;
			var element = $(e.currentTarget);
			var params = {
				module: app.getModuleName(),
				view: 'Inventory',
				mode: 'showTaxes',
				currency: thisInstance.getCurrency(),
				sourceRecord: app.getRecordId()
			};
			if (element.hasClass('groupTax')) {
				parentRow = thisInstance.getInventoryItemsContainer();
				var totalPrice = 0;
				if (parentRow.find('tfoot .colNetPrice').length > 0) {
					totalPrice = parentRow.find('tfoot .colNetPrice').text();
				} else if (parentRow.find('tfoot .colTotalPrice ').length > 0) {
					totalPrice = parentRow.find('tfoot .colTotalPrice ').text();
				}
				params.totalPrice = app.parseNumberToFloat(totalPrice);
				params.taxType = 1;
			} else {
				parentRow = element.closest(thisInstance.rowClass);
				params.totalPrice = thisInstance.getNetPrice(parentRow);
				params.taxType = 0;
				params.record = parentRow.find('.rowName .sourceField').val();
				params.recordModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
			}
			var progressInstace = jQuery.progressIndicator();
			AppConnector.request(params).then(
					function (data) {
						app.showModalWindow(data, function (data) {
							thisInstance.initTaxParameters(parentRow, $(data));
							thisInstance.registerChangeTaxModal(data, parentRow, params);
						});
						progressInstace.hide();
					},
					function (error, err) {
						progressInstace.hide();
						console.error(error, err);
					}
			);
		});
	},
	lockCurrencyChange: false,
	registerChangeCurrency: function (container) {
		var thisInstance = this;
		container.on('change', '[name="currency"]', function (e) {
			var element = $(e.currentTarget);
			var symbol = element.find('option:selected').data('conversionSymbol');
			thisInstance.currencyChangeActions(element, element.find('option:selected'));
			container.find('.currencySymbol').text(symbol);
		});
	},
	registerChangeTaxModal: function (modal, parentRow, params) {
		var thisInstance = this;
		modal.on('change', '.individualTaxType', function (e) {
			var element = $(e.currentTarget);
			modal.find('.individualTaxContainer .input-group-addon').text(element.data('symbol'));
		});
		modal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
			var element = $(e.currentTarget);

			if (element.attr('type') == 'checkbox' && this.checked) {
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else if (element.attr('type') == 'radio') {
				modal.find('.panel').removeClass('activepanel');
				modal.find('.panel .panel-body').hide();
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else {
				element.closest('.panel').find('.panel-body').hide();
				element.closest('.panel').removeClass('activepanel');
			}
		});
		modal.on('change', '.activeCheckbox, .globalTax, .individualTaxValue, .groupTax, .regionalTax', function (e) {
			thisInstance.calculateTax(parentRow, modal);
		});
		modal.on('click', '.saveTaxs', function (e) {
			thisInstance.saveTaxsParameters(parentRow, modal);
			if (params.taxType == '0') {
				thisInstance.setTax(parentRow, app.parseNumberToFloat(modal.find('.valueTax').text()));
				thisInstance.quantityChangeActions(parentRow);
			} else {
				var rate = app.parseNumberToFloat(modal.find('.valueTax').text()) / app.parseNumberToFloat(modal.find('.valueNetPrice').text());
				parentRow.find(thisInstance.rowClass).each(function (index) {
					var totalPrice;
					if ($('.netPrice', $(this)).length > 0) {
						totalPrice = thisInstance.getNetPrice($(this));
					} else if ($('.totalPrice', $(this)).length > 0) {
						totalPrice = thisInstance.getTotalPrice($(this));
					}
					thisInstance.setTax($(this), totalPrice * rate);
					thisInstance.quantityChangeActions($(this));
				});
			}
			app.hideModalWindow();
		});
	},
	registerRowAutoComplete: function (container) {
		var thisInstance = this;
		var sourceFieldElement = container.find('.sourceField');
		sourceFieldElement.on(Vtiger_Edit_Js.postReferenceSelectionEvent, function (e, params) {
			var record;
			var element = $(e.currentTarget);
			var parentRow = element.closest(thisInstance.rowClass);
			if (params.data.label) {
				record = params.data.id;
			} else {
				for (var id in params.data) {
					record = id;
				}
			}
			var selectedModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
			var dataUrl = "index.php?module=" + app.getModuleName() + "&action=Inventory&mode=getDetails&record=" + record + "&currency_id=" + thisInstance.getCurrency() + '&fieldname=' + element.data('columnname');
			AppConnector.request(dataUrl).then(function (data) {
				for (var id in data) {
					if (typeof data[id] == "object") {
						var recordData = data[id];
						thisInstance.mapResultsToFields(selectedModule, parentRow, recordData);
					}
				}
			});
		});
	},
	calculateItemNumbers: function () {
		var thisInstance = this;
		var items = this.getInventoryItemsContainer();
		var i = 1;
		items.find(thisInstance.rowClass).each(function (index) {
			$(this).find('.itemNumberText').text(i);
			i++;
		});
	},
	initItem: function (container) {
		var thisInstance = this;
		if (typeof container == 'undefined') {
			container = thisInstance.getInventoryItemsContainer();
		}
		thisInstance.registerDeleteLineItemEvent(container);
		thisInstance.registerPriceBookPopUp(container);
		thisInstance.registerRowChangeEvent(container);
		thisInstance.registerRowAutoComplete(container);
		thisInstance.checkDeleteIcon();
		thisInstance.rowsCalculations();
		thisInstance.updateRowSequence();
	},
	registerChangeQtyparam: function (container) {
		container.on('click', '.qtyparamButton', function (e) {
			var element = $(e.currentTarget);
			var rowNum = element.data('rownum');
			var qtyParamInput = $('input[name="qtyparam' + rowNum + '"]');
			if (qtyParamInput.is(':checked')) {
				element.removeClass('active');
				qtyParamInput.prop('checked', false);
			} else {
				element.addClass('active');
				qtyParamInput.prop('checked', true);
			}
		});
	},
	/**
	 * Function which will register all the events
	 */
	registerEvents: function (container) {
		this.registerInventorySaveData(container);
		this.registerAddItem(container);

		this.initItem();
		this.registerSortableItems();
		this.registerSubProducts(container);
		this.registerChangeDiscount(container);
		this.registerChangeTax(container);
		this.registerClearReferenceSelection(container);
		this.registerShowHideExpanded(container);
		this.registerChangeCurrency(container);
		this.registerChangeQtyparam(container);
	}
});
jQuery(document).ready(function () {
	var moduleName = app.getModuleName();
	var moduleClassName = moduleName + "_Inventory_Js";
	if (typeof window[moduleClassName] == 'undefined') {
		moduleClassName = "Vtiger_Inventory_Js";
	}
	if (typeof window[moduleClassName] != 'undefined') {
		var inventoryController = new window[moduleClassName]();
		inventoryController.registerEvents(Vtiger_Edit_Js.getInstance().getForm());
	}
});
