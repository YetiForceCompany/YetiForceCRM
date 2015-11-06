/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Settings_CurrencyUpdate_Index_Js", {}, {
	/*
	 * Shows or hides block informing about supported currencies by presently chosen bank
	 */
	registerInfoButton: function (container) {
		container.find('#supportedCurrencies').on('click', function () {
			jQuery('#infoBlock').toggleClass('hide');
		});
	},
	/*
	 * Shows or hides block informing about unsupported currencies by presently chosen bank
	 */
	registerAlertButton: function (container) {
		container.find('#unsupportedCurrencies').on('click', function () {
			container.find('#alertBlock').toggleClass('hide');
		});
	},
	/*
	 * Event fired on bank picklist change.
	 * Daves the chosen bank as active in database.
	 * Updates information about supported and unsupported currencies for currently chosen bank,
	 */
	registerBankChange: function (container) {
		container.find('#bank').on('change', function () {
			bankName = container.find('#bank option:selected').data('name');
			container.find('#alertSpan').html('');
			container.find('#infoSpan').html('');
			var infoProgress = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {};
			params.data = {
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				action: 'GetBankCurrencies',
				mode: 'supported',
				name: bankName
			};
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						var html = '';
						for (var name in response) {
							html += '<p><strong>'+name+'</strong> - '+response[name]+'</p>';
						}
						container.find('#infoSpan').html(html);
					},
					function (data, err) {

					}
			);

			params.data = {
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				action: 'GetBankCurrencies',
				mode: 'unsupported',
				name: bankName
			};
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (jQuery.isEmptyObject(response)) {
							if (!container.find('#unsupportedCurrencies').hasClass('hide')) {
								container.find('#unsupportedCurrencies').addClass('hide');
							}
							if (!container.find('#alertBlock').hasClass('hide')) {
								container.find('#alertBlock').addClass('hide')
							}
						} else {
							container.find('#unsupportedCurrencies').removeClass('hide');
						}
						var html = '';
						for (var name in response) {
							html += '<p><strong>'+name+'</strong> - '+response[name]+'</p>';
						}
						container.find('#alertSpan').html(html);
					},
					function (data, err) {

					}
			);

			var bankId = jQuery('#bank option:selected').val();
			params.data = {
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				action: 'SaveActiveBank',
				id: bankId
			};
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (response['success']) {
							var params = {
								text: response['message'],
								animation: 'show',
								type: 'success'
							};
							Vtiger_Helper_Js.showPnotify(params);
						}
						else {
							var params = {
								text: response['message'],
								animation: 'show',
								hide: false,
								type: 'error'
							};
							Vtiger_Helper_Js.showPnotify(params);
						}
						infoProgress.progressIndicator({'mode': 'hide'});
					},
					function (data, err) {

					}
			);
		});
	},
	/**
	 * Register events
	 */
	registerEvents: function () {
		var container = jQuery('#currencyUpdateContainer');
		app.registerEventForDatePickerFields('#datepicker', false, {});
		this.registerInfoButton(container);
		this.registerAlertButton(container);
		this.registerBankChange(container);
	}
});
