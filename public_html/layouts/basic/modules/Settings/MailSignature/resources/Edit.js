/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_MailSignature_Edit_Js',
	{},
	{
		registerSubmitForm: function () {
			const form = this.getForm();
			form.on('submit', (e) => {
				e.preventDefault();
				if (form.validationEngine('validate')) {
					const progress = jQuery.progressIndicator({
						blockInfo: { enabled: true }
					});
					this.preSaveValidation().done((response) => {
						if (response === true) {
							let formData = form.serializeFormData();
							formData['mode'] = 'save';
							AppConnector.request(formData)
								.done(function (data) {
									progress.progressIndicator({ mode: 'hide' });
									if (true == data.result.success) {
										window.location.href = data.result.url;
									} else {
										app.showNotify({ text: data.result.message, type: 'error' });
									}
								})
								.fail(function (textStatus) {
									progress.progressIndicator({ mode: 'hide' });
									app.showNotify({ text: textStatus, type: 'error' });
								});
						} else {
							progress.progressIndicator({ mode: 'hide' });
						}
					});
				} else {
					app.formAlignmentAfterValidation(form);
				}
				e.stopPropagation();
				return false;
			});
		},
		/**
		 * PreSave validation
		 */
		preSaveValidation: function () {
			const aDeferred = $.Deferred();
			let formData = this.getForm().serializeFormData();
			formData['mode'] = 'preSaveValidation';
			AppConnector.request(formData)
				.done((data) => {
					let response = data.result;
					for (let i in response) {
						if (response[i].result !== true) {
							app.showNotify({
								text: response[i].message ? response[i].message : app.vtranslate('JS_ERROR'),
								type: 'error'
							});
							if (response[i].hoverField != undefined) {
								this.container.find('[name="' + response[i].hoverField + '"]').focus();
							}
						}
					}
					aDeferred.resolve(data.result.length <= 0);
				})
				.fail((textStatus, errorThrown) => {
					app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
					app.errorLog(textStatus, errorThrown);
					aDeferred.resolve(false);
				});

			return aDeferred.promise();
		},
		getRecordsListParams: function (container) {
			return { module: $('input[name="popupReferenceModule"]', container).val() };
		},
		registerEvents: function () {
			const form = this.getForm();
			if (form.length) {
				form.validationEngine(app.validationEngineOptions);
			}
			this.registerSubmitForm();
			this.registerEventForEditor();
			this.registerBasicEvents(form);
			App.Tools.VariablesPanel.registerRefreshCompanyVariables(form);
			App.Fields.Text.registerCopyClipboard(form.find('.js-container-variable'));
		}
	}
);
