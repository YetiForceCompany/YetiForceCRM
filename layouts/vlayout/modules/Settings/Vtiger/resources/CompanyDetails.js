/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_Vtiger_CompanyDetails_Js", {}, {
	registerUpdateDetailsClickEvent: function () {
		jQuery('#updateCompanyDetails').on('click', function (e) {
			jQuery('#companyDetailsContainer').addClass('hide');
			jQuery('#updateCompanyDetailsForm').removeClass('hide');
			jQuery('#updateCompanyDetails').addClass('hide');
			jQuery('#addCustomFieldForm').addClass('hide');
			jQuery('#addCustomField').addClass('hide');
		});
	},
	registerAddFieldEvent: function () {
		var thisInstance = this;
		var contents = jQuery('.contentsDiv ');
		contents.find('#addCustomField').on('click', function (e) {
			var addBlockContainer = contents.find('.addCustomFieldModal').clone(true, true);
			var callBackFunction = function (data) {
				data.find('.addCustomFieldModal').removeClass('hide').show();
				var form = data.find('.addCustomBlockForm');
				jQuery('[name="saveButton"]').off('click').on('click', function () {
					var progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					thisInstance.registerSaveCompanyDetailsEvent(form).then(
							function (data) {
								var params = {};
								var result = data['result'];
								if (result['success'] == true) {
									params['text'] = result['message'];
									Settings_Vtiger_Index_Js.showMessage(params);
									var params = {};
									params['module'] = app.getModuleName();
									params['view'] = 'CompanyDetails';
									params['parent'] = app.getParentModuleName();
									AppConnector.request(params).then(function (data) {
										jQuery('.contentsDiv').html(data);
										thisInstance.registerEvents();
										progressIndicatorElement.progressIndicator({'mode': 'hide'});
									});
								}
								else {
									progressIndicatorElement.progressIndicator({'mode': 'hide'});
									params['text'] = result['message'];
									params['type'] = 'error';
									Settings_Vtiger_Index_Js.showMessage(params);
								}
							}
					);
					app.hideModalWindow();
					return true;
				});
				jQuery('.cancelLink').off('click').on('click', function () {
					var progressIndicatorElement = jQuery.progressIndicator({
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					var params = {};
					params['module'] = app.getModuleName();
					params['view'] = 'CompanyDetails';
					params['parent'] = app.getParentModuleName();
					AppConnector.request(params).then(function (data) {
						jQuery('.contentsDiv').html(data);
						thisInstance.registerEvents();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
				});
				form.submit(function (e) {
					e.preventDefault();
				})
			}
			app.showModalWindow(addBlockContainer, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			});

		});
	},
	registerSaveCompanyDetailsEvent: function (form) {
		var thisInstance = this;
		var params = $('.addCustomBlockForm').serializeFormData();
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'CompanyDetailsFieldSave';
		if (params['fieldName'] == '') {
			var params = [];
			params['text'] = app.vtranslate('JS_FILL_FORM_ERROR');
			params['type'] = 'error';
			Settings_Vtiger_Index_Js.showMessage(params);
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			var params = {};
			params['module'] = app.getModuleName();
			params['view'] = 'CompanyDetails';
			params['parent'] = app.getParentModuleName();
			AppConnector.request(params).then(function (data) {
				jQuery('.contentsDiv').html(data);
				thisInstance.registerEvents();
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		}
		else {
			var aDeferred = jQuery.Deferred();
			AppConnector.request(params).then(
					function (data) {
						aDeferred.resolve(data);
					},
					function (error) {
						aDeferred.reject(error);
					}
			);
			return aDeferred.promise();
		}
		return true;
	},
	registerCancelClickEvent: function () {
		jQuery('#updateCompanyDetailsForm button:reset').on('click', function () {
			jQuery('#addCustomField').removeClass('hide');
			jQuery('#companyDetailsContainer').removeClass('hide');
			jQuery('#updateCompanyDetailsForm').addClass('hide');
			jQuery('#updateCompanyDetails').removeClass('hide');
			jQuery('#addCustomFieldForm').removeClass('hide');
			jQuery('#addCustomFieldForm').addClass('hide');
		});
	},
	checkValidation: function () {
		var imageObj = jQuery('#logoFile');
		var imageName = imageObj.val();
		if (imageName != '') {
			var image_arr = new Array();
			image_arr = imageName.split(".");
			var image_arr_last_index = image_arr.length - 1;
			if (image_arr_last_index < 0) {
				imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE'), 'error', 'topLeft', true);
				imageObj.val('');
				return false;
			}
			var image_extensions = JSON.parse(jQuery('#supportedImageFormats').val());
			var image_ext = image_arr[image_arr_last_index].toLowerCase();
			if (image_extensions.indexOf(image_ext) != '-1') {
				var size = imageObj[0].files[0].size;
				if (size < 1024000) {
					return true;
				} else {
					imageObj.validationEngine('showPrompt', app.vtranslate('LBL_MAXIMUM_SIZE_EXCEEDS'), 'error', 'topLeft', true);
					return false;
				}
			} else {
				imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE'), 'error', 'topLeft', true);
				imageObj.val('');
				return false;
			}

		}
	},
	getParameterByName: function (name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	},
	registerEvents: function () {
		this.registerUpdateDetailsClickEvent();
		this.registerCancelClickEvent();
		this.registerAddFieldEvent();
		jQuery('#updateCompanyDetailsForm').validationEngine(app.validationEngineOptions);
	}

});
