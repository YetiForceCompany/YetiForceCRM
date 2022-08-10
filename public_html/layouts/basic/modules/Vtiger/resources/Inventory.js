/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Vtiger_Inventory_Js',
	{
		inventoryInstance: false,

		/**
		 * Get inventory instance
		 * @param {jQuery} container
		 * @returns {Vtiger_Inventory_Js}
		 */
		getInventoryInstance: function (container) {
			if (this.inventoryInstance === false) {
				let moduleClassName = container.find('[name="module"]').val() + '_Inventory_Js';
				if (typeof window[moduleClassName] === 'undefined') {
					moduleClassName = 'Vtiger_Inventory_Js';
				}
				if (typeof window[moduleClassName] !== 'undefined') {
					this.inventoryInstance = new window[moduleClassName]();
					this.inventoryInstance.registerEvents(container);
				}
			}
			return this.inventoryInstance;
		}
	},
	{
		form: false,
		discount: false,
		tax: false,
		inventoryContainer: false,
		inventoryHeadContainer: false,
		summaryTaxesContainer: false,
		summaryDiscountContainer: false,
		summaryCurrenciesContainer: false,
		rowClass: 'tr.inventoryRow',
		discountModalFields: [
			'aggregationType',
			'globalDiscount',
			'groupCheckbox',
			'groupDiscount',
			'individualDiscount',
			'individualDiscountType',
			'additionalDiscount'
		],
		taxModalFields: ['aggregationType', 'globalTax', 'groupCheckbox', 'groupTax', 'individualTax', 'regionalTax'],
		/**
		 * Get current form element
		 * @returns {jQuery}
		 */
		getForm() {
			return this.form;
		},
		/**
		 * Get current form element
		 * @returns {jQuery}
		 */
		loadConfig() {
			this.discount = JSON.parse(this.form.find('.js-discount-config').val());
			this.tax = JSON.parse(this.form.find('.js-tax-config').val());
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
			let $inventoryItemsNo = $('#inventoryItemsNo');
			let rowNumber = parseInt($inventoryItemsNo.val()) + 1;
			$inventoryItemsNo.val(rowNumber);
			return rowNumber;
		},
		getAccountId: function () {
			let accountReferenceField = $('#accountReferenceField').val();
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
			let parentRow = element.closest('tr');
			let productField = parentRow.find('.recordLabel');
			return productField.validationEngine('validate');
		},
		getTaxModeSelectElement: function (row) {
			let items = this.getInventoryHeadContainer();
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
			let selectedOption = this.getTaxModeSelectElement(row).find('option:selected');
			if (selectedOption.length !== 0) {
				return selectedOption.val() == 1;
			}
			return this.tax.default_mode == 1;
		},
		isGroupTaxMode: function () {
			let selectedOption = this.getTaxModeSelectElement();
			if (selectedOption && (selectedOption = selectedOption.find('option:selected')) && selectedOption.length !== 0) {
				return selectedOption.val() == 0;
			}
			return this.tax.default_mode == 0;
		},
		showIndividualTax: function (row) {
			let thisInstance = this;
			let groupTax = thisInstance.getInventorySummaryTaxesContainer().find('.groupTax');
			let items = thisInstance.getInventoryItemsContainer();
			let newRow = $('#blackIthemTable').find('tbody');
			if (thisInstance.isIndividualTaxMode()) {
				groupTax.addClass('d-none');
				items.find('.changeTax').removeClass('d-none');
				newRow.find('.changeTax').removeClass('d-none');
				let parentRow = thisInstance.getInventoryItemsContainer();
				let taxParam = { aggregationType: 'global' };

				parentRow.find(thisInstance.rowClass).each(function () {
					let thisItem = $(this);
					taxParam['globalTax'] = parseFloat(thisItem.find('.js-tax').attr('data-default-tax'));
					thisInstance.setTaxParam(thisItem, taxParam);
				});
			} else {
				thisInstance.setTax(items, 0);
				thisInstance.setTaxPercent(items, 0);
				thisInstance.setTaxParam(items, []);
				thisInstance.setDefaultGlobalTax(row);
				groupTax.removeClass('d-none');
				items.find('.changeTax').addClass('d-none');
				newRow.find('.changeTax').addClass('d-none');
			}
			thisInstance.rowsCalculations();
		},
		setDefaultGlobalTax: function () {
			let thisInstance = this;
			let parentRow = thisInstance.getInventoryItemsContainer();
			let taxDefaultValue = thisInstance
				.getInventorySummaryTaxesContainer()
				.find('.js-default-tax')
				.data('tax-default-value');
			let isGroupTax = thisInstance.isGroupTaxMode();
			let summaryContainer = $('#blackIthemTable');
			if (isGroupTax) {
				let taxParam = thisInstance.getTaxParams(summaryContainer);
				if (taxParam === false && taxDefaultValue) {
					taxParam = { aggregationType: 'global' };
					taxParam['globalTax'] = taxDefaultValue;
					taxParam['individualTax'] = '';
				}
				if (taxParam) {
					thisInstance.setTaxParam(summaryContainer, taxParam);
					thisInstance.setTaxParam(parentRow, taxParam);
					parentRow.closest('.inventoryItems').data('taxParam', JSON.stringify(taxParam));
					parentRow.find(thisInstance.rowClass).each(function () {
						thisInstance.quantityChangeActions($(this));
					});
				}
			} else {
				thisInstance.setTaxParam(summaryContainer, []);
				parentRow.closest('.inventoryItems').data('taxParam', '[]');
			}
		},
		getDiscountModeSelectElement: function (row) {
			let items = this.getInventoryHeadContainer();
			if (items.find('thead .js-discountmode').length > 0) {
				return $('.js-discountmode');
			}
			return row.find('.js-discountmode');
		},
		isIndividualDiscountMode: function (row) {
			let selectedOption = this.getDiscountModeSelectElement(row).find('option:selected');
			if (selectedOption.length === 0) {
				return this.discount.default_mode == 1;
			}
			return selectedOption.val() == 1;
		},
		showIndividualDiscount: function (row) {
			let thisInstance = this;
			let groupDiscount = thisInstance.getInventorySummaryDiscountContainer().find('.groupDiscount');
			let items = thisInstance.getInventoryItemsContainer();
			let newRow = $('#blackIthemTable').find('tbody');
			if (thisInstance.isIndividualDiscountMode(row)) {
				groupDiscount.addClass('d-none');
				items.find('.js-change-discount').removeClass('d-none');
				newRow.find('.js-change-discount').removeClass('d-none');
			} else {
				groupDiscount.removeClass('d-none');
				items.find('.js-change-discount').addClass('d-none');
				newRow.find('.js-change-discount').addClass('d-none');
			}
			thisInstance.setDiscount(items, 0);
			thisInstance.setDiscountParam(items, []);
			thisInstance.rowsCalculations();
		},
		getCurrency: function () {
			return $('.js-currency', this.getInventoryHeadContainer()).find('option:selected').val();
		},
		/**
		 * Get discount aggregation
		 *@returns {int}
		 */
		getDiscountAggregation: function () {
			const element = $('.js-discount_aggreg', this.getInventoryHeadContainer()).find('option:selected');
			if (element.length) {
				return parseInt(element.val());
			}
			return parseInt(this.discount.aggregation);
		},
		getTax: function (row) {
			const self = this;
			let taxParams = row.find('.taxParam').val();
			if (taxParams == '' || taxParams == '[]' || taxParams == undefined) return 0;
			taxParams = JSON.parse(taxParams);
			let valuePrices = this.getNetPrice(row);
			let taxRate = 0;
			let types = taxParams.aggregationType;
			if (typeof types == 'string') {
				types = [types];
			}
			if (types) {
				types.forEach(function (entry) {
					let taxValue = 0;
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
					taxRate += valuePrices * (taxValue / 100);
					if (self.tax.aggregation == 2) {
						valuePrices = valuePrices + taxRate;
					}
				});
			}
			return taxRate;
		},
		getTaxPercent: function (row) {
			let taxParams = row.find('.taxParam').val();
			if (taxParams == '' || taxParams == '[]' || taxParams == undefined) return 0;
			taxParams = JSON.parse(taxParams);
			let taxPercent = 0;
			let types =
				typeof taxParams.aggregationType === 'string' ? [taxParams.aggregationType] : taxParams.aggregationType;
			types.forEach(function (aggregationType) {
				taxPercent += taxParams[aggregationType + 'Tax'] || 0;
			});
			return taxPercent;
		},
		getTaxParams: function (row) {
			let taxParams = row.find('.taxParam').val();
			if (taxParams == '' || taxParams == '[]' || taxParams == undefined) return false;
			return JSON.parse(taxParams);
		},
		getQuantityValue: function (row) {
			return $('.qty', row).getNumberFromValue();
		},
		getUnitPriceValue: function (row) {
			return $('.unitPrice', row).getNumberFromValue();
		},
		getDiscount: function (row) {
			let discountParams = row.find('.discountParam').val();
			if (discountParams == '' || discountParams == 'null' || discountParams == '[]' || discountParams == undefined) {
				return 0;
			}
			const aggregation = this.getDiscountAggregation();
			discountParams = JSON.parse(discountParams);
			let valuePrices = this.getTotalPrice(row),
				discountRate = 0,
				types = discountParams.aggregationType;
			if (typeof types == 'string') {
				types = [types];
			}
			if (types) {
				types.forEach((entry) => {
					switch (entry) {
						case 'individual':
							if (discountParams.individualDiscountType === 'percentage') {
								discountRate += valuePrices * (discountParams.individualDiscount / 100);
							} else {
								discountRate += discountParams.individualDiscount;
							}
							break;
						case 'global':
							discountRate += valuePrices * (discountParams.globalDiscount / 100);
							break;
						case 'group':
							discountRate += valuePrices * ((discountParams.groupDiscount ? discountParams.groupDiscount : 0) / 100);
							break;
						case 'additional':
							discountRate += valuePrices * (discountParams.additionalDiscount / 100);
							break;
					}
					if (aggregation === 2) {
						valuePrices = valuePrices - discountRate;
					}
				});
			}
			return discountRate;
		},
		getNetPrice: function (row) {
			return this.getTotalPrice(row) - this.getDiscount(row);
		},
		getTotalPrice: function (row) {
			return this.getQuantityValue(row) * this.getUnitPriceValue(row);
		},
		getGrossPrice: function (row) {
			return $('.grossPrice', row).getNumberFromValue();
		},
		getPurchase: function (row) {
			let qty = this.getQuantityValue(row);
			let element = $('.purchase', row);
			let purchase = 0;
			if (element.length > 0) {
				purchase = App.Fields.Double.formatToDb(element.val());
			}
			return purchase * qty;
		},
		getSummaryGrossPrice: function () {
			let thisInstance = this;
			let price = 0;
			this.getInventoryItemsContainer()
				.find(thisInstance.rowClass)
				.each(function () {
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
			row
				.parent()
				.find('[numrowex=' + row.attr('numrow') + '] .comment')
				.val(val);
		},
		/**
		 * Set inventory row unit price
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setUnitPrice: function (row, val) {
			val = App.Fields.Double.formatToDisplay(val);
			row.find('.unitPrice').val(val).attr('title', val);
			return this;
		},
		/**
		 * Set inventory row purchase
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setPurchase: function (row, val) {
			row.find('.purchase').val(App.Fields.Double.formatToDisplay(val));
			return this;
		},
		/**
		 * Set inventory row net price
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setNetPrice: function (row, val) {
			val = App.Fields.Double.formatToDisplay(val);
			$('.netPriceText', row).text(val);
			$('.netPrice', row).val(val);
		},
		/**
		 * Set inventory row gross price
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setGrossPrice: function (row, val) {
			val = App.Fields.Double.formatToDisplay(val);
			$('.grossPriceText', row).text(val);
			$('.grossPrice', row).val(val);
		},
		/**
		 * Set inventory row total price
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setTotalPrice: function (row, val) {
			val = App.Fields.Double.formatToDisplay(val);
			$('.totalPriceText', row).text(val);
			$('.totalPrice', row).val(val);
		},
		/**
		 * Set inventory row margin
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setMargin: function (row, val) {
			$('.margin', row).val(App.Fields.Double.formatToDisplay(val));
		},
		/**
		 * Set inventory row margin percent
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setMarginP: function (row, val) {
			$('.marginp', row).val(App.Fields.Double.formatToDisplay(val));
		},
		/**
		 * Set inventory row discount
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setDiscount: function (row, val) {
			$('.discount', row).val(App.Fields.Double.formatToDisplay(val));
		},
		/**
		 * Set inventory row discount param
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setDiscountParam: function (row, val) {
			$('.discountParam', row).val(JSON.stringify(val));
		},
		/**
		 * Set inventory row tax
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setTax: function (row, val) {
			$('.tax', row).val(App.Fields.Double.formatToDisplay(val));
		},
		/**
		 * Set inventory row tax percent
		 * @param {jQuery} row
		 * @param {string} val
		 */
		setTaxPercent: function (row, val) {
			$('.js-tax-percent', row).val(App.Fields.Double.formatToDisplay(val));
		},
		/**
		 * Set inventory row tax param
		 * @param {jQuery} row
		 * @param {string} val
		 */
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
			app.event.trigger('Inventory.RowCalculations', this, row);
		},
		rowsCalculations: function () {
			const self = this;
			this.getInventoryItemsContainer()
				.find(self.rowClass)
				.each(function () {
					let row = $(this);
					self.syncHeaderData(row);
					self.quantityChangeActions(row);
				});
			self.calculateItemNumbers();
		},
		calculateDiscounts: function (row) {
			this.setDiscount(row, this.getDiscount(row));
		},
		calculateTaxes: function (row) {
			this.setTax(row, this.getTax(row));
			this.setTaxPercent(row, this.getTaxPercent(row));
		},
		summaryCalculations: function () {
			let thisInstance = this;
			thisInstance
				.getInventoryItemsContainer()
				.find('tfoot .wisableTd')
				.each(function () {
					thisInstance.calculateSummary($(this), $(this).data('sumfield'));
				});
			thisInstance.calculateDiscountSummary();
			thisInstance.calculateTaxSummary();
			thisInstance.calculateCurrenciesSummary();
			thisInstance.calculateMarginPSummary();
		},
		calculateSummary: function (element, field) {
			let thisInstance = this;
			let sum = 0;
			this.getInventoryItemsContainer()
				.find(thisInstance.rowClass)
				.each(function () {
					let e = $(this).find('.' + field);
					if (e.length > 0) {
						sum += App.Fields.Double.formatToDb(e.val());
					}
				});
			element.text(App.Fields.Double.formatToDisplay(sum));
		},
		calculateMarginPSummary: function () {
			let sumRow = this.getInventoryItemsContainer().find('tfoot'),
				totalPriceField =
					sumRow.find('[data-sumfield="netPrice"]').length > 0
						? sumRow.find('[data-sumfield="netPrice"]')
						: sumRow.find('[data-sumfield="totalPrice"]'),
				sumPrice = totalPriceField.getNumberFromText(),
				purchase = 0,
				marginp = 0;
			this.getInventoryItemsContainer()
				.find(this.rowClass)
				.each(function () {
					let qty = $(this).find('.qty').getNumberFromValue(),
						purchasePrice = $(this).find('.purchase').getNumberFromValue();
					if (qty > 0 && purchasePrice > 0) {
						purchase += qty * purchasePrice;
					}
				});

			let subtraction = sumPrice - purchase;
			if (purchase !== 0 && sumPrice !== 0) {
				marginp = (subtraction * 100) / purchase;
			}
			sumRow.find('[data-sumfield="marginP"]').text(App.Fields.Double.formatToDisplay(marginp) + '%');
		},
		calculateDiscountSummary: function () {
			let thisInstance = this;
			let discount = thisInstance.getAllDiscount();
			let container = thisInstance.getInventorySummaryDiscountContainer();
			container.find('input').val(App.Fields.Double.formatToDisplay(discount));
		},
		getAllDiscount: function () {
			let thisInstance = this;
			let discount = 0;
			this.getInventoryItemsContainer()
				.find(thisInstance.rowClass)
				.each(function (index) {
					let row = $(this);
					discount += thisInstance.getDiscount(row);
				});
			return discount;
		},
		calculateCurrenciesSummary: function () {
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
			let taxes = this.getAllTaxes();
			let sum = 0;
			container.find('.js-panel__body').html('');
			$.each(taxes, function (index, value) {
				if (value != undefined) {
					value = value * conversionRate;
					let row = container.find('.d-none .form-group').clone();
					row.find('.percent').text(index + '%');
					row.find('input').val(App.Fields.Double.formatToDisplay(value));
					row.appendTo(container.find('.js-panel__body'));
					sum += value;
				}
			});
			container.find('.js-panel__footer input').val(App.Fields.Double.formatToDisplay(sum));
		},
		calculateTaxSummary: function () {
			let thisInstance = this;
			let taxes = thisInstance.getAllTaxes();
			let container = thisInstance.getInventorySummaryTaxesContainer();
			container.find('.js-panel__body').html('');
			let sum = 0;
			for (let index in taxes) {
				let row = container.find('.d-none .form-group').clone();
				row.find('.percent').text(App.Fields.Double.formatToDisplay(index) + '%');
				row.find('input').val(App.Fields.Double.formatToDisplay(taxes[index]));
				row.appendTo(container.find('.js-panel__body'));
				sum += taxes[index];
			}
			container.find('.js-panel__footer input').val(App.Fields.Double.formatToDisplay(sum));
		},
		getAllTaxes: function () {
			let thisInstance = this;
			let tax = [];
			let typeSummary = $('.aggregationTypeTax').val();
			this.getInventoryItemsContainer()
				.find(thisInstance.rowClass)
				.each(function () {
					let row = $(this);
					let netPrice = thisInstance.getNetPrice(row);
					let params = row.find('.taxParam').val();
					if (params != '' && params != '[]' && params != undefined) {
						let param = JSON.parse(params);
						if (typeof param.aggregationType == 'string') {
							param.aggregationType = [param.aggregationType];
						}
						if (param.aggregationType)
							$.each(param.aggregationType, function (_, name) {
								name = name + 'Tax';
								if (param[name] == undefined) {
									return;
								}
								let percent = parseFloat(param[name]);
								let old = 0;
								if (tax[percent] != undefined) {
									old = parseFloat(tax[percent]);
								}
								let taxRate = netPrice * (percent / 100);
								tax[percent] = old + taxRate;
								if (typeSummary == '2') {
									netPrice += taxRate;
								}
							});
					}
				});
			return tax;
		},
		calculateNetPrice: function (row) {
			this.setNetPrice(row, this.getNetPrice(row));
		},
		calculateGrossPrice: function (row) {
			let netPrice = this.getNetPrice(row);
			if (this.isIndividualTaxMode(row) || this.isGroupTaxMode(row)) {
				netPrice += this.getTax(row);
			}
			this.setGrossPrice(row, netPrice);
		},
		calculateTotalPrice: function (row) {
			this.setTotalPrice(row, this.getTotalPrice(row));
		},
		calculateMargin: function (row) {
			let netPrice;
			if ($('.netPrice', row).length) {
				netPrice = this.getNetPrice(row);
			} else {
				netPrice = this.getTotalPrice(row) - this.getDiscount(row);
			}
			let purchase = this.getPurchase(row);
			let margin = netPrice - purchase;
			this.setMargin(row, margin);
			let marginp = '0';
			if (purchase !== 0) {
				marginp = (margin / purchase) * 100;
			}
			this.setMarginP(row, marginp);
		},
		calculateDiscount: function (_row, modal) {
			const netPriceBeforeDiscount = App.Fields.Double.formatToDb(modal.find('.valueTotalPrice').text()),
				discountsType = modal.find('.discountsType').val();
			let valuePrices = netPriceBeforeDiscount,
				globalDiscount = 0,
				groupDiscount = 0,
				individualDiscount = 0,
				additionalDiscount = 0;
			if (discountsType == 0 || discountsType == 1) {
				if (modal.find('.js-active .globalDiscount').length > 0) {
					globalDiscount = App.Fields.Double.formatToDb(modal.find('.js-active .globalDiscount').val());
				}
				if (modal.find('.js-active .additionalDiscountValue').length > 0) {
					additionalDiscount = App.Fields.Double.formatToDb(modal.find('.js-active .additionalDiscountValue').val());
				}
				if (modal.find('.js-active .individualDiscountType').length > 0) {
					let value = App.Fields.Double.formatToDb(modal.find('.js-active .individualDiscountValue').val());
					if (modal.find('.js-active .individualDiscountType:checked').val() == 'percentage') {
						individualDiscount = netPriceBeforeDiscount * (value / 100);
					} else {
						individualDiscount = value;
					}
				}
				if (
					modal.find('.js-active .groupCheckbox').length > 0 &&
					modal.find('.js-active .groupCheckbox').prop('checked') == true
				) {
					groupDiscount = App.Fields.Double.formatToDb(modal.find('.groupValue').val());
					groupDiscount = netPriceBeforeDiscount * (groupDiscount / 100);
				}
				valuePrices = valuePrices * ((100 - globalDiscount) / 100);
				valuePrices = valuePrices * ((100 - additionalDiscount) / 100);
				valuePrices = valuePrices - individualDiscount;
				valuePrices = valuePrices - groupDiscount;
			} else if (discountsType == 2) {
				modal.find('.js-active').each(function () {
					let panel = $(this);
					if (panel.find('.globalDiscount').length > 0) {
						valuePrices =
							valuePrices * ((100 - App.Fields.Double.formatToDb(panel.find('.globalDiscount').val())) / 100);
					} else if (panel.find('.groupCheckbox').length > 0 && panel.find('.groupCheckbox').prop('checked') == true) {
						valuePrices = valuePrices * ((100 - App.Fields.Double.formatToDb(panel.find('.groupValue').val())) / 100);
					} else if (panel.find('.individualDiscountType').length > 0) {
						let value = App.Fields.Double.formatToDb(panel.find('.individualDiscountValue').val());
						if (panel.find('.individualDiscountType[name="individualDiscountType"]:checked').val() === 'percentage') {
							valuePrices = valuePrices * ((100 - value) / 100);
						} else {
							valuePrices = valuePrices - value;
						}
					} else if (panel.find('.additionalDiscountValue').length > 0) {
						valuePrices =
							valuePrices * ((100 - App.Fields.Double.formatToDb(panel.find('.additionalDiscountValue').val())) / 100);
					}
				});
			}
			modal.find('.valuePrices').text(App.Fields.Double.formatToDisplay(valuePrices));
			modal.find('.valueDiscount').text(App.Fields.Double.formatToDisplay(netPriceBeforeDiscount - valuePrices));
		},
		calculateTax: function (_row, modal) {
			let netPriceWithoutTax = App.Fields.Double.formatToDb(modal.find('.valueNetPrice').text()),
				valuePrices = netPriceWithoutTax,
				globalTax = 0,
				groupTax = 0,
				regionalTax = 0,
				individualTax = 0;

			let taxType = modal.find('.taxsType').val();
			if (taxType == '0' || taxType == '1') {
				if (modal.find('.js-active .globalTax').length > 0) {
					globalTax = App.Fields.Double.formatToDb(modal.find('.js-active .globalTax').val());
				}
				if (modal.find('.js-active .individualTaxValue').length > 0) {
					let value = App.Fields.Double.formatToDb(modal.find('.js-active .individualTaxValue').val());
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
				modal.find('.js-active').each(function () {
					let panel = $(this);
					if (panel.find('.globalTax').length > 0) {
						valuePrices = valuePrices * ((100 + App.Fields.Double.formatToDb(panel.find('.globalTax').val())) / 100);
					} else if (panel.find('.groupTax').length > 0) {
						valuePrices = valuePrices * ((100 + App.Fields.Double.formatToDb(panel.find('.groupTax').val())) / 100);
					} else if (panel.find('.regionalTax').length > 0) {
						valuePrices = valuePrices * ((100 + App.Fields.Double.formatToDb(panel.find('.regionalTax').val())) / 100);
					} else if (panel.find('.individualTaxValue').length > 0) {
						valuePrices =
							((App.Fields.Double.formatToDb(panel.find('.individualTaxValue').val()) + 100) / 100) * valuePrices;
					}
				});
			}
			if (netPriceWithoutTax) {
				let taxValue = ((valuePrices - netPriceWithoutTax) / netPriceWithoutTax) * 100;
				modal.find('.js-tax-value').text(App.Fields.Double.formatToDisplay(taxValue));
			}
			modal.find('.valuePrices').text(App.Fields.Double.formatToDisplay(valuePrices));
			modal.find('.valueTax').text(App.Fields.Double.formatToDisplay(valuePrices - netPriceWithoutTax));
		},
		updateRowSequence: function () {
			let items = this.getInventoryItemsContainer();
			items.find(this.rowClass).each(function (index) {
				$(this)
					.find('.sequence')
					.val(index + 1);
			});
		},
		registerInventorySaveData: function () {
			const thisInstance = this;
			thisInstance.form.on(Vtiger_Edit_Js.recordPreSave, function () {
				thisInstance.syncHeaderData();
				if (!thisInstance.checkLimits(thisInstance.form)) {
					return false;
				}
			});
		},
		syncHeaderData(container) {
			let header = this.getInventoryHeadContainer();
			if (typeof container === 'undefined') {
				container = this.getInventoryItemsContainer();
			}
			container.find('.js-sync').each(function () {
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
			app.showRecordsList(
				{
					module: 'PriceBooks',
					src_module: $('[name="popupReferenceModule"]', rowName).val(),
					src_record: $('.sourceField', rowName).val(),
					src_field: $('[name="popupReferenceModule"]', rowName).data('field'),
					currency_id: thisInstance.getCurrency() || CONFIG.defaultCurrencyId
				},
				(modal, instance) => {
					instance.setSelectEvent((responseData) => {
						AppConnector.request({
							module: 'PriceBooks',
							action: 'ProductListPrice',
							record: responseData.id,
							src_record: $('.sourceField', rowName).val()
						}).done(function (data) {
							if (data.result) {
								thisInstance.setUnitPrice(lineItemRow, data.result);
								thisInstance.quantityChangeActions(lineItemRow);
							} else {
								app.errorLog('Incorrect data', responseData);
							}
						});
					});
				}
			);
		},
		subProductsCashe: [],
		loadSubProducts: function (parentRow, indicator) {
			let thisInstance = this;
			let progressInstace;
			let recordId = $('input.sourceField', parentRow).val();
			let recordModule = parentRow.find('.rowName input[name="popupReferenceModule"]').val();
			thisInstance.removeSubProducts(parentRow);
			if (recordId == '0' || recordId == '' || $.inArray(recordModule, ['Products', 'Services']) < 0) {
				return false;
			}
			if (thisInstance.subProductsCashe[recordId]) {
				thisInstance.addSubProducts(parentRow, thisInstance.subProductsCashe[recordId]);
				return false;
			}
			let subProrductParams = {
				module: 'Products',
				action: 'SubProducts',
				record: recordId
			};
			if (indicator) {
				progressInstace = $.progressIndicator();
			}
			AppConnector.request(subProrductParams)
				.done(function (data) {
					let responseData = data.result;
					thisInstance.subProductsCashe[recordId] = responseData;
					thisInstance.addSubProducts(parentRow, responseData);
					if (progressInstace) {
						progressInstace.hide();
					}
				})
				.fail(function () {
					if (progressInstace) {
						progressInstace.hide();
					}
				});
		},
		removeSubProducts: function (parentRow) {
			let subProductsContainer = $('.subProductsContainer ul', parentRow);
			subProductsContainer.find('li').remove();
		},
		addSubProducts: function (parentRow, responseData) {
			let subProductsContainer = $('.subProductsContainer ul', parentRow);
			for (let id in responseData) {
				subProductsContainer.append($('<li>').text(responseData[id]));
			}
		},
		mapResultsToFields: function (referenceModule, parentRow, responseData) {
			let unit,
				taxParam = [];
			let thisInstance = this;
			let isGroupTax = thisInstance.isGroupTaxMode();
			for (let id in responseData) {
				let recordData = responseData[id];
				let description = recordData.description;
				let unitPriceValues = recordData.unitPriceValues;
				let unitPriceValuesJson = JSON.stringify(unitPriceValues);
				// Load taxes detail
				if (isGroupTax) {
					let parameters = parentRow.closest('.inventoryItems').data('taxParam');
					if (parameters) {
						taxParam = JSON.parse(parameters);
					}
				} else if (recordData['taxes']) {
					taxParam = { aggregationType: recordData.taxes.type };
					taxParam[recordData.taxes.type + 'Tax'] = recordData.taxes.value;
				}
				if (recordData['taxes']) {
					parentRow.find('.js-tax').attr('data-default-tax', recordData.taxes.value);
				}
				thisInstance.setPurchase(parentRow, recordData.purchase);
				thisInstance.setTaxParam(parentRow, taxParam);
				thisInstance.setTax(parentRow, 0);
				thisInstance.setTaxPercent(parentRow, 0);

				for (let field in recordData['autoFields']) {
					let inputField = parentRow.find('input.' + field);
					if (inputField.attr('type') === 'checkbox') {
						inputField.prop('checked', recordData['autoFields'][field]);
					} else {
						inputField.val(recordData['autoFields'][field]);
					}
					if (recordData['autoFields'][field + 'Text']) {
						parentRow.find('.' + field + 'Text').text(recordData['autoFields'][field + 'Text']);
					}
				}
				if (recordData.price !== undefined) {
					thisInstance.setUnitPrice(parentRow, recordData.price);
				}
				if (unitPriceValuesJson !== undefined) {
					$('input.unitPrice', parentRow).attr('list-info', unitPriceValuesJson);
				}
				let commentElement = $('textarea.js-inventory-item-comment', parentRow.next());
				let editorInstance = CKEDITOR.instances[commentElement.attr('id')];
				if (editorInstance) {
					editorInstance.setData(description);
				} else {
					commentElement.val(description);
				}
				if (typeof recordData['autoFields']['unit'] !== 'undefined') {
					unit = recordData['autoFields']['unit'];
				}
				app.event.trigger('Inventory.SelectionItem', thisInstance, parentRow, recordData, referenceModule);
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
			const typeName = 'aggregationType',
				panels = modal.find('[name="' + typeName + '"]:checked');
			let info = {};
			info[typeName] = [];
			panels.each(function () {
				let type = $(this).val(),
					container = $(this).closest('.js-panel');
				if (panels.length > 1) {
					info[typeName].push(type);
				} else {
					info[typeName] = type;
				}
				container.find('[name="' + type + 'Discount"]').each(function () {
					let param = type + 'Discount';
					let element = $(this);
					switch (type) {
						case 'group':
							if (element.closest('.input-group').find('.groupCheckbox').prop('checked')) {
								info[param] = App.Fields.Double.formatToDb(element.val());
							}
							break;
						case 'individual':
							let name = 'individualDiscountType';
							info[name] = container.find('[name="' + name + '"]:checked').val();
							info[param] = App.Fields.Double.formatToDb(element.val());
							break;
						case 'global':
						case 'additional':
							info[param] = App.Fields.Double.formatToDb(element.val());
							break;
					}
				});
			});
			this.setDiscountParam($('#blackIthemTable'), info);
			this.setDiscountParam(parentRow, info);
		},
		saveTaxsParameters: function (parentRow, modal) {
			let info = {};
			const extend = ['aggregationType', 'groupCheckbox', 'individualTaxType'];
			$.each(this.taxModalFields, function (_, param) {
				if ($.inArray(param, extend) >= 0) {
					if (modal.find('[name="' + param + '"]:checked').length > 1) {
						info[param] = [];
						modal.find('[name="' + param + '"]:checked').each(function () {
							info[param].push($(this).val());
						});
					} else {
						info[param] = modal.find('[name="' + param + '"]:checked').val();
					}
				} else {
					info[param] = App.Fields.Double.formatToDb(modal.find('[name="' + param + '"]').val());
				}
			});
			parentRow.data('taxParam', JSON.stringify(info));
			this.setTaxParam(parentRow, info);
			this.setTaxParam($('#blackIthemTable'), info);
		},
		showExpandedRow: function (row) {
			const inventoryRowExpanded = this.getInventoryItemsContainer().find('[numrowex="' + row.attr('numrow') + '"]');
			const element = row.find('.toggleVisibility');
			element.data('status', '1');
			inventoryRowExpanded.removeClass('d-none');
		},
		hideExpandedRow: function (row) {
			const inventoryRowExpanded = this.getInventoryItemsContainer().find('[numrowex="' + row.attr('numrow') + '"]');
			const element = row.find('.toggleVisibility');
			element.data('status', '0');
			inventoryRowExpanded.addClass('d-none');
		},
		initDiscountsParameters: function (parentRow, modal) {
			let parameters = parentRow.find('.discountParam').val();
			if (parameters == '' || parameters == undefined) {
				return;
			}
			parameters = JSON.parse(parameters);
			$.each(this.discountModalFields, function (_, param) {
				let parameter = parameters[param];
				let field = modal.find('[name="' + param + '"]');
				if (field.attr('type') == 'checkbox' || field.attr('type') == 'radio') {
					if ('groupCheckbox' === param && parameters['groupDiscount'] !== undefined) {
						field.prop('checked', true);
						return true;
					}
					let array = parameter;
					if (!$.isArray(array)) {
						array = [array];
					}
					$.each(array, function (_, arrayValue) {
						let value = field.filter('[value="' + arrayValue + '"]').prop('checked', true);
						if (param == 'aggregationType') {
							value.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
							value.closest('.js-panel').addClass('js-active');
						}
					});
				} else if (field.prop('tagName') == 'SELECT') {
					field
						.find('option[value="' + parameter + '"]')
						.prop('selected', 'selected')
						.change();
				} else if (!field.prop('readonly')) {
					field.val(parameter);
				}
			});
			this.calculateDiscount(parentRow, modal);
		},
		initTaxParameters: function (parentRow, modal) {
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
			$.each(this.taxModalFields, function (_, param) {
				let parameter = parameters[param],
					field = modal.find('[name="' + param + '"]');

				if (field.attr('type') === 'checkbox' || field.attr('type') === 'radio') {
					let array = parameter,
						value;
					if (!$.isArray(array)) {
						array = [array];
					}
					$.each(array, function (_, arrayValue) {
						value = field.filter('[value="' + arrayValue + '"]').prop('checked', true);
						if (param === 'aggregationType') {
							value.closest('.js-panel').find('.js-panel__body').removeClass('d-none');
							value.closest('.js-panel').addClass('js-active');
						}
					});
				} else if (field.prop('tagName') === 'SELECT') {
					field
						.find('option[value="' + parameter + '"]')
						.prop('selected', 'selected')
						.change();
				} else {
					let input = modal.find('[name="' + param + '"]');
					input.val(App.Fields.Double.formatToDisplay(parameter));
					if (param === 'individualTax') {
						input.formatNumber();
					}
				}
			});
			this.calculateTax(parentRow, modal);
		},
		limitEnableSave: false,
		checkLimits: function () {
			const account = this.getAccountId(),
				limit = parseInt(app.getMainParams('inventoryLimit'));
			let response = true;
			if (account == '' || this.limitEnableSave || !limit) {
				return response;
			}
			let progressInstace = $.progressIndicator();
			AppConnector.request({
				async: false,
				dataType: 'json',
				data: {
					module: app.getModuleName(),
					action: 'Inventory',
					mode: 'checkLimits',
					record: account,
					currency: this.getCurrency(),
					price: thisInthisstance.getSummaryGrossPrice()
				}
			})
				.done(function (data) {
					progressInstace.hide();
					if (data.result.status == false) {
						app.showModalWindow(data.result.html, function () {});
						response = false;
					}
				})
				.fail(function () {
					progressInstace.hide();
				});
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
			let thisInstance = this;
			if (thisInstance.lockCurrencyChange == true) {
				return;
			}
			thisInstance.lockCurrencyChange = true;
			let block = select.closest('th');
			let modal = block.find('.modelContainer').clone();
			app.showModalWindow(modal, function (data) {
				let modal = $(data);
				let currencyParam = JSON.parse(block.find('.js-currencyparam').val());

				if (currencyParam != false) {
					if (typeof currencyParam[option.val()] === 'undefined') {
						let defaultCurrencyParams = {
							value: 1,
							date: ''
						};
						currencyParam[option.val()] = defaultCurrencyParams;
					}
					modal.find('.currencyName').text(option.text());
					modal.find('.currencyRate').val(currencyParam[option.val()]['value']);
					modal.find('.currencyDate').text(currencyParam[option.val()]['date']);
				}
				modal
					.on('click', 'button[type="submit"]', function () {
						let rate = modal.find('.currencyRate').val();
						let value = App.Fields.Double.formatToDb(rate);
						let conversionRate = 1 / App.Fields.Double.formatToDb(rate);

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
					})
					.one('hidden.bs.modal', function () {
						select.val(select.data('oldValue')).change();
						thisInstance.lockCurrencyChange = false;
					});
			});
		},
		currencyConvertValues: function (select, selected) {
			const self = this;
			let previous = select.find('option[value="' + select.data('oldValue') + '"]');
			let conversionRate = selected.data('conversionRate');
			let prevConversionRate = previous.data('conversionRate');
			conversionRate = parseFloat(conversionRate) / parseFloat(prevConversionRate);
			this.getInventoryItemsContainer()
				.find(self.rowClass)
				.each(function (_) {
					let row = $(this);
					self.syncHeaderData(row);
					self.setUnitPrice(row, self.getUnitPriceValue(row) * conversionRate);
					self.setDiscount(row, self.getDiscount(row) * conversionRate);
					self.setTax(row, self.getTax(row) * conversionRate);
					self.setPurchase(row, self.getPurchase(row) * conversionRate);
					self.quantityChangeActions(row);
				});
		},
		/**
		 * Set up all row data that comes from request
		 * @param {jQuery} row
		 * @param {object} rowData
		 */
		setRowData(row, rowData) {
			this.setName(row, rowData.name, rowData.info.name);
			this.setQuantity(row, App.Fields.Double.formatToDisplay(rowData.qty));
			this.setUnit(row, rowData.info.autoFields.unit, rowData.info.autoFields.unitText);
			if (typeof rowData.info.autoFields !== 'undefined' && typeof rowData.info.autoFields.subunit !== 'undefined') {
				this.setSubUnit(row, rowData.info.autoFields.subunit, rowData.info.autoFields.subunitText);
			}
			this.setComment(row, rowData.comment1);
			this.setUnitPrice(row, App.Fields.Double.formatToDisplay(rowData.price));
			this.setNetPrice(row, App.Fields.Double.formatToDisplay(rowData.net));
			this.setGrossPrice(row, App.Fields.Double.formatToDisplay(rowData.gross));
			this.setTotalPrice(row, App.Fields.Double.formatToDisplay(rowData.total));
			let discountParam = rowData.discountparam || null;
			this.setDiscountParam(row, JSON.parse(discountParam));
			this.setDiscount(row, App.Fields.Double.formatToDisplay(rowData.discount));
			this.setTaxParam(row, JSON.parse(rowData.taxparam));
			this.setTax(row, App.Fields.Double.formatToDisplay(rowData.tax));
			this.setTaxPercent(row, App.Fields.Double.formatToDisplay(rowData.tax_percent));
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
			const moduleLabels = newRow.data('moduleLbls');
			newRow.html(replaced);
			newRow = newRow.children().appendTo(items.find('.js-inventory-items-body'));
			newRow.find('.rowName input[name="popupReferenceModule"]').val(module).data('field', baseTableId);
			newRow.find('.js-module-icon').removeClass().addClass(`yfm-${module}`);
			newRow.find('.rowName span.input-group-text').attr('data-content', moduleLabels[module]);
			newRow.find('.colPicklistField select').each(function (_, select) {
				select = $(select);
				select.find('option').each(function (_, option) {
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
				this.quantityChangeActions(newRow);
			}
			return newRow;
		},

		/**
		 * Register add item button click
		 * @param {jQuery} container
		 */
		registerAddItem() {
			const thisInstance = this;
			const itemsHeader = thisInstance.getInventoryHeadContainer();
			itemsHeader.find('.js-inv-add-item').on('click', function () {
				const btn = $(this);
				thisInstance.addItem(btn.data('module'), btn.data('field'));
			});
		},
		registerSortableItems: function () {
			let thisInstance = this;
			let items = thisInstance.getInventoryItemsContainer();
			items.sortable({
				handle: '.dragHandle',
				items: thisInstance.rowClass,
				revert: true,
				tolerance: 'pointer',
				placeholder: 'ui-state-highlight',
				helper: function (e, ui) {
					ui.children().each(function (_, element) {
						element = $(element);
						element.width(element.width());
					});
					return ui;
				},
				start: function (_, ui) {
					items.find(thisInstance.rowClass).each(function (_, element) {
						let row = $(element);
						thisInstance.hideExpandedRow(row);
					});
					let num = $(ui.item).attr('numrow');
					items.find('[numrowex="' + num + '"] .js-inventory-item-comment').each(function () {
						App.Fields.Text.destroyEditor($(this));
					});
					ui.item.startPos = ui.item.index();
				},
				stop: function (_, ui) {
					let numrow = $(ui.item).attr('numrow');
					let child = items.find('.numRow' + numrow);
					items.find('[numrow="' + numrow + '"]').after(child);
					App.Fields.Text.Editor.register(child);
					thisInstance.updateRowSequence();
				}
			});
		},
		registerShowHideExpanded: function () {
			const thisInstance = this;
			thisInstance.form.on('click', '.toggleVisibility', function (e) {
				let element = $(e.currentTarget);
				let row = thisInstance.getClosestRow(element);
				if (element.data('status') == 0) {
					thisInstance.showExpandedRow(row);
				} else {
					thisInstance.hideExpandedRow(row);
				}
			});
		},
		registerPriceBookModal: function (container) {
			let thisInstance = this;
			container.find('.js-price-book-modal').on('click', function (e) {
				let element = $(e.currentTarget);
				let response = thisInstance.isRecordSelected(element);
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
			let headContainer = this.getInventoryHeadContainer();
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
			thisInstance.form.find('.inventoryItems ' + thisInstance.rowClass).each(function () {
				thisInstance.loadSubProducts($(this), false);
			});
		},
		/**
		 * Register clear reference selection
		 */
		registerClearReferenceSelection() {
			this.form.on('click', '.clearReferenceSelection', (e) => {
				const referenceGroup = $(e.currentTarget).closest('div.referenceGroup');
				if (referenceGroup.length) {
					referenceGroup.find('input[id$="_display"]').val('').removeAttr('readonly');
				} else {
					const row = this.getClosestRow($(e.currentTarget));
					this.removeSubProducts(row);
					row
						.find('.unitPrice,.tax,.discount,.margin,.purchase,.js-tax-percent')
						.val(App.Fields.Double.formatToDisplay(0));
					row.find('.qty').val(1);
					row.find('textarea,.valueVal').val('');
					row.find('.valueText').text('');
					row.find('.qtyParamInfo').addClass('d-none');
					row.find('.recordLabel').val('').removeAttr('readonly');
					if (!this.isGroupTaxMode()) {
						this.setTaxParam(row, []);
					}
					this.quantityChangeActions(row);
				}
			});
		},
		registerDeleteLineItemEvent: function (container) {
			container.on('click', '.deleteRow', (e) => {
				let num = this.getClosestRow($(e.currentTarget)).attr('numrow');
				this.deleteLineItem(num);
			});
		},
		deleteLineItem: function (num) {
			this.getInventoryItemsContainer()
				.find('[numrowex="' + num + '"] .js-inventory-item-comment')
				.each(function () {
					App.Fields.Text.destroyEditor($(this));
				});
			this.getInventoryItemsContainer()
				.find('[numrow="' + num + '"], [numrowex="' + num + '"]')
				.remove();
			this.checkDeleteIcon();
			this.rowsCalculations();
			if (this.getInventoryItemsContainer().find('.inventoryRow').length === 0) {
				$('#inventoryItemsNo').val(0);
			}
			this.updateRowSequence();
		},
		registerChangeDiscount: function () {
			this.form.on('click', '.js-change-discount', (e) => {
				let parentRow;
				const element = $(e.currentTarget);
				let params = {
					module: app.getModuleName(),
					view: 'Inventory',
					mode: 'showDiscounts',
					currency: this.getCurrency(),
					discountAggregation: this.getDiscountAggregation(),
					relatedRecord: this.getAccountId()
				};
				if (element.hasClass('groupDiscount')) {
					parentRow = this.getInventoryItemsContainer();
					if (parentRow.find('tfoot .colTotalPrice').length != 0) {
						params.totalPrice = App.Fields.Double.formatToDb(parentRow.find('tfoot .colTotalPrice').text());
					} else {
						params.totalPrice = 0;
					}
					params.discountType = 1;
				} else {
					parentRow = element.closest(this.rowClass);
					params.totalPrice = this.getTotalPrice(parentRow);
					params.discountType = 0;
				}
				let progressInstace = $.progressIndicator();
				AppConnector.request(params)
					.done((data) => {
						app.showModalWindow(data, (data) => {
							this.initDiscountsParameters(parentRow, $(data));
							this.registerChangeDiscountModal(data, parentRow, params);
						});
						progressInstace.hide();
					})
					.fail(function () {
						progressInstace.hide();
					});
			});
		},
		registerChangeDiscountModal: function (modal, parentRow, params) {
			let thisInstance = this;
			let form = modal.find('form');
			form.validationEngine(app.validationEngineOptions);
			modal.on('change', '.individualDiscountType', function (e) {
				let element = $(e.currentTarget);
				modal.find('.individualDiscountContainer .input-group-text').text(element.data('symbol'));
			});
			modal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
				let element = $(e.currentTarget);

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
			modal.on(
				'change',
				'.activeCheckbox, .globalDiscount,.individualDiscountValue,.individualDiscountType,.groupCheckbox,.additionalDiscountValue',
				function () {
					thisInstance.calculateDiscount(parentRow, modal);
				}
			);
			modal.on('click', '.js-save-discount', function () {
				if (form.validationEngine('validate') === false) {
					return;
				}
				thisInstance.saveDiscountsParameters(parentRow, modal);
				if (params.discountType == 0) {
					thisInstance.setDiscount(parentRow, App.Fields.Double.formatToDb(modal.find('.valueDiscount').text()));
					thisInstance.quantityChangeActions(parentRow);
				} else {
					let rate =
						App.Fields.Double.formatToDb(modal.find('.valueDiscount').text()) /
						App.Fields.Double.formatToDb(modal.find('.valueTotalPrice').text());
					parentRow.find(thisInstance.rowClass).each(function () {
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
				let parentRow;
				let element = $(e.currentTarget);
				let params = {
					module: app.getModuleName(),
					view: 'Inventory',
					mode: 'showTaxes',
					currency: thisInstance.getCurrency(),
					relatedRecord: thisInstance.getAccountId()
				};
				if (element.hasClass('groupTax')) {
					parentRow = thisInstance.getInventoryItemsContainer();
					let totalPrice = 0;
					if (parentRow.find('tfoot .colNetPrice').length > 0) {
						totalPrice = parentRow.find('tfoot .colNetPrice').text();
					} else if (parentRow.find('tfoot .colTotalPrice ').length > 0) {
						totalPrice = parentRow.find('tfoot .colTotalPrice ').text();
					}
					params.totalPrice = App.Fields.Double.formatToDb(totalPrice);
					params.taxType = 1;
				} else {
					parentRow = element.closest(thisInstance.rowClass);
					let sourceRecord = parentRow.find('.rowName .sourceField').val();
					params.totalPrice = thisInstance.getNetPrice(parentRow);
					params.taxType = 0;
					if (sourceRecord) {
						params.record = sourceRecord;
					}
					params.recordModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
				}
				let progressInstace = $.progressIndicator();
				AppConnector.request(params)
					.done(function (data) {
						app.showModalWindow(data, function (data) {
							thisInstance.initTaxParameters(parentRow, $(data));
							thisInstance.registerChangeTaxModal(data, parentRow, params);
						});
						progressInstace.hide();
					})
					.fail(function () {
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
		registerChangeDiscountAggregation() {
			this.getInventoryHeadContainer().on('change', '.js-discount_aggreg', (e) => {
				this.rowsCalculations();
			});
		},
		registerChangeTaxModal: function (modal, parentRow, params) {
			let thisInstance = this;
			let form = modal.find('form');
			form.validationEngine(app.validationEngineOptions);
			modal.on('change', '.individualTaxType', function (e) {
				let element = $(e.currentTarget);
				modal.find('.individualTaxContainer .input-group-text').text(element.data('symbol'));
			});
			modal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
				let element = $(e.currentTarget);
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
			modal.on('change', '.activeCheckbox, .globalTax, .individualTaxValue, .groupTax, .regionalTax', function () {
				thisInstance.calculateTax(parentRow, modal);
			});
			modal.on('click', '.js-save-taxs', function () {
				if (form.validationEngine('validate') === false) {
					return;
				}
				thisInstance.saveTaxsParameters(parentRow, modal);
				if (params.taxType == '0') {
					thisInstance.setTax(parentRow, App.Fields.Double.formatToDb(modal.find('.valueTax').text()));
					thisInstance.setTaxPercent(parentRow, App.Fields.Double.formatToDb(modal.find('.js-tax-value').text()));
					thisInstance.quantityChangeActions(parentRow);
				} else {
					let rate =
						App.Fields.Double.formatToDb(modal.find('.valueTax').text()) /
						App.Fields.Double.formatToDb(modal.find('.valueNetPrice').text());
					parentRow.find(thisInstance.rowClass).each(function () {
						let totalPrice;
						if ($('.netPrice', $(this)).length > 0) {
							totalPrice = thisInstance.getNetPrice($(this));
						} else if ($('.totalPrice', $(this)).length > 0) {
							totalPrice = thisInstance.getTotalPrice($(this));
						}
						thisInstance.setTax($(this), totalPrice * rate);
						thisInstance.setTaxPercent($(this), App.Fields.Double.formatToDb(modal.find('.js-tax-value').text()));
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
				let record = params.record;
				let element = $(e.currentTarget);
				let parentRow = element.closest(thisInstance.rowClass);
				let selectedModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
				let dataUrl =
					'index.php?module=' +
					app.getModuleName() +
					'&action=Inventory&mode=getDetails&record=' +
					record +
					'&fieldname=' +
					element.data('columnname');
				if (thisInstance.getCurrency()) {
					dataUrl += '&currency_id=' + thisInstance.getCurrency();
				}
				AppConnector.request(dataUrl).done(function (data) {
					for (let id in data) {
						if (typeof data[id] == 'object') {
							let recordData = data[id];
							thisInstance.mapResultsToFields(selectedModule, parentRow, recordData);
						}
					}
				});
			});
		},

		/**
		 * Mass add entries.
		 */
		registerMassAddItem: function () {
			this.getForm().on('click', '.js-mass-add', (e) => {
				let currentTarget = $(e.currentTarget);
				let moduleName = currentTarget.data('module');
				let url = currentTarget.data('url');
				app.showRecordsList(url, (_, instance) => {
					instance.setSelectEvent((data) => {
						for (let i in data) {
							let parentElem = this.addItem(moduleName);
							Vtiger_Edit_Js.getInstance().setReferenceFieldValue(parentElem, {
								name: data[i],
								id: i
							});
						}
					});
				});
			});
		},

		calculateItemNumbers: function () {
			let thisInstance = this;
			let items = this.getInventoryItemsContainer();
			let i = 1;
			items.find(thisInstance.rowClass).each(function () {
				$(this).find('.itemNumberText').text(i);
				i++;
			});
		},
		initItem: function (container) {
			let thisInstance = this;
			if (typeof container === 'undefined') {
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
			App.Fields.Text.Editor.register(container);
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
			const progressLoader = $.progressIndicator({ blockInfo: { enabled: true } });
			return new Promise((resolve, reject) => {
				AppConnector.request({
					module: app.getModuleName(),
					src_module: sourceModule,
					src_record: recordId,
					action: 'Inventory',
					mode: 'getTableData',
					record: app.getRecordId()
				})
					.done((response) => {
						let activeModules = [];
						this.getInventoryHeadContainer()
							.find('.js-inv-add-item')
							.each((_, addBtn) => {
								activeModules.push($(addBtn).data('module'));
							});
						progressLoader.progressIndicator({ mode: 'hide' });
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
						this.clearInventory();
						$.each(response.result, (_, row) => {
							if (activeModules.indexOf(row.moduleName) !== -1) {
								this.addItem(row.moduleName, row.basetableid, row);
							} else {
								Vtiger_Helper_Js.showMessage({
									type: 'error',
									text: app
										.vtranslate('JS_INVENTORY_ITEM_MODULE_NOT_FOUND')
										.replace('${sourceModule}', row.moduleName)
										.replace('${position}', row.info.name)
								});
							}
						});
						this.summaryCalculations();
						resolve(response.result);
						if (typeof success === 'function') {
							success(response.result);
						}
					})
					.fail((error, err) => {
						progressLoader.progressIndicator({ mode: 'hide' });
						reject(error, err);
						if (typeof fail === 'function') {
							fail(error, err);
						}
					});
			});
		},
		/**
		 * Clear inventory data
		 */
		clearInventory: function () {
			this.getInventoryItemsContainer()
				.find('.inventoryRow')
				.each((_, e) => {
					let num = $(e).attr('numrow');
					this.deleteLineItem(num);
				});
		},
		/**
		 * Function which will register all the events
		 */
		registerEvents: function (container) {
			this.form = container;
			this.loadConfig();
			this.registerInventorySaveData();
			this.registerAddItem();
			this.registerMassAddItem();
			this.initItem();
			this.registerSortableItems();
			this.registerSubProducts();
			this.registerChangeDiscount();
			this.registerChangeTax();
			this.registerClearReferenceSelection();
			this.registerShowHideExpanded();
			this.registerChangeCurrency();
			this.registerChangeDiscountAggregation();
			this.setDefaultGlobalTax(container);
		}
	}
);
