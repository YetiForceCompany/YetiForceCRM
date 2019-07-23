/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Settings_YetiForce_BuyModal_Js = class Settings_YetiForce_BuyModal_Js {
	constructor() {
		this.container = $("[data-view='ProductModal']");
		this.registerEvents();
	}
	/**
	 * Register events for form checkbox element
	 */
	registerEvents() {
		this.container.find('[name="saveButton"]').on('click', e => {
			app.hideModalWindow();
			app.showModalWindow(
				null,
				`index.php?module=YetiForce&parent=Settings&view=BuyModal&product=${this.container
					.find('.js-data')
					.data('product')}`
			);
		});
	}
};

new window.Settings_YetiForce_BuyModal_Js();
