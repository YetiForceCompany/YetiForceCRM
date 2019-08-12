/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Settings_YetiForce_Shop_Js.
 * @type {window.Settings_YetiForce_Shop_Js}
 */
window.Settings_YetiForce_Shop_Js = class Settings_YetiForce_Shop_Js {
	/**
	 * Constructor.
	 */
	constructor(modalUrl = 'index.php?module=YetiForce&parent=Settings') {
		this.container = $('.js-products-container');
		this.modalUrl = modalUrl;
	}
	/**
	 * Register events.
	 */
	registerEvents() {
		this.registerProductModalClick();
		this.registerBuyModalClick();
	}
	/**
	 * Register product modal click.
	 *
	 */
	registerProductModalClick() {
		this.container.find('.js-product').on('click', e => {
			const currentTarget = $(e.currentTarget);
			this.showProductModal(currentTarget.data('product'), this.getDepartment(currentTarget));
		});
	}
	/**
	 * Show product modal action.
	 *
	 * @param   {string}  productName
	 * @param   {string}  department
	 */
	showProductModal(productName, department) {
		app.showModalWindow(
			null,
			`${this.modalUrl}&view=ProductModal&product=${productName}&department=${department}`,
			modalContainer => {
				modalContainer.find('.js-modal__save').on('click', _ => {
					app.hideModalWindow();
					this.showBuyModal(productName, department);
				});
			}
		);
	}
	/**
	 * Register buy modal click.
	 *
	 */
	registerBuyModalClick() {
		this.container.find('.js-buy-modal').on('click', e => {
			e.stopPropagation();
			const currentTarget = $(e.currentTarget);
			console.log(currentTarget.data('product'));
			this.showBuyModal(currentTarget.data('product'), this.getDepartment(currentTarget));
		});
	}
	/**
	 * Show buy modal action.
	 *
	 * @param   {string}  productName
	 * @param   {string}  department
	 */
	showBuyModal(productName, department) {
		AppConnector.request(`${this.modalUrl}&view=ProductModal&product=${productName}&department=${department}`).done(
			res => {
				console.log(res);
			}
		);
		app.showModalWindow(
			null,
			`${this.modalUrl}&view=BuyModal&product=${productName}${department ? '&department=' + department : ''}`,
			this.registerBuyModalEvents.bind(this)
		);
		console.log(this.modalUrl);
	}

	registerBuyModalEvents(modalContainer) {
		const companyForm = modalContainer.find('.js-update-company-form');
		const buyForm = modalContainer.find('.js-buy-form');
		modalContainer.find('.js-modal__save').on('click', _ => {
			this.registerBuyModalForms(companyForm, buyForm);
		});
		if (companyForm.length) {
			companyForm.validationEngine(app.validationEngineOptions);
			companyForm.find('[data-inputmask]').inputmask();
		}
	}
	registerBuyModalForms(companyForm, buyForm) {
		if (companyForm.length) {
			if (companyForm.validationEngine('validate') === true) {
				app.removeEmptyFilesInput(companyForm[0]);
				const formData = new FormData(companyForm[0]);
				const params = {
					url: 'index.php',
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				};
				const progressIndicatorElement = $.progressIndicator({
					blockInfo: { enabled: true }
				});
				AppConnector.request(params).done(data => {
					if (data.success) {
						buyForm.submit();
						app.hideModalWindow();
					}
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			} else {
				app.formAlignmentAfterValidation(companyForm);
			}
		} else {
			buyForm.submit();
			app.hideModalWindow();
		}
	}
	/**
	 * Get department.
	 *
	 * @param   {object}  element  jQuery
	 *
	 * @return  {string}
	 */
	getDepartment(element) {
		let department = element.closest('.js-department');
		return department.length ? department.data('department') : '';
	}
};
