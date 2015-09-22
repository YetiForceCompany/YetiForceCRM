/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

jQuery.Class("Users_ChangePassword_Js", {}, {
	registerChangePass: function () {
		var thisInstance = this;
		var form = jQuery('#changePassword');
		var params = app.validationEngineOptionsForRecord;
		params.onValidationComplete = function (form, valid) {
			if (valid) {
				thisInstance.savePassword(form)
			}
			return false;
		}
		form.validationEngine(app.validationEngineOptionsForRecord);

	},
	savePassword: function (form) {
		var new_password = form.find('[name="new_password"]');
		var confirm_password = form.find('[name="confirm_password"]');
		var old_password = form.find('[name="old_password"]');
		var userid = form.find('[name="userid"]').val();

		if (new_password.val() == confirm_password.val()) {
			var params = {
				'module': 'Users',
				'action': "SaveAjax",
				'mode': 'savePassword',
				'old_password': old_password.val(),
				'new_password': new_password.val(),
				'userid': userid
			}
			AppConnector.request(params).then(
					function (data) {
						if (data.success) {
							app.hideModalWindow();
							Vtiger_Helper_Js.showPnotify({text: app.vtranslate(data.result.message), type: 'success'});
						} else {
							//old_password.validationEngine('showPrompt', app.vtranslate(data.error.message) , 'error','topLeft',true);
							Vtiger_Helper_Js.showPnotify(data.error.message);
							return false;
						}
					}
			);
		} else {
			new_password.validationEngine('showPrompt', app.vtranslate('JS_REENTER_PASSWORDS'), 'error', 'topLeft', true);
			return false;
		}
	},
	registerEvents: function () {
		this.registerChangePass();
	}

});

jQuery(document).ready(function (e) {
	var instance = new Users_ChangePassword_Js();
	instance.registerEvents();
})
