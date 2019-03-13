/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class("Vtiger_Inventory_Js", {
	inventoryInstance: false,
	/**
	 * Get inventory instance
	 * @param {jQuery} container
	 * @returns {Vtiger_Inventory_Js}
	 */
	getInventoryInstance: function (container) {
		if (this.inventoryInstance === false) {
			let moduleClassName = container.find('[name="module"]').val() + "_Inventory_Js";
			if (typeof window[moduleClassName] === "undefined") {
				moduleClassName = "Vtiger_Inventory_Js";
			}
			if (typeof window[moduleClassName] !== "undefined") {
				this.inventoryInstance = new window[moduleClassName]();
				this.inventoryInstance.registerEvents(container);
			}
		}
		return this.inventoryInstance;
	},
}, {
	form: false,
	inventoryContainer: false,
	inventoryHeadContainer: false,
	summaryTaxesContainer: false,
	summaryDiscountContainer: false,
	summaryCurrenciesContainer: false,
	rowClass: 'tr.inventoryRow',
	discountModalFields: ['aggregationType', 'globalDiscount', 'groupCheckbox', 'groupDiscount', 'individualDiscount', 'individualDiscountType'],
	taxModalFields: ['aggregationType', 'globalTax', 'groupCheckbox', 'groupTax', 'individualTax'],
	/**
	 * Get current form element
	 * @returns {jQuery}
	 */
	getForm() {
		return this.form;
	},
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
		var rowNumber = parseInt($inventoryItemsNo.val()) + 1;
		$inventoryItemsNo.val(rowNumber);
		return rowNumber;
	},
	getAccountId: function () {
		var accountReferenceField = $('#accountReferenceField').val();
		if (accountReferenceField != '') {
			return $('[name="' + accountReferenceField + '"]').val();
		}
		return '';
	},
	checkDeleteIcon: function () {
		if (this.getInventoryItemsContainer().find(this.rowClass).length > 1) {
			this.showLineItemsDeleteIcon();
		} else if (app.getMainParams('isRequiredInventory')) {
			this.hideLineItemsDeleteIcon();
		}
	},
	showLineItemsDeleteIcon: function () {
		this.getInventoryItemsContainer().find('.deleteRow').removeClass('d-none');
	},
	hideLineItemsDeleteIcon: function () {
		this.getInventoryItemsContainer().find('.deleteRow').addClass('d-none');
	},
	getClosestRow: function (element) {
		return element.closest(this.rowClass);
	},
	/**
	 * Function which will return the basic row which can be used to add new rows
	 * @return jQuery object which you can use to
	 */
	getBasicRow: function () {
		return this.getForm().find('.js-inventory-base-item').eq(0).clone(true, true);
	},
	isRecordSelected: function (element) {
		var parentRow = element.closest('tr');
		var productField = parentRow.find('.recordLabel');
		return productField.validationEngine('validate');
	},
	getTaxModeSelectElement: function (row) {
		var items = this.getInventoryHeadContainer();
		if (items.find('thead .js-taxmode').length > 0) {
			return $('.js-taxmode');
		}
		if (row) {
			return row.find('.js-taxmode');
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
			groupTax.addClass('d-none');
			items.find('.changeTax').removeClass('d-none');
			newRow.find('.changeTax').removeClass('d-none');
			let parentRow = thisInstance.getInventoryItemsContainer();
			let taxParam = {aggregationType: 'global'};

			parentRow.find(thisInstance.rowClass).each(function () {
				let thisItem = $(this);
				taxParam['globalTax'] = App.Fields.Double.formatToDb(thisItem.find('.js-tax').attr('data-default-tax'));
				thisInstance.setTaxParam(thisItem, taxParam);
			});
		} else {
			thisInstance.setTax(items, 0);
			thisInstance.setTaxParam(items, []);
			thisInstance.setDefaultGlobalTax(row);
			groupTax.removeClass('d-none');
			items.find('.changeTax').addClass('d-none');
			newRow.find('.changeTax').addClass('d-none');
		}
		thisInstance.rowsCalculations();
	},
	setDefaultGlobalTax: function (row) {
		let thisInstance = this;
		let parentRow = thisInstance.getInventoryItemsContainer();
		let taxDefaultValue = thisInstance.getInventorySummaryTaxesContainer().find('.js-default-tax').data('tax-default-value');
		let isGroupTax = thisInstance.isGroupTaxMode();
		if (isGroupTax) {
			if (taxDefaultValue) {
				let taxParam = {aggregationType: 'global'};
				taxParam['globalTax'] = App.Fields.Double.formatToDisplay(taxDefaultValue);
				taxParam['individualTax'] = '';
				thisInstance.setTaxParam($('#blackIthemTable'), taxParam);
				thisInstance.setTaxParam(parentRow, taxParam);
				parentRow.closest('.inventoryItems').data('taxParam', JSON.stringify(taxParam));
				parentRow.find(thisInstance.rowClass).each(function () {
					thisInstance.quantityChangeActions($(this));
				});
			}
		} else {
			thisInstance.setTaxParam($('#blackIthemTable'), []);
			parentRow.closest('.inventoryItems').data('taxParam', []);
		}
	},
	getDiscountModeSelectElement: function (row) {
		var items = this.getInventoryHeadContainer();
		if (items.find('thead .js-discountmode').length > 0) {
			return $('.js-discountmode');
		}
		return row.find('.js-discountmode');
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
		if (thisInstance.isIndividualDiscountMode(row)) {
			groupDiscount.addClass('d-none');
			items.find('.changeDiscount').removeClass('d-none');
			newRow.find('.changeDiscount').removeClass('d-none');
		} else {
			groupDiscount.removeClass('d-none');
			items.find('.changeDiscount').addClass('d-none');
			newRow.find('.changeDiscount').addClass('d-none');
		}
		thisInstance.setDiscount(items, 0);
		thisInstance.setDiscountParam(items, []);
		thisInstance.rowsCalculations();
	},
	getCurrency: function () {
		let currency = $('.js-currency', this.getInventoryHeadContainer());
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
			purchase = App.Fields.Double.formatToDb(element.val());
		}
		return purchase * qty;
	},
	getSummaryGrossPrice: function () {
		var thisInstance = this;
		var price = 0;
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			price += thisInstance.getGrossPrice($(this));
		});
		return App.Fields.Double.formatToDb(price);
	},
	/**
	 * Set currency
	 * @param {int} val
	 */
	setCurrency(val) {
		this.getInventoryHeadContainer().find('.js-currency').val(val).trigger('change');
	},
	/**
	 * Set currency param
	 * @param {string} val json string
	 */
	setCurrencyParam(val) {
		this.getInventoryHeadContainer().find('.js-currencyparam').val(val);
	},
	/**
	 * Set discount mode
	 * @param {int} val
	 */
	setDiscountMode(val) {
		this.getInventoryHeadContainer().find('.js-discountmode').val(val).trigger('change');
	},
	/**
	 * Set tax mode
	 * @param {int} val
	 */
	setTaxMode(val) {
		this.getInventoryHeadContainer().find('.js-taxmode').val(val).trigger('change');
	},
	/**
	 * Set inventory id
	 * @param {jQuery} row
	 * @param {int} val
	 * @param {string} display
	 */
	setName(row, val, display) {
		row.find('.js-name').val(val).trigger('change');
		row.find('.js-name_display').val(display).attr('readonly', 'true').trigger('change');
	},
	/**
	 * Set inventory row quantity
	 * @param {jQuery} row
	 * @param {int} val
	 */
	setQuantity(row, val) {
		row.find('.qty').val(val).trigger('change');
	},
	/**
	 * Set unit original (db) value
	 * @param {jQuery} row
	 * @param {string} val
	 * @param {string} display
	 */
	setUnit(row, val, display) {
		row.find('.unit').val(val).trigger('change');
		row.find('.unitText').text(display).trigger('change');
	},
	/**
	 * Set subUnit original (db) value
	 * @param {jQuery} row
	 * @param {string} val
	 * @param {string} display
	 */
	setSubUnit(row, val, display) {
		row.find('.subunit').val(val);
		row.find('.subunitText').val(display);
	},
	/**
	 * Set inventory row comment
	 * @param {jQuery} row
	 * @param {string} val
	 */
	setComment(row, val) {
		row.parent().find('[numrowex=' + row.attr('numrow') + ']').find('.comment').val(val).trigger('change');
	},
	setUnitPrice: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		row.find('.unitPrice').val(val).attr('title', val);
		return this;
	},
	setNetPrice: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		$('.netPriceText', row).text(val);
		$('.netPrice', row).val(val);
	},
	setGrossPrice: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		$('.grossPriceText', row).text(val);
		$('.grossPrice', row).val(val);
	},
	setTotalPrice: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		$('.totalPriceText', row).text(val);
		$('.totalPrice', row).val(val);
	},
	setMargin: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		$('.margin', row).val(val);
	},
	setMarginP: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		$('.marginp', row).val(val);
	},
	setDiscount: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
		$('.discount', row).val(val);
	},
	setDiscountParam: function (row, val) {
		$('.discountParam', row).val(JSON.stringify(val));
	},
	setTax: function (row, val) {
		val = App.Fields.Double.formatToDisplay(val);
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
						discountRate += App.Fields.Double.formatToDb(discountValue);
					}
				}
				if (entry == 'global') {
					discountRate += valuePrices * (App.Fields.Double.formatToDb(discountParams.globalDiscount) / 100);
				}
				if (entry == 'group') {
					discountRate += valuePrices * (App.Fields.Double.formatToDb(discountParams.groupDiscount) / 100);
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
				taxRate += valuePrices * (App.Fields.Double.formatToDb(taxValue) / 100);
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
				sum += App.Fields.Double.formatToDb(element.val());
			}
		});
		element.text(App.Fields.Double.formatToDisplay(sum));
	},
	calculatMarginPSummary: function () {
		var thisInstance = this;
		var purchase = 0;
		var totalOrNet = 0;
		var sumRow = thisInstance.getInventoryItemsContainer().find('tfoot');
		var total = App.Fields.Double.formatToDb(sumRow.find('[data-sumfield="totalPrice"]').text());
		var netPrice = App.Fields.Double.formatToDb(sumRow.find('[data-sumfield="netPrice"]').text());
		this.getInventoryItemsContainer().find(thisInstance.rowClass).each(function (index) {
			var qty = $(this).find('.qty');
			var purchasPrice = $(this).find('.purchase');
			if ((qty.length > 0) && (purchasPrice.length > 0)) {
				purchase += App.Fields.Double.formatToDb(qty.val()) * App.Fields.Double.formatToDb(purchasPrice.val());
			}
		})
		if (netPrice != total) {
			totalOrNet += App.Fields.Double.formatToDb(netPrice);
		} else {
			totalOrNet += App.Fields.Double.formatToDb(total);
		}
		var marginp = '0';
		if (purchase !== 0) {
			var subtraction = (totalOrNet - purchase);
			marginp = (subtraction / totalOrNet) * 100;
		}
		sumRow.find('[data-sumfield="marginP"]').text(App.Fields.Double.formatToDisplay(marginp))
	},
	calculatDiscountSummary: function () {
		var thisInstance = this;
		var discount = thisInstance.getAllDiscount();
		var container = thisInstance.getInventorySummaryDiscountContainer();
		container.find('input').val(App.Fields.Double.formatToDisplay(discount));
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
		let container = this.getInventorySummaryCurrenciesContainer(),
			selected = $('.js-currency option:selected', this.getInventoryHeadContainer()),
			base = $('.js-currency option[data-base-currency="1"]', this.getInventoryHeadContainer()),
			conversionRate = selected.data('conversionRate'),
			baseConversionRate = base.data('conversionRate');
		if (conversionRate == baseConversionRate) {
			container.addClass('d-none');
			return;
		}
		conversionRate = parseFloat(baseConversionRate) / parseFloat(conversionRate);
		container.removeClass('d-none');
		var taxs = this.getAllTaxs();
		var sum = 0;
		container.find('.js-panel__body').html('');
		$.each(taxs, function (index, value) {
			if (value != undefined) {
				value = value * conversionRate;
				var row = container.find('.d-none .form-group').clone();
				row.find('.percent').text(index + '%');
				row.find('input').val(App.Fields.Double.formatToDisplay(value));
				row.appendTo(container.find('.js-panel__body'));
				sum += value;
			}
		});
		container.find('.js-panel__footer input').val(App.Fields.Double.formatToDisplay(sum));
	},
	calculatTaxSummary: function () {
		var thisInstance = this;
		var taxs = thisInstance.getAllTaxs();
		var container = thisInstance.getInventorySummaryTaxesContainer();
		container.find('.js-panel__body').html('');
		var sum = 0;
		for (var index in taxs) {
			var row = container.find('.d-none .form-group').clone();
			row.find('.percent').text(App.Fields.Double.formatToDisplay(App.Fields.Double.formatToDb(index)) + '%');
			row.find('input').val(App.Fields.Double.formatToDisplay(taxs[index]));
			row.appendTo(container.find('.js-panel__body'));
			sum += taxs[index];
		}
		container.find('.js-panel__footer input').val(App.Fields.Double.formatToDisplay(sum));
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
				var param = JSON.parse(params);
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
						var taxRate = netPrice * (App.Fields.Double.formatToDb(precent) / 100);
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
		if ($('.netPrice', row).length) {
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
		var netPriceBeforeDiscount = App.Fields.Double.formatToDb(modal.find('.valueTotalPrice').text()),
			valuePrices = netPriceBeforeDiscount,
			globalDiscount = 0,
			groupDiscount = 0,
			individualDiscount = 0,
			valueDiscount = 0;

		var discountsType = modal.find('.discountsType').val();

		if (discountsType == '0' || discountsType == '1') {
			if (modal.find('.js-active .globalDiscount').length > 0) {
				globalDiscount = App.Fields.Double.formatToDb(modal.find('.js-active .globalDiscount').val());
			}
			if (modal.find('.js-active .individualDiscountType').length > 0) {
				var individualTypeDiscount = modal.find('.js-active .individualDiscountType:checked').val();
				var value = App.Fields.Double.formatToDb(modal.find('.js-active .individualDiscountValue').val());
				if (individualTypeDiscount == 'percentage') {
					individualDiscount = netPriceBeforeDiscount * (value / 100);
				} else {
					individualDiscount = value;
				}
			}
			if (modal.find('.js-active .groupCheckbox').length > 0 && modal.find('.js-active .groupCheckbox').prop("checked") == true) {
				groupDiscount = App.Fields.Double.formatToDb(modal.find('.groupValue').val());
				groupDiscount = netPriceBeforeDiscount * (groupDiscount / 100);
			}

			valuePrices = valuePrices * ((100 - globalDiscount) / 100);
			valuePrices = valuePrices - individualDiscount;
			valuePrices = valuePrices - groupDiscount;
		} else if (discountsType == '2') {
			modal.find('.js-active').each(function (index) {
				var panel = $(this);
				if (panel.find('.globalDiscount').length > 0) {
					var globalDiscount = App.Fields.Double.formatToDb(panel.find('.globalDiscount').val());
					valuePrices = valuePrices * ((100 - globalDiscount) / 100);
				} else if (panel.find('.groupCheckbox').length > 0 && panel.find('.groupCheckbox').prop("checked") == true) {
					var groupDiscount = App.Fields.Double.formatToDb(panel.find('.groupValue').val());
					valuePrices = valuePrices * ((100 - groupDiscount) / 100);
				} else if (panel.find('.individualDiscountType').length > 0) {
					var value = App.Fields.Double.formatToDb(panel.find('.individualDiscountValue').val());
					if (panel.find('.individualDiscountType[name="individualDiscountType"]:checked').val() == 'percentage') {
						valuePrices = valuePrices * ((100 - value) / 100);
					} else {
						valuePrices = valuePrices - value;
					}
				}
			});
		}

		modal.find('.valuePrices').text(App.Fields.Double.formatToDisplay(valuePrices));
		modal.find('.valueDiscount').text(App.Fields.Double.formatToDisplay(netPriceBeforeDiscount - valuePrices));
	},
	calculateTax: function (row, modal) {
		var netPriceWithoutTax = App.Fields.Double.formatToDb(modal.find('.valueNetPrice').text()),
			valuePrices = netPriceWithoutTax,
			globalTax = 0,
			groupTax = 0,
			regionalTax = 0,
			individualTax = 0;

		var taxType = modal.find('.taxsType').val();
		if (taxType == '0' || taxType == '1') {
			if (modal.find('.js-active .globalTax').length > 0) {
				globalTax = App.Fields.Double.formatToDb(modal.find('.js-active .globalTax').val());
			}
			if (modal.find('.js-active .individualTaxValue').length > 0) {
				var value = App.Fields.Double.formatToDb(modal.find('.js-active .individualTaxValue').val());
				individualTax = (value / 100) * valuePrices;
			}
			if (modal.find('.js-active .groupTax').length > 0) {
				groupTax = App.Fields.Double.formatToDb(modal.find('.groupTax').val());
				groupTax = netPriceWithoutTax * (groupTax / 100);
			}
			if (modal.find('.js-active .regionalTax').length > 0) {
				regionalTax = App.Fields.Double.formatToDb(modal.find('.regionalTax').val());
				regionalTax = netPriceWithoutTax * (regionalTax / 100);
			}

			valuePrices = valuePrices * ((100 + globalTax) / 100);
			valuePrices = valuePrices + individualTax;
			valuePrices = valuePrices + groupTax;
			valuePrices = valuePrices + regionalTax;
		} else if (taxType == '2') {
			modal.find('.js-active').each(function (index) {
				var panel = $(this);
				if (panel.find('.globalTax').length > 0) {
					var globalTax = App.Fields.Double.formatToDb(panel.find('.globalTax').val());
					valuePrices = valuePrices * ((100 + globalTax) / 100);
				} else if (panel.find('.groupTax').length > 0) {
					var groupTax = App.Fields.Double.formatToDb(panel.find('.groupTax').val());
					valuePrices = valuePrices * ((100 + groupTax) / 100);
				} else if (panel.find('.regionalTax').length > 0) {
					var regionalTax = App.Fields.Double.formatToDb(panel.find('.regionalTax').val());
					valuePrices = valuePrices * ((100 + regionalTax) / 100);
				} else if (panel.find('.individualTaxValue').length > 0) {
					var value = App.Fields.Double.formatToDb(panel.find('.individualTaxValue').val());
					valuePrices = ((value + 100) / 100) * valuePrices;
				}
			});
		}
		if (netPriceWithoutTax) {
			let taxValue = (valuePrices - netPriceWithoutTax) / netPriceWithoutTax * 100;
			modal.find('.js-tax-value').text(App.Fields.Double.formatToDisplay(taxValue));
		}
		modal.find('.valuePrices').text(App.Fields.Double.formatToDisplay(valuePrices));
		modal.find('.valueTax').text(App.Fields.Double.formatToDisplay(valuePrices - netPriceWithoutTax));
	},
	updateRowSequence: function () {
		var items = this.getInventoryItemsContainer();
		items.find(this.rowClass).each(function (index) {
			$(this).find('.sequence').val(index + 1);
		});
	},
	registerInventorySaveData: function () {
		const thisInstance = this;
		thisInstance.form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			thisInstance.syncHeaderData();
			if (!thisInstance.checkLimits(thisInstance.form)) {
				return false;
			}
			let table = thisInstance.form.find('#blackIthemTable');
			table.find('[name]').removeAttr('name');
		});
	},
	syncHeaderData() {
		let header = this.getInventoryHeadContainer();
		this.getInventoryItemsContainer().find('.js-sync').each(function () {
			let element = $(this);
			element.val(header.find('.js-' + element.data('syncId')).val());
		});
	},
	/**
	 * Function which will be used to handle price book popup
	 * @params :  element - popup image element
	 */
	pricebooksModalHandler: function (element) {
		const thisInstance = this;
		let lineItemRow = element.closest(this.rowClass);
		let rowName = lineItemRow.find('.rowName');
		app.showRecordsList({
			module: 'PriceBooks',
			src_module: $('[name="popupReferenceModule"]', rowName).val(),
			src_record: $('.sourceField', rowName).val(),
			src_field: $('[name="popupReferenceModule"]', rowName).data('field'),
			currency_id: thisInstance.getCurrency(),
		}, (modal, instance) => {
			instance.setSelectEvent((responseData) => {
				AppConnector.request({
					module: 'PriceBooks',
					action: 'ProductListPrice',
					record: responseData.id,
					src_record: $('.sourceField', rowName).val(),
				}).done(function (data) {
					if (data.result) {
						thisInstance.setUnitPrice(lineItemRow, data.result);
						thisInstance.quantityChangeActions(lineItemRow);
					} else {
						app.errorLog('Incorrect data', responseData);
					}
				});
			});
		});
	},
	subProductsCashe: [],
	loadSubProducts: function (parentRow, indicator) {
		var thisInstance = this;
		var recordId = $('input.sourceField', parentRow).val();
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
			var progressInstace = $.progressIndicator();
		}
		AppConnector.request(subProrductParams).done(function (data) {
			var responseData = data.result;
			thisInstance.subProductsCashe[recordId] = responseData;
			thisInstance.addSubProducts(parentRow, responseData);
			if (progressInstace) {
				progressInstace.hide();
			}
		}).fail(function (error, err) {
			if (progressInstace) {
				progressInstace.hide();
			}
		});
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
		let unit, unitPrice, taxParam = [];
		var thisInstance = this;
		var isGroupTax = thisInstance.isGroupTaxMode();
		for (var id in responseData) {
			var recordData = responseData[id];
			var description = recordData.description;
			var unitPriceValues = recordData.unitPriceValues;
			var unitPriceValuesJson = JSON.stringify(unitPriceValues);
			// Load taxses detail
			if (isGroupTax) {
				var parameters = parentRow.closest('.inventoryItems').data('taxParam');
				if (parameters) {
					taxParam = JSON.parse(parameters);
				}
			} else if (recordData['taxes']) {
				taxParam = {aggregationType: recordData.taxes.type};
				taxParam[recordData.taxes.type + 'Tax'] = recordData.taxes.value;
			}
			if (recordData['taxes']) {
				parentRow.find('.js-tax').attr('data-default-tax', App.Fields.Double.formatToDisplay(recordData.taxes.value));
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
			let currencyId = thisInstance.getCurrency();
			if (currencyId && unitPriceValues && typeof unitPriceValues[currencyId] !== "undefined") {
				unitPrice = App.Fields.Double.formatToDb(unitPriceValues[currencyId]);
			} else if (recordData.price !== undefined) {
				unitPrice = recordData.price;
			}
			if (unitPrice) {
				thisInstance.setUnitPrice(parentRow, unitPrice);
			}
			if (unitPriceValuesJson !== undefined) {
				$('input.unitPrice', parentRow).attr('list-info', unitPriceValuesJson);
			}
			var commentElement = $('textarea.commentTextarea', parentRow.next());
			var editorInstance = CKEDITOR.instances[commentElement.attr('id')];
			if (editorInstance) {
				editorInstance.setData(description);
			} else {
				commentElement.val(description);
			}
			if (typeof recordData['autoFields']['unit'] !== "undefined") {
				unit = recordData['autoFields']['unit'];
			}
			this.triggerQtyParam(unit, recordData.qtyPerUnit, parentRow);
		}
		if (referenceModule === 'Products') {
			thisInstance.loadSubProducts(parentRow, true);
		}
		thisInstance.quantityChangeActions(parentRow);
	},
	/**
	 * Update qtyparam
	 * @param {null|string} unit
	 * @param int perUnit
	 * @param {jQuery} parentRow
	 */
	triggerQtyParam(unit, perUnit, parentRow) {
		let validationEngine;
		switch (unit) {
			default:
				$('.qtyParamInfo', parentRow).addClass('d-none');
				validationEngine = 'validate[required,funcCall[Vtiger_NumberUserFormat_Validator_Js.invokeValidation]]';
				break;
			case 'pack':
				$('.qtyParamInfo', parentRow).removeClass('d-none').removeClass('active');
				$('.qtyParamInfo', parentRow).attr('data-content', perUnit);
				validationEngine = 'validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]';
				break;
			case 'pcs':
				$('.qtyParamInfo', parentRow).addClass('d-none');
				validationEngine = 'validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]';
				break;
		}
		$('input.qty', parentRow).attr('data-validation-engine', validationEngine);
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
				var value = modal.find('[name="' + param + '"]').val()
				if (param === 'individualDiscount') {
					value = App.Fields.Double.formatToDb(modal.find('[name="' + param + '"]').val());
				}
				info[param] = value;
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
		parentRow.data('taxParam', JSON.stringify(info));
		thisInstance.setTaxParam(parentRow, info);
		thisInstance.setTaxParam($('#blackIthemTable'), info);
	},
	showExpandedRow: function (row) {
		var thisInstance = this;
		var items = thisInstance.getInventoryItemsContainer();
		var inventoryRowExpanded = items.find('[numrowex="' + row.attr('numrow') + '"]');
		var element = row.find('.toggleVisibility');
		element.data('status', '1');
		inventoryRowExpanded.removeClass('d-none');
		var listInstance = Vtiger_Edit_Js.getInstance();
		$.each(inventoryRowExpanded.find('.js-editor'), function (key, data) {
			listInstance.loadEditorElement($(data));
		});
	},
	hideExpandedRow: function (row) {
		var thisInstance = this;
		var items = thisInstance.getInventoryItemsContainer();
		var inventoryRowExpanded = items.find('[numrowex="' + row.attr('numrow') + '"]');
		var element = row.find('.toggleVisibility');
		element.data('status', '0');
		inventoryRowExpanded.addClass('d-none');
		$.each(inventoryRowExpanded.find('.js-editor'), function (key, data) {
			var editorInstance = CKEDITOR.instances[$(data).attr('id')];
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
						value.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
						value.closest('.js-panel').addClass('js-active');
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
		const thisInstance = this;
		let parameters;
		if (parentRow.data('taxParam')) {
			parameters = parentRow.data('taxParam');
		} else {
			parameters = parentRow.find('.taxParam').val();
		}
		if (!parameters) {
			return;
		}
		parameters = JSON.parse(parameters.toString());
		$.each(thisInstance.taxModalFields, function (index, param) {
			let parameter = parameters[param],
				field = modal.find('[name="' + param + '"]');

			if (field.attr('type') === 'checkbox' || field.attr('type') === 'radio') {
				let array = parameter,
					value;
				if (!$.isArray(array)) {
					array = [array];
				}
				$.each(array, function (index, arrayValue) {
					value = field.filter('[value="' + arrayValue + '"]').prop('checked', true);
					if (param === 'aggregationType') {
						value.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
						value.closest('.js-panel').addClass('js-active');
					}
				});
			} else if (field.prop("tagName") === 'SELECT') {
				field.find('option[value="' + parameter + '"]').prop('selected', 'selected').change();
			} else {
				let input = modal.find('[name="' + param + '"]')
				input.val(parameter);
				if (param === 'individualTax') {
					input.formatNumber();
				}
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
		var progressInstace = $.progressIndicator();
		AppConnector.request(params).done(function (data) {
			progressInstace.hide();
			if (data.result.status == false) {
				app.showModalWindow(data.result.html, function (data) {
				});
				response = false;
			}
		}).fail(function (error, err) {
				progressInstace.hide();
			}
		);
		return response;
	},
	currencyChangeActions: function (select, option) {
		if (option.data('baseCurrency') !== select.val()) {
			this.showCurrencyChangeModal(select, option);
		} else {
			this.currencyConvertValues(select, option);
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
			var currencyParam = JSON.parse(block.find('.js-currencyparam').val());

			if (currencyParam != false) {
				if (typeof currencyParam[option.val()] === "undefined") {
					var defaultCurrencyParams = {
						value: 1,
						date: ''
					};
					currencyParam[option.val()] = defaultCurrencyParams;
				}
				modal.find('.currencyName').text(option.text());
				modal.find('.currencyRate').val(currencyParam[option.val()]['value']);
				modal.find('.currencyDate').text(currencyParam[option.val()]['date']);
			}
			modal.on('click', 'button[type="submit"]', function (e) {
				var rate = modal.find('.currencyRate').val();
				var value = App.Fields.Double.formatToDb(rate);
				var conversionRate = 1 / App.Fields.Double.formatToDb(rate);

				option.data('conversionRate', conversionRate);
				currencyParam[option.val()] = {
					date: option.data('conversionDate'),
					value: value.toString(),
					conversion: conversionRate.toString()
				};
				block.find('.js-currencyparam').val(JSON.stringify(currencyParam));

				thisInstance.currencyConvertValues(select, option);
				select.data('oldValue', select.val());
				app.hideModalWindow();
				thisInstance.lockCurrencyChange = false;
			}).one('hidden.bs.modal', function () {
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

			thisInstance.setUnitPrice(row, App.Fields.Double.formatToDb(thisInstance.getUnitPriceValue(row) * conversionRate));
			thisInstance.setDiscount(row, App.Fields.Double.formatToDb(thisInstance.getDiscount(row) * conversionRate));
			thisInstance.setTax(row, App.Fields.Double.formatToDb(thisInstance.getTax(row) * conversionRate));
			thisInstance.quantityChangeActions(row);
		});
	},
	/**
	 * Set up all row data that comes from request
	 * @param {jQuery} row
	 * @param {object} rowData
	 */
	setRowData(row, rowData) {
		this.setName(row, rowData.name, rowData.info.name);
		this.setQuantity(row, rowData.qty);
		this.setUnit(row, rowData.info.autoFields.unit, rowData.info.autoFields.unitText);
		if (typeof rowData.info.autoFields !== 'undefined' && typeof rowData.info.autoFields.subunit !== 'undefined') {
			this.setSubUnit(row, rowData.info.autoFields.subunit, rowData.info.autoFields.subunitText);
		}
		this.setComment(row, rowData.comment1);
		this.setUnitPrice(row, rowData.price);
		this.setNetPrice(row, rowData.net);
		this.setGrossPrice(row, rowData.gross);
		this.setTotalPrice(row, rowData.total);
		this.setDiscountParam(row, JSON.parse(rowData.discountparam));
		this.setDiscount(row, rowData.discount);
		this.setTaxParam(row, JSON.parse(rowData.taxparam));
		this.setTax(row, rowData.tax);
	},
	/**
	 * Add new row to inventory list
	 * @param {string} module
	 * @param {string} baseTableId
	 * @param {object} rowData [optional]
	 */
	addItem(module, baseTableId, rowData = false) {
		const items = this.getInventoryItemsContainer();
		let newRow = this.getBasicRow();
		const sequenceNumber = this.getNextLineItemRowNumber();
		const replaced = newRow.html().replace(/\_NUM_/g, sequenceNumber);
		newRow.html(replaced);
		newRow = newRow.children().appendTo(items.find('.js-inventory-items-body'));
		newRow.find('.rowName input[name="popupReferenceModule"]').val(module).data('field', baseTableId);
		newRow.find('.js-module-icon').removeClass().addClass(`userIcon-${module}`);
		newRow.find('.colPicklistField select').each(function (index, select) {
			select = $(select);
			select.find('option').each(function (index, option) {
				option = $(option);
				if (option.data('module') !== module) {
					option.remove();
				}
			});
		});
		this.initItem(newRow);
		Vtiger_Edit_Js.getInstance().registerAutoCompleteFields(newRow);
		if (rowData) {
			this.setRowData(newRow, rowData);
		}
	},

	/**
	 * Register add item button click
	 * @param {jQuery} container
	 */
	registerAddItem() {
		const thisInstance = this;
		thisInstance.form.find('.js-add-item').on('click', function (e) {
			const btn = $(this);
			thisInstance.addItem(btn.data('module'), btn.data('field'));
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
				var numrow = $(ui.item).attr('numrow');
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
	registerShowHideExpanded: function () {
		const thisInstance = this;
		thisInstance.form.on('click', '.toggleVisibility', function (e) {
			var element = $(e.currentTarget);
			var row = thisInstance.getClosestRow(element);
			if (element.data('status') == 0) {
				thisInstance.showExpandedRow(row);
			} else {
				thisInstance.hideExpandedRow(row);
			}
		});
	},
	registerPriceBookModal: function (container) {
		var thisInstance = this;
		container.on('click', '.js-price-book-modal', function (e) {
			var element = $(e.currentTarget);
			var response = thisInstance.isRecordSelected(element);
			if (response == true) {
				return;
			}
			thisInstance.pricebooksModalHandler(element);
		});
	},
	registerRowChangeEvent: function (container) {
		container.on('focusout', '.qty', (e) => {
			let element = $(e.currentTarget);
			element.formatNumber();
			this.quantityChangeActions(this.getClosestRow(element));
		});
		container.on('focusout', '.unitPrice', (e) => {
			let element = $(e.currentTarget);
			element.formatNumber();
			this.quantityChangeActions(this.getClosestRow(element));
		});
		container.on('focusout', '.purchase', (e) => {
			let element = $(e.currentTarget);
			element.formatNumber();
			this.quantityChangeActions(this.getClosestRow(element));
		});
		var headContainer = this.getInventoryHeadContainer();
		headContainer.on('change', '.js-taxmode', (e) => {
			let element = $(e.currentTarget);
			this.showIndividualTax(this.getClosestRow(element));
			this.rowsCalculations();
		});
		headContainer.on('change', '.js-discountmode', (e) => {
			let element = $(e.currentTarget);
			this.showIndividualDiscount(this.getClosestRow(element));
			this.rowsCalculations();
		});
	},
	registerSubProducts: function () {
		const thisInstance = this;
		thisInstance.form.find('.inventoryItems ' + thisInstance.rowClass).each(function (index) {
			thisInstance.loadSubProducts($(this), false);
		});
	},
	/**
	 * Register clear reference selection
	 */
	registerClearReferenceSelection() {
		this.form.on('click', '.clearReferenceSelection', (e) => {
			const row = this.getClosestRow($(e.currentTarget));
			this.removeSubProducts(row);
			row.find('.unitPrice,.tax,.discount,.margin,.purchase').val(App.Fields.Double.formatToDisplay(0));
			row.find('.qty').val(1);
			row.find('textarea,.valueVal').val('');
			row.find('.valueText').text('');
			row.find('.qtyParamInfo').addClass('d-none');
			row.find('.recordLabel').val('').removeAttr('readonly');
			if (!this.isGroupTaxMode()) {
				this.setTaxParam(row, []);
			}
			this.quantityChangeActions(row);
		});
	},
	registerDeleteLineItemEvent: function (container) {
		var thisInstance = this;
		container.on('click', '.deleteRow', function (e) {
			let num = thisInstance.getClosestRow($(e.currentTarget)).attr('numrow');
			thisInstance.getInventoryItemsContainer().find('[numrow="' + num + '"], [numrowex="' + num + '"]').remove();
			thisInstance.checkDeleteIcon();
			thisInstance.rowsCalculations();
			if (thisInstance.getInventoryItemsContainer().find('.inventoryRow').length === 0) {
				$('#inventoryItemsNo').val(0);
			}
			thisInstance.updateRowSequence();
		});
	},
	registerChangeDiscount: function () {
		var thisInstance = this;
		thisInstance.form.on('click', '.changeDiscount', function (e) {
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
					params.totalPrice = App.Fields.Double.formatToDb(parentRow.find('tfoot .colTotalPrice').text());
				} else {
					params.totalPrice = 0;
				}
				params.discountType = 1;
			} else {
				parentRow = element.closest(thisInstance.rowClass);
				params.totalPrice = thisInstance.getTotalPrice(parentRow);
				params.discountType = 0;
			}

			var progressInstace = $.progressIndicator();
			AppConnector.request(params).done(function (data) {
				app.showModalWindow(data, function (data) {
					thisInstance.initDiscountsParameters(parentRow, $(data));
					thisInstance.registerChangeDiscountModal(data, parentRow, params);
				});
				progressInstace.hide();
			}).fail(function (error, err) {
				progressInstace.hide();
			});
		});
	},
	registerChangeDiscountModal: function (modal, parentRow, params) {
		var thisInstance = this;
		modal.on('change', '.individualDiscountType', function (e) {
			var element = $(e.currentTarget);
			modal.find('.individualDiscountContainer .input-group-text').text(element.data('symbol'));
		});
		modal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
			var element = $(e.currentTarget);

			if (element.attr('type') == 'checkbox' && this.checked) {
				element.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
				element.closest('.js-panel').addClass('js-active');
			} else if (element.attr('type') == 'radio') {
				modal.find('.js-panel').removeClass('js-active');
				modal.find('.js-panel .js-panel__body').addClass('d-none');
				element.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
				element.closest('.js-panel').addClass('js-active');
			} else {
				element.closest('.js-panel').find('.js-panel__body').addClass('d-none');
				element.closest('.js-panel').removeClass('js-active');
			}
		});
		modal.on('change', '.activeCheckbox, .globalDiscount,.individualDiscountValue,.individualDiscountType,.groupCheckbox', function (e) {
			thisInstance.calculateDiscount(parentRow, modal);
		});
		modal.on('click', '.saveDiscount', function (e) {
			thisInstance.saveDiscountsParameters(parentRow, modal);
			if (params.discountType == 0) {
				thisInstance.setDiscount(parentRow, App.Fields.Double.formatToDb(modal.find('.valueDiscount').text()));
				thisInstance.quantityChangeActions(parentRow);
			} else {
				var rate = App.Fields.Double.formatToDb(modal.find('.valueDiscount').text()) / App.Fields.Double.formatToDb(modal.find('.valueTotalPrice').text());
				parentRow.find(thisInstance.rowClass).each(function (index) {
					thisInstance.setDiscount($(this), thisInstance.getTotalPrice($(this)) * rate);
					thisInstance.quantityChangeActions($(this));
				});
			}
			app.hideModalWindow();
		});
	},
	registerChangeTax: function () {
		const thisInstance = this;
		thisInstance.form.on('click', '.changeTax', function (e) {
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
				params.totalPrice = App.Fields.Double.formatToDb(totalPrice);
				params.taxType = 1;
			} else {
				parentRow = element.closest(thisInstance.rowClass);
				params.totalPrice = thisInstance.getNetPrice(parentRow);
				params.taxType = 0;
				params.record = parentRow.find('.rowName .sourceField').val();
				params.recordModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
			}
			var progressInstace = $.progressIndicator();
			AppConnector.request(params).done(function (data) {
				app.showModalWindow(data, function (data) {
					thisInstance.initTaxParameters(parentRow, $(data));
					thisInstance.registerChangeTaxModal(data, parentRow, params);
				});
				progressInstace.hide();
			}).fail(function (error, err) {
				progressInstace.hide();
			});
		});
	},
	lockCurrencyChange: false,
	registerChangeCurrency() {
		this.getInventoryHeadContainer().on('change', '.js-currency', (e) => {
			let element = $(e.currentTarget),
				symbol = element.find('option:selected').data('conversionSymbol');
			this.currencyChangeActions(element, element.find('option:selected'));
			this.form.find('.currencySymbol').text(symbol);
		});
	},
	registerChangeTaxModal: function (modal, parentRow, params) {
		var thisInstance = this;
		modal.on('change', '.individualTaxType', function (e) {
			var element = $(e.currentTarget);
			modal.find('.individualTaxContainer .input-group-text').text(element.data('symbol'));
		});
		modal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
			var element = $(e.currentTarget);

			if (element.attr('type') == 'checkbox' && this.checked) {
				element.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
				element.closest('.js-panel').addClass('js-active');
			} else if (element.attr('type') == 'radio') {
				modal.find('.js-panel').removeClass('js-active');
				modal.find('.js-panel .js-panel__body').addClass('d-none');
				element.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
				element.closest('.js-panel').addClass('js-active');
			} else {
				element.closest('.js-panel').find('.js-panel__body').addClass('d-none');
				element.closest('.js-panel').removeClass('js-active');
			}
		});
		modal.on('change', '.activeCheckbox, .globalTax, .individualTaxValue, .groupTax, .regionalTax', function (e) {
			thisInstance.calculateTax(parentRow, modal);
		});
		modal.on('click', '.saveTaxs', function (e) {
			thisInstance.saveTaxsParameters(parentRow, modal);
			if (params.taxType == '0') {
				thisInstance.setTax(parentRow, App.Fields.Double.formatToDb(modal.find('.valueTax').text()));
				thisInstance.quantityChangeActions(parentRow);
			} else {
				var rate = App.Fields.Double.formatToDb(modal.find('.valueTax').text()) / App.Fields.Double.formatToDb(modal.find('.valueNetPrice').text());
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
		const thisInstance = this;
		let sourceFieldElement = container.find('.sourceField.js-name');
		sourceFieldElement.on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, params) {
			var record = params.record;
			var element = $(e.currentTarget);
			var parentRow = element.closest(thisInstance.rowClass);
			var selectedModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
			var dataUrl = "index.php?module=" + app.getModuleName() + "&action=Inventory&mode=getDetails&record=" + record + '&fieldname=' + element.data('columnname');
			if (thisInstance.getCurrency()) {
				dataUrl += "&currency_id=" + thisInstance.getCurrency();
			}
			AppConnector.request(dataUrl).done(function (data) {
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
		let thisInstance = this;
		if (typeof container === "undefined") {
			container = thisInstance.getInventoryItemsContainer();
		}
		thisInstance.registerDeleteLineItemEvent(container);
		thisInstance.registerPriceBookModal(container);
		thisInstance.registerRowChangeEvent(container);
		thisInstance.registerRowAutoComplete(container);
		thisInstance.checkDeleteIcon();
		thisInstance.rowsCalculations();
		thisInstance.updateRowSequence();
		App.Fields.Picklist.showSelect2ElementView(container.find('.selectInv'));
		App.Fields.Date.register(container, true, {}, 'dateFieldInv');
		container.validationEngine('detach');
		container.validationEngine(app.validationEngineOptions);
	},
	/**
	 * Load inventory data for specified record
	 * @param {int} recordId
	 * @param {string} sourceModule
	 * @param {function|bool} success callback
	 * @param {function|bool} fail callback
	 * @returns Promise
	 */
	loadInventoryData(recordId, sourceModule, success = false, fail = false) {
		const progressLoader = $.progressIndicator({'blockInfo': {'enabled': true}});
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: sourceModule,
				action: 'Inventory',
				mode: 'getTableData',
				record: recordId
			}).done((response) => {
				let activeModules = [];
				this.getInventoryHeadContainer().find('.js-add-item').each((index, addBtn) => {
					activeModules.push($(addBtn).data('module'));
				});
				progressLoader.progressIndicator({mode: 'hide'});
				const oldCurrencyChangeAction = this.currencyChangeActions;
				this.currencyChangeActions = function changeCurrencyActions(select, option) {
					this.currencyConvertValues(select, option);
					select.data('oldValue', select.val());
				};
				const first = response.result[Object.keys(response.result)[0]];
				this.setCurrencyParam(first.currencyparam);
				this.setCurrency(first.currency);
				this.setDiscountMode(first.discountmode);
				this.setTaxMode(first.taxmode);
				this.currencyChangeActions = oldCurrencyChangeAction;
				$.each(response.result, (index, row) => {
					if (activeModules.indexOf(row.moduleName) !== -1) {
						this.addItem(row.moduleName, row.basetableid, row);
					} else {
						Vtiger_Helper_Js.showMessage({
							type: 'error',
							text: app.vtranslate('JS_INVENTORY_ITEM_MODULE_NOT_FOUND').replace('${sourceModule}', row.moduleName).replace('${position}', row.info.name)
						});
					}
				});
				this.summaryCalculations();
				resolve(response.result);
				if (typeof success === 'function') {
					success(response.result);
				}
			}).fail((error, err) => {
				progressLoader.progressIndicator({mode: 'hide'});
				reject(error, err);
				if (typeof fail === 'function') {
					fail(error, err);
				}
			});
		});
	},
	/**
	 * Function which will register all the events
	 */
	registerEvents: function (container) {
		this.form = container;
		this.registerInventorySaveData();
		this.registerAddItem();
		this.initItem();
		this.registerSortableItems();
		this.registerSubProducts();
		this.registerChangeDiscount();
		this.registerChangeTax();
		this.registerClearReferenceSelection();
		this.registerShowHideExpanded();
		this.registerChangeCurrency();
		this.setDefaultGlobalTax(container);
	}
});
