/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class(
	'Base_RecordAddsTemplates_JS',
	{},
	{
		editInstance: false,
		/**
		 * Function listener to send a form
		 * @param {jQuery} container
		 */
		registerSubmitForm: function (container) {
			const aDeferred = $.Deferred();
			let instanse = this;
			container.on('click', '.js-modal__save', () => {
				let params = {
					action: 'RecordAddsTemplates',
					recordAddsType: container.find('.js-modal-body').find("[name='recordAddsType']").val()
				};
				let validate = true;
				container.find('form.js-record-template').each(function () {
					let form = $(this);
					instanse.editInstance.registerValidationsFields(form);
					let formSerializeData = form.serializeFormData();
					params[formSerializeData.module] = formSerializeData;
					if (!form.validationEngine('validate')) {
						validate = false;
					}
				});
				if (validate) {
					AppConnector.request(params)
						.done(function (data) {
							let response = data.result;
							let preSave = response.preSave;
							if (typeof preSave !== 'undefined' && preSave.length) {
								for (let i = 0; i < preSave.length; i++) {
									if (preSave[i] !== null && preSave[i].result !== true) {
										app.showNotify(preSave[i].message ? preSave[i].message : app.vtranslate('JS_ERROR'));
									}
								}
							} else {
								if (response.success) {
									app.hideModalWindow();
									app.showNotify({
										text: response.message ? response.message : app.vtranslate('JS_SAVE_NOTIFY_OK'),
										type: 'success'
									});
									if (response.url) {
										window.location.href = response.url;
									} else {
										window.location.reload();
									}
								} else {
									app.showNotify({
										text: response.message ? response.message : app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								}
							}
							if (response.length <= 0) {
								aDeferred.resolve(true);
							} else {
								aDeferred.resolve(false);
							}
						})
						.fail((textStatus, errorThrown) => {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
							app.errorLog(textStatus, errorThrown);
							aDeferred.resolve(false);
						});
				}
			});
		},

		/**
		 * Register events function
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.editInstance = Vtiger_Edit_Js.getInstance();
			this.editInstance.registerBasicEvents(modalContainer);
			this.registerSubmitForm(modalContainer);
		}
	}
);
