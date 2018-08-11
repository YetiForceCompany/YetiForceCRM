/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Vtiger_Edit_Js('FCorectingInvoice_Edit_Js', {}, {

	/**
	 * Load correcting invoice data to before block
	 */
	loadInvoiceData(container) {
		const invoiceidInput = container.find('[name="finvoiceid"]');
		if (invoiceidInput.length && invoiceidInput.val()) {
			const form = this.getForm();
			const progressLoader = $.progressIndicator({'blockInfo': {'enabled': true}});
			AppConnector.request({
				module: 'FInvoice',
				record: invoiceidInput.val(),
				mode: 'showInventoryDetails',
				view: 'Detail'
			}).done((response) => {
				form.find('#beforeInventory').html(response);
				progressLoader.progressIndicator({mode: 'hide'});
			}).fail(() => {
				progressLoader.progressIndicator({mode: 'hide'});
			});
		}
	},
	/**
	 * setReferenceFieldValue override - action when correcting invoice is selected
	 * @param {jQuery} container
	 * @param {} params
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
	 * Action for copy from correcting invoice button - load data before positions to position in data after block
	 */
	registerCopyFromInvoice() {
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
				action: 'GetInventoryTable',
				record: finvoiceid
			}).done((response) => {
				progressLoader.progressIndicator({mode: 'hide'});
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
				response.result.forEach((row) => {
					if (activeModules.indexOf(row.moduleName) !== -1) {
						inventoryController.addItem(row.moduleName, row.basetableid, row);
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
		this.getForm().find('.c-panel__header .js-popover-tooltip').on('click', (e) => {
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
		this.loadInvoiceData(this.getForm());
	}

});

