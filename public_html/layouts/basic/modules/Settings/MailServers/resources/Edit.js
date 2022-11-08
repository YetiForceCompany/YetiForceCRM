/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_MailServers_Edit_Js',
	{},
	{
		container: false,
		advanceFilterInstance: false,
		conditionBuilders: [],
		setContainer: function (container) {
			this.container = container;
			return this.container;
		},
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('div.contentsDiv form');
			}
			return this.container;
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
		changeAuthMethod: function () {
			this.container.find('[name="auth_method"]').on('change', (e) => {
				let value = e.currentTarget.value;
				console.log(value);
				let clientFields = this.container
					.find('[name="oauth_provider"],[name="client_id"],[name="client_secret"],[name="redirect_uri_id"]')
					.closest('.js-field-container');
				console.log(clientFields);
				if (value === 'oauth2') {
					clientFields.removeClass('d-none');
				} else {
					clientFields.addClass('d-none');
				}
			});
		},
		registerBasicEvents: function () {
			this.container.validationEngine(app.validationEngineOptionsForRecord);
			this.registerSubmitEvent();
			this.changeAuthMethod();
			app.registerBlockToggleEvent(this.container);
			App.Fields.Password.register(this.container);
			App.Fields.Text.registerCopyClipboard(this.container);
		},
		registerEvents: function () {
			this.setContainer($('.contentsDiv form'));
			this.registerBasicEvents();
			app.showPopoverElementView(this.container.find('.js-popover-tooltip'));
		}
	}
);
