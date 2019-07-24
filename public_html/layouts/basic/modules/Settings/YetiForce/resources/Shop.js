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
	constructor() {
		this.container = $('.js-products-container');
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
		this.container.find('.js-product-modal').on('click', e => {
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
			`index.php?module=YetiForce&parent=Settings&view=ProductModal&product=${productName}&department=${department}`,
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
		app.showModalWindow(
			null,
			`index.php?module=YetiForce&parent=Settings&view=BuyModal&product=${productName}${
				department ? '&department=' + department : ''
			}`,
			modalContainer => {
				modalContainer.find('.js-modal__save').on('click', _ => {
					modalContainer.find('form').submit();
					app.hideModalWindow();
				});
			}
		);
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
