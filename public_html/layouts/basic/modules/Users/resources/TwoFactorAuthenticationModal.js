/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class("Base_TwoFactorAuthenticationModal_JS", {}, {
	/**
	 * Function to handle sending the AJAX form
	 * @param data
	 */
	registerSubmitFrom: (data) => {
		let thisInstance = this;
		data.find('button[name=saveButton]').prop("disabled", true);
		data.find('input[name=user_code]').on('keyup', (e) => {
			if (e.keyCode !== 13) {
				data.find('button[name=saveButton]').prop("disabled", $(e.currentTarget).val().length === 0);
			}
		});
		data.find('input[name=user_code]').on('change', (e) => {
			data.find('button[name=saveButton]').prop("disabled", $(e.currentTarget).val().length === 0);
		});
		let form = data.find('form');
		form.on('submit', (e) => {
			let progress = $.progressIndicator({blockInfo: {'enabled': true}});
			AppConnector.request(form.serializeFormData()).then((respons) => {
				if (respons.result.success) {
					app.hideModalWindow();
					Vtiger_Helper_Js.showPnotify({
						text: app.vtranslate(respons.result.message),
						type: 'success',
						animation: 'show'
					});
				} else {
					let wrongCode = form.find('.js-wrong-code');
					if (wrongCode.hasClass('hide')) {
						wrongCode.removeClass('hide');
					}
				}
				progress.progressIndicator({mode: 'hide'});
			});
			e.preventDefault();
		});
	},
	/**
	 * Register base events
	 * @param {jQuery} modalContainer
	 */
	registerEvents: function (modalContainer) {
		this.registerSubmitFrom(modalContainer);
	}
});
