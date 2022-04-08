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

Vtiger_Detail_Js(
	'Users_Detail_Js',
	{
		/*
		 * function to trigger delete record action
		 * @params: delete record url.
		 */
		triggerDeleteUser: function (deleteUserUrl) {
			app.showConfirmModal({
				title: app.vtranslate('JS_DELETE_USER_CONFIRMATION'),
				confirmedCallback: () => {
					AppConnector.request(deleteUserUrl).done(function (data) {
						if (data) {
							var callback = function (data) {
								var params = app.validationEngineOptions;
								params.onValidationComplete = function (form, valid) {
									if (valid) {
										Users_Detail_Js.deleteUser(form);
									}
									return false;
								};
								$('#deleteUser').validationEngine(app.validationEngineOptions);
							};
							app.showModalWindow(data, function (data) {
								if (typeof callback == 'function') {
									callback(data);
								}
							});
						}
					});
				}
			});
		},
		deleteUser: function (form) {
			var userid = form.find('[name="userid"]').val();
			var transferUserId = form.find('[name="tranfer_owner_id"]').val();

			var params = {
				module: app.getModuleName(),
				action: 'DeleteAjax',
				mode: 'deleteUser',
				transfer_user_id: transferUserId,
				userid: userid,
				permanent: form.find('[name="deleteUserPermanent"]:checked').val()
			};
			AppConnector.request(params).done(function (data) {
				if (data.success) {
					app.hideModalWindow();
					app.showNotify({
						text: app.vtranslate(data.result.message),
						type: 'success'
					});
					var url = data.result.listViewUrl;
					window.location.href = url;
				}
			});
		},
		triggerChangeAccessKey: function (url) {
			app.showConfirmModal({
				title: app.vtranslate('JS_NEW_ACCESS_KEY_REQUESTED'),
				text: app.vtranslate('JS_CHANGE_ACCESS_KEY_CONFIRMATION'),
				confirmedCallback: () => {
					AppConnector.request(url).done(function (data) {
						let params = {},
							message;
						if (data['success']) {
							data = data.result;
							params['type'] = 'success';
							message = app.vtranslate(data.message);
							let accessKeyEle = $('#Users_detailView_fieldValue_accesskey');
							if (accessKeyEle.length) {
								accessKeyEle.find('.value').html(data.accessKey);
							}
						} else {
							message = app.vtranslate(data['error']['message']);
						}
						params['text'] = message;
						app.showNotify(params);
					});
				}
			});
		}
	},
	{
		usersEditInstance: false,
		updateStartHourElement: function (form) {
			this.usersEditInstance.triggerHourFormatChangeEvent(form);
			this.updateStartHourElementValue();
		},
		hourFormatUpdateEvent: function () {
			var thisInstance = this;
			this.getForm().on(this.fieldUpdatedEvent, '[name="hour_format"]', function (e, params) {
				thisInstance.updateStartHourElementValue();
			});
		},
		updateStartHourElementValue: function () {
			var form = this.getForm();
			var startHourSelectElement = $('select[name="start_hour"]', form);
			var selectedElementValue = startHourSelectElement.find('option:selected').text();
			startHourSelectElement.closest('.fieldValue').find('span.value').text(selectedElementValue);
			var endHourSelectElement = $('select[name="end_hour"]', form);
			endHourSelectElement
				.closest('.fieldValue')
				.find('span.value')
				.text(endHourSelectElement.find('option:selected').text());
		},
		startHourUpdateEvent: function (form) {
			var thisInstance = this;
			form.on(this.fieldUpdatedEvent, '[name="start_hour"]', function (e, params) {
				thisInstance.updateStartHourElement(form);
			});
		},
		saveFieldValues: function (fieldDetailList) {
			var aDeferred = $.Deferred();
			var thisInstance = this;
			var lock = false;
			var recordId = this.getRecordId();
			var data = {};
			if (typeof fieldDetailList !== 'undefined') {
				data = fieldDetailList;
				if (data['field'] == 'email1') {
					thisInstance.usersEditInstance.checkEmail(data['value']).done(
						function (data) {},
						function (data, error) {
							lock = true;
							aDeferred.reject();
						}
					);
				}
			}
			if (lock !== true) {
				data['record'] = recordId;
				data['module'] = app.getModuleName();
				data['action'] = 'SaveAjax';

				var params = {};
				params.data = data;
				params.async = false;
				params.dataType = 'json';
				AppConnector.request(params).done(function (reponseData) {
					aDeferred.resolve(reponseData);
				});
			}
			return aDeferred.promise();
		},
		registerEvents: function () {
			this._super();
			var form = this.getForm();
			this.usersEditInstance = Vtiger_Edit_Js.getInstance();
			this.updateStartHourElement(form);
			this.hourFormatUpdateEvent();
			this.startHourUpdateEvent(form);
			Users_Edit_Js.registerChangeEventForCurrencySeparator();
		}
	}
);
