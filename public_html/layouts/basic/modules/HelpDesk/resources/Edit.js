/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
			const self = this;
			let lockSave = true;
			form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
				let closedStatus = JSON.parse(app.getMainParams('closeTicketForStatus'));
				let status = form.find('[name="ticketstatus"] :selected').val();
				let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
				let isClosedStatusSet = status in closedStatus;
				const recordId = app.getRecordId();
				if (
					(app.getMainParams('checkIfRecordHasTimeControl') || app.getMainParams('checkIfRelatedTicketsAreClosed')) &&
					isClosedStatusSet &&
					recordId &&
					!data.module
				) {
					if (lockSave && recordId) {
						e.preventDefault();
						AppConnector.request({
							action: 'CheckValidateToClose',
							module: app.getModuleName(),
							record: recordId,
							status: form.find('[name="ticketstatus"] :selected').val()
						}).done((response) => {
							progress.progressIndicator({ mode: 'hide' });
							if (response.result.hasTimeControl.result && response.result.relatedTicketsClosed.result) {
								lockSave = false;
								form.submit();
							}
							if (!response.result.hasTimeControl.result) {
								app.showNotify({
									text: response.result.hasTimeControl.message,
									type: 'info'
								});
								self.addTimeControl({
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
						});
					}
				}
				if (isClosedStatusSet && (!recordId || data.module)) {
					app.showNotify({
						text: app.vtranslate('JS_CANT_CLOSE_NEW_RECROD'),
						type: 'info'
					});
					progress.progressIndicator({ mode: 'hide' });
					e.preventDefault();
				}
			});
		},
		/**
		 * Add time control when closed ticket
		 * @param {array} params
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
