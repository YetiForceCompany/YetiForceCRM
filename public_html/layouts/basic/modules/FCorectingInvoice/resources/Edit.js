/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Vtiger_Edit_Js('FCorectingInvoice_Edit_Js', {}, {

	/**
	 * Load correcting invoice data to before block
	 * @param {int} recordId
	 */
	loadInvoiceData(recordId = false) {
		if (!recordId) {
			recordId = this.getForm().find('input[name="finvoiceid"]').val();
		}
		if (recordId) {
			const form = this.getForm();
			const progressLoader = $.progressIndicator({'blockInfo': {'enabled': true}});
			AppConnector.request({
				module: 'FInvoice',
				record: recordId,
				mode: 'showInventoryDetails',
				view: 'Detail'
			}).done((response) => {
				form.find('.js-before-inventory').html(response);
				progressLoader.progressIndicator({mode: 'hide'});
			}).fail(() => {
				progressLoader.progressIndicator({mode: 'hide'});
			});
		}
	},
	/**
	 * register reference fields events
	 */
	registerReferenceFieldsEvents() {
		app.event.on("EditView.SelectReference", (e, params) => {
			if (params.source_module === 'FInvoice') {
				this.loadInvoiceData(params.record);
			}
		});
		const form = this.getForm();
		app.event.on("EditView.ClearField", (e, params) => {
			if (params.fieldName === 'finvoiceid') {
				const invoiceidInput = form.find('[name="finvoiceid"]');
				if (invoiceidInput.length) {
					form.find('.js-before-inventory').html('<div class="text-center">' + app.vtranslate('JS_FCORECTINGINVOICE_CHOOSE_INVOICE') + '</div>');
				}
			}
		});
	},
	/**
	 * Action for copy from correcting invoice button - load data before positions to position in data after block
	 */
	registerCopyFromInvoice() {
		const form = this.getForm();
		const thisInstance = this;
		let activeModules = this.activeModules = [];
		form.find('.js-add-item').each((index, addBtn) => {
			this.activeModules.push($(addBtn).data('module'));
		});
		form.find('.js-copy-from-invoice').on('click', function (e) {
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
			thisInstance.inventoryController.loadInventoryData(finvoiceid, 'FInvoice');
		});
	},
	/**
	 * registerEvents override
	 */
	registerEvents() {
		this._super();
		this.registerCopyFromInvoice();
		this.registerReferenceFieldsEvents();
		this.loadInvoiceData();
	}

});

