/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
function SaveResult() {
	this.skipCheckData = []
	this.skipCheckDataOneTime = '';
	this.executeTaskStatus = true
	this.recordValue = false;
	this.loadFormData = function (formData) {
		this.recordValue = formData
	}
	this.checkData = function (formData, form) {
		var thisInstnce = this;
		var params = {};
		var resp = [];
		var staus = true;
		delete thisInstnce.recordValue['__vtrftk'];
		delete thisInstnce.recordValue['picklistDependency'];
		delete formData['__vtrftk'];
		delete formData['picklistDependency'];
		if (thisInstnce.recordValue != false) {
			$.each(thisInstnce.recordValue, function (key, value) {
				formData['p_' + key] = value;
			});
		}
		params.data = {
			module: 'DataAccess',
			parent: 'Settings',
			action: 'ExecuteHandlers',
			param: formData,
		};
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(
				function (response) {
					resp = response['result']['data'];
					Vtiger_Helper_Js.hidePnotify();
					$.each(resp, function (key, object) {
						if (jQuery.inArray(key, thisInstnce.skipCheckData) != -1 || thisInstnce.skipCheckDataOneTime === key) {
							return;
						}
						if (typeof object.type != 'undefined') {
							staus = thisInstnce.executeTask(object, key, form)
						}
					});
					thisInstnce.skipCheckDataOneTime = '';
				}
		);
		return staus; // staus ==  true = ok, false = block edit
	}
	this.executeTask = function (data, key, form) {
		var instance = this;
		data.type = parseInt(data.type)
		switch (data.type) {
			case 0:
				this.showNotify(data.info)
				this.executeTaskStatus = data.save_record;
				break;
			case 1:
				this.showQuickCreate(data, this.executeTaskStatus, form, key)
				view = app.getViewName();
				if (view != 'Detail') {
					this.executeTaskStatus = false
				}
				break;
			case 2:
				if (data.save_record) {
					this.executeTaskStatus = data.save_record;
					break;
				}
				this.showNotify(data.info, data.type, form, key)
				view = app.getViewName();
				if (view != 'Detail') {
					this.executeTaskStatus = false
				}
				break;
			case 3:
				if (data.save_record) {
					this.executeTaskStatus = data.save_record;
					break;
				}
				var userId = app.getMainParams('current_user_id');
				var recordId = app.getMainParams('recordId');
				var saveDuplicateCache = app.cacheGet('SaveDuplicateCache_' + recordId + '_' + userId, false);
				if (!saveDuplicateCache) {
					this.showBootBoxModal(data.info, form, key);
					this.executeTaskStatus = false;
				} else {
					this.executeTaskStatus = true;
				}
				break;
		}
		return this.executeTaskStatus; // true = ok, false = block edit
	}
	this.showBootBoxModal = function (info, form, key) {
		var instance = this;
		bootbox.dialog({
			message: info.text,
			title: info.title,
			buttons: {
				success: {
					label: app.vtranslate('JS_LBL_SAVE'),
					className: info.type ? "btn-success" : 'hide',
					callback: function () {
						var cache = $("input[name='cache']:checked").val();
						if (cache) {
							var userId = app.getMainParams('current_user_id');
							var recordId = app.getMainParams('recordId');
							app.cacheSet('SaveDuplicateCache_' + recordId + '_' + userId, true);
						}
						if (typeof form != 'undefined') {
							instance.skipCheckDataOneTime = key;
							form.submit();
						}
					}
				},
				danger: {
					label: app.vtranslate('JS_LBL_CANCEL'),
					className: "btn-warning",
					callback: function () {
					}
				}
			}
		});
	}
	this.showNotify = function (info, type, form, key) {
		var instance = this;
		var params = {
			text: info.text,
			type: 'info',
			animation: 'show',
			width: 'auto'
		};
		if (info.ntype) {
			params.type = info.ntype;
		}
		if (typeof form != 'undefined' && type == 2) {
			params.confirm = {
				confirm: true,
				buttons: [{
						text: app.vtranslate('JS_OK'),
						addClass: 'btn-primary saveButton',
						click: function (notice) {
							instance.skipCheckData.push(key);
							notice.remove();
							form.submit();
						}
					}, {addClass: 'hide', }]
			};
			params.buttons = {
				closer: false,
				sticker: false
			};
			params.history = {
				history: false
			};
			//waiting time before automatic saving.
			params.before_open = function (notice) {
				setTimeout(function () {
					notice.get().find('.saveButton').trigger('click');
				}, 10000);
			}
		}
		params = jQuery.extend(info, params);
		Vtiger_Helper_Js.showPnotify(params);
	}

	this.showQuickCreate = function (data, orgExecuteTaskStatus, form, key) {
		var instance = this;
		var moduleName = data.module;
		var sourceRecord = jQuery('input[name="record"]').val();
		if (typeof sourceRecord == 'undefined') {
			sourceRecord = app.getRecordId();
		}
		var sourceModule = app.getModuleName();
		var preQuickCreateSave = function (data) {
			var index, queryParam, queryParamComponents;
			jQuery('<input type="hidden" name="sourceModule" value="' + sourceModule + '" />').appendTo(data);
			jQuery('<input type="hidden" name="sourceRecord" value="' + sourceRecord + '" />').appendTo(data);
			jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);
		}
		var postQuickCreateSave = function (data) {
			if (typeof form != 'undefined') {
				instance.skipCheckData.push(key);
				form.submit();
			}
		}
		var quickCreateParams = {};
		var relatedParams = {};
		var quickcreateUrl = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		relatedParams['sourceModule'] = sourceModule;
		relatedParams['sourceRecord'] = sourceRecord;
		relatedParams['relationOperation'] = true;
		if ((app.getViewName() == 'Detail' || app.getViewName() == 'Edit') && moduleName == 'OSSTimeControl') {
			if (data.title) {
				relatedParams['name'] = data.title;
			} else {
				relatedParams['name'] = jQuery('.recordLabel').attr('title');
			}
		}
		quickCreateParams['callbackFunction'] = postQuickCreateSave;
		quickCreateParams['callbackPostShown'] = preQuickCreateSave;
		quickCreateParams['data'] = relatedParams;
		quickCreateParams['noCache'] = true;
		var progress = jQuery.progressIndicator();
		var headerInstance = new Vtiger_Header_Js();
		headerInstance.getQuickCreateForm(quickcreateUrl, moduleName, quickCreateParams).then(function (data) {
			headerInstance.handleQuickCreateData(data, quickCreateParams);
			progress.progressIndicator({'mode': 'hide'});
			jQuery('form[name="QuickCreate"]').closest('.modal').on('click', '.cancelLink ,.close', function (e) {
				if (typeof form != 'undefined') {
					instance.skipCheckDataOneTime = key;
					form.submit();
				}
			});
		});
	}
}
