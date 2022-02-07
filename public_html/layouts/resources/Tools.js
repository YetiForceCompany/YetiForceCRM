/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

App.Tools = {
	VariablesPanel: {
		/**
		 * Generate values based on selected company
		 * @param container
		 */
		refreshCompanyVariables(container) {
			const companyId = container.find('.js-company-list').val();
			container.find('.js-company-variable > optgroup > option').each(function () {
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
		}
	},
	Form: {
		/**
		 * Generate values based on selected company
		 * @param {jQuery} container
		 */
		registerBlockToggle(container) {
			container.on('click', '.js-toggle-block', function (e) {
				const target = $(e.target);
				if (
					target.is('input') ||
					target.is('button') ||
					target.parents().is('button') ||
					target.hasClass('js-stop-propagation') ||
					target.parents().hasClass('js-stop-propagation')
				) {
					return false;
				}
				const blockHeader = $(e.currentTarget);
				const blockContents = blockHeader.next();
				const icon = blockHeader.find('.js-toggle-icon');
				if (blockContents.hasClass('d-none')) {
					blockContents.removeClass('d-none');
					icon.removeClass(icon.data('hide')).addClass(icon.data('show'));
				} else {
					blockContents.addClass('d-none');
					icon.removeClass(icon.data('show')).addClass(icon.data('hide'));
				}
			});
		}
	}
};
