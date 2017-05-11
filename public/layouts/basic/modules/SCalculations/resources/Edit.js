/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

Vtiger_Edit_Js("SCalculations_Edit_Js", {
}, {
	getPopUpParams: function (container) {
		var params = this._super(container);
		// Limit the choice of products/services only to the ones related to currently selected Opportunity - first step.
		var potential = jQuery('input[name="salesprocessid"]');
		if (jQuery.inArray(params.module, ['Products', 'Services']) != -1 && potential.length) {
			params.salesprocessid = potential.val();
		}
		return params;
	},
});

