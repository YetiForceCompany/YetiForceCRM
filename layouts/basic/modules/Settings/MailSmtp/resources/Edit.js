/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_Edit_Js('Settings_MailSmtp_Edit_Js', {}, {
	container: false,
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.editViewContainer');
		}
		return this.container;
	},
	registerSubmitForm: function () {
		var form = this.getContainer().find('form');
		form.on('submit', function (e) {
			if (form.validationEngine('validate') === true) {
				return false;
				var paramsForm = form.serializeFormData();
				app.saveAjax('updateSmtp', paramsForm).then(function (respons) {
					console.log(respons.result.url)
					if(true == respons.result.success){
						window.location.href = 	respons.result.url
					}else{
						Settings_Vtiger_Index_Js.showMessage({text: respons.result.message});
					}
				});
				return false;
			} else {
				app.formAlignmentAfterValidation(form);
			}
		})
	},
	registerEvents: function () {
		var container = this.getContainer()
		var form = container.find('form');
		if (form.length) {
			form.validationEngine(app.validationEngineOptions);
			form.find(":input").inputmask();
		}
		this.registerSubmitForm();


	}
})
