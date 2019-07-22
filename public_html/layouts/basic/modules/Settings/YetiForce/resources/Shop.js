/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Settings_YetiForce_Shop_Js = class Settings_YetiForce_Shop_Js {
	constructor() {}
	registerEvents() {
		this.showProductModal();
	}
	showProductModal() {
		$('.js-product-modal').on('click', e => {
			app.showModalWindow(
				null,
				`index.php?module=YetiForce&parent=Settings&view=ProductModal&product=${$(e.currentTarget).data('product')}`
			);
		});
	}
};
