/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

jQuery.Class('Settings_PickListDependency_Js', {
	//holds the picklist dependency instance
	pickListDependencyInstance: false,
	/**
	 * Function used to triggerAdd new Dependency for the picklists
	 */
	triggerAdd: function (event) {
		event.stopPropagation();
		var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
		instance.updatedSourceValues = [];
		instance.showEditView(instance.listViewForModule).done(function (data) {
			instance.registerAddViewEvents();
		});
	},
	/**
	 * This function used to trigger Edit picklist dependency
	 */
	triggerEdit: function (event, module, sourceField, targetField) {
		event.stopPropagation();
		var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
		instance.updatedSourceValues = [];
		instance.showEditView(module, sourceField, targetField).done(
			function (data) {
				var form = jQuery('#pickListDependencyForm');
				form.find('select[name="sourceModule"],select[name="sourceField"],select[name="targetField"]').prop("disabled", true);
				instance.registerDependencyGraphEvents();
				instance.registerSubmitEvent();
			}
		);
	},
	/**
	 * This function used to trigger Delete picklist dependency
	 */
	triggerDelete: function (event, module, sourceField, targetField) {
		event.stopPropagation();
		var currentTarget = jQuery(event.currentTarget);
		var currentTrEle = currentTarget.closest('tr');
		var instance = Settings_PickListDependency_Js.pickListDependencyInstance;

		var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
			instance.deleteDependency(module, sourceField, targetField).done(function (data) {
				var params = {};
				params.text = app.vtranslate('JS_DEPENDENCY_DELETED_SUEESSFULLY');
				Settings_Vtiger_Index_Js.showMessage(params);
				currentTrEle.fadeOut('slow').remove();
			});
		});
	}

}, {
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
	 *			targetField - target picklist
	 */
	showEditView: function (module, sourceField, targetField) {
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'Edit';
		params['sourceModule'] = module;
		params['sourcefield'] = sourceField;
		params['targetfield'] = targetField;

		AppConnector.requestPjax(params).done(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var container = jQuery('.contentsDiv');
			container.html(data);
			//register all select2 Elements
			App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'), {dropdownCss: {'z-index': 0}});
			aDeferred.resolve(data);
		}).fail(function (error) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
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
			var forModule = form.find('[name="sourceModule"]').val();
			thisInstance.showEditView(forModule).done(function (data) {
				thisInstance.registerAddViewEvents();
			});
		});
	},
	/**
	 * Register change event for picklist fields in add/edit picklist dependency
	 */
	registerPicklistFieldsChangeEvent: function (form) {
		var thisInstance = this;
		form.find('[name="sourceField"],[name="targetField"]').on('change', function () {
			thisInstance.checkValuesForDependencyGraph(form);
		});
	},
	/**
	 * Function used to check the selected picklist fields are valid before showing dependency graph
	 */
	checkValuesForDependencyGraph: function (form) {
		var thisInstance = this;
		var sourceField = form.find('[name="sourceField"]');
		var targetField = form.find('[name="targetField"]');
		var select2SourceField = app.getSelect2ElementFromSelect(sourceField);
		var select2TargetField = app.getSelect2ElementFromSelect(targetField);
		var sourceFieldValue = sourceField.val();
		var targetFieldValue = targetField.val();
		var dependencyGraph = jQuery('#dependencyGraph');
		if (sourceFieldValue != '' && targetFieldValue != '') {
			var resultMessage = app.vtranslate('JS_SOURCE_AND_TARGET_FIELDS_SHOULD_NOT_BE_SAME');
			form.find('.errorMessage').addClass('d-none');
			if (sourceFieldValue == targetFieldValue) {
				select2TargetField.validationEngine('showPrompt', resultMessage, 'error', 'topLeft', true);
				dependencyGraph.html('');
			} else {
				select2SourceField.validationEngine('hide');
				select2TargetField.validationEngine('hide');
				var sourceModule = form.find('[name="sourceModule"]').val();
				var progressIndicatorElement = jQuery.progressIndicator({
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
				thisInstance.checkCyclicDependency(sourceModule, sourceFieldValue, targetFieldValue).done(function (data) {
					var result = data['result'];
					if (!result['result']) {
						thisInstance.addNewDependencyPickList(sourceModule, sourceFieldValue, targetFieldValue);
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					} else {
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
						form.find('.errorMessage').removeClass('d-none');
						form.find('.cancelAddView').removeClass('d-none');
						dependencyGraph.html('');
					}
				}).fail(function (error, err) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				});
			}
		} else {
			form.find('.errorMessage').addClass('d-none');
			var result = app.vtranslate('JS_SELECT_SOME_VALUE');
			if (sourceFieldValue == '') {
				select2SourceField.validationEngine('showPrompt', result, 'error', 'topLeft', true);
			} else if (targetFieldValue == '') {
				select2TargetField.validationEngine('showPrompt', result, 'error', 'topLeft', true);
			}
		}
	},
	/**
	 * Function used to check the cyclic dependency of the selected picklist fields
	 * @params: sourceModule - selected module
	 *            sourceFieldValue - source picklist value
	 *            targetFieldValue - target picklist value
	 */
	checkCyclicDependency: function (sourceModule, sourceFieldValue, targetFieldValue) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = 'checkCyclicDependency';
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Index';
		params['sourceModule'] = sourceModule;
		params['sourcefield'] = sourceFieldValue;
		params['targetfield'] = targetFieldValue;

		AppConnector.request(params).done(
			function (data) {
				aDeferred.resolve(data);
			}, function (error, err) {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	/**
	 * Function used to show the new picklist dependency graph
	 * @params: sourceModule - selected module
	 *            sourceFieldValue - source picklist value
	 *            targetFieldValue - target picklist value
	 */
	addNewDependencyPickList: function (sourceModule, sourceFieldValue, targetFieldValue) {
		var thisInstance = this;
		var dependencyGraph = $('#dependencyGraph');
		thisInstance.updatedSourceValues = [];
		AppConnector.request({
			mode: 'getDependencyGraph',
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			view: 'IndexAjax',
			sourceModule: sourceModule,
			sourcefield: sourceFieldValue,
			targetfield: targetFieldValue
		}).done(function (data) {
			dependencyGraph.html(data).css({'padding': '10px', 'border': '1px solid #ddd', 'background': '#fff'});
			thisInstance.registerDependencyGraphEvents();
		});
	},
	/**
	 * This function will delete the pickList Dependency
	 * @params: module - selected module
	 *            sourceField - source picklist value
	 *            targetField - target picklist value
	 */
	deleteDependency: function (module, sourceField, targetField) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'DeleteAjax';
		params['sourceModule'] = module;
		params['sourcefield'] = sourceField;
		params['targetfield'] = targetField;
		AppConnector.request(params).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error, err) {
				aDeferred.reject(error, err);
			}
		);
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
		})
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
		thisInstance.registerTargetFieldsClickEvent(dependencyGraph);
		thisInstance.registerTargetFieldsUnmarkAll(dependencyGraph);
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
		//thisInstance.triggerDisplayTypeEvent();
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
		})
	},
	/**
	 * Register the click event for target fields in dependency graph
	 */
	registerTargetFieldsClickEvent: function (dependencyGraph) {
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
	registerTargetFieldsUnmarkAll: function (dependencyGraph) {
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
		var thisInstance = this;
		thisInstance.valueMapping = [];
		var sourceValuesArray = thisInstance.updatedSourceValues;
		var dependencyTable = dependencyGraph.find('.pickListDependencyTable');
		for (var key in sourceValuesArray) {
			let encodedSourceValue;
			if (typeof sourceValuesArray[key] == 'string') {
				encodedSourceValue = sourceValuesArray[key].replace(/"/g, '\\"');
			} else {
				encodedSourceValue = sourceValuesArray[key];
			}
			var selectedTargetValues = dependencyTable.find('td[data-source-value="' + encodedSourceValue + '"]').filter('.selectedCell');
			var targetValues = [];
			if (selectedTargetValues.length > 0) {
				jQuery.each(selectedTargetValues, function (index, element) {
					targetValues.push(jQuery(element).data('targetValue'));
				});
			} else {
				targetValues.push('');
			}
			thisInstance.valueMapping.push({'sourcevalue': sourceValuesArray[key], 'targetvalues': targetValues});
		}
	},
	/**
	 * Register click event for select source values button in add/edit view
	 */
	registerSelectSourceValuesClick(dependencyGraph) {
		dependencyGraph.find('button.sourceValues').on('click', () => {
			const selectSourceValues = dependencyGraph.find('.modalCloneCopy');
			const clonedContainer = selectSourceValues.clone(true, true).removeClass('modalCloneCopy');
			app.showModalWindow(clonedContainer, (data) => {
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
			}, {'width': '1000px'});
		});
	},
	/**
	 * Function used to load mapping for selected picklist fields
	 */
	loadMappingForSelectedValues: function (dependencyGraph) {
		var thisInstance = this;
		var allSourcePickListValues = JSON.parse(dependencyGraph.find('.allSourceValues').val());
		var dependencyTable = dependencyGraph.find('.pickListDependencyTable');
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
	savePickListDependency: function (form) {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var data = form.serializeFormData();
		data['module'] = app.getModuleName();
		data['parent'] = app.getParentModuleName();
		data['action'] = 'SaveAjax';
		data['mapping'] = JSON.stringify(thisInstance.valueMapping);
		AppConnector.request(data).done(function (data) {
			if (data['success']) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				var params = {};
				params.text = app.vtranslate('JS_PICKLIST_DEPENDENCY_SAVED');
				Settings_Vtiger_Index_Js.showMessage(params);
				thisInstance.loadListViewContents(thisInstance.listViewForModule);
			}
		}).fail(function (error) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
		});
	},
	/**
	 * This function will load the listView contents after Add/Edit picklist dependency
	 */
	loadListViewContents: function (forModule) {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'List';
		params['formodule'] = forModule;

		AppConnector.requestPjax(params).done(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			//replace the new list view contents
			jQuery('.contentsDiv').html(data);
			App.Fields.Picklist.changeSelectElementView(jQuery('.contentsDiv').find('.pickListSupportedModules'));
			thisInstance.registerListViewEvents();
		}).fail(function (error, err) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
		});
	},
	/**
	 * trigger the display type event to show the width
	 */
	triggerDisplayTypeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if (widthType) {
			var elements = jQuery('.listViewEntriesTable').find('td,th');
			elements.attr('class', widthType);
		}
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
				bootbox.alert(e.message);
				return;
			}
			if (form.find('.editDependency').val() != "true" && thisInstance.valueMapping.length < 1) {
				var params = {};
				params.text = app.vtranslate('JS_PICKLIST_DEPENDENCY_NO_SAVED');
				params.type = 'info';
				Settings_Vtiger_Index_Js.showMessage(params);
			} else {
				thisInstance.savePickListDependency(form);
			}

		});
	},
	/**
	 * register events for picklist dependency
	 */
	registerEvents: function () {
		var thisInstance = this;
		var form = jQuery('#pickListDependencyForm');
		if (form.length > 0) {
			if (form.find('.editDependency').val() == "true") {
				form.find('select[name="sourceModule"],select[name="sourceField"],select[name="targetField"]').prop("disabled", true);
				thisInstance.registerDependencyGraphEvents();
				thisInstance.registerSubmitEvent();
			} else {
				thisInstance.registerAddViewEvents();
			}
		} else {
			thisInstance.registerListViewEvents();
		}
	}

});

jQuery(document).ready(function () {
	var instance = new Settings_PickListDependency_Js();
	instance.registerEvents();
})
