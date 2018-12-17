/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

App.Tools = {
	variablesPanel: {
		refreshCompanyVariables: function (container) {
			const companyId = container.find(".companyList").val();
			container.find(".companyVariable > optgroup > option").each(function () {
				let template = $(this).data('value-template');
				this.value = template.replace(/__X__/i, companyId);
			});
		},
		registerRefreshCompanyVariables: function (container) {
			container.find('.companyList').on('change', function (e) {
				App.Tools.variablesPanel.refreshCompanyVariables(container);
			});
		},
	}
};