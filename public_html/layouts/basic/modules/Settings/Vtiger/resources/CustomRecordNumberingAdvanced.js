/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
$.Class('Settings_CustomRecordNumberingAdvanced_Js', {}, {
	form: false,
	modalForm: false,
	/**
	 * Get parent form element
	 * @returns {jQuery}
	 */
	getForm() {
		if (this.form == false) {
			this.form = $('#EditView');
		}
		return this.form;
	},
	/**
	 * Get current modal form element
	 * @returns {jQuery}
	 */
	getModalForm() {
		if (this.modalForm == false) {
			this.modalForm = $('.js-custom-record-numbering-advanced');
		}
		return this.modalForm;
	},
	/**
	 * Register modal events
	 */
	registerModalEvents() {
		const parentForm = this.getForm();
		const modalForm = this.getModalForm();
		const sourceModule = parentForm.find('[name="sourceModule"]').val();
		modalForm.validationEngine(app.validationEngineOptionsForRecord);
		modalForm.on('submit', function(e) {
			e.preventDefault();
			if (modalForm.validationEngine('validate')){
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: "CustomRecordNumberingAjax",
					mode: "saveModuleCustomNumberingAdvanceData",
					sourceModule: sourceModule,
					sequenceNumber: modalForm.serializeFormData()
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					if (data.success === true) {
						Settings_Vtiger_Index_Js.showMessage({
							text: app.vtranslate('JS_RECORD_NUMBERING_SAVED_SUCCESSFULLY_FOR') + " " + parentForm.find('option[value="' + sourceModule + '"]').text()
						});
						app.hideModalWindow();
					}
				});
			}
		});
	}
});
$(document).ready(function () {
	var customRecordNumberingInstance = new Settings_CustomRecordNumberingAdvanced_Js();
	customRecordNumberingInstance.registerModalEvents();
});
