/* {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
		this.registerShopSearch();
		this.registerCategories();
		this.registerSwitch();
		this.registerRefresh();
	}
	/**
	 * Register events.
	 */
	registerShopSearch() {
		let searchField = this.container.find('.js-shop-search');
		searchField = searchField.length ? searchField : $('.js-shop-search');
		searchField
			.on('keyup', (e) => {
				let value = $(e.currentTarget).val().toLowerCase();
				this.container.find('.js-product .js-text-search').filter(function () {
					let item = $(this).closest('.js-product');
					if ($(this).text().toLowerCase().indexOf(value) > -1) {
						item.removeClass('d-none');
					} else {
						item.addClass('d-none');
					}
				});
			})
			.on('click', (e) => {
				e.stopPropagation();
			});
	}
	/**
	 * Register product modal click.
	 *
	 */
	registerProductModalClick() {
		this.container.find('.js-product').on('click', (e) => {
			const target = $(e.target);
			if (target.hasClass('js-product-switch') || target.closest('.js-stop-parent-trigger').length) {
				return;
			}
			let progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
			const currentTarget = $(e.currentTarget);
			this.showProductModal(currentTarget.data('product'), currentTarget.data('productId'));
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		});
	}
	/**
	 * Register switch
	 */
	registerSwitch() {
		this.container.find('.js-product-switch').on('change', (e) => {
			const currentTarget = $(e.currentTarget);
			let isChecked = currentTarget.is(':checked');
			let confirm = currentTarget.data('confirm');
			if (confirm) {
				app.showConfirmModal({
					title: confirm,
					confirmedCallback: () => {
						let url = isChecked ? currentTarget.data('url-on') : currentTarget.data('url-off');
						if (url) {
							$.progressIndicator({ blockInfo: { enabled: true } });
							AppConnector.request(url).done((_) => {
								window.location.reload();
							});
						}
					},
					rejectedCallback: () => {
						currentTarget.prop('checked', !isChecked);
					}
				});
			}
		});
	}
	/**
	 * Show product modal action.
	 *
	 * @param   {string}  productName
	 */
	showProductModal(productName, productId = '') {
		app.showModalWindow(
			null,
			`${this.modalUrl}&view=ProductModal&product=${productName}&productId=${productId}`,
			(modalContainer) => {
				modalContainer.find('.js-modal__save').on('click', (_) => {
					app.hideModalWindow();
					this.showBuyModal(productName, productId);
				});
			}
		);
	}
	/**
	 * Register buy modal click.
	 *
	 */
	registerBuyModalClick() {
		this.container.find('.js-buy-modal').on('click', (e) => {
			let progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
			e.stopPropagation();
			const currentTarget = $(e.currentTarget);
			this.showBuyModal(currentTarget.data('product'), currentTarget.data('productId'));
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		});
	}
	/**
	 * Show buy modal action.
	 *
	 * @param   {string}  productName
	 * @param   {string}  productId
	 */
	showBuyModal(productName, productId) {
		app.showModalWindow(
			null,
			`${this.modalUrl}&view=BuyModal&product=${productName}${productId ? '&productId=' + productId : ''}`,
			this.registerBuyModalEvents.bind(this)
		);
	}

	registerBuyModalEvents(modalContainer) {
		const buyForm = modalContainer.find('.js-buy-form');
		const companyForm = modalContainer.find('.js-update-company-form');
		modalContainer.find('.js-modal__save').on('click', (_) => {
			this.registerBuyModalForms(companyForm, buyForm);
		});
		companyForm.validationEngine(app.validationEngineOptions);
		companyForm.find('[data-inputmask]').inputmask();
		if (buyForm.length) {
			buyForm.validationEngine(app.validationEngineOptions);
			buyForm.find('[data-inputmask]').inputmask();
		}
		modalContainer.find('.js-price-by-size').on('change', (e) => {
			let dataset = e.currentTarget.selectedOptions[0].dataset;
			for (let d in dataset) {
				modalContainer.find(`.js-buy-text[data-key="${d}"]`).text(dataset[d]);
				modalContainer.find(`.js-buy-value[name="${d}"]`).val(dataset[d]);
			}
		});
	}
	registerBuyModalForms(companyForm, buyForm) {
		if (companyForm.validationEngine('validate') === true) {
			let formData = companyForm.serializeFormData();
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: { enabled: true }
			});
			AppConnector.request(formData).done((data) => {
				let response = data.result;
				if (data.success && response && response.success && response.orderId) {
					let customField = buyForm.find('input[name="custom"]');
					customField.val(`${customField.val()}|${response.orderId}`);
					buyForm.submit();
					app.hideModalWindow();
				} else {
					app.showNotify({
						text: response?.message || app.vtranslate('JS_ERROR'),
						type: data.result.type,
						hide: true,
						delay: 8000,
						textTrusted: false
					});
				}
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
			});
		} else {
			app.formAlignmentAfterValidation(companyForm);
		}
	}
	/**
	 * Register categories.
	 *
	 */
	registerCategories() {
		this.container.find('.js-select-category').on('click', (e) => {
			this.changeCategory($(e.currentTarget).data('tab'));
		});
		this.changeCategory(this.container.find('.js-select-category.active').data('tab'));
	}
	/**
	 * Register categories.
	 *
	 */
	changeCategory(category) {
		this.container.find('.js-nav-premium .js-product').each(function () {
			let product = $(this);
			if (category === 'All') {
				product.removeClass('d-none');
			} else if (product.data('category') === category) {
				product.removeClass('d-none');
			} else {
				product.addClass('d-none');
			}
		});
	}

	/** Check registration status */
	registerRefresh() {
		this.container.find('.js-refresh-status').on('click', function () {
			const progressIndicator = $.progressIndicator({
				blockInfo: { enabled: true }
			});
			AppConnector.request({
				parent: 'Settings',
				module: 'Companies',
				action: 'CheckStatus'
			}).done((data) => {
				progressIndicator.progressIndicator({ mode: 'hide' });
				if (data.success && data.result) {
					if (data.result.message) {
						app.showNotify({
							text: data.result.message,
							type: data.result.type,
							hide: true,
							delay: 8000,
							textTrusted: false
						});
					}
					if (data.result.success) {
						window.location.reload();
					}
				}
			});
		});
	}
};
