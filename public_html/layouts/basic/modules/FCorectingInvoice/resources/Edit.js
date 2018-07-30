/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Vtiger_Edit_Js('FCorectingInvoice_Edit_Js',{},{

	setReferenceFieldValue(container, params){
		this._super(container, params);
		const invoiceidInput = container.find('[name="finvoiceid"]');
		const formContainer = container.closest('.recordEditView');
		if(invoiceidInput.length){
			AppConnector.request({
				module:'FCorectingInvoice',
				mode:'get',
				view:'FInvoiceRecords',
				record:params.id
			}).done((response)=>{
				formContainer.find('#beforeInventory').html(response);
			});
		}
	},

	clearFieldValue(element){
		this._super(element);
		const invoiceidInput = element.closest('.referenceGroup').find('[name="finvoiceid"]');
		if(invoiceidInput.length){
			this.getContainer().find('#beforeInventory').html('');
		}
	}


});

