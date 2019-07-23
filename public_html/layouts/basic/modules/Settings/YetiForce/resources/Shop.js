/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Settings_YetiForce_Shop_Js = class Settings_YetiForce_Shop_Js {

	constructor() {
		this.container = $('.js-products-container');
	}

	registerEvents() {
		this.registerProductModalClick();
		this.registerBuyModalClick();
	}

	registerProductModalClick() {
		this.container.find('.js-product-modal').on('click', e => {
			const currentTarget = $(e.currentTarget)
			this.showProductModal(currentTarget.data('product'), this.getDepartment(currentTarget));
		});
	}

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

	registerBuyModalClick() {
		this.container.find('.js-buy-modal').on('click', e => {
			e.stopPropagation();
			const currentTarget = $(e.currentTarget)
			this.showBuyModal(currentTarget.data('product'), this.getDepartment(currentTarget));
		});
	}

	showBuyModal(productName, department) {
		console.log(`index.php?module=YetiForce&parent=Settings&view=BuyModal&product=${productName}${department ? '&department=' + department : ''}`)
		app.showModalWindow(
			null,
			`index.php?module=YetiForce&parent=Settings&view=BuyModal&product=${productName}${department ? '&department=' + department : ''}`,
			modalContainer => {
				modalContainer.find('.js-modal__save').on('click', _ => {
					modalContainer.find('form').submit();
					app.hideModalWindow();
				});
			}
		);
	}

	getDepartment(element) {
		let department = element.closest('.js-department');
		return department.length ? department.data('department') : '';
	}

};
