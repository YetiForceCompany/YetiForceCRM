/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Settings_PickListDependency_Js',
	{
		//holds the picklist dependency instance
		pickListDependencyInstance: false,
		/**
		 * Function used to triggerAdd new Dependency for the picklists
		 */
		triggerAdd: function (event) {
			event.stopPropagation();
			var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
			instance.updatedSourceValues = [];
			instance.showEditView().done(function (data) {
				instance.registerEvents();
				//	instance.registerAddViewEvents();
			});
		},
		/**
		 * This function used to trigger Edit picklist dependency
		 */
		triggerEdit: function (event, id) {
			event.stopPropagation();
			var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
			instance.updatedSourceValues = [];
			instance.showEditView(id).done(function (data) {
				var form = jQuery('#pickListDependencyForm');
				form
					.find('select[name="sourceModule"],select[name="sourceField"],select[name="secondField"]')
					.prop('disabled', true);
				instance.registerDependencyGraphEvents();
				instance.registerSubmitEvent();
			});
		},
		/**
		 * This function used to trigger Delete picklist dependency
		 */
		triggerDelete: function (event, dependencyId) {
			event.stopPropagation();
			let currentTrEle = jQuery(event.currentTarget).closest('tr');
			let instance = Settings_PickListDependency_Js.pickListDependencyInstance;

			app.showConfirmModal({
				title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
				confirmedCallback: () => {
					instance.deleteDependency(dependencyId).done(function (data) {
						var params = {};
						params.text = app.vtranslate('JS_DEPENDENCY_DELETED_SUEESSFULLY');
						Settings_Vtiger_Index_Js.showMessage(params);
						currentTrEle.fadeOut('slow').remove();
					});
				}
			});
		}
	},
	{
		//constructor
		init: function () {
			Settings_PickListDependency_Js.pickListDependencyInstance = this;
		},
		//holds the listview forModule
		listViewForModule: '',
		//holds the updated sourceValues while editing dependency
		updatedSourceValues: [],
		//holds the new mapping of source values and target values
		valueMapping: [],
		//holds the list of selected source values for dependency
		selectedSourceValues: [],
		/*
		 * function to show editView for Add/Edit Dependency
		 * @params: module - selected module
		 *			sourceField - source picklist
		 *			secondField - target picklist
		 */
		showEditView: function (pickListDependencyId = '') {
			var aDeferred = jQuery.Deferred();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'Edit';
			params['recordId'] = pickListDependencyId;

			params['sourceModule'] = this.container.find('[name="sourceModule"]').val();
			/*
			params['sourcefield'] = sourceField;
			params['targetfield'] = targetField;
			*/

			AppConnector.requestPjax(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					var container = jQuery('.contentsDiv');
					container.html(data);
					//register all select2 Elements
					App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'), {
						dropdownCss: { 'z-index': 0 }
					});
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},
		/**
		 * Function to get the Dependency graph based on selected module
		 */
		getModuleDependencyGraph: function (form) {
			var thisInstance = this;
			form.find('[name="sourceModule"]').on('change', function () {
				thisInstance.showEditView().done(function (data) {
					thisInstance.registerAddViewEvents();
				});
			});
		},
		/**
		 * Register change event for picklist fields in add/edit picklist dependency
		 */
		registerPicklistFieldsChangeEvent: function (form) {
			var thisInstance = this;
			form.find('[name="sourceField"],[name="secondField"],[name="thirdField"]').on('change', function () {
				thisInstance.checkValuesForDependencyGraph(form);
			});
		},
		/**
		 * Function used to check the selected picklist fields are valid before showing dependency graph
		 */
		checkValuesForDependencyGraph: function (form) {
			//TODO Jak będzie czas to refactor
			var thisInstance = this;
			var sourceField = form.find('[name="sourceField"]');
			var secondField = form.find('[name="secondField"]');
			let thirdField = form.find('[name="thirdField"]').length > 0 ? form.find('[name="thirdField"]') : false;

			var select2SourceField = app.getSelect2ElementFromSelect(sourceField);
			var select2SecondField = app.getSelect2ElementFromSelect(secondField);
			let select2ThirdField = thirdField ? app.getSelect2ElementFromSelect(thirdField) : '';

			var sourceFieldValue = sourceField.val();
			var secondFieldValue = secondField.val();
			let thirdFieldValue = thirdField ? thirdField.val() : '';
			var dependencyGraph = jQuery('#dependencyGraph');
			select2SourceField.validationEngine('hide');

			if (
				sourceFieldValue != '' &&
				secondFieldValue != '' &&
				(thirdField === false || (thirdField && thirdFieldValue != ''))
			) {
				var resultMessage = app.vtranslate('JS_SOURCE_AND_TARGET_FIELDS_SHOULD_NOT_BE_SAME');
				form.find('.errorMessage').addClass('d-none');
				if (
					sourceFieldValue == secondFieldValue &&
					(thirdField === false || (thirdField && sourceFieldValue == thirdFieldValue))
				) {
					select2SecondField.validationEngine('showPrompt', resultMessage, 'error', 'topLeft', true);
					dependencyGraph.html('');
				} else {
					select2SourceField.validationEngine('hide');
					select2SecondField.validationEngine('hide');
					if (select2ThirdField) {
						select2ThirdField.validationEngine('hide');
					}

					var sourceModule = form.find('[name="sourceModule"]').val();
					let progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});

					thisInstance
						.checkCyclicDependency(sourceModule, sourceFieldValue, secondFieldValue)
						.done(function (data) {
							var result = data['result'];
							if (!result['result']) {
								thisInstance.addNewDependencyPickList();
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
							} else {
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
								form.find('.errorMessage').removeClass('d-none');
								form.find('.cancelAddView').removeClass('d-none');
								dependencyGraph.html('');
							}
						})
						.fail(function (error, err) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
				}
			} else {
				form.find('.errorMessage').addClass('d-none');
				var result = app.vtranslate('1JS_SELECT_SOME_VALUE');
				if (sourceFieldValue == '') {
					select2SourceField.validationEngine('showPrompt', result, 'error', 'topLeft', true);
				} else if (secondFieldValue == '') {
					select2SecondField.validationEngine('showPrompt', result, 'error', 'topLeft', true);
				} else if (thirdField && secondFieldValue == '') {
					select2ThirdField.validationEngine('showPrompt', result, 'error', 'topLeft', true);
				}
			}
		},
		/**
		 * Function used to check the cyclic dependency of the selected picklist fields
		 * @params: sourceModule - selected module
		 *            sourceFieldValue - source picklist value
		 *            secondFieldValue - target picklist value
		 */
		checkCyclicDependency: function (sourceModule, sourceFieldValue, secondFieldValue) {
			var aDeferred = jQuery.Deferred();
			var params = {};
			params['mode'] = 'checkCyclicDependency';
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'Index';
			params['sourceModule'] = sourceModule;
			params['sourcefield'] = sourceFieldValue;
			params['secondField'] = secondFieldValue;

			AppConnector.request(params).done(
				function (data) {
					aDeferred.resolve(data);
				},
				function (error, err) {
					aDeferred.reject();
				}
			);
			return aDeferred.promise();
		},
		/**
		 * Function used to show the new picklist dependency graph
		 * @params: sourceModule - selected module
		 *            sourceFieldValue - source picklist value
		 *            secondFieldValue - target picklist value
		 */
		addNewDependencyPickList: function () {
			var thisInstance = this;
			var dependencyGraph = $('#dependencyGraph');
			thisInstance.updatedSourceValues = [];
			AppConnector.request({
				mode: 'getDependencyGraph',
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'IndexAjax',
				sourceModule: this.form.find('[name="sourceModule"] option:selected').val(),
				sourcefield: this.form.find('[name="sourceField"] option:selected').val(),
				secondField: this.form.find('[name="secondField"] option:selected').val(),
				thirdField: this.form.find('[name="thirdField"] option:selected').val()
			}).done(function (data) {
				dependencyGraph.html(data).css({ padding: '10px', border: '1px solid #ddd', background: '#fff' });
				thisInstance.registerDependencyGraphEvents();
				/* to przenieśc do innej funkcji */
				thisInstance.registerSaveDependentPicklist();
				thisInstance.registerChangeSourceValue();
			});
		},
		/**
		 * This function will delete the pickList Dependency
		 * @params: module - selected module
		 *            sourceField - source picklist value
		 *            secondField - target picklist value
		 */
		deleteDependency: function (dependencyId) {
			let aDeferred = jQuery.Deferred();
			let params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'DeleteAjax';
			params['recordId'] = dependencyId;
			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {
					aDeferred.reject(error, err);
				});
			return aDeferred.promise();
		},
		/**
		 * Function used to show cancel link in add view and register click event for cancel
		 */
		registerCancelAddView: function (form) {
			var thisInstance = this;
			var cancelDiv = form.find('.cancelAddView');
			cancelDiv.removeClass('d-none');
			cancelDiv.find('.cancelLink').on('click', function () {
				thisInstance.loadListViewContents(thisInstance.listViewForModule);
			});
		},
		/**
		 * Register all the events related to addView of picklist dependency
		 */
		registerAddViewEvents: function () {
			var thisInstance = this;
			var form = jQuery('#pickListDependencyForm');
			thisInstance.registerCancelAddView(form);
			thisInstance.getModuleDependencyGraph(form);
			thisInstance.registerPicklistFieldsChangeEvent(form);
			thisInstance.registerSubmitEvent();
		},
		/**
		 * Register all the events in editView of picklist dependency
		 */
		registerDependencyGraphEvents: function () {
			var thisInstance = this;
			var form = jQuery('#pickListDependencyForm');
			var dependencyGraph = jQuery('#dependencyGraph');
			form.find('.cancelAddView').addClass('d-none');
			thisInstance.registersecondFieldsClickEvent(dependencyGraph);
			thisInstance.registersecondFieldsUnmarkAll(dependencyGraph);
			thisInstance.registerSelectSourceValuesClick(dependencyGraph);
			thisInstance.registerCancelDependency(form);
		},
		/**
		 * Register all the events related to listView of picklist dependency
		 */
		registerListViewEvents: function () {
			var thisInstance = this;
			var forModule = jQuery('.contentsDiv').find('.pickListSupportedModules').val();
			thisInstance.listViewForModule = forModule;
			thisInstance.registerSourceModuleChangeEvent();
		},
		/**
		 * Register the click event for cancel picklist dependency changes
		 */
		registerCancelDependency: function (form) {
			var thisInstance = this;
			//Register click event for cancel link
			var cancelDependencyLink = form.find('.cancelDependency');
			cancelDependencyLink.on('click', function () {
				thisInstance.loadListViewContents(thisInstance.listViewForModule);
			});
		},
		/**
		 * Register the click event for target fields in dependency graph
		 */
		registersecondFieldsClickEvent: function (dependencyGraph) {
			var thisInstance = this;
			thisInstance.updatedSourceValues = [];
			dependencyGraph.find('td.picklistValueMapping').on('click', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var sourceValue = currentTarget.data('sourceValue');
				if (jQuery.inArray(sourceValue, thisInstance.updatedSourceValues) == -1) {
					thisInstance.updatedSourceValues.push(sourceValue);
				}
				if (currentTarget.hasClass('selectedCell')) {
					currentTarget.addClass('unselectedCell').removeClass('selectedCell');
				} else {
					currentTarget.addClass('selectedCell').removeClass('unselectedCell');
				}
			});
		},
		registersecondFieldsUnmarkAll: function (dependencyGraph) {
			var thisInstance = this;
			thisInstance.updatedSourceValues = [];
			dependencyGraph.find('.unmarkAll').on('click', function (e) {
				dependencyGraph.find('td.picklistValueMapping').each(function (index) {
					var currentTarget = jQuery(this);
					var sourceValue = currentTarget.data('sourceValue');
					if (jQuery.inArray(sourceValue, thisInstance.updatedSourceValues) == -1) {
						thisInstance.updatedSourceValues.push(sourceValue);
					}
					currentTarget.addClass('unselectedCell').removeClass('selectedCell');
				});
			});
		},
		/**
		 * Function used to update the value mapping to save the picklist dependency
		 */
		updateValueMapping: function (dependencyGraph) {
			const self = this;
			self.valueMapping = [];
			let sourceValuesArray = self.updatedSourceValues;
			let dependencyTable = dependencyGraph.find('.js-picklist-dependency-table');
			for (var key in sourceValuesArray) {
				let encodedSourceValue;
				if (typeof sourceValuesArray[key] == 'string') {
					encodedSourceValue = sourceValuesArray[key].replace(/"/g, '\\"');
				} else {
					encodedSourceValue = sourceValuesArray[key];
				}
				let selectedTargetValues = dependencyTable
					.find('td[data-source-value="' + encodedSourceValue + '"]')
					.filter('.selectedCell');
				let targetValues = [];
				if (selectedTargetValues.length > 0) {
					jQuery.each(selectedTargetValues, function (index, element) {
						targetValues.push(jQuery(element).data('targetValue'));
					});
				} else {
					targetValues.push('');
				}
				self.valueMapping.push({
					sourcevalue: sourceValuesArray[key],
					targetvalues: targetValues
				});
			}
		},
		/**
		 * Register click event for select source values button in add/edit view
		 */
		registerSelectSourceValuesClick(dependencyGraph) {
			dependencyGraph.find('button.sourceValues').on('click', () => {
				const selectSourceValues = dependencyGraph.find('.modalCloneCopy');
				const clonedContainer = selectSourceValues.clone(true, true).removeClass('modalCloneCopy');
				app.showModalWindow(
					clonedContainer,
					(data) => {
						data.find('.sourcePicklistValuesModal').removeClass('d-none');
						data.find('[name="saveButton"]').on('click', (e) => {
							this.selectedSourceValues = [];
							const sourceValues = data.find('.sourceValue');
							$.each(sourceValues, (index, ele) => {
								const element = $(ele);
								const elementId = element.attr('id');
								const hiddenElement = selectSourceValues.find('#' + elementId);
								if (element.is(':checked')) {
									this.selectedSourceValues.push(element.val());
									hiddenElement.prop('checked', true);
								} else {
									hiddenElement.prop('checked', false);
								}
							});
							app.hideModalWindow();
							this.loadMappingForSelectedValues(dependencyGraph);
						});
					},
					{ width: '1000px' }
				);
			});
		},
		/**
		 * Function used to load mapping for selected picklist fields
		 */
		loadMappingForSelectedValues: function (dependencyGraph) {
			var thisInstance = this;
			var allSourcePickListValues = JSON.parse(dependencyGraph.find('.allSourceValues').val());
			var dependencyTable = dependencyGraph.find('.js-picklist-dependency-table');
			for (var key in allSourcePickListValues) {
				if (typeof allSourcePickListValues[key] == 'string') {
					var encodedSourcePickListValue = allSourcePickListValues[key].replace(/"/g, '\\"');
				} else {
					encodedSourcePickListValue = allSourcePickListValues[key];
				}
				var mappingCells = dependencyTable.find('[data-source-value="' + encodedSourcePickListValue + '"]');
				if (jQuery.inArray(allSourcePickListValues[key], thisInstance.selectedSourceValues) == -1) {
					mappingCells.hide();
				} else {
					mappingCells.show();
				}
			}
		},
		/**
		 * This function will save the picklist dependency details
		 */
		savePickListDependency: function (mapping) {
			var form = jQuery('#pickListDependencyForm');
			const self = this;
			let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				}),
				params = form.serializeFormData();
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'SaveAjax';
			params['mapping'] = mapping;
			AppConnector.request(params)
				.done(function (data) {
					if (data['success']) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						Vtiger_Helper_Js.showMessage({
							text: app.vtranslate('JS_PICKLIST_DEPENDENCY_SAVED'),
							type: 'success'
						});
						//TODO
						//	self.loadListViewContents(params['sourceModule']);
					}
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
		},
		/**
		 * This function will load the listView contents after Add/Edit picklist dependency
		 */
		loadListViewContents: function (forModule) {
			var thisInstance = this;
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'List';
			params['formodule'] = forModule;

			AppConnector.requestPjax(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					//replace the new list view contents
					jQuery('.contentsDiv').html(data);
					App.Fields.Picklist.changeSelectElementView(jQuery('.contentsDiv'));
					thisInstance.registerListViewEvents();
				})
				.fail(function (error, err) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
		},

		/**
		 * register change event for source module in add/edit picklist dependency
		 */
		registerSourceModuleChangeEvent: function () {
			var thisInstance = this;
			var container = jQuery('.contentsDiv');
			container.find('.pickListSupportedModules').on('change', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var forModule = currentTarget.val();
				thisInstance.loadListViewContents(forModule);
			});
		},
		/**
		 * register the form submit event
		 */
		registerSubmitEvent: function () {
			var thisInstance = this;
			var form = jQuery('#pickListDependencyForm');
			var dependencyGraph = jQuery('#dependencyGraph');
			form.on('submit', function (e) {
				e.preventDefault();
				try {
					thisInstance.updateValueMapping(dependencyGraph);
				} catch (e) {
					app.showAlert(e.message);
					return;
				}
				if (form.find('.editDependency').val() != 'true' && thisInstance.valueMapping.length < 1) {
					var params = {};
					params.text = app.vtranslate('JS_PICKLIST_DEPENDENCY_NO_SAVED');
					params.type = 'info';
					Settings_Vtiger_Index_Js.showMessage(params);
				} else {
					thisInstance.savePickListDependency(self.valueMapping);
				}
			});
		},

		registerAddThirdField: function () {
			this.container.find('.js-add-next-level-field').on('click', () => {
				let params = this.getDefaultParamsForThirdField();
				params.thirdField = true;
				let progress = jQuery.progressIndicator();
				AppConnector.request(params)
					.done((data) => {
						let dependentFieldsContainer = this.container.find('.js-dependent-fields');
						progress.progressIndicator({ mode: 'hide' });
						dependentFieldsContainer.html(data);
						App.Fields.Picklist.showSelect2ElementView(dependentFieldsContainer.find('select.select2'));
						this.checkValuesForDependencyGraph(this.form);
						this.registerPicklistFieldsChangeEvent(this.form);
						this.container.find('#dependencyGraph').html('');
					})
					.fail((_) => {
						app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
						progress.progressIndicator({ mode: 'hide' });
					});
			});
		},
		getDefaultParamsForThirdField() {
			let params = {
				module: this.form.find('[name="module"]').length
					? this.form.find('[name="module"]').val()
					: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: this.form.find('[name="sourceModule"]').val(),
				view: 'DependentFields'
			};
			return params;
		},

		registerSaveDependentPicklist() {
			this.container.find('.js-save-dependent-picklists').on('click', () => {
				this.setPicklistDependencies();
				let picklistDependencies = this.container.find('.js-picklist-dependencies-data').val();
				console.log(picklistDependencies.length);
				if (picklistDependencies !== '{}') {
					this.savePickListDependency(picklistDependencies);
				} else {
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_PICKLIST_DEPENDENCY_NO_SAVED'),
						type: 'info'
					});
				}

				return;
			});
		},
		registerChangeSourceValue() {
			this.container.find('.js-source-field-value').on('change', () => {
				this.setPicklistDependencies();
				this.clearAllMarkedValues();
				this.setMarkedValues();
			});
		},
		setPicklistDependencies() {
			if (this.form.find('.thirdField').val() !== '') {
				const dependencyTable = this.container.find('.js-picklist-dependency-table');
				let sourceFieldValue = dependencyTable.find('.js-source-field-value option:selected').val();
				let selectedOldSourceData = dependencyTable.find('.js-source-field-value option[data-old-source-value]');
				let selectedSourceValue = selectedOldSourceData.attr('data-old-source-value');
				let picklistDependencies =
					this.container.find('.js-picklist-dependencies-data').val() !== ''
						? JSON.parse(this.container.find('.js-picklist-dependencies-data').val())
						: {};

				dependencyTable.find('.js-second-field-value').each(function (_index, element) {
					let secondFieldValue = $(element).attr('data-source-value').replace(/"/g, '\\"');
					if (secondFieldValue) {
						let allValuesInColumn = dependencyTable.find('td[data-source-value="' + secondFieldValue + '"]');
						let selectedTargetValues = dependencyTable
							.find('td[data-source-value="' + secondFieldValue + '"]')
							.filter('.selectedCell');
						let targetValues = [];
						if (selectedTargetValues.length > 0) {
							jQuery.each(selectedTargetValues, function (_index, element) {
								targetValues.push(jQuery(element).data('targetValue'));
							});
							if (selectedTargetValues.length !== allValuesInColumn.length) {
								if (picklistDependencies[selectedSourceValue] === undefined) {
									picklistDependencies[selectedSourceValue] = {};
								}
								picklistDependencies[selectedSourceValue][secondFieldValue] = targetValues;
							}
						}
					}
				});
				selectedOldSourceData.attr('data-old-source-value', sourceFieldValue);
				this.container.find('.js-picklist-dependencies-data').val(JSON.stringify(picklistDependencies));
			}
		},
		clearAllMarkedValues() {
			const dependencyTable = this.container.find('.js-picklist-dependency-table');
			let selectedTargetValues = dependencyTable.find('.picklistValueMapping').filter('.selectedCell');
			if (selectedTargetValues.length > 0) {
				jQuery.each(selectedTargetValues, function (_index, element) {
					jQuery(element).removeClass('selectedCell');
				});
			}
		},
		setMarkedValues() {
			let picklistDependencies = this.container.find('.js-picklist-dependencies-data').val();
			if (picklistDependencies !== '') {
				let sourceFieldValue = this.container.find('.js-source-field-value option:selected').val();
				let targetValues = {};
				let secondFieldValue = '';
				let parsedValues = JSON.parse(picklistDependencies);
				const dependencyTable = this.container.find('.js-picklist-dependency-table');
				for (let sourceMappedValue of Object.keys(parsedValues)) {
					if (sourceFieldValue === sourceMappedValue) {
						targetValues = parsedValues[sourceMappedValue];
						for (let secondValueKey of Object.keys(targetValues)) {
							for (const selectedThirdValue of targetValues[secondValueKey]) {
								secondFieldValue = dependencyTable
									.find(
										'td[data-source-value="' + secondValueKey + '"][data-target-value="' + selectedThirdValue + '"]'
									)
									.addClass('selectedCell')
									.removeClass('unselectedCell');
							}
						}
					}
				}
			}
		},

		/**
		 * register events for picklist dependency
		 */
		registerEvents: function () {
			var thisInstance = this;
			this.container = jQuery('.js-picklist-dependent-container');
			this.form = jQuery('#pickListDependencyForm');
			if (this.form.length > 0) {
				if (this.form.find('.editDependency').val() == 'true') {
					this.form
						.find(
							'select[name="sourceModule"],select[name="sourceField"],select[name="secondField"],select[name="thirdField"]'
						)
						.prop('disabled', true);
					thisInstance.registerDependencyGraphEvents();
					thisInstance.registerSubmitEvent();
					//for three
					thisInstance.registerSaveDependentPicklist();
					thisInstance.registerChangeSourceValue();
				} else {
					thisInstance.registerAddViewEvents();
				}
				thisInstance.registerAddThirdField();
			} else {
				thisInstance.registerListViewEvents();
			}
		}
	}
);

jQuery(document).ready(function () {
	var instance = new Settings_PickListDependency_Js();
	instance.registerEvents();
});
