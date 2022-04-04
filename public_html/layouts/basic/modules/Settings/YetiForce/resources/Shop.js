/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
		this.showInitialModal();
	}
	showInitialModal() {
		const request = app.convertUrlToObject(window.location.href);
		if (request.mode) {
			if (request.showBuyModal === 'buy') {
				this.showBuyModal(request.product, request.department);
			} else if (request.mode === 'showProductModal') {
				this.showProductModal(request.product, request.department);
			}
		}
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
			const currentTarget = $(e.currentTarget);
			this.showProductModal(currentTarget.data('product'), this.getDepartment(currentTarget));
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
	 * @param   {string}  department
	 */
	showProductModal(productName, department) {
		app.showModalWindow(
			null,
			`${this.modalUrl}&view=ProductModal&product=${productName}${department ? '&department=' + department : ''}`,
			(modalContainer) => {
				modalContainer.find('.js-modal__save').on('click', (_) => {
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
		this.container.find('.js-buy-modal').on('click', (e) => {
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
			`${this.modalUrl}&view=BuyModal&product=${productName}${department ? '&department=' + department : ''}`,
			this.registerBuyModalEvents.bind(this)
		);
	}

	registerBuyModalEvents(modalContainer) {
		const companyForm = modalContainer.find('.js-update-company-form');
		const buyForm = modalContainer.find('.js-buy-form');
		modalContainer.find('.js-modal__save').on('click', (_) => {
			this.registerBuyModalForms(companyForm, buyForm);
		});
		if (companyForm.length) {
			companyForm.validationEngine(app.validationEngineOptions);
			companyForm.find('[data-inputmask]').inputmask();
		}
		if (buyForm.length) {
			buyForm.validationEngine(app.validationEngineOptions);
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
				AppConnector.request(params).done((data) => {
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
			if (buyForm.validationEngine('validate') === true) {
				this.updateCustomData(buyForm);
				buyForm.submit();
				app.hideModalWindow();
			} else {
				app.formAlignmentAfterValidation(buyForm);
			}
		}
	}
	/**
	 * Update custom data.
	 */
	updateCustomData(buyForm) {
		let customField = buyForm.find('.js-custom-data');
		let priceBySize = buyForm.find('.js-price-by-size');
		if (customField.length) {
			let customFields = buyForm.find('.js-custom-field');
			customFields.each((i, el) => {
				let field = $(el);
				customField.val(
					`${customField.val()}${field.data('name')}::${field.val()}${customFields.length - 1 !== i ? '|' : ''}`
				);
			});
		}
		if (priceBySize.length) {
			priceBySize
				.siblings('.js-price-by-size-input')
				.val(priceBySize.find(`option[value="${priceBySize.val()}"]`).data('os0'));
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
};
