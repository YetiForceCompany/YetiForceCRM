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

Settings_Workflows_Edit_Js(
	'Settings_Workflows_Edit3_Js',
	{},
	{
		step3Container: false,
		advanceFilterInstance: false,
		conditionBuilderInstance: false,
		ckEditorInstance: false,
		fieldValueMap: false,
		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.step3Container;
		},
		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step3Container = element;
			return this;
		},
		/**
		 * Function  to intialize the reports step1
		 */
		initialize: function (container) {
			if (typeof container === 'undefined') {
				container = $('#workflow_step3');
			}
			if (container.is('#workflow_step3')) {
				this.setContainer(container);
			} else {
				this.setContainer($('#workflow_step3'));
			}
		},
		registerEditTaskEvent: function () {
			let thisInstance = this,
				container = this.getContainer();
			container.on('click', '[data-url]', function (e) {
				let currentElement = $(e.currentTarget),
					params = currentElement.data('url'),
					progressIndicatorElement = $.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
				app.showModalWindow(null, params, function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (data) {
						let clipboard = App.Fields.Text.registerCopyClipboard(data);
						container.one('hidden.bs.modal', () => {
							clipboard.destroy();
						});
					}
					thisInstance.registerVTCreateTodoTaskEvents();
					var taskType = $('#taskType').val();
					var functionName = 'register' + taskType + 'Events';
					if (typeof thisInstance[functionName] !== 'undefined') {
						thisInstance[functionName].apply(thisInstance, data);
					}
					thisInstance.registerSaveTaskSubmitEvent(taskType);
					$('#saveTask').validationEngine(app.validationEngineOptions);
					thisInstance.registerFillTaskFieldsEvent();
					thisInstance.registerCheckSelectDateEvent();
					App.Tools.VariablesPanel.registerRefreshCompanyVariables(data);
					thisInstance.conditionBuilderInstance = new Vtiger_ConditionBuilder_Js(
						data.find('.js-condition-builder'),
						data.find('.js-source-module').val()
					);
					thisInstance.conditionBuilderInstance.registerEvents();
				});
			});
		},
		registerCheckSelectDateEvent: function () {
			$('[name="check_select_date"]').on('change', function (e) {
				if ($(e.currentTarget).is(':checked')) {
					$('#checkSelectDateContainer').removeClass('d-none').addClass('show');
				} else {
					$('#checkSelectDateContainer').removeClass('show').addClass('d-none');
				}
			});
		},
		/**
		 * Register save task submit event
		 * @param {string} taskType
		 */
		registerSaveTaskSubmitEvent(taskType) {
			$('#saveTask').on('submit', (e) => {
				let form = $(e.currentTarget);
				if (form.validationEngine('validate') === true) {
					let customValidationFunctionName = taskType + 'CustomValidation';
					if (typeof this[customValidationFunctionName] !== 'undefined') {
						let result = this[customValidationFunctionName].apply(this);
						if (result !== true) {
							app.showNotify({
								title: app.vtranslate('JS_MESSAGE'),
								text: result,
								type: 'error'
							});
							e.preventDefault();
							return;
						}
					}
					let preSaveActionFunctionName = 'preSave' + taskType;
					if (typeof this[preSaveActionFunctionName] !== 'undefined') {
						this[preSaveActionFunctionName].apply(this, [taskType]);
					}
					let formData = form.serializeFormData();
					let createEntityModule = form.find('.createEntityModule:visible option:selected');
					formData.entity_type = createEntityModule.val();
					formData.relationId = createEntityModule.attr('data-relation-id');
					AppConnector.request(formData).done((data) => {
						if (data.result) {
							this.getTaskList();
							app.hideModalWindow();
						}
					});
				}
				e.preventDefault();
			});
		},
		VTUpdateFieldsTaskCustomValidation: function () {
			return this.checkDuplicateFieldsSelected();
		},
		VTCreateEntityTaskCustomValidation: function () {
			return this.checkDuplicateFieldsSelected();
		},
		checkDuplicateFieldsSelected: function () {
			var selectedFieldNames = $('#save_fieldvaluemapping').find('.js-conditions-row').find('[name="fieldname"]');
			var result = true;
			var failureMessage = app.vtranslate('JS_SAME_FIELDS_SELECTED_MORE_THAN_ONCE');
			$.each(selectedFieldNames, function (i, ele) {
				var fieldName = $(ele).attr('value');
				var fields = $('[name=' + fieldName + ']').not(':hidden');
				if (fields.length > 1) {
					result = failureMessage;
					return false;
				}
			});
			return result;
		},
		preSaveVTUpdateFieldsTask: function (tasktype) {
			var values = this.getValues(tasktype);
			$('[name="field_value_mapping"]').val(JSON.stringify(values));
		},
		preSaveVTCreateEntityTask: function (tasktype) {
			var values = this.getValues(tasktype);
			$('[name="field_value_mapping"]').val(JSON.stringify(values));
		},
		preSaveVTEmailTask: function (tasktype) {
			var textAreaElement = $('#content');
			//To keep the plain text value to the textarea which need to be
			//sent to server
			textAreaElement.val(CKEDITOR.instances['content'].getData());
		},
		preSaveVTUpdateRelatedFieldTask: function (tasktype) {
			var values = this.getValues(tasktype);
			$('[name="field_value_mapping"]').val(JSON.stringify(values));
		},
		preSaveSumFieldFromDependent: function (tasktype) {
			$('[name="conditions"]').val(JSON.stringify(this.conditionBuilderInstance.getConditions()));
		},
		/**
		 * Function to check if the field selected is empty field
		 * @params : select element which represents the field
		 * @return : boolean true/false
		 */
		isEmptyFieldSelected: function (fieldSelect) {
			var selectedOption = fieldSelect.find('option:selected');
			//assumption that empty field will be having value none
			if (selectedOption.val() === 'none' || !selectedOption.val()) {
				return true;
			}
			return false;
		},
		getVTCreateEntityTaskFieldList: function () {
			return new Array('fieldname', 'value', 'valuetype', 'modulename');
		},
		getVTUpdateFieldsTaskFieldList: function () {
			return new Array('fieldname', 'value', 'valuetype');
		},
		getVTUpdateRelatedFieldTaskFieldList: function () {
			return new Array('fieldname', 'value', 'valuetype');
		},
		/**
		 * Get values
		 * @param {string} tasktype
		 * @returns {Array}
		 */
		getValues(tasktype) {
			let fieldListFunctionName = 'get' + tasktype + 'FieldList',
				fieldList = [];
			if (typeof this[fieldListFunctionName] !== 'undefined') {
				fieldList = this[fieldListFunctionName].apply();
			}

			let values = [];
			$('.js-conditions-row', $('#save_fieldvaluemapping')).each((i, conditionDomElement) => {
				let rowElement = $(conditionDomElement),
					fieldSelectElement = $('[name="fieldname"]', rowElement),
					valueSelectElement = $('[data-value="value"]', rowElement);
				//To not send empty fields to server
				if (this.isEmptyFieldSelected(fieldSelectElement)) {
					return true;
				}
				let fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo'),
					fieldType = fieldDataInfo.type,
					rowValues = {},
					key,
					field;
				if (fieldType === 'owner') {
					for (key in fieldList) {
						field = fieldList[key];
						if (field == 'value' && valueSelectElement.is('select')) {
							rowValues[field] = valueSelectElement.find('option:selected').val();
						} else {
							rowValues[field] = $('[name="' + field + '"]', rowElement).val();
						}
					}
				} else if (fieldType === 'picklist' || fieldType == 'multipicklist') {
					for (key in fieldList) {
						field = fieldList[key];
						if (field === 'value' && valueSelectElement.is('input')) {
							let pickListValues = valueSelectElement.data('picklistvalues'),
								valuesArr = valueSelectElement.val().split(','),
								newValuesArr = [];
							for (let j = 0; j < valuesArr.length; j++) {
								if (typeof pickListValues[valuesArr[j]] !== 'undefined') {
									newValuesArr.push(pickListValues[valuesArr[j]]);
								} else {
									newValuesArr.push(valuesArr[j]);
								}
							}
							rowValues[field] = newValuesArr.join(',');
						} else if (field === 'value' && valueSelectElement.is('select') && fieldType == 'picklist') {
							rowValues[field] = valueSelectElement.val();
						} else if (field === 'value' && valueSelectElement.is('select') && fieldType == 'multipicklist') {
							var value = valueSelectElement.val();
							if (value === null) {
								rowValues[field] = value;
							} else {
								rowValues[field] = value.join(',');
							}
						} else {
							rowValues[field] = $('[name="' + field + '"]', rowElement).val();
						}
					}
				} else {
					for (key in fieldList) {
						field = fieldList[key];
						if (field == 'value') {
							rowValues[field] = valueSelectElement.val();
						} else {
							rowValues[field] = $('[name="' + field + '"]', rowElement).val();
						}
					}
				}
				if ($('[name="valuetype"]', rowElement).val() == 'false' || $('[name="valuetype"]', rowElement).length == 0) {
					rowValues['valuetype'] = 'rawtext';
				}

				values.push(rowValues);
			});
			return values;
		},
		getTaskList: function () {
			var container = this.getContainer();
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'TasksList',
				record: $('[name="record"]', container).val()
			};
			var progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(params).done((data) => {
				$('#taskListContainer').html(data);
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				this.registerSortWorkflowActionsTasks();
			});
		},
		registerTaskStatusChangeEvent: function () {
			var container = this.getContainer();
			container.on('change', '.taskStatus', function (e) {
				var currentStatusElement = $(e.currentTarget);
				var url = currentStatusElement.data('statusurl');
				if (currentStatusElement.is(':checked')) {
					url = url + '&status=true';
				} else {
					url = url + '&status=false';
				}
				var progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(url).done(function (data) {
					if (data.result == 'ok') {
						var params = {
							title: app.vtranslate('JS_MESSAGE'),
							text: app.vtranslate('JS_STATUS_CHANGED_SUCCESSFULLY'),
							type: 'success'
						};
						app.showNotify(params);
					}
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
				e.stopImmediatePropagation();
			});
		},
		registerTaskDeleteEvent: function () {
			var thisInstance = this;
			var container = this.getContainer();
			container.on('click', '.deleteTask', function (e) {
				app.showConfirmModal({
					title: app.vtranslate('LBL_DELETE_CONFIRMATION'),
					confirmedCallback: () => {
						var currentElement = $(e.currentTarget);
						var deleteUrl = currentElement.data('deleteurl');
						AppConnector.request(deleteUrl).done(function (data) {
							if (data.result == 'ok') {
								thisInstance.getTaskList();
								app.showNotify({
									title: app.vtranslate('JS_MESSAGE'),
									text: app.vtranslate('JS_TASK_DELETED_SUCCESSFULLY'),
									type: 'success'
								});
							}
						});
					}
				});
			});
		},
		registerFillTaskFromEmailFieldEvent: function () {
			$('#saveTask').on('change', '#fromEmailOption', function (e) {
				var currentElement = $(e.currentTarget);
				var inputElement = currentElement.closest('.row').find('.fields');
				inputElement.val(currentElement.val());
			});
		},
		registerFillTaskFieldsEvent: function () {
			$('#saveTask').on('change', '.task-fields', function (e) {
				var currentElement = $(e.currentTarget);
				var inputElement = currentElement.closest('.row').find('.fields');
				var oldValue = inputElement.val();
				var newValue = oldValue + currentElement.val();
				inputElement.val(newValue);
			});
		},
		registerVTEmailTaskEvents: function () {
			var textAreaElement = $('#content');
			new App.Fields.Text.Editor(textAreaElement);
			this.registerFillTaskFromEmailFieldEvent();
			this.registerCcAndBccEvents();
		},
		registerVTCreateTodoTaskEvents: function () {
			app.registerEventForClockPicker();
		},
		registerVTUpdateFieldsTaskEvents: function () {
			var thisInstance = this;
			this.registerAddFieldEvent();
			this.registerDeleteConditionEvent();
			this.registerFieldChange();
			this.fieldValueMap = false;
			if ($('#fieldValueMapping').val() != '') {
				this.fieldValueReMapping();
			}
			var fields = $('#save_fieldvaluemapping').find('select[name="fieldname"]');
			$.each(fields, function (i, field) {
				thisInstance.loadFieldSpecificUi($(field));
			});
			this.getPopUp($('#saveTask'));
		},
		registerVTUpdateRelatedFieldTaskEvents: function (container) {
			var thisInstance = this;
			this.registerAddFieldEvent();
			this.registerDeleteConditionEvent();
			this.registerConditionsModal($(container));
			this.registerFieldChange();
			this.fieldValueMap = false;
			if ($('#fieldValueMapping').val() != '') {
				this.fieldValueReMapping();
			}
			var fields = $('#save_fieldvaluemapping').find('select[name="fieldname"]');
			$.each(fields, function (i, field) {
				thisInstance.loadFieldSpecificUi($(field));
			});
			this.getPopUp($('#saveTask'));
		},
		/**
		 * Add field
		 * @param {jQuery|null} replaceElement - if we want to replace existing field container with new one
		 */
		addField(replaceElement = null) {
			const newAddFieldContainer = $('.js-add-basic-field-container')
				.clone(true, true)
				.removeClass('js-add-basic-field-container d-none')
				.addClass('js-conditions-row');
			$('select', newAddFieldContainer).addClass('select2');
			if (replaceElement === null) {
				$('#save_fieldvaluemapping').append(newAddFieldContainer);
			} else {
				replaceElement.replaceWith(newAddFieldContainer);
			}
			//change in to select elements
			App.Fields.Picklist.changeSelectElementView(newAddFieldContainer);
		},
		/**
		 * Register add field event
		 */
		registerAddFieldEvent() {
			$('#addFieldBtn').on('click', (e) => {
				this.addField();
			});
		},
		registerDeleteConditionEvent() {
			$('#saveTask').on('click', '.js-condition-delete', (e) => {
				$(e.currentTarget).closest('.js-conditions-row').remove();
			});
		},
		/**
		 * Register condition wizard
		 * @param {jQuery} container
		 */
		registerConditionsModal(container) {
			container.on('click', '.js-condition-modal', (e) => {
				let element = $(e.currentTarget);
				let fieldValue = element.closest('.js-conditions-row').find('[name="fieldname"]').val();
				let sourceField = container.find('.js-condition-value');
				if (!fieldValue || sourceField.length <= 0) {
					return;
				}
				let value = sourceField.val() ? { ...JSON.parse(sourceField.val()) } : {};
				let moduleName;
				let fieldValueParts = fieldValue.split('::');
				if (fieldValueParts.length === 2) {
					moduleName = fieldValueParts[0];
				} else {
					moduleName = fieldValueParts[1];
				}
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'ConditionBuilder',
					mode: 'builder',
					sourceModuleName: moduleName,
					relatedModuleSkip: true,
					advanceCriteria: value[fieldValue] || []
				}).done((data) => {
					app.showModalHtml({
						class: 'modal-lg',
						header: element.attr('title'),
						headerIcon: 'fas fa-filter',
						body: data,
						footerButtons: [
							{
								text: app.vtranslate('JS_APPLY'),
								icon: 'fas fa-check',
								class: 'btn-success js-condition-apply'
							},
							{
								text: app.vtranslate('JS_CANCEL'),
								icon: 'fas fa-times',
								class: 'btn-danger',
								data: { dismiss: 'modal' }
							}
						],
						cb: (modal) => {
							let conditionBuilder = new Vtiger_ConditionBuilder_Js(modal.find('.js-condition-builder'), {
								sourceModuleName: moduleName,
								relatedModuleSkip: true
							});
							conditionBuilder.registerEvents();
							modal.on('click', '.js-condition-apply', () => {
								let conditions = conditionBuilder.getConditions(true);
								if (conditions && Object.keys(conditions).length) {
									value[fieldValue] = conditions;
								} else if (typeof value[fieldValue] !== 'undefined') {
									delete value[fieldValue];
								}
								sourceField.val(JSON.stringify(value));
								app.hideModalWindow(false, modal.closest('.js-modal-container').attr('id'));
							});
						}
					});
				});
			});
		},
		/**
		 * Function which will register field change event
		 */
		registerFieldChange() {
			$('#saveTask').on('change', 'select[name="fieldname"]', (e) => {
				const selectedElement = $(e.currentTarget);
				const conditionRow = selectedElement.closest('.js-conditions-row');
				if (selectedElement.val() !== 'none' && selectedElement.val()) {
					var moduleNameElement = conditionRow.find('[name="modulename"]');
					if (moduleNameElement.length > 0) {
						let workflowModuleName = selectedElement.closest('form').find('#workflowModuleName').val();
						let selectedOption = selectedElement.find('option:selected');
						var selectedOptionFieldInfo = selectedOption.data('fieldinfo');
						var type = selectedOptionFieldInfo.type;
						if (type == 'picklist' || type == 'multipicklist') {
							var selectElement = $('select.createEntityModule:not(:disabled)');
							var moduleName = selectElement.val();
							moduleNameElement.val(moduleName).change().prop('disabled', true);
						} else if (
							selectedOption.data('reference') &&
							moduleNameElement.find(`option[value="${workflowModuleName}"]`).length
						) {
							moduleNameElement.val(workflowModuleName).change().prop('disabled', true);
						} else {
							moduleNameElement.prop('disabled', false);
						}
					}
					this.loadFieldSpecificUi(selectedElement);
				} else {
					this.addField(conditionRow);
				}
			});
		},
		getModuleName: function () {
			return app.getModuleName();
		},
		getFieldValueMapping: function () {
			var fieldValueMap = this.fieldValueMap;
			if (fieldValueMap != false) {
				return fieldValueMap;
			} else {
				return '';
			}
		},
		fieldValueReMapping: function () {
			var object = JSON.parse($('#fieldValueMapping').val());
			var fieldValueReMap = {};

			$.each(object, function (i, array) {
				fieldValueReMap[array.fieldname] = {};
				var values = {};
				$.each(array, function (key, value) {
					values[key] = value;
				});
				fieldValueReMap[array.fieldname] = values;
			});
			this.fieldValueMap = fieldValueReMap;
		},
		/**
		 * Load field specific UI
		 * @param {jQuery} fieldSelect
		 * @returns this
		 */
		loadFieldSpecificUi(fieldSelect) {
			const selectedOption = fieldSelect.find('option:selected');
			const row = fieldSelect.closest('div.js-conditions-row');
			const fieldUiHolder = row.find('.fieldUiHolder');
			const fieldInfo = selectedOption.data('fieldinfo');
			const fieldValueMapping = this.getFieldValueMapping();
			let selectField = '';
			if (fieldValueMapping && typeof fieldValueMapping[fieldInfo.name] !== 'undefined') {
				selectField = fieldValueMapping[fieldInfo.name];
			} else if (fieldValueMapping && typeof fieldValueMapping[fieldSelect.val()] !== 'undefined') {
				selectField = fieldValueMapping[fieldSelect.val()];
			}
			if (selectField) {
				fieldInfo.value = selectField['value'];
				fieldInfo.workflow_valuetype = selectField['valuetype'];
			} else {
				fieldInfo.workflow_valuetype = 'rawtext';
			}
			const moduleName = this.getModuleName();
			const fieldModel = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
			this.fieldModelInstance = fieldModel;
			const fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);
			//remove validation since we dont need validations for all eleements
			// Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
			let fieldName = fieldModel.getName();
			if (fieldModel.getType() == 'multipicklist') {
				fieldName = fieldName + '[]';
			}
			fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-value', 'value');
			fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-value', 'value');
			fieldSpecificUi.filter('[name="valuetype"]').removeAttr('data-validation-engine');
			fieldSpecificUi.find('[name="valuetype"]').removeAttr('data-validation-engine');
			//If the workflowValueType is rawtext then only validation should happen
			const workflowValueType = fieldSpecificUi.filter('[name="valuetype"]').val();
			if (workflowValueType != 'rawtext' && typeof workflowValueType !== 'undefined') {
				fieldSpecificUi.filter('[name="' + fieldName + '"]').removeAttr('data-validation-engine');
				fieldSpecificUi.find('[name="' + fieldName + '"]').removeAttr('data-validation-engine');
			}
			fieldUiHolder.html(fieldSpecificUi);
			if (fieldSpecificUi.is('input.select2') || fieldSpecificUi.is('select')) {
				App.Fields.Picklist.showSelect2ElementView(fieldSpecificUi);
			} else if (fieldSpecificUi.is('input.dateField')) {
				App.Fields.Date.register(fieldSpecificUi);
			} else if (fieldSpecificUi.is('input.dateRangeField')) {
				App.Fields.Date.registerRange(fieldSpecificUi, { ranges: false });
			}
			return this;
		},
		/**
		 * Functiont to get the field specific ui for the selected field
		 * @prarms : fieldSelectElement - select element which will represents field list
		 * @return : jquery object which represents the ui for the field
		 */
		getFieldSpecificUi: function (fieldSelectElement) {
			var fieldModel = this.fieldModelInstance;
			return $(fieldModel.getUiTypeSpecificHtml());
		},
		registerVTCreateEventTaskEvents: function () {
			app.registerEventForClockPicker();
		},
		registerVTCreateEntityTaskEvents: function () {
			this.registerChangeCreateEntityEvent();
			this.registerVTUpdateFieldsTaskEvents();
		},
		/**
		 * Register record collector events.
		 */
		registerRecordCollectorEvents: function () {
			const recordCollector = $('[name="recordCollector"]');
			const selectedFields = $('.js-fields-map');
			recordCollector.on('change', function (e) {
				let fieldsMap = '';
				recordCollector.find('.js-fields').each(function (_, e) {
					let row = $(e);
					if (row.data('fields') && row.is(':checked')) {
						fieldsMap = row.data('fields');
					}
				});
				if (fieldsMap !== '') {
					if (selectedFields.is('select')) {
						let newOptions = new $();
						$.each(fieldsMap, (v, l) => {
							newOptions = newOptions.add(new Option(l, v, false));
						});
						selectedFields.html(newOptions);
					}
				}
			});
			if (recordCollector.val() && selectedFields.val().length < 1) {
				recordCollector.trigger('change');
			}
		},
		registerChangeCreateEntityEvent: function () {
			var thisInstance = this;
			$('[name="mappingPanel"]').on('change', function (e) {
				var currentTarget = $(e.currentTarget);
				app.setMainParams('mappingPanel', currentTarget.val());
				$('#addCreateEntityContainer').html('');
				var hideElementByClass = $('.' + currentTarget.data('hide'));
				var showElementByClass = $('.' + currentTarget.data('show'));
				var taskFields = app.getMainParams('taskFields', true);
				hideElementByClass
					.addClass('d-none')
					.find('input,select')
					.each(function (e, n) {
						var element = $(this);
						var name = element.attr('name');
						if ($.inArray(name, taskFields) >= 0) {
							if (element.is('select')) {
								element.val('').trigger('change');
							}
							element.prop('disabled', true);
						}
					});
				showElementByClass
					.removeClass('d-none')
					.find('input,select')
					.each(function (e, n) {
						var element = $(this);
						var name = element.attr('name');
						if ($.inArray(name, taskFields) >= 0) {
							element.prop('disabled', false);
							if (element.is('select')) {
								element.val('').trigger('change');
							}
						}
					});
			});
			$('.createEntityModule').on('change', function (e) {
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'CreateEntity',
					for_workflow: $('[name="for_workflow"]').val(),
					mappingPanel: app.getMainParams('mappingPanel')
				};
				var relatedModule = $(e.currentTarget).val();
				if (relatedModule) {
					params['relatedModule'] = relatedModule;
				}
				var progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(params).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					var createEntityContainer = $('#addCreateEntityContainer');
					createEntityContainer.html(data);
					App.Fields.Picklist.changeSelectElementView(createEntityContainer);
					App.Fields.Picklist.showSelect2ElementView(createEntityContainer.find('.select2'));
					thisInstance.registerAddFieldEvent();
					thisInstance.fieldValueMap = false;
					if ($('#fieldValueMapping').val() != '') {
						this.fieldValueReMapping();
					}
					var fields = $('#save_fieldvaluemapping').find('select[name="fieldname"]');
					$.each(fields, function (i, field) {
						thisInstance.loadFieldSpecificUi($(field));
					});
				});
			});
		},
		/**
		 * Function which will change the UI styles based on recurring type
		 * @params - recurringType - which recurringtype is selected
		 */
		changeRecurringTypesUIStyles: function (recurringType) {
			if (recurringType == 'Daily' || recurringType == 'Yearly') {
				$('#repeatWeekUI').removeClass('show').addClass('d-none');
				$('#repeatMonthUI').removeClass('show').addClass('d-none');
			} else if (recurringType == 'Weekly') {
				$('#repeatWeekUI').removeClass('d-none').addClass('show');
				$('#repeatMonthUI').removeClass('show').addClass('d-none');
			} else if (recurringType == 'Monthly') {
				$('#repeatWeekUI').removeClass('show').addClass('d-none');
				$('#repeatMonthUI').removeClass('d-none').addClass('show');
			}
		},
		checkHiddenStatusofCcandBcc: function () {
			var ccLink = $('#ccLink');
			var bccLink = $('#bccLink');
			if (ccLink.hasClass('d-none') && bccLink.hasClass('d-none')) {
				ccLink.closest('div.row').addClass('d-none');
			}
		},
		/*
		 * Function to register the events for bcc and cc links
		 */
		registerCcAndBccEvents: function () {
			var thisInstance = this;
			$('#ccLink').on('click', function (e) {
				var ccContainer = $('#ccContainer');
				ccContainer.removeClass('d-none');
				var taskFieldElement = ccContainer.find('select.task-fields');
				taskFieldElement.addClass('select2');
				App.Fields.Picklist.changeSelectElementView(taskFieldElement);
				$(e.currentTarget).addClass('d-none');
				thisInstance.checkHiddenStatusofCcandBcc();
			});
			$('#bccLink').on('click', function (e) {
				var bccContainer = $('#bccContainer');
				bccContainer.removeClass('d-none');
				var taskFieldElement = bccContainer.find('select.task-fields');
				taskFieldElement.addClass('select2');
				App.Fields.Picklist.changeSelectElementView(taskFieldElement);
				$(e.currentTarget).addClass('d-none');
				thisInstance.checkHiddenStatusofCcandBcc();
			});
		},
		/**
		 * Register sortable
		 */
		registerSortWorkflowActionsTasks: function () {
			let tasks = this.container.find('.js-workflow-tasks-list');
			tasks.sortable({
				containment: tasks,
				items: tasks.find('.js-workflow-task'),
				handle: '.js-drag',
				revert: true,
				tolerance: 'pointer',
				cursor: 'move',
				classes: {
					'ui-sortable-helper': 'bg-light'
				},
				helper: function (_e, ui) {
					ui.children().each(function (_index, element) {
						element = $(element);
						element.width(element.width());
					});
					return ui;
				},
				update: () => {
					this.saveSequence();
				}
			});
		},
		/**
		 * Save sequence
		 */
		saveSequence: function () {
			let tasks = [];
			this.container.find('.js-workflow-task').each(function (index) {
				tasks[index] = $(this).data('id');
			});
			AppConnector.request({
				module: this.container.find('[name="module"]').length
					? this.container.find('[name="module"]').val()
					: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'sequenceTasks',
				tasks: tasks
			})
				.done(function (data) {
					if (data.result.message) {
						app.showNotify({ text: data.result.message });
					}
				})
				.fail(function () {
					app.showNotify({
						text: app.vtranslate('JS_UNEXPECTED_ERROR'),
						type: 'error'
					});
				});
		},
		registerEvents: function () {
			this.container = this.getContainer();
			App.Fields.Picklist.changeSelectElementView(this.container);
			this.registerEditTaskEvent();
			this.registerTaskStatusChangeEvent();
			this.registerTaskDeleteEvent();
			this.registerSortWorkflowActionsTasks();
		}
	}
);

//http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery
$.fn.extend({
	insertAtCaret: function (myValue) {
		return this.each(function (i) {
			if (document.selection) {
				//For browsers like Internet Explorer
				this.focus();
				var sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			} else if (this.selectionStart || this.selectionStart == '0') {
				//For browsers like Firefox and Webkit based
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
				this.focus();
				this.selectionStart = startPos + myValue.length;
				this.selectionEnd = startPos + myValue.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += myValue;
				this.focus();
			}
		});
	}
});
