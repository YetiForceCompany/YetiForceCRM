/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.AppComponents_CurrencyConverter_Js = class {
	/**
	 * Register change currency
	 */
	registerChangeCurrency() {
		this.container.on('change', '.js-currencyc_list', (e) => {
			this.container
				.find('.js-currency-conv-rate')
				.text(App.Fields.Double.formatToDisplay(e.currentTarget.selectedOptions[0].dataset.conversionRate));
			this.container.find('.js-currency-conv-date').text(e.currentTarget.selectedOptions[0].dataset.conversionDate);
			this.container.find('.js-currencyc_value').first().trigger('keyup');
		});
		this.container.find('.js-currencyc_list').trigger('change');
	}
	/**
	 * Register convert currency value
	 */
	registerConvert() {
		let fields = this.container.find('.js-currencyc_value');
		fields.on('keyup focusout', (e) => {
			let value = App.Fields.Double.formatToDb(e.currentTarget.value);
			let currentCurrencyData = $(e.currentTarget).parent().find('.js-currencyc_list option:selected').data();
			fields.each((_n, ve) => {
				let currencyData = $(ve).parent().find('.js-currencyc_list option:selected').data();
				if (currentCurrencyData.currencyId === currencyData.currencyId) {
					return;
				}
				$(ve).val(
					App.Fields.Double.formatToDisplay((value * currencyData.conversionRate) / currentCurrencyData.conversionRate),
					false
				);
			});
		});
	}
	/**
	 * Register events
	 */
	registerEvents(container) {
		this.container = container;
		this.form = container.find('form');
		App.Fields.Text.registerCopyClipboard(this.container);
		this.registerConvert();
		this.registerChangeCurrency();
	}
};
