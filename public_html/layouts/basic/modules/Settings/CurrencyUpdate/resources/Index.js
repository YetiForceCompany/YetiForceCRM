/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

jQuery.Class("Settings_CurrencyUpdate_Index_Js", {}, {
	/*
	 * Shows or hides block informing about supported currencies by presently chosen bank
	 */
	registerInfoButton: function (container) {
		container.find('#supportedCurrencies').on('click', function () {
			jQuery('#infoBlock').toggleClass('d-none');
		});
	},
	/*
	 * Shows or hides block informing about unsupported currencies by presently chosen bank
	 */
	registerAlertButton: function (container) {
		container.find('#unsupportedCurrencies').on('click', function () {
			container.find('#alertBlock').toggleClass('d-none');
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
						html += '<p><strong>' + name + '</strong> - ' + response[name] + '</p>';
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
						if (!container.find('#unsupportedCurrencies').hasClass('d-none')) {
							container.find('#unsupportedCurrencies').addClass('d-none');
						}
						if (!container.find('#alertBlock').hasClass('d-none')) {
							container.find('#alertBlock').addClass('d-none')
						}
					} else {
						container.find('#unsupportedCurrencies').removeClass('d-none');
					}
					var html = '';
					for (var name in response) {
						html += '<p><strong>' + name + '</strong> - ' + response[name] + '</p>';
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
							type: 'success'
						};
						Vtiger_Helper_Js.showPnotify(params);
					} else {
						var params = {
							text: response['message'],
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
		App.Fields.Date.register('#datepicker', false, {});
		this.registerInfoButton(container);
		this.registerAlertButton(container);
		this.registerBankChange(container);
	}
});
