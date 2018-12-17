/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

App.Tools = {
	VariablesPanel: {
		refreshCompanyVariables(container) {
			const companyId = container.find(".companyList").val();
			container.find(".companyVariable > optgroup > option").each(function () {
				let template = $(this).data('value-template');
				this.value = template.replace(/__X__/i, companyId);
			});
		},
		registerRefreshCompanyVariables(container) {
			container.find('.companyList').on('change', function (e) {
				App.Tools.VariablesPanel.refreshCompanyVariables(container);
			});
		},
	}
};