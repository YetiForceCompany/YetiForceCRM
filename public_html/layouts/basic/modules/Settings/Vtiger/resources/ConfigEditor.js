/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

jQuery.Class("Settings_Vtiger_ConfigEditor_Js", {}, {

	/*
	 * Function to save the Configuration Editor content
	 */
	saveConfigEditor: function (form) {
		var aDeferred = jQuery.Deferred();

		let params = form.serializeFormData();
		params.module = app.getModuleName();
		params.parent = app.getParentModuleName();
		params.action = 'ConfigEditorSaveAjax';
		AppConnector.request(params).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error, err) {
			aDeferred.reject(error, err);
		});
		return aDeferred.promise();
	},

	/*
	 * Function to load the contents from the url through pjax
	 */
	loadContents: function (url) {
		var aDeferred = jQuery.Deferred();
		AppConnector.requestPjax(url).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error, err) {
			aDeferred.reject();
		});
		return aDeferred.promise();
	},

	/*
	 * function to register the events in editView
	 */
	registerEditViewEvents: function () {
		var thisInstance = this;
		var form = jQuery('#ConfigEditorForm');
		var detailUrl = form.data('detailUrl');

		//register all select2 Elements
		App.Fields.Picklist.showSelect2ElementView(form.find('select.select2'), {dropdownCss: {'z-index': 0}});

		//register validation engine
		var params = app.validationEngineOptions;
		params.onValidationComplete = function (form, valid) {
			if (valid) {
				var progressIndicatorElement = jQuery.progressIndicator({
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
				thisInstance.saveConfigEditor(form).done(function (data) {
					var params = {};
					if (data['success']) {
						params['text'] = app.vtranslate('JS_CONFIGURATION_DETAILS_SAVED');
						thisInstance.loadContents(detailUrl).done(function (data) {
							progressIndicatorElement.progressIndicator({'mode': 'hide'});
							jQuery('.contentsDiv').html(data);
							thisInstance.registerDetailViewEvents();
						});
						Settings_Vtiger_Index_Js.showMessage(params);
					} else {
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					}
				}).fail(function (error, err) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				});
				return valid;
			}
		}
		form.validationEngine(params);

		form.on('submit', function (e) {
			e.preventDefault();
		})

		//Register click event for cancel link
		var cancelLink = form.find('.cancelLink');
		cancelLink.on('click', function () {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			thisInstance.loadContents(detailUrl).done(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'})
				jQuery('.contentsDiv').html(data);
				thisInstance.registerDetailViewEvents();
			});
		});
	},

	/*
	 * function to register the events in DetailView
	 */
	registerDetailViewEvents: function () {
		var thisInstance = this;
		var container = jQuery('#ConfigEditorDetails');
		var editButton = container.find('.editButton');

		//Register click event for edit button
		editButton.on('click', function () {
			var url = editButton.data('url');
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			thisInstance.loadContents(url).done(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				jQuery('.contentsDiv').html(data);
				thisInstance.registerEditViewEvents();
			}).fail(function (error, err) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		});
	},

	registerEvents: function () {
		if (jQuery('#ConfigEditorDetails').length > 0) {
			this.registerDetailViewEvents();
		} else {
			this.registerEditViewEvents();
		}
	}

});

jQuery(document).ready(function (e) {
	var tacInstance = new Settings_Vtiger_ConfigEditor_Js();
	tacInstance.registerEvents();
})
Vtiger_WholeNumberGreaterThanZero_Validator_Js("Vtiger_NumberRange100_Validator_Js", {

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var rangeInstance = new Vtiger_NumberRange100_Validator_Js();
		rangeInstance.setElement(field);
		var response = rangeInstance.validate();
		if (response != true) {
			return rangeInstance.getError();
		}
	}

}, {
	/**
	 * Function to validate the percentage field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var response = this._super();
		if (response != true) {
			return response;
		} else {
			var fieldValue = this.getFieldValue();
			if (fieldValue < 1 || fieldValue > 100) {
				var errorInfo = app.vtranslate('JS_PLEASE_ENTER_NUMBER_IN_RANGE_1TO100');
				this.setError(errorInfo);
				return false;
			}
			return true;
		}
	}

})
