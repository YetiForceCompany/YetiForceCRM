/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_PDF_Edit_Js("Settings_PDF_Edit6_Js", {}, {
	step6Container: false,
	advanceFilterInstance: false,
	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer: function () {
		return this.step6Container;
	},
	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step6Container = element;
		return this;
	},
	/**
	 * Function  to intialize the reports step1
	 */
	initialize: function (container) {
		if (typeof container === 'undefined') {
			container = jQuery('#pdf_step6');
		}
		if (container.is('#pdf_step6')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#pdf_step6'));
		}
	},
	calculateValues: function () {
		//handled advanced filters saved values.
		var enableFilterElement = jQuery('#enableAdvanceFilters');
		if (enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
			jQuery('#advanced_filter').val(jQuery('#olderConditions').val());
		} else {
			jQuery('[name="filtersavedinnew"]').val("6");
			var advfilterlist = this.advanceFilterInstance.getValues();
			jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
		}
	},
	submit: function () {
		var aDeferred = jQuery.Deferred();
		this.calculateValues();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var saveData = form.serializeFormData();
		saveData['action'] = 'Save';
		saveData['step'] = 6;
		saveData['view'] = '';
		AppConnector.request(saveData).then(
				function (data) {
					data = JSON.parse(data);
					if (data.success == true) {
						Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});

						AppConnector.request(formData).then(
								function (data) {
									form.hide();
									progressIndicatorElement.progressIndicator({
										'mode': 'hide'
									})
									aDeferred.resolve(data);
								},
								function (error, err) {
									app.errorLog(error, err);
								}
						);
					}
				},
				function (error, err) {
					app.errorLog(error, err);
				}
		);
		return aDeferred.promise();
	},
	registerCancelStepClickEvent: function (form) {
		jQuery('button.cancelLink', form).on('click', function () {
			window.history.back();
		});
	},
	getPopUp: function (container) {
		var thisInstance = this;
		if (typeof container == 'undefined') {
			container = thisInstance.getContainer();
		}
		container.on('click', '.getPopupUi', function (e) {
			var fieldValueElement = jQuery(e.currentTarget);
			var fieldValue = fieldValueElement.val();
			var fieldUiHolder = fieldValueElement.closest('.fieldUiHolder');
			var valueType = fieldUiHolder.find('[name="valuetype"]').val();
			if (valueType == '') {
				valueType = 'rawtext';
			}
			var conditionsContainer = fieldValueElement.closest('.conditionsContainer');
			var conditionRow = fieldValueElement.closest('.conditionRow');

			var clonedPopupUi = conditionsContainer.find('.popupUi').clone(true, true).removeClass('popupUi').addClass('clonedPopupUi')
			clonedPopupUi.find('select').addClass('chzn-select');
			clonedPopupUi.find('.fieldValue').val(fieldValue);
			if (fieldValueElement.hasClass('date')) {
				clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
				var dataFormat = fieldValueElement.data('date-format');
				if (valueType == 'rawtext') {
					var value = fieldValueElement.val();
				} else {
					value = '';
				}
				var clonedDateElement = '<input type="text" class="dateField fieldValue col-md-4" value="' + value + '" data-date-format="' + dataFormat + '" data-input="true" >'
				clonedPopupUi.find('.fieldValueContainer').prepend(clonedDateElement);
			} else if (fieldValueElement.hasClass('time')) {
				clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
				if (valueType == 'rawtext') {
					var value = fieldValueElement.val();
				} else {
					value = '';
				}
				var clonedTimeElement = '<input type="text" class="timepicker-default fieldValue col-md-4 form-control" value="' + value + '" data-input="true" >'
				clonedPopupUi.find('.fieldValueContainer').prepend(clonedTimeElement);
			} else if (fieldValueElement.hasClass('boolean')) {
				clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
				if (valueType == 'rawtext') {
					var value = fieldValueElement.val();
				} else {
					value = '';
				}
				var clonedBooleanElement = '<input type="checkbox" class="fieldValue col-md-4" value="' + value + '" data-input="true" >';
				clonedPopupUi.find('.fieldValueContainer').prepend(clonedBooleanElement);

				var fieldValue = clonedPopupUi.find('.fieldValueContainer input').val();
				if (value == 'true:boolean' || value == '') {
					clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
				} else {
					clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
				}
			}

			var callBackFunction = function (data) {
				data.find('.clonedPopupUi').removeClass('hide');
				var moduleNameElement = conditionRow.find('[name="modulename"]');
				if (moduleNameElement.length > 0) {
					var moduleName = moduleNameElement.val();
					data.find('.useFieldElement').addClass('hide');
					data.find('[name="' + moduleName + '"]').removeClass('hide');
				}
				app.changeSelectElementView(data);
				app.registerEventForDatePickerFields(data);
				app.registerEventForTimeFields(data);
				thisInstance.postShowModalAction(data, valueType);
				thisInstance.registerChangeFieldEvent(data);
				thisInstance.registerSelectOptionEvent(data);
				thisInstance.registerPopUpSaveEvent(data, fieldUiHolder);
				thisInstance.registerRemoveModalEvent(data);
				data.find('.fieldValue').filter(':visible').trigger('focus');
				data.find('[data-close-modal="modal"]').off('click').on('click', function () {
					jQuery(this).closest('.modal').removeClass('in').css('display', 'none');
				})
			}

			conditionsContainer.find('.clonedPopUp').html(clonedPopupUi);
			jQuery('.clonedPopupUi').on('shown.bs.modal', function () {
				if (typeof callBackFunction == 'function') {
					callBackFunction(jQuery('.clonedPopupUi', conditionsContainer));
				}
			});
			jQuery('.clonedPopUp', conditionsContainer).find('.clonedPopupUi').modal();
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
				fieldValueElement.attr('data-validation-engine', 'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
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
			data.modal('hide');
			fieldValueElement.validationEngine('hide');
		});
	},
	postShowModalAction: function (data, valueType) {
		if (valueType == 'fieldname') {
			jQuery('.useFieldContainer', data).removeClass('hide');
			jQuery('.textType', data).val(valueType).trigger('chosen:updated');
		} else if (valueType == 'expression') {
			jQuery('.useFieldContainer', data).removeClass('hide');
			jQuery('.useFunctionContainer', data).removeClass('hide');
			jQuery('.textType', data).val(valueType).trigger('chosen:updated');
		}
		jQuery('#' + valueType + '_help', data).removeClass('hide');
		var uiType = jQuery('.textType', data).find('option:selected').data('ui');
		jQuery('.fieldValue', data).hide();
		jQuery('[data-' + uiType + ']', data).show();
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
				useFieldContainer.removeClass('hide');
				useFunctionContainer.addClass('hide');
			} else if (valueType == 'expression') {
				useFieldContainer.removeClass('hide');
				useFunctionContainer.removeClass('hide');
			} else {
				useFieldContainer.addClass('hide');
				useFunctionContainer.addClass('hide');
			}
			jQuery('.helpmessagebox', data).addClass('hide');
			jQuery('#' + valueType + '_help', data).removeClass('hide');
			data.find('.fieldValue').val('');
		});
	},
	registerSelectOptionEvent: function (data) {
		jQuery('.useField,.useFunction', data).on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var newValue = currentElement.val();
			var oldValue = data.find('.fieldValue').filter(':visible').val();
			if (currentElement.hasClass('useField')) {
				if (oldValue != '') {
					var concatenatedValue = oldValue + ' ' + newValue;
				} else {
					concatenatedValue = newValue;
				}
			} else {
				concatenatedValue = oldValue + newValue;
			}
			data.find('.fieldValue').val(concatenatedValue);
			currentElement.val('').trigger('chosen:updated');
		});
	},
	registerEvents: function () {
		var container = this.getContainer();

		var opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function (form, valid) {
			//returns the valid status
			return valid;
		};
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		this.registerCancelStepClickEvent(container);
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', container));
		this.getPopUp(container);
		app.changeSelectElementView(container);
	}
});
