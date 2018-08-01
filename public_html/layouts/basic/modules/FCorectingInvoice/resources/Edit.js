/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Vtiger_Edit_Js('FCorectingInvoice_Edit_Js', {}, {

	/**
	 * Load correcting invoice data to before block
	 */
	loadInvoiceData() {
		const form = this.getForm();
		const invoiceidInput = form.find('[name="finvoiceid"]');
		if (invoiceidInput.length && invoiceidInput.val()) {
			const progressLoader = $.progressIndicator({'blockInfo': {'enabled': true}});
			AppConnector.request({
				module: 'FCorectingInvoice',
				mode: 'get',
				view: 'FInvoiceRecords',
				record: invoiceidInput.val()
			}).done((response) => {
				form.find('#beforeInventory').html(response);
				progressLoader.progressIndicator({mode: 'hide'});
			});
		}
	},
	/**
	 * setReferenceFieldValue override - action when correcting invoice is selected
	 * @param container
	 * @param params
	 */
	setReferenceFieldValue(container, params) {
		this._super(container, params);
		this.loadInvoiceData(container);
	},
	/**
	 * clearFieldValue override - action when correcting invoice is cleared
	 * @param element
	 */
	clearFieldValue(element) {
		this._super(element);
		const invoiceidInput = element.closest('.fieldValue').find('[name="finvoiceid"]');
		if (invoiceidInput.length) {
			element.closest('form').find('#beforeInventory').html('<div class="text-center">' + app.vtranslate('JS_FCORECTINGINVOICE_CHOOSE_INVOICE') + '</div>');
		}
	},
	/**
	 * Action for copy from correcting invoice button - load data before positions to data after block
	 */
	registerCopyFromInvoice() {
		const thisInstance = this;
		const form = this.getForm();
		this.activeModules = activeModules = [];
		form.find('.addItem').each((index, addBtn) => {
			this.activeModules.push($(addBtn).data('module'));
		});
		form.find('#copyFromInvoice').on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			const finvoiceidInput = form.find('input[name="finvoiceid"]');
			if (!finvoiceidInput.length) {
				return false;
			}
			const finvoiceid = finvoiceidInput.val();
			if (!finvoiceid) {
				return Vtiger_Helper_Js.showMessage({
					type: 'error',
					text: app.vtranslate('JS_FCORECTINGINVOICE_CHOOSE_INVOICE')
				});
			}
			const progressLoader = $.progressIndicator({'blockInfo': {'enabled': true}});
			AppConnector.request({
				module: 'FCorectingInvoice',
				action: 'GetProductsAndServices',
				record: finvoiceid
			}).done((response) => {
				progressLoader.progressIndicator({mode: 'hide'});
				const items = inventoryController.getInventoryItemsContainer();
				const recordsBefore = items.find(inventoryController.rowClass).length;
				const oldCurrencyChangeAction = inventoryController.currencyChangeActions;
				inventoryController.currencyChangeActions = function changeCurrencyActions(select, option) {
					this.currencyConvertValues(select, option);
					select.data('oldValue', select.val());
				};
				const first = response.result[0];
				form.find('[name="currencyparam"]').val(first.currencyparam);
				form.find('[name="currency"]').val(first.currency).trigger('change');
				form.find('[name="discountmode"]').val(first.discountmode).trigger('change');
				form.find('[name="taxmode"]').val(first.taxmode).trigger('change');
				inventoryController.currencyChangeActions = oldCurrencyChangeAction;
				response.result.forEach((row, index) => {
					if (activeModules.indexOf(row.moduleName) !== -1) {
						form.find('.addItem[data-module="' + row.moduleName + '"]').eq(0).trigger('click');
						const realIndex = recordsBefore + index + 1;
						const rows = items.find(inventoryController.rowClass);
						const rowElem = rows.eq(index + recordsBefore);
						rowElem.find('input[name="name' + realIndex + '"]').val(row.name).trigger('change');
						rowElem.find('input[name="name' + realIndex + '_display"]').val(row.info.name).attr('readonly', 'true').trigger('change');
						rowElem.find('.qty').val(row.qty).trigger('change');
						rowElem.find('.unitText').text(row.info.autoFields.unitText).trigger('change');
						rowElem.find('input[name="unit' + realIndex + '"]').val(row.info.autoFields.unit).trigger('change');
						if (typeof row.info.autoFields.subunit !== 'undefined') {
							rowElem.find('input[name="subunit' + realIndex + '"]').val(row.info.autoFields.subunit);
							rowElem.find('.subunitText').text(row.info.autoFields.subunitText);
						}
						rowElem.parent().find('[numrowex=' + realIndex + ']').find('textarea').val(row.comment1).trigger('change');
						inventoryController.setUnitPrice(rowElem, row.price);
						inventoryController.setNetPrice(rowElem, row.net);
						inventoryController.setGrossPrice(rowElem, row.gross);
						inventoryController.setTotalPrice(rowElem, row.total);
						inventoryController.setDiscountParam(rowElem, JSON.parse(row.discountparam));
						inventoryController.setDiscount(rowElem, row.discount);
						inventoryController.setTaxParam(rowElem, JSON.parse(row.taxparam));
						inventoryController.setTax(rowElem, row.tax);
					} else {
						Vtiger_Helper_Js.showMessage({
							type: 'error',
							text: app.vtranslate('JS_FCORECTINGINVOICE_ITEM_MODULE_NOT_FOUND').replace('${module}', row.moduleName).replace('${position}', row.info.name)
						});
					}
				});
				inventoryController.summaryCalculations();
			});
		});
	},
	/**
	 * prevent popovers to show/hide block
	 */
	registerPopoverClick() {
		this.getForm().find('.js-popover-tooltip').on('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
		});
	},
	/**
	 * registerEvents override
	 */
	registerEvents() {
		this._super();
		this.registerPopoverClick();
		this.registerCopyFromInvoice();
		this.loadInvoiceData();
	}

});

