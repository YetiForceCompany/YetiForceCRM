/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Settings_Workflows_Edit_Js(
	'Settings_Workflows_Edit2_Js',
	{},
	{
		step2Container: false,

		advanceFilterInstance: false,

		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.step2Container;
		},

		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step2Container = element;
			return this;
		},

		/**
		 * Function  to intialize the reports step1
		 */
		initialize: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('#workflow_step2');
			}
			if (container.is('#workflow_step2')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('#workflow_step2'));
			}
		},

		calculateValues: function () {
			//handled advanced filters saved values.
			var enableFilterElement = jQuery('#enableAdvanceFilters');
			if (enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
				jQuery('#advanced_filter').val(jQuery('#olderConditions').val());
			} else {
				jQuery('[name="filtersavedinnew"]').val('6');
				var advfilterlist = this.advanceFilterInstance.getValues();
				jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
			}
		},

		submit: function () {
			var aDeferred = jQuery.Deferred();
			var form = this.getContainer();
			this.calculateValues();
			var formData = form.serializeFormData();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(formData).done(function (data) {
				form.hide();
				if (data.result) {
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_WORKFLOW_SAVED_SUCCESSFULLY')
					});
					var workflowRecordElement = jQuery('[name="record"]', form);
					if (workflowRecordElement.val() == '') {
						workflowRecordElement.val(data.result.id);
					}
					var params = {
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						view: 'Edit',
						mode: 'step3',
						record: data.result.id
					};
					AppConnector.request(params).done(function (data) {
						aDeferred.resolve(data);
					});
				}
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
			});
			return aDeferred.promise();
		},

		registerEnableFilterOption: function () {
			jQuery('[name="conditionstype"]').on('change', function (e) {
				var advanceFilterContainer = jQuery('#advanceFilterContainer');
				var currentRadioButtonElement = jQuery(e.currentTarget);
				if (currentRadioButtonElement.hasClass('recreate')) {
					if (currentRadioButtonElement.is(':checked')) {
						advanceFilterContainer.removeClass('zeroOpacity');
					}
				} else {
					advanceFilterContainer.addClass('zeroOpacity');
				}
			});
		},

		registerEvents: function () {
			var opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'bottomRight';
			jQuery('#workflow_step2').validationEngine(opts);

			var container = this.getContainer();
			// When you come to step2 we should remove validation for condition values other than rawtwxt
			jQuery('button[type="submit"]', container).on('click', function (e) {
				var fieldUiHolders = jQuery('.fieldUiHolder');
				for (var i = 0; i < fieldUiHolders.length; i++) {
					var fieldUiHolder = fieldUiHolders[i];
					var fieldValueElement = jQuery('.getPopupUi', fieldUiHolder);
					var valueType = jQuery('[name="valuetype"]', fieldUiHolder).val();
					if (valueType != 'rawtext') {
						fieldValueElement.removeAttr('data-validation-engine');
						fieldValueElement.removeClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
					}
				}
			});
			App.Fields.Picklist.changeSelectElementView(container);
			this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', container));
			this.getPopUp();
			if (jQuery('[name="filtersavedinnew"]', container).val() == '5') {
				this.registerEnableFilterOption();
			}
		}
	}
);
