/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'SRequirementsCards_Edit_Js',
	{},
	{
		getRecordsListParams: function (container) {
			var params = this._super(container);
			// Limit the choice of products/services only to the ones related to currently selected Opportunity - first step.
			let potential = this.getForm().find('input[name="salesprocessid"]');
			if (jQuery.inArray(params.module, ['Products', 'Services']) != -1 && potential.length) {
				params.salesprocessid = potential.val();
			}
			return params;
		}
	}
);
