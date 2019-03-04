/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

Vtiger_Edit_Js("Products_Edit_Js", {}, {
	baseCurrency: '',
	baseCurrencyName: '',
	//Container which stores unit price
	unitPrice: false,
	/**
	 * Function to get unit price
	 */
	getUnitPrice: function () {
		if (this.unitPrice == false) {
			this.unitPrice = $('input.unitPrice', this.getForm());
		}
		return this.unitPrice;
	},
	/**
	 * Function which aligns data just below global search element
	 */
	alignBelowUnitPrice: function (dataToAlign) {
		var parentElem = $('input[name="unit_price"]', this.getForm());
		dataToAlign.position({
			'of': parentElem,
			'my': "left top",
			'at': "left bottom",
			'collision': 'flip'
		});
		return this;
	},
	/**
	 * Function to get current Element
	 */
	getCurrentElem: function (e) {
		return $(e.currentTarget);
	},
	/**
	 *Function to register events for taxes
	 */
	registerEventForTaxes: function () {
		var thisInstance = this;
		var formElem = this.getForm();
		$('.taxes').on('change', function (e) {
			var elem = thisInstance.getCurrentElem(e);
			var taxBox = elem.data('taxName');
			if (elem.is(':checked')) {
				$('input[name=' + taxBox + ']', formElem).removeAttr('readonly');
			} else {
				$('input[name=' + taxBox + ']', formElem).attr('readonly', 'readonly');
			}

		});
		return this;
	},
	/**
	 * Function to register event for enabling base currency on radio button clicked
	 */
	registerEnableBaseCurrencyEvent: function (container) {
		const form = this.getForm();
		container.on('change', '.js-base-currency', (e) => {
			let element = $(e.currentTarget);
			let parentElem = element.closest('tr');
			if (element.is(':checked')) {
				this.baseCurrencyName = parentElem.data('currencyId');
				this.baseCurrency = $('.js-converted-price', parentElem).val();
				form.find('.js-currency').text(parentElem.data('currency-symbol'));
			}
		});
	},
	/**
	 * Function to register event for reseting the currencies
	 */
	registerResetCurrencyEvent: function (container) {
		container.on('click', '.js-currency-reset', (e) => {
			let parentElem = $(e.currentTarget).closest('tr');
			let price = this.getUnitPrice().getNumberFromValue() * parentElem.find('.js-conversion-rate').getNumberFromValue();
			$('.js-converted-price', parentElem).val(App.Fields.Double.formatToDisplay(price));
		});
	},
	calculateConversionRate: function (container) {
		let baseCurrencyConvestationRate = container.find('.js-base-currency').filter(':checked').closest('tr').find('.js-conversion-rate');
		//if basecurrency has conversation rate as 1 then you dont have caliculate conversation rate
		if (baseCurrencyConvestationRate.val() == "1") {
			return;
		}
		console.log('do sprawdzenia', container.find('.js-conversion-rate'));
		let baseCurrencyRatePrevValue = baseCurrencyConvestationRate.val();
		container.find('.js-conversion-rate').each(function (key, domElement) {
			let element = $(domElement);
			console.log(element);
			if (!element.is(baseCurrencyConvestationRate)) {
				element.val(App.Fields.Double.formatToDisplay(element.getNumberFromValue() / baseCurrencyRatePrevValue));
			}
		});
		baseCurrencyConvestationRate.val("1");
	},
	/**
	 * Function to register event for enabling currency on checkbox checked
	 */

	registerEnableCurrencyEvent: function (container) {
		container.on('change', '.js-enable-currency', (e) => {
			let element = $(e.currentTarget);
			let parentRow = element.closest('tr');
			if (element.is(':checked')) {
				element.attr('checked', 'checked');
				let price = this.getUnitPrice().getNumberFromValue() * parentRow.find('.js-conversion-rate').getNumberFromValue();
				$('input', parentRow).attr('disabled', true).removeAttr('disabled');
				parentRow.find('.js-currency-reset').attr('disabled', true).removeAttr('disabled');
				parentRow.find('.js-converted-price').val(App.Fields.Double.formatToDisplay(price));
			} else {
				if (parentRow.find('.js-base-currency').is(':checked')) {
					Vtiger_Helper_Js.showPnotify({
						type: 'error',
						title: '"' + parentRow.find('.js-currency-name').text() + '" ' + app.vtranslate('JS_BASE_CURRENCY_CHANGED_TO_DISABLE_CURRENCY')
					});
					element.prop('checked', true);
					return;
				}
				parentRow.find('input').attr('disabled', 'disabled');
				parentRow.find('.js-currency-reset').attr('disabled', 'disabled');
				element.removeAttr('disabled checked');
			}
		});
	},
	/*
	 * function to register events for more currencies link
	 */
	registerEventForMoreCurrencies: function () {
		const self = this;
		const form = this.getForm();
		form.find('.js-more-currencies').on('click', () => {
			let modal = $('<form>').append(form.find('.js-currencies-container .js-currencies-modal').clone());
			app.showModalWindow({
				data: modal,
				css: {'text-align': 'left', 'width': '65%'},
				cb: function (data) {
					let form = data.parent();
					form.validationEngine(app.validationEngineOptionsForRecord);
					form.on('submit', function (e) {
						e.preventDefault();
						if (form.validationEngine('validate') && self.saveCurrencies(form)) {
							app.hideModalWindow();
						}
					});
					self.baseCurrency = self.getUnitPrice().val();
					self.calculateConversionRate(form);
					self.registerEnableCurrencyEvent(form);
					self.registerEnableBaseCurrencyEvent(form);
					self.registerResetCurrencyEvent(form);
					self.triggerForBaseCurrencyCalc(form);
				}
			});
		});
	},
	saveCurrencies: function (modalContainer) {
		const thisInstance = this;
		let enabledBaseCurrency = modalContainer.find('.js-enable-currency').filter(':checked');
		if (enabledBaseCurrency.length < 1) {
			Vtiger_Helper_Js.showMessage({
				text: app.vtranslate('JS_PLEASE_SELECT_BASE_CURRENCY_FOR_PRODUCT'),
				type: 'error'
			});
			return false;
		}
		enabledBaseCurrency.attr('checked', "checked");
		modalContainer.find('.js-enable-currency').filter(":not(:checked)").removeAttr('checked');
		let selectedBaseCurrency = modalContainer.find('.js-base-currency').filter(':checked');
		if (selectedBaseCurrency.length < 1) {
			Vtiger_Helper_Js.showMessage({
				text: app.vtranslate('JS_PLEASE_ENABLE_BASE_CURRENCY_FOR_PRODUCT'),
				type: 'error'
			});
			return false;
		}
		selectedBaseCurrency.attr('checked', "checked");
		modalContainer.find('.js-base-currency').filter(":not(:checked)").removeAttr('checked');
		let parentElem = selectedBaseCurrency.closest('tr');
		thisInstance.baseCurrencyName = parentElem.data('currencyId');
		thisInstance.baseCurrency = $('.js-converted-price', parentElem).val();
		thisInstance.getUnitPrice().val(thisInstance.baseCurrency);
		$('input[name="base_currency"]', thisInstance.getForm()).val(thisInstance.baseCurrencyName);
		$('.js-base-currency-check-id', thisInstance.getForm()).attr('name', thisInstance.baseCurrencyName.replace('name', '_') + '_check');
		thisInstance.getForm().find('.js-currencies-container').html('').append(modalContainer.children().clone());
		return true;
	},
	/**
	 * Function to calculate base currency price value if unit
	 * present on click of more currencies
	 */
	triggerForBaseCurrencyCalc: function (form) {
		if (form == undefined) {
			form = this.getForm();
		}
		form.find('.js-currencies-modal .js-enable-currency').each(function (index, element) {
			element = $(element);
			if (element.is(':checked')) {
				let baseCurrencyRow = element.closest('tr');
				if (parseFloat(baseCurrencyRow.find('.js-converted-price').val()) == 0) {
					baseCurrencyRow.find('.js-currency-reset').trigger('click');
				}
			} else {
				element.closest('tr').find('.js-converted-price').val('');
			}
		});
	},
	/**
	 * Function to register onchange event for unit price
	 */
	registerEventForUnitPrice: function () {
		this.getUnitPrice().on('change', () => {
			this.triggerForBaseCurrencyCalc();
			let baseValue = this.getForm().find('.js-base-curencies-value');
			baseValue.val(this.getUnitPrice().val());
			baseValue.formatNumber();
		});
	},
	registerEventForUsageunit: function () {
		this.checkUsageUnit();
		$('select[name="usageunit"]').on('change', this.checkUsageUnit);
	},
	checkUsageUnit: function () {
		var selectUsageunit = $('select[name="usageunit"]');
		var inputQtyPerUnit = $('input[name="qty_per_unit"]');
		var value = selectUsageunit.val();
		if (value === 'pack') {
			inputQtyPerUnit.prop('disabled', false);
		} else {
			inputQtyPerUnit.prop('disabled', true);
		}
	},
	registerEvents: function () {
		this._super();
		this.registerEventForMoreCurrencies();
		this.registerEventForTaxes();
		this.registerEventForUnitPrice();
		this.registerEventForUsageunit();
	}
});
