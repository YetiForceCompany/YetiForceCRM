/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

App.Tools = {
	VariablesPanel: {
		/**
		 * Generate values based on selected company
		 * @param container
		 */
		refreshCompanyVariables(container) {
			const companyId = container.find(".js-company-list").val();
			container.find(".js-company-variable > optgroup > option").each(function () {
				let template = $(this).data('value-template');
				this.value = template.replace(/__X__/i, companyId);
			});
		},
		/**
		 * Register change company event
		 * @param container
		 */
		registerRefreshCompanyVariables(container) {
			container.find('.js-company-list').on('change', function (e) {
				App.Tools.VariablesPanel.refreshCompanyVariables(container);
			});
		},
	}
};