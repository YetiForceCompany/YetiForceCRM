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
		if (this.modalContainer == false) {
			this.modalContainer = $('.js-filter-modal__container');
		}
		return this.modalContainer;
	}

	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement() {
		if (this.columnSelectElement == false) {
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

	getShortFieldNames() {
		return this.container
			.find('.js-short-name-fields option')
			.toArray()
			.map((item) => ({ text: item.text, value: item.value }));
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
		container.on('click', '.js-duplicates-remove', function (e) {
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
				for (let i = 0; i < selectedOptions.length; i++) {
					if ($.inArray(selectedOptions[i], mandatoryFieldsList) >= 0) {
						mandatoryFieldsMissing = false;
						break;
					}
				}
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
			if (result == true) {
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
				contentContainer.find('.js-short-field-names').val(JSON.stringify(this.getShortFieldNames()));

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
				module: app.getModuleName(),
				advancedConditions: advancedConditions
			})
				.done((data) => {
					if (data) {
						app.showModalWindow(data, (modalContainer) => {
							App.Tools.Form.registerBlockToggle(modalContainer);
							this.registerAdvancedConditionsEvents(modalContainer);
							modalContainer.find('[name="saveButton"]').on('click', (e) => {
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
		relationSelect.on('change', function (e) {
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
	registerChangeViewColumns() {
		this.container.find('.js-view-columns-select').on('change', () => {
			this.registerSetColumnsNameShorter();
		});
	}
	registerSetColumnsNameShorter() {
		let shorterNamesContainer = this.container.find('.js-short-name-fields');
		let shorterNamesColumns = this.getShortFieldNames();
		let selectedColumns = this.container
			.find('.js-view-columns-select option:selected')
			.toArray()
			.map((item) => ({ text: item.getAttribute('data-field-label'), value: item.value }));
		shorterNamesContainer.empty();
		let shorterName = '';
		$.each(selectedColumns, function (_index, element) {
			let found = shorterNamesColumns.find((shorterNameElement) => shorterNameElement.value == element.value);
			if (undefined === found) {
				shorterName = element.text;
			} else {
				console.log(found);
				shorterName = found.text;
			}
			shorterNamesContainer.append(
				$('<option>').val(element.value).text(shorterName).data({
					shorterName: shorterName
				})
			);
		});
		App.Fields.Picklist.showSelect2ElementView(shorterNamesContainer);
	}
	registerUpdateShorterName() {
		this.container.find('.js-update-shorter-name').on('click', (e) => {
			let shorterValueContainer = this.container.find('.js-field-shorter-name');
			let shorterValue = shorterValueContainer.val();
			let selectedShorterNameOption = this.container.find('.js-short-name-fields option:selected');
			if (shorterValue && selectedShorterNameOption) {
				selectedShorterNameOption.text(shorterValue);
				shorterValueContainer.val('');

				let shorterNamesContainer = this.container.find('.js-short-name-fields');
				App.Fields.Picklist.showSelect2ElementView(shorterNamesContainer);
			}
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
		this.registerChangeViewColumns();
		this.registerSetColumnsNameShorter();
		this.registerUpdateShorterName();
	}
}
