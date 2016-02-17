/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

Vtiger_Edit_Js("SCalculations_Edit_Js", {
}, {
	inventoryFieldToCopy: {sum_margin: 'margin', sum_marginp: 'marginP', sum_total: 'totalPrice'},
	getPopUpParams: function (container) {
		var params = this._super(container);
		// Limit the choice of products/services only to the ones related to currently selected Opportunity - first step.
		var potential = jQuery('input[name="salesprocessid"]');
		if (jQuery.inArray(params.module, ['Products', 'Services']) != -1 && potential.length) {
			params.salesprocessid = potential.val();
		}
		return params;
	},
	copyInventorySummary: function (form) {
		var thisInstance = this;
		for (var i in this.inventoryFieldToCopy) {
			var source = jQuery('.inventoryItems tfoot .wisableTd[data-sumfield="' + this.inventoryFieldToCopy[i] + '"]');
			var target = form.find('input[name="' + i + '"]');
			if (source.length) {
				thisInstance.setInventorySummaryField(source, target, form, i);
			}
		}
	},
	setInventorySummaryField: function (source, target, form, name) {
		var thisInstance = this;
		if (target.length) {
			target.val(app.parseNumberToFloat(source.text()));
		} else {
			form.append('<input name="' + name + '" value="' + app.parseNumberToFloat(source.text()) + '">');
		}
	},
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent: function (form) {
		var thisInstance = this;
		if (typeof form == 'undefined') {
			form = this.getForm();
		}
		form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			thisInstance.copyInventorySummary(form);
		})
	},
	registerBasicEvents: function (container) {
		this._super(container);
		this.registerRecordPreSaveEvent(container);
	}
});

