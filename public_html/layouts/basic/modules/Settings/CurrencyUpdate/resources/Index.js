/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_CurrencyUpdate_Index_Js',
	{},
	{
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
				let bankName = container.find('#bank option:selected').data('name');
				container.find('#alertSpan').html('');
				container.find('#infoSpan').html('');
				let infoProgress = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					}),
				params = {};
				let bankId = jQuery('#bank option:selected').val();
				params.data = {
					parent: app.getParentModuleName(),
					module: app.getModuleName(),
					name: bankName,
					id: bankId
				};
				params.dataType = 'json';
				params.data.action = 'SaveActiveBank';
				AppConnector.request(params).done(function (data) {
					let response = data['result'];
					if (response['success']) {
						app.showNotify({
							text: response['message'],
							type: 'success'
						});
					} else {
						app.showNotify({
							text: response['message'],
							hide: false,
							type: 'error'
						});
					}
					infoProgress.progressIndicator({ mode: 'hide' });
				});

				params.data.action = 'GetBankCurrencies';
				params.data.mode = 'supported';
				AppConnector.request(params).done(function (data) {
					console.log(data);
					let response = data['result'],
						html = '',
						name;
					for (name in response) {
						html += '<p><strong>' + name + '</strong> - ' + response[name] + '</p>';
					}
					container.find('#infoSpan').html(html);
				});

				params.data.mode = 'unsupported';
				AppConnector.request(params).done(function (data) {
					let response = data['result'];
					if (jQuery.isEmptyObject(response)) {
						if (!container.find('#unsupportedCurrencies').hasClass('d-none')) {
							container.find('#unsupportedCurrencies').addClass('d-none');
						}
						if (!container.find('#alertBlock').hasClass('d-none')) {
							container.find('#alertBlock').addClass('d-none');
						}
					} else {
						container.find('#unsupportedCurrencies').removeClass('d-none');
					}
					let html = '',
						name;
					for (name in response) {
						html += '<p><strong>' + name + '</strong> - ' + response[name] + '</p>';
					}
					container.find('#alertSpan').html(html);
				});
				window.location.reload();
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
	}
);
