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

Settings_Vtiger_Edit_Js(
	'Settings_Workflows_Edit_Js',
	{
		instance: {}
	},
	{
		currentInstance: false,
		workFlowsContainer: false,
		init: function () {
			this.initiate();
		},
		/**
		 * Function to get the container which holds all the workflow elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.workFlowsContainer;
		},
		/**
		 * Function to set the reports container
		 * @params : element - which represents the workflow container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.workFlowsContainer = element;
			return this;
		},
		/*
		 * Function to return the instance based on the step of the Workflow
		 */
		getInstance: function (step) {
			if (step in Settings_Workflows_Edit_Js.instance) {
				return Settings_Workflows_Edit_Js.instance[step];
			} else {
				var moduleClassName = 'Settings_Workflows_Edit' + step + '_Js';
				Settings_Workflows_Edit_Js.instance[step] = new window[moduleClassName]();
				return Settings_Workflows_Edit_Js.instance[step];
			}
		},
		/*
		 * Function to get the value of the step
		 * returns 1 or 2 or 3
		 */
		getStepValue: function () {
			var container = this.currentInstance.getContainer();
			return jQuery('.step', container).val();
		},
		/*
		 * Function to initiate the step 1 instance
		 */
		initiate: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('.workFlowContents');
			}
			if (container.is('.workFlowContents')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('.workFlowContents', container));
			}
			this.initiateStep('1');
			this.currentInstance.registerEvents();
		},
		/*
		 * Function to initiate all the operations for a step
		 * @params step value
		 */
		initiateStep: function (stepVal) {
			var step = 'step' + stepVal;
			this.activateHeader(step);
			var currentInstance = this.getInstance(stepVal);
			this.currentInstance = currentInstance;
		},
		/*
		 * Function to activate the header based on the class
		 * @params class name
		 */
		activateHeader: function (step) {
			var headersContainer = jQuery('.crumbs ');
			headersContainer.find('.active').removeClass('active');
			jQuery('#' + step, headersContainer).addClass('active');
		},
		/*
		 * Function to register the click event for next button
		 */
		registerFormSubmitEvent: function (form) {
			const thisInstance = this;
			if (jQuery.isFunction(thisInstance.currentInstance.submit)) {
				form.on('submit', function (e) {
					let specialValidation = true;
					if (jQuery.isFunction(thisInstance.currentInstance.isFormValidate)) {
						specialValidation = thisInstance.currentInstance.isFormValidate();
					}
					if (jQuery(e.currentTarget).validationEngine('validate') && specialValidation) {
						thisInstance.currentInstance.submit().done(function (data) {
							thisInstance.getContainer().append(data);
							thisInstance.initiateStep(parseInt(thisInstance.getStepValue()) + 1);
							thisInstance.currentInstance.initialize();
							thisInstance.registerFormSubmitEvent(thisInstance.currentInstance.getContainer());
							thisInstance.currentInstance.registerEvents();
						});
					}
					e.preventDefault();
				});
			}
		},
		back: function () {
			var step = this.getStepValue();
			var prevStep = parseInt(step) - 1;
			this.currentInstance.initialize();
			var container = this.currentInstance.getContainer();
			var workflowRecordElement = jQuery('[name="record"]', container);
			var workFlowId = workflowRecordElement.val();
			container.remove();
			this.initiateStep(prevStep);
			var currentContainer = this.currentInstance.getContainer();
			currentContainer.show();
			jQuery('[name="record"]', currentContainer).val(workFlowId);
			var modulesList = jQuery('#moduleName', currentContainer);
			if (modulesList.length > 0 && workFlowId != '') {
				modulesList.attr('disabled', 'disabled').trigger('change');
			}
		},
		/**
		 * Get popup with value
		 * @param container
		 */
		getPopUp(container) {
			if (typeof container === 'undefined') {
				container = this.getContainer();
			}
			container.on('click', '.getPopupUi', (e) => {
				if (container.find('[name="execution_condition"]').val() == 6) {
					return false;
				}
				const fieldValueElement = jQuery(e.currentTarget);
				const fieldValue = fieldValueElement.val();
				const fieldUiHolder = fieldValueElement.closest('.fieldUiHolder');
				let valueType = fieldUiHolder.find('[name="valuetype"]').val();
				if (valueType === '') {
					valueType = 'rawtext';
				}
				const conditionsContainer = fieldValueElement.closest('.js-conditions-container');
				const conditionRow = fieldValueElement.closest('.js-conditions-row');
				var clonedPopupUi = conditionsContainer
					.find('.popupUi')
					.clone(true, true)
					.removeClass('popupUi')
					.addClass('clonedPopupUi');
				clonedPopupUi.find('select').addClass('select2');
				clonedPopupUi.find('.fieldValue').val(fieldValue);
				let value;
				if (fieldValueElement.hasClass('date')) {
					clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
					const dataFormat = fieldValueElement.data('date-format');
					if (valueType === 'rawtext') {
						value = fieldValueElement.val();
					} else {
						value = '';
					}
					const clonedDateElement =
						'<input type="text" class="dateField fieldValue col-md-4 form-control" value="' +
						value +
						'" data-date-format="' +
						dataFormat +
						'" data-input="true" >';
					clonedPopupUi.find('.fieldValueContainer').prepend(clonedDateElement);
				} else if (fieldValueElement.hasClass('time')) {
					clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
					if (valueType === 'rawtext') {
						value = fieldValueElement.val();
					} else {
						value = '';
					}
					const clonedTimeElement =
						'<input type="text" class="timepicker-default fieldValue col-md-4 form-control" value="' +
						value +
						'" data-input="true" >';
					clonedPopupUi.find('.fieldValueContainer').prepend(clonedTimeElement);
				} else if (fieldValueElement.hasClass('boolean')) {
					clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
					if (valueType === 'rawtext') {
						value = fieldValueElement.val();
					} else {
						value = '';
					}
					const clonedBooleanElement =
						'<input type="checkbox" class="fieldValue col-md-4 form-control" value="' + value + '" data-input="true" >';
					clonedPopupUi.find('.fieldValueContainer').prepend(clonedBooleanElement);
					if (value === 'true:boolean' || value === '') {
						clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
					} else {
						clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
					}
				}
				conditionsContainer.find('.clonedPopUp').html(clonedPopupUi);
				const clonedPopupElement = $('.clonedPopUp', conditionsContainer).find('.clonedPopupUi');
				$('.clonedPopupUi', conditionsContainer).on('shown.bs.modal', () => {
					const data = $('.clonedPopupUi', conditionsContainer);
					data.find('.clonedPopupUi').removeClass('d-none');
					const moduleNameElement = conditionRow.find('[name="modulename"]');
					if (moduleNameElement.length > 0) {
						const moduleName = moduleNameElement.val();
						data.find('.useFieldElement').addClass('d-none');
						data.find('[name="' + moduleName + '"]').removeClass('d-none');
					}
					App.Fields.Picklist.changeSelectElementView(data);
					App.Fields.Date.register(data);
					app.registerEventForClockPicker(data);
					this.postShowModalAction(data, valueType);
					this.registerChangeFieldEvent(data);
					this.registerSelectOptionEvent(data);
					this.registerPopUpSaveEvent(data, fieldUiHolder);
					data.find('.fieldValue').filter(':visible').trigger('focus');
					clonedPopupElement
						.find('[data-close-modal="modal"], [data-dismiss="modal"]')
						.off('click')
						.on('click', function (e) {
							e.preventDefault();
							e.stopPropagation();
							$(this).closest('.modal').removeClass('in').css('display', 'none');
						});
				});
				clonedPopupElement.modal();
			});
		},
		registerRemoveModalEvent: function (data) {
			data.on('click', '.closeModal', function (e) {
				data.modal('hide');
			});
		},
		registerPopUpSaveEvent: function (data, fieldUiHolder) {
			jQuery('[name="saveButton"]', data).on('click', function (e) {
				var valueType = jQuery('.textType', data).val();

				fieldUiHolder.find('[name="valuetype"]').val(valueType);
				var fieldValueElement = fieldUiHolder.find('.getPopupUi');
				if (valueType != 'rawtext') {
					fieldValueElement.removeAttr('data-validation-engine');
					fieldValueElement.removeClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
				} else {
					fieldValueElement.addClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
					fieldValueElement.attr(
						'data-validation-engine',
						'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]'
					);
				}
				var fieldType = data.find('.fieldValue').filter(':visible').attr('type');
				var fieldValue = data.find('.fieldValue').filter(':visible').val();
				//For checkbox field type, handling fieldValue
				if (fieldType == 'checkbox') {
					if (data.find('.fieldValue').filter(':visible').is(':checked')) {
						fieldValue = 'true:boolean';
					} else {
						fieldValue = 'false:boolean';
					}
				}
				fieldValueElement.val(fieldValue);
				fieldValueElement.validationEngine('hide');
				data.remove();
			});
		},
		registerSelectOptionEvent: (data) => {
			$('.useField,.useFunction', data).on('change', (e) => {
				let currentElement = $(e.currentTarget);
				let newValue = currentElement.val();
				let oldValue = data.find('.fieldValue').filter(':visible').val(),
					concatenatedValue;
				if (currentElement.hasClass('useField')) {
					if (oldValue != '') {
						concatenatedValue = oldValue + ' ' + newValue;
					} else {
						concatenatedValue = newValue;
					}
				} else {
					concatenatedValue = oldValue + newValue;
				}
				data.find('.fieldValue').val(concatenatedValue);
				currentElement.val('').trigger('change.select2');
			});
		},
		registerChangeFieldEvent: function (data) {
			jQuery('.textType', data).on('change', function (e) {
				var valueType = jQuery(e.currentTarget).val();
				var useFieldContainer = jQuery('.useFieldContainer', data);
				var useFunctionContainer = jQuery('.useFunctionContainer', data);
				var uiType = jQuery(e.currentTarget).find('option:selected').data('ui');
				jQuery('.fieldValue', data).hide();
				jQuery('[data-' + uiType + ']', data).show();
				if (valueType == 'fieldname') {
					useFieldContainer.removeClass('d-none');
					useFunctionContainer.addClass('d-none');
				} else if (valueType == 'expression') {
					useFieldContainer.removeClass('d-none');
					useFunctionContainer.removeClass('d-none');
				} else {
					useFieldContainer.addClass('d-none');
					useFunctionContainer.addClass('d-none');
				}
				jQuery('.helpmessagebox', data).addClass('d-none');
				jQuery('#' + valueType + '_help', data).removeClass('d-none');
				data.find('.fieldValue').val('');
			});
		},
		postShowModalAction: function (data, valueType) {
			if (valueType == 'fieldname') {
				jQuery('.useFieldContainer', data).removeClass('d-none');
				jQuery('.textType', data).val(valueType).trigger('change');
			} else if (valueType == 'expression') {
				jQuery('.useFieldContainer', data).removeClass('d-none');
				jQuery('.useFunctionContainer', data).removeClass('d-none');
				jQuery('.textType', data).val(valueType).trigger('change');
			}
			jQuery('#' + valueType + '_help', data).removeClass('d-none');
			var uiType = jQuery('.textType', data).find('option:selected').data('ui');
			jQuery('.fieldValue', data).hide();
			jQuery('[data-' + uiType + ']', data).show();
		},
		/*
		 * Function to register the click event for back step
		 */
		registerBackStepClickEvent: function () {
			var thisInstance = this;
			var container = this.getContainer();
			container.on('click', '.backStep', function (e) {
				thisInstance.back();
			});
		},
		registerEvents: function () {
			var form = this.currentInstance.getContainer();
			this.registerFormSubmitEvent(form);
			this.registerBackStepClickEvent();
		}
	}
);
