/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Settings_CurrencyUpdate_Index_Js", {}, {
	/*
	 * Shows or hides block informing about supported currencies by presently chosen bank
	 */
	registerInfoButton: function () {
		jQuery('#supportedCurrencies').on('click', function () {
			jQuery('#infoBlock').toggleClass('hide');
		});
	},
	/*
	 * Shows or hides block informing about unsupported currencies by presently chosen bank
	 */
	registerAlertButton: function () {
		jQuery('#unsupportedCurrencies').on('click', function () {
			jQuery('#alertBlock').toggleClass('hide');
		});
	},
	/*
	 * Event fired on bank picklist change.
	 * Daves the chosen bank as active in database.
	 * Updates information about supported and unsupported currencies for currently chosen bank,
	 */
	registerBankChange: function () {
		jQuery('#bank').on('change', function () {
			bankName = jQuery('#bank option:selected').data('name');
			jQuery('#alertSpan').html('');
			jQuery('#infoSpan').html('');
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
						jQuery('#infoSpan').html(response);
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
						console.log(response);
						if (response === '') {
							console.log('puste');
							if (!jQuery('#unsupportedCurrencies').hasClass('hide')) {
								jQuery('#unsupportedCurrencies').addClass('hide');
							}
							if (!jQuery('#alertBlock').hasClass('hide')) {
								jQuery('#alertBlock').addClass('hide')
							}
						} else {
							console.log(' nie puste');
							jQuery('#unsupportedCurrencies').removeClass('hide');
						}
						jQuery('#alertSpan').html(response);
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
		})
	},
	/**
	 * Register events
	 */
	registerEvents: function () {
		app.registerEventForDatePickerFields('#datepicker', false, {});
		this.registerInfoButton();
		this.registerAlertButton();
		this.registerBankChange();
	}
});
