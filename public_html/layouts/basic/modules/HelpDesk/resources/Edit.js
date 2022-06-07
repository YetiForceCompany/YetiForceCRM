/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'HelpDesk_Edit_Js',
	{},
	{
		/**
		 * Register pre save event
		 * @param {jQuery} form
		 */
		registerRecordPreSaveEventEvent: function (form) {
			this._super(form);
			form.on(Vtiger_Edit_Js.recordPreSave, (e, data) => {
				try {
					this.validateToClose(form).done((response) => {
						if (response !== true) {
							e.preventDefault();
						}
					});
				} catch (error) {
					app.errorLog(error);
					app.showNotify({
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					});
					e.preventDefault();
				}
			});
		},
		validateToClose: function (form) {
			const aDeferred = $.Deferred();
			let closedStatus = app.getMainParams('closeTicketForStatus', true);
			let status = form.find('[name="ticketstatus"] :selected').val();
			let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			let isClosedStatusSet = status in closedStatus;
			const recordId = form.find('[name="record"]').val();
			if (
				(app.getMainParams('checkIfRecordHasTimeControl') || app.getMainParams('checkIfRelatedTicketsAreClosed')) &&
				isClosedStatusSet &&
				recordId
			) {
				let formData = {
					action: 'CheckValidateToClose',
					module: app.getModuleName(),
					record: recordId,
					status: form.find('[name="ticketstatus"] :selected').val()
				};
				AppConnector.request({
					async: false,
					url: 'index.php',
					type: 'POST',
					data: formData
				}).done((response) => {
					progress.progressIndicator({ mode: 'hide' });
					if (response.result.hasTimeControl.result && response.result.relatedTicketsClosed.result) {
						aDeferred.resolve(true);
					} else {
						if (!response.result.hasTimeControl.result) {
							app.showNotify({
								text: response.result.hasTimeControl.message,
								type: 'info'
							});
							this.addTimeControl({
								recordId: recordId,
								url: `index.php?module=OSSTimeControl&view=Edit&sourceModule=HelpDesk&sourceRecord=${recordId}&relationOperation=true&subprocess=${recordId}&subprocess=${recordId}`
							});
						}
						if (!response.result.relatedTicketsClosed.result) {
							app.showNotify({
								text: response.result.relatedTicketsClosed.message,
								type: 'info'
							});
						}
						aDeferred.resolve(false);
					}
				});
			} else if (isClosedStatusSet && !recordId) {
				app.showNotify({
					text: app.vtranslate('JS_CANT_CLOSE_NEW_RECROD'),
					type: 'info'
				});
				progress.progressIndicator({ mode: 'hide' });
				aDeferred.resolve(false);
			} else {
				aDeferred.resolve(true);
			}

			return aDeferred.promise();
		},
		/**
		 * Add time control when closed ticket
		 * @param {array} params
		 * @returns {Promise}
		 */
		addTimeControl: function (params) {
			let aDeferred = jQuery.Deferred();
			let referenceModuleName = 'OSSTimeControl';
			let parentId = params.recordId;
			let parentModule = 'HelpDesk';
			let quickCreateParams = {};
			let relatedParams = {};
			let relatedField = 'subprocess';
			let fullFormUrl = params.url;
			relatedParams[relatedField] = parentId;
			let eliminatedKeys = new Array('view', 'module', 'mode', 'action');

			let preQuickCreateSave = function (data) {
				let index, queryParam, queryParamComponents;
				let queryParameters = [];

				if (typeof fullFormUrl !== 'undefined' && fullFormUrl.indexOf('?') !== -1) {
					let urlSplit = fullFormUrl.split('?');
					let queryString = urlSplit[1];
					queryParameters = queryString.split('&');
					for (index = 0; index < queryParameters.length; index++) {
						queryParam = queryParameters[index];
						queryParamComponents = queryParam.split('=');
						if (queryParamComponents[0] == 'mode' && queryParamComponents[1] == 'Calendar') {
							data.find('a[data-tab-name="Task"]').trigger('click');
						}
					}
				}
				jQuery('<input type="hidden" name="sourceModule" value="' + parentModule + '" />').appendTo(data);
				jQuery('<input type="hidden" name="sourceRecord" value="' + parentId + '" />').appendTo(data);
				jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);

				if (typeof relatedField !== 'undefined') {
					let field = data.find('[name="' + relatedField + '"]');
					if (field.length == 0) {
						jQuery('<input type="hidden" name="' + relatedField + '" value="' + parentId + '" />').appendTo(data);
					}
				}
				for (index = 0; index < queryParameters.length; index++) {
					queryParam = queryParameters[index];
					queryParamComponents = queryParam.split('=');
					if (
						jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1' &&
						data.find('[name="' + queryParamComponents[0] + '"]').length == 0
					) {
						jQuery(
							'<input type="hidden" name="' + queryParamComponents[0] + '" value="' + queryParamComponents[1] + '" />'
						).appendTo(data);
					}
				}
			};
			if (typeof fullFormUrl !== 'undefined' && fullFormUrl.indexOf('?') !== -1) {
				let urlSplit = fullFormUrl.split('?');
				let queryString = urlSplit[1];
				let queryParameters = queryString.split('&');
				for (let index = 0; index < queryParameters.length; index++) {
					let queryParam = queryParameters[index];
					let queryParamComponents = queryParam.split('=');
					if (jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1') {
						relatedParams[queryParamComponents[0]] = queryParamComponents[1];
					}
				}
			}

			quickCreateParams['data'] = relatedParams;
			quickCreateParams['callbackFunction'] = function () {};
			quickCreateParams['callbackPostShown'] = preQuickCreateSave;
			quickCreateParams['noCache'] = true;
			App.Components.QuickCreate.createRecord(referenceModuleName, quickCreateParams);
			return aDeferred.promise();
		}
	}
);
