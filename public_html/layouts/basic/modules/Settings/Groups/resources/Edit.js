/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_Groups_Edit_Js',
	{},
	{
		/**
		 * Function to register form for validation
		 */
		registerFormForValidation: function () {
			this.getForm().validationEngine(app.getvalidationEngineOptions(true));
		},

		/**
		 * Function to register the submit event of form
		 */
		registerSubmitEvent: function () {
			let form = this.getForm();
			form.on('submit', (e) => {
				if (form.data('submit') == 'true' && form.data('performCheck') == 'true') {
					document.progressLoader = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						position: 'html',
						blockInfo: { enabled: true }
					});
					return true;
				} else {
					if (form.data('jqv').InvalidFields.length <= 0) {
						let formData = form.serializeFormData();
						this.validate(formData)
							.done(function (_) {
								form.data('submit', 'true');
								form.data('performCheck', 'true');
								form.submit();
							})
							.fail(function (data, err) {
								Settings_Vtiger_Index_Js.showMessage({ text: data['message'], type: 'error' });
								return false;
							});
					} else {
						//If validation fails, form should submit again
						form.removeData('submit');
						app.formAlignmentAfterValidation(form);
					}
					e.preventDefault();
				}
			});
		},

		/*
		 * Function to check Duplication of Group Names
		 * returns boolean true or false
		 */
		validate: function (params) {
			const aDeferred = jQuery.Deferred();
			params.mode = 'preSaveValidation';
			AppConnector.request(params)
				.done(function (data) {
					let response = data.result;
					if (response.success) {
						aDeferred.reject(response);
					} else {
						aDeferred.resolve(response);
					}
				})
				.fail(function (error, err) {
					aDeferred.reject(error, err);
				});
			return aDeferred.promise();
		},
		/**
		 * Register events for section "modules"
		 */
		registerButtonsModule: function () {
			const editViewForm = this.getForm();
			editViewForm.find('.js-modules-select-all, .js-modules-deselect-all').on('click', function (e) {
				editViewForm
					.find('[name="modules[]"] option')
					.prop('selected', $(this).hasClass('js-modules-select-all'))
					.parent()
					.trigger('change');
			});
		},
		/**
		 * Function which will handle the registrations for the elements
		 */
		registerEvents: function () {
			this._super();
			this.registerSubmitEvent();
			this.registerButtonsModule();
		}
	}
);
