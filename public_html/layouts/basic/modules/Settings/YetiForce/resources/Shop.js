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
			this.showProductModal($(e.currentTarget).data('product'));
		});
	}
	showProductModal(productName) {
		app.showModalWindow(
			null,
			`index.php?module=YetiForce&parent=Settings&view=ProductModal&product=${productName}`,
			modalContainer => {
				modalContainer.find('.js-modal__save').on('click', _ => {
					app.hideModalWindow();
					this.showBuyModal(productName);
				});
			}
		);
	}
	registerBuyModalClick() {
		this.container.find('.js-buy-modal').on('click', e => {
			e.stopPropagation();
			this.showBuyModal($(e.currentTarget).data('product'));
		});
	}
	showBuyModal(productName) {
		app.showModalWindow(
			null,
			`index.php?module=YetiForce&parent=Settings&view=BuyModal&product=${productName}`,
			modalContainer => {
				modalContainer.find('.js-modal__save').on('click', _ => {
					modalContainer.find('form').submit();
					app.hideModalWindow();
				});
			}
		);
	}
};
