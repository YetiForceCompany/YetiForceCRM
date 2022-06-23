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

class CustomView {
	constructor(url) {
		let progressIndicatorElement = $.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		app.showModalWindow(null, url, () => {
			this.modalContainer = $('.js-filter-modal__container');
			this.advanceFilterInstance = new Vtiger_ConditionBuilder_Js(
				this.modalContainer.find('.js-condition-builder-view .js-condition-builder'),
				this.modalContainer.find('#sourceModule').val()
			);
			this.advanceFilterInstance.registerEvents();
			CustomView.registerAdvancedConditionsEvents(this.modalContainer);

			//This will store the columns selection container
			this.columnSelectElement = false;
			this.registerEvents();
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		});
	}

	/** @type {Vtiger_ConditionBuilder_Js} Condition builder object */
	static advancedConditionsBuilder;

	loadDateFilterValues() {
		let selectedDateFilter = $('#standardDateFilter option:selected');
		let currentDate = selectedDateFilter.data('currentdate');
		let endDate = selectedDateFilter.data('enddate');
		$('#standardFilterCurrentDate').val(currentDate);
		$('#standardFilterEndDate').val(endDate);
	}

	/**
	 * Function to get the contents container
	 * @return : jQuery object of contents container
	 */
	getContentsContainer() {
		if (!this.modalContainer) {
			this.modalContainer = $('.js-filter-modal__container');
		}
		return this.modalContainer;
	}

	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement() {
		if (!this.columnSelectElement) {
			this.columnSelectElement = $('#viewColumnsSelect');
		}
		return this.columnSelectElement;
	}

	/**
	 * Function which will get the selected columns
	 * @return : array of selected values
	 */
	getSelectedColumns() {
		let columnListSelectElement = this.getColumnSelectElement();
		return columnListSelectElement.val();
	}
	/**
	 * Get custom labels
	 * @returns array
	 */
	getCustomLabels() {
		let customFieldNames = {};
		this.getContentsContainer()
			.find('.js-short-label')
			.each(function () {
				customFieldNames[$(this).attr('data-field-value')] = $(this).val();
			});
		return customFieldNames;
	}

	saveFilter() {
		let aDeferred = $.Deferred();
		let formData = $('#CustomView').serializeFormData();
		AppConnector.request(formData, true)
			.done(function (data) {
				aDeferred.resolve(data);
			})
			.fail(function (error) {
				aDeferred.reject(error);
			});
		return aDeferred.promise();
	}

	saveAndViewFilter() {
		this.saveFilter().done(function (data) {
			let response = data.result;
			if (response && response.success) {
				let url;
				if (app.getParentModuleName() == 'Settings') {
					url = 'index.php?module=CustomView&parent=Settings&view=Index&sourceModule=' + $('#sourceModule').val();
				} else {
					url = response.listviewurl;
				}
				window.location.href = url;
			} else {
				$.unblockUI();
				app.showNotify({
					title: app.vtranslate('JS_DUPLICATE_RECORD'),
					text: response.message,
					type: 'error'
				});
			}
		});
	}

	registerIconEvents() {
		this.getContentsContainer()
			.find('.js-filter-preferences')
			.on('change', '.js-filter-preference', (e) => {
				let currentTarget = $(e.currentTarget);
				let iconElement = currentTarget.next();
				if (currentTarget.prop('checked')) {
					iconElement.removeClass(iconElement.data('unchecked')).addClass(iconElement.data('check'));
				} else {
					iconElement.removeClass(iconElement.data('check')).addClass(iconElement.data('unchecked'));
				}
			});
	}

	registerColorEvent() {
		const container = this.getContentsContainer();
		let picker = container.find('.js-color-picker');
		let pickerField = picker.find('.js-color-picker__field');
		let showPicker = () => {
			App.Fields.Colors.showPicker({
				color: pickerField.val(),
				bgToUpdate: picker.find('.js-color-picker__color'),
				fieldToUpdate: pickerField
			});
		};
		picker.on('click', showPicker);
	}

	/**
	 * Get list of fields to duplicates
	 * @returns {Array}
	 */
	getDuplicateFields() {
		let fields = [];
		const container = this.getContentsContainer();
		container.find('.js-duplicates-container .js-duplicates-row').each(function () {
			fields.push({
				fieldid: $(this).find('.js-duplicates-field').val(),
				ignore: $(this).find('.js-duplicates-ignore').is(':checked')
			});
		});
		return fields;
	}
	/**
	 * Register events for block "Find duplicates"
	 */
	registerDuplicatesEvents() {
		const container = this.getContentsContainer();
		App.Fields.Picklist.showSelect2ElementView(container.find('.js-duplicates-container .js-duplicates-field'));
		container.on('click', '.js-duplicates-remove', function () {
			$(this).closest('.js-duplicates-row').remove();
		});
		container.find('.js-duplicate-add-field').on('click', function () {
			let template = container.find('.js-duplicates-field-template').clone();
			template.removeClass('d-none');
			template.removeClass('js-duplicates-field-template');
			App.Fields.Picklist.showSelect2ElementView(template.find('.js-duplicates-field'));
			container.find('.js-duplicates-container').append(template);
		});
	}
	registerSubmitEvent(select2Element) {
		$('#CustomView').on('submit', (e) => {
			const form = $(e.currentTarget);
			let selectElement = this.getColumnSelectElement();
			if ($('#viewname').val().length > 100) {
				app.showNotify({
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_VIEWNAME_ALERT'),
					type: 'error'
				});
				e.preventDefault();
				return;
			}
			//Mandatory Fields selection validation
			//Any one Mandatory Field should select while creating custom view.
			let mandatoryFieldsList = JSON.parse($('#mandatoryFieldsList').val());
			let selectedOptions = selectElement.val();
			let mandatoryFieldsMissing = true;
			if (selectedOptions) {
				mandatoryFieldsMissing = selectedOptions.filter((value) => mandatoryFieldsList.includes(value)).length <= 0;
			}
			if (mandatoryFieldsMissing) {
				selectElement.validationEngine(
					'showPrompt',
					app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_MANDATORY_FIELD'),
					'error',
					'topLeft',
					true
				);
				e.preventDefault();
				return;
			} else {
				select2Element.validationEngine('hide');
			}
			//Mandatory Fields validation ends
			let result = form.validationEngine('validate');
			if (result) {
				//handled standard filters saved values.
				let stdfilterlist = {};

				if (
					$('#standardFilterCurrentDate').val() != '' &&
					$('#standardFilterEndDate').val() != '' &&
					$('select.standardFilterColumn option:selected').val() != 'none'
				) {
					stdfilterlist['columnname'] = $('select.standardFilterColumn option:selected').val();
					stdfilterlist['stdfilter'] = $('select#standardDateFilter option:selected').val();
					stdfilterlist['startdate'] = $('#standardFilterCurrentDate').val();
					stdfilterlist['enddate'] = $('#standardFilterEndDate').val();
					$('#stdfilterlist').val(JSON.stringify(stdfilterlist));
				}
				//handled advanced filters saved values.
				let contentContainer = this.getContentsContainer();
				$('#advfilterlist').val(JSON.stringify(this.advanceFilterInstance.getConditions()));
				form.find('#advancedConditions').val(JSON.stringify(CustomView.getAdvancedConditions(form)));
				$('[name="duplicatefields"]').val(JSON.stringify(this.getDuplicateFields()));
				$('input[name="columnslist"]', contentContainer).val(JSON.stringify(this.getSelectedColumns()));
				contentContainer.find('.js-custom-field-names').val(JSON.stringify(this.getCustomLabels()));

				this.saveAndViewFilter();
				return false;
			} else {
				app.formAlignmentAfterValidation($(e.currentTarget));
			}
		});
	}

	/**
	 * Block submit on press enter key
	 */
	registerDisableSubmitOnEnter() {
		this.getContentsContainer()
			.find('#viewname, [name="color"]')
			.on('keydown', (e) => {
				if (e.key === 'Enter') {
					e.preventDefault();
				}
			});
	}

	/**
	 * Function to register the advanced conditions events for customview
	 * @param {jQuery} listViewContainer
	 */
	static registerCustomViewAdvCondEvents(listViewContainer) {
		listViewContainer.on('click', '.js-custom-view-adv-cond-modal', () => {
			const customViewAdvCond = listViewContainer.find('.js-custom-view-adv-cond');
			let advancedConditions = customViewAdvCond.val();
			if (advancedConditions) {
				advancedConditions = JSON.parse(advancedConditions);
			}
			AppConnector.request({
				module: app.getModuleName(),
				view: 'CustomViewAdvCondModal',
				advancedConditions: advancedConditions
			})
				.done((data) => {
					if (data) {
						app.showModalWindow(data, (modalContainer) => {
							App.Tools.Form.registerBlockToggle(modalContainer);
							this.registerAdvancedConditionsEvents(modalContainer);
							modalContainer.find('[name="saveButton"]').on('click', () => {
								customViewAdvCond.val(JSON.stringify(this.getAdvancedConditions(modalContainer)));
								app.hideModalWindow();
								if (typeof app.pageController.getListViewRecords !== 'undefined') {
									app.pageController.getListViewRecords();
								}
							});
						});
					}
				})
				.fail((_textStatus, errorThrown) => {
					app.showNotify({
						textTrusted: false,
						title: app.vtranslate('JS_ERROR'),
						text: errorThrown,
						type: 'error'
					});
				});
		});
	}
	/**
	 * Function to register the advanced conditions events for custom view
	 * @param {jQuery} container
	 */
	static registerAdvancedConditionsEvents(container) {
		const self = this;
		const builder = container.find('.js-adv-condition-builder-view');
		const relationSelect = container.find('.js-relation-select');
		if (relationSelect.val() != 0) {
			this.advancedConditionsBuilder = new Vtiger_ConditionBuilder_Js(
				builder.find('.js-condition-builder'),
				relationSelect.find('option:selected').data('module')
			);
			this.advancedConditionsBuilder.registerEvents();
		}
		relationSelect.on('change', function () {
			const moduleName = $(this).find('option:selected').data('module');
			builder.html('');
			delete self.advancedConditionsBuilder;
			if (moduleName) {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'ConditionBuilder',
					mode: 'builder',
					sourceModuleName: moduleName
				}).done((data) => {
					builder.html(data);
					self.advancedConditionsBuilder = new Vtiger_ConditionBuilder_Js(
						builder.find('.js-condition-builder'),
						moduleName
					);
					self.advancedConditionsBuilder.registerEvents();
				});
			}
		});
	}
	/**
	 * Function to register the advanced conditions events for custom view
	 * @param {jQuery} container
	 * @return {object}
	 */
	static getAdvancedConditions(container) {
		const advancedConditions = {
			relationId: container.find('.js-relation-select').val()
		};
		container.find('.js-relation-checkbox:checked').each(function () {
			if (typeof advancedConditions.relationColumns === 'undefined') {
				advancedConditions.relationColumns = [];
			}
			advancedConditions.relationColumns.push($(this).val());
		});
		if (this.advancedConditionsBuilder) {
			advancedConditions.relationConditions = this.advancedConditionsBuilder.getConditions();
		}
		return advancedConditions;
	}
	/**
	 * Register change selected columns
	 */
	registerChangeSelectedColumns() {
		this.container.find('.js-view-columns-select').on('change', () => {
			this.registerAppendCustomLabels();
		});
	}
	/**
	 *	Register append custom labels
	 */
	registerAppendCustomLabels() {
		let shorterNamesContainer = this.container.find('.js-custom-name-fields');
		let selectedColumns = this.container
			.find('.js-view-columns-select option:selected')
			.toArray()
			.map((item) => ({
				text: item.getAttribute('data-field-label'),
				value: item.value,
				customLabel: item.getAttribute('data-custom-label') || ''
			}));
		shorterNamesContainer.empty();
		let newCustomLabelElement = '';
		let customLabelElement = '';
		let customLabelValue = '';
		let inputContainerElement = '';
		let inputElement = '';
		$.each(selectedColumns, function (_index, element) {
			newCustomLabelElement = document.createElement('div');
			newCustomLabelElement.setAttribute('class', 'd-flex mb-1');

			customLabelElement = document.createElement('div');
			customLabelElement.setAttribute('class', 'col-form-label col-md-2 pl-0');
			customLabelValue = document.createTextNode(element.text);
			customLabelElement.appendChild(customLabelValue);
			newCustomLabelElement.appendChild(customLabelElement);

			inputContainerElement = document.createElement('div');
			inputContainerElement.setAttribute('class', 'col-md-4');

			inputElement = document.createElement('input');
			inputElement.setAttribute('type', 'text');
			inputElement.setAttribute('class', 'form-control js-short-label');
			inputElement.setAttribute('data-field-value', element.value);
			inputElement.setAttribute(
				'data-validation-engine',
				'validate[maxSize[50], funcCall[Vtiger_Base_Validator_Js.invokeValidation]]'
			);
			inputElement.setAttribute('data-validator', '[{"name":"FieldLabel"}]');
			inputElement.setAttribute('value', element.customLabel);

			inputContainerElement.appendChild(inputElement);
			newCustomLabelElement.appendChild(inputContainerElement);
			shorterNamesContainer.append(newCustomLabelElement);
		});
	}
	/**
	 * Register events
	 */
	registerEvents() {
		this.container = this.getContentsContainer();
		this.registerIconEvents();
		App.Fields.Tree.register(this.getContentsContainer());
		App.Tools.Form.registerBlockToggle(this.getContentsContainer());
		this.registerColorEvent();
		this.registerDuplicatesEvents();
		const select2Element = App.Fields.Picklist.showSelect2ElementView(this.getColumnSelectElement());
		this.registerSubmitEvent(select2Element);
		$('.stndrdFilterDateSelect').datepicker();
		$('#standardDateFilter').on('change', () => {
			this.loadDateFilterValues();
		});
		$('#CustomView').validationEngine(app.validationEngineOptions);
		this.registerDisableSubmitOnEnter();
		this.registerChangeSelectedColumns();
		this.registerAppendCustomLabels();
	}
}

Vtiger_Base_Validator_Js(
	'Vtiger_FieldLabel_Validator_Js',
	{
		/** @inheritdoc */
		invokeValidation: function (field, _rules, _i, _options) {
			let instance = new Vtiger_FieldLabel_Validator_Js();
			instance.setElement(field);
			let response = instance.validate();
			if (response !== true) {
				return instance.getError();
			}
		}
	},
	{
		/** @inheritdoc */
		validate: function () {
			return this.validateValue(this.getFieldValue());
		},
		/** @inheritdoc */
		validateValue: function (fieldValue) {
			let specialChars = /[&\<\>\:\'\"\,]/;
			if (specialChars.test(fieldValue)) {
				let errorInfo = app.vtranslate('JS_SPECIAL_CHARACTERS') + ' & < > \' " : , ' + app.vtranslate('JS_NOT_ALLOWED');
				this.setError(errorInfo);
				return false;
			}
			return true;
		}
	}
);
