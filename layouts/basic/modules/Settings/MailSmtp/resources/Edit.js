/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_Edit_Js('Settings_MailSmtp_Edit_Js', {}, {
	registerSubmitForm: function () {
		var form = this.getForm()
		form.on('submit', function (e) {
			form.find('.alert').hide()
			if (form.validationEngine('validate') === true) {
				var paramsForm = form.serializeFormData();
				var progressIndicatorElement = jQuery.progressIndicator({
					blockInfo: {'enabled': true}
				});
				app.saveAjax('updateSmtp', paramsForm).then(function (respons) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					if(true == respons.result.success){
						window.location.href = 	respons.result.url
					}else{
						form.find('.alert').show()
						form.find('.alert p').text(respons.result.message)
					}
				});
				return false;
			} else {
				app.formAlignmentAfterValidation(form);
			}
		})
	},
	registerEvents: function () {
		var form = this.getForm()
		if (form.length) {
			form.validationEngine(app.validationEngineOptions);
			form.find(":input").inputmask();
		}
		this.registerSubmitForm();
		app.showPopoverElementView(form.find('.popoverTooltip'));
	}
})
