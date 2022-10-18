/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_MailSmtp_Edit_Js',
	{},
	{
		registerDependency() {
			console.log('sssss');
			let dependency = JSON.parse(this.container.find('.js-smtp-dependency').val());
			for (let field in dependency) {
				let fieldEl = this.container.find(`[name="${field}"]`);
				let conditions = dependency[field]['condition'];
				let hide = false;
				for (let conField in conditions) {
					let conFieldEl = this.container.find(`[data-fieldinfo][name="${conField}"]`);
					let conFieldElVal =
						conFieldEl.attr('type') === 'checkbox' ? Number(conFieldEl.is(':checked')) : conFieldEl.val();
					let { value, operator } = conditions[conField];
					console.log([
						field,
						conField,
						value,
						operator,
						conFieldElVal,
						[operator === 'e' && value == conFieldElVal, operator === 'n' && value != conFieldElVal],
						conFieldEl.attr('type') === 'checkbox'
					]);
					if (operator === 'e' && value == conFieldElVal) {
						hide = true;
						break;
					} else if (operator === 'n' && value != conFieldElVal) {
						hide = true;
						break;
					}
				}
				if (hide) {
					fieldEl.closest('.js-field-container').addClass('d-none');
				} else {
					fieldEl.closest('.js-field-container').removeClass('d-none');
				}
			}
		},
		changeMailerType: function () {
			this.container.find('select').on('change', (e) => {
				this.registerDependency();
			});
			this.container.find('input[type="checkbox"]').on('click', (e) => {
				this.registerDependency();
			});
		},
		/**
		 * PreSave validation
		 */
		preSaveValidation: function () {
			const aDeferred = $.Deferred();
			let formData = new FormData(this.container[0]);
			formData.append('mode', 'preSaveValidation');
			AppConnector.request({
				async: false,
				url: 'index.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false
			})
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

		/**
		 * Register submit event
		 */
		registerSubmitEvent() {
			this.container.off('submit').on('submit', (e) => {
				e.preventDefault();
				this.container.find('.js-toggle-panel').find('.js-block-content').removeClass('d-none');
				if ($(e.currentTarget).validationEngine('validate')) {
					document.progressLoader = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					for (var key in this.conditionBuilders) {
						this.container
							.find(`input[name="${key}"]`)
							.val(JSON.stringify(this.conditionBuilders[key].getConditions()));
					}

					this.preSaveValidation().done((response) => {
						if (response === true) {
							let formData = this.container.serializeFormData();
							app
								.saveAjax('save', [], formData)
								.done(function (data) {
									if (data.result && data.result.success) {
										Settings_Vtiger_Index_Js.showMessage({ text: app.vtranslate('JS_SAVE_SUCCESS') });
										window.location.href = data.result.url;
									} else {
										document.progressLoader.progressIndicator({ mode: 'hide' });
										app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
									}
								})
								.fail(function () {
									document.progressLoader.progressIndicator({ mode: 'hide' });
									app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
								});
						} else {
							document.progressLoader.progressIndicator({ mode: 'hide' });
						}
					});
				}
				e.stopPropagation();
				return false;
			});
		},

		registerBasicEvents: function () {
			this.referenceModulePopupRegisterEvent(this.container);
			this.registerAutoCompleteFields(this.container);
			this.registerClearReferenceSelectionEvent(this.container);
			this.container.validationEngine(app.validationEngineOptionsForRecord);
			this.registerSubmitEvent();
			app.registerBlockToggleEvent(this.container);
			App.Fields.Password.register(this.container);
			App.Fields.Text.registerCopyClipboard(this.container);
		},

		registerEvents: function () {
			this.container = this.getForm();
			this.changeMailerType();
			this.registerBasicEvents(this.container);
		}
	}
);
