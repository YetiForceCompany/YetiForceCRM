/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

var Settings_Picklist_Js = {

	registerModuleChangeEvent: function () {
		jQuery('#pickListModules').on('change', function (e) {
			var selectedModule = jQuery(e.currentTarget).val();
			if (selectedModule.length <= 0) {
				Settings_Vtiger_Index_Js.showMessage({
					'type': 'error',
					'text': app.vtranslate('JS_PLEASE_SELECT_MODULE')
				});
				return;
			}
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				source_module: selectedModule,
				view: 'IndexAjax',
				mode: 'getPickListDetailsForModule'
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request(params).done(function (data) {
				jQuery('#modulePickListContainer').html(data);
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				App.Fields.Picklist.changeSelectElementView(jQuery('#modulePickListContainer'));
				Settings_Picklist_Js.registerModulePickListChangeEvent();
				jQuery('#modulePickList').trigger('change');
			});
		});
	},

	registerModulePickListChangeEvent: function () {
		jQuery('#modulePickList').on('change', function (e) {
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				source_module: jQuery('#pickListModules').val(),
				view: 'IndexAjax',
				mode: 'getPickListValueForField',
				pickListFieldId: jQuery(e.currentTarget).val()
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request(params).done(function (data) {
				jQuery('#modulePickListValuesContainer').html(data);
				App.Fields.Picklist.showSelect2ElementView(jQuery('#rolesList'));
				Settings_Picklist_Js.registerItemActions();
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		});
	},

	registerAddItemEvent: function () {
		jQuery('#addItem').on('click', function (e) {
			var data = jQuery('#createViewContents').find('.modal');
			var clonedCreateView = data.clone(true, true).removeClass('basicCreateView').addClass('createView');
			clonedCreateView.find('.rolesList').addClass('select2');
			clonedCreateView.find('.automation-list').addClass('select2');
			var callBackFunction = function (data) {
				jQuery('[name="addItemForm"]', data).validationEngine();
				Settings_Picklist_Js.registerAddItemSaveEvent(data);
				Settings_Picklist_Js.regiserSelectRolesEvent(data);
			}
			app.showModalWindow(clonedCreateView, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			});
		});
	},

	registerAssingValueToRuleEvent: function () {
		jQuery('#assignValue').on('click', function () {
			var pickListValuesTable = jQuery('#pickListValuesTable');
			var selectedListItem = jQuery('.selectedListItem', pickListValuesTable);
			if (selectedListItem.length > 0) {
				var selectedValues = [];
				jQuery.each(selectedListItem, function (i, element) {
					selectedValues.push(jQuery(element).closest('tr').data('key'));

				});
			}

			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				source_module: jQuery('#pickListModules').val(),
				view: 'IndexAjax',
				mode: 'showAssignValueToRoleView',
				pickListFieldId: jQuery('#modulePickList').val()
			}
			AppConnector.request(params).done(function (data) {
				app.showModalWindow(data);
				jQuery('[name="addItemForm"]', jQuery(data)).validationEngine();
				Settings_Picklist_Js.registerAssignValueToRoleSaveEvent(jQuery(data));
				if (selectedListItem.length > 0) {
					jQuery('[name="assign_values[]"]', jQuery('#assignValueToRoleForm')).select2('val', selectedValues);
				}
			});
		});
	},

	registerAssignValueToRoleSaveEvent: function (data) {
		jQuery('#assignValueToRoleForm').on('submit', function (e) {
			var form = jQuery(e.currentTarget);

			var assignValuesSelectElement = jQuery('[name="assign_values[]"]', form);
			var assignValuesSelect2Element = app.getSelect2ElementFromSelect(assignValuesSelectElement);
			var assignValueResult = Vtiger_MultiSelect_Validator_Js.invokeValidation(assignValuesSelectElement);
			if (assignValueResult != true) {
				assignValuesSelect2Element.validationEngine('showPrompt', assignValueResult, 'error', 'topLeft', true);
			} else {
				assignValuesSelect2Element.validationEngine('hide');
			}

			var rolesSelectElement = jQuery('[name="rolesSelected[]"]', form);
			var select2Element = app.getSelect2ElementFromSelect(rolesSelectElement);
			var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(rolesSelectElement);
			if (result != true) {
				select2Element.validationEngine('showPrompt', result, 'error', 'bottomLeft', true);
			} else {
				select2Element.validationEngine('hide');
			}

			if (assignValueResult != true || result != true) {
				e.preventDefault();
				return;
			} else {
				form.find('[name="saveButton"]').attr('disabled', "disabled");
			}
			var params = jQuery(e.currentTarget).serializeFormData();
			AppConnector.request(params).done(function (data) {
				if (typeof data.result !== "undefined") {
					app.hideModalWindow();
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_VALUE_ASSIGNED_SUCCESSFULLY'),
						type: 'success'
					})
				}
			});
			e.preventDefault();
		});
	},

	registerEnablePickListValueClickEvent: function () {
		jQuery('#listViewContents').on('click', '.assignToRolePickListValue', function (e) {
			jQuery('#saveOrder').removeAttr('disabled');

			var pickListVaue = jQuery(e.currentTarget)
			if (pickListVaue.hasClass('selectedCell')) {
				pickListVaue.removeClass('selectedCell').addClass('unselectedCell');
				pickListVaue.find('.fas').remove();
			} else {
				pickListVaue.removeClass('unselectedCell').addClass('selectedCell');
				pickListVaue.prepend('<i class="fas fa-check float-left"></span>');
			}
		});
	},

	registerenableOrDisableListSaveEvent: function () {
		jQuery('#saveOrder').on('click', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true,
					'elementToBlock': jQuery('.tab-content')
				}
			});
			var pickListValues = jQuery('.assignToRolePickListValue');
			var disabledValues = [];
			var enabledValues = [];
			jQuery.each(pickListValues, function (i, element) {
				var currentValue = jQuery(element);
				if (currentValue.hasClass('selectedCell')) {
					enabledValues.push(currentValue.data('id'));
				} else {
					disabledValues.push(currentValue.data('id'));
				}
			});
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'enableOrDisable',
				enabled_values: enabledValues,
				disabled_values: disabledValues,
				picklistName: jQuery('[name="picklistName"]').val(),
				rolesSelected: jQuery('#rolesList').val()
			}
			AppConnector.request(params).done(function (data) {
				if (typeof data.result !== "undefined") {
					jQuery(e.currentTarget).attr('disabled', 'disabled');
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_LIST_UPDATED_SUCCESSFULLY'),
						type: 'success'
					})
				}
			});
		});
	},

	regiserSelectRolesEvent: function (data) {
		data.find('[name="rolesSelected[]"]').on('change', function (e) {
			var rolesSelectElement = jQuery(e.currentTarget);
			var selectedValue = rolesSelectElement.val();
			if (jQuery.inArray('all', selectedValue) != -1) {
				rolesSelectElement.select2("val", "");
				rolesSelectElement.select2("val", "all");
				rolesSelectElement.select2("close");
				rolesSelectElement.find('option').not(':first').attr('disabled', 'disabled');
				data.find(jQuery('.modal-body')).append('<div class="alert alert-info textAlignCenter">' + app.vtranslate('JS_ALL_ROLES_SELECTED') + '</div>')
			} else {
				rolesSelectElement.find('option').removeAttr('disabled', 'disabled');
				data.find('.modal-body').find('.alert').remove();
			}
		});
	},

	registerRenameItemEvent: function () {
		const thisInstance = this;
		$('#renameItem').on('click', function (e) {
			var selectedListItem = $('.selectedListItem', $('#pickListValuesTable'));
			var selectedListItemLength = selectedListItem.length;
			if (selectedListItemLength < 1) {
				Vtiger_Helper_Js.showPnotify({
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_NO_ITEM_SELECTED'),
					type: 'error'
				});
				return false;
			} else if (selectedListItemLength > 1) {
				Vtiger_Helper_Js.showPnotify({
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_MORE_THAN_ONE_ITEM_SELECTED'),
					type: 'error'
				});
				return false;
			} else {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					source_module: $('#pickListModules').val(),
					view: 'IndexAjax',
					mode: 'showEditView',
					pickListFieldId: $('#modulePickList').val(),
					fieldValueId: selectedListItem.closest('tr').data('key-id')
				}).done(function (data) {
					app.showModalWindow(data);
					let form = $('#renameItemForm');
					thisInstance.registerScrollForNonEditablePicklistValues(form);
					form.validationEngine();
					Settings_Picklist_Js.registerRenameItemSaveEvent();
				});
			}
		});
	},

	/**
	 * Function to register the scroll bar for NonEditable Picklist Values
	 */
	registerScrollForNonEditablePicklistValues: function (container) {
		jQuery(container).find('.nonEditablePicklistValues').slimScroll({
			height: '70px',
			size: '6px'
		});
	},

	registerDeleteItemEvent: function () {
		var thisInstance = this;
		jQuery('#deleteItem').on('click', function (e) {
			var pickListValuesTable = jQuery('#pickListValuesTable');
			var selectedListItem = jQuery('.selectedListItem', pickListValuesTable);
			var selectedListItemsArray = [];

			jQuery.each(selectedListItem, function (index, element) {
				selectedListItemsArray.push(jQuery(element).closest('tr').data('key'));
			})
			var pickListValues = jQuery('.pickListValue', pickListValuesTable);
			if (pickListValues.length == selectedListItem.length) {
				Settings_Vtiger_Index_Js.showMessage({
					text: app.vtranslate('JS_YOU_CANNOT_DELETE_ALL_THE_VALUES'),
					type: 'error'
				})
				return;
			}
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				source_module: jQuery('#pickListModules').val(),
				view: 'IndexAjax',
				mode: 'showDeleteView',
				pickListFieldId: jQuery('#modulePickList').val(),
				fieldValue: JSON.stringify(selectedListItemsArray)
			}
			thisInstance.showDeleteItemForm(params);
		});
	},

	registerDeleteOptionEvent: function () {
		let replaceValueElement = jQuery('#replaceValue');
		jQuery('[name="delete_value[]"]').on("select2:unselect", function (e) {
			let id = e.params.data.id;
			let text = e.params.data.text;
			replaceValueElement.append('<option value="' + id + '">' + text + '</option>').trigger('change');
		});
		jQuery('[name="delete_value[]"]').on("select2:select", function (e) {
			let id = e.params.data.id;
			jQuery('#replaceValue option[value="' + id + '"]').remove();
			replaceValueElement.trigger('change');
		});
	},

	registerChangeRoleEvent: function () {
		jQuery('#rolesList').on('change', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true,
					'elementToBlock': jQuery('.tab-content')
				}
			});
			var rolesList = jQuery(e.currentTarget);
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'IndexAjax',
				mode: 'getPickListValueByRole',
				rolesSelected: rolesList.val(),
				pickListFieldId: jQuery('#modulePickList').val(),
				sourceModule: jQuery('input[name="source_module"]').val()
			};
			AppConnector.request(params).done(function (data) {
				jQuery('#pickListValeByRoleContainer').html(data);
				Settings_Picklist_Js.registerenableOrDisableListSaveEvent();
				progressIndicatorElement.progressIndicator({mode: 'hide'});
			});
		});
	},

	registerAddItemSaveEvent: function (container) {
		container.find('[name="addItemForm"]').on('submit', function (e) {
			var form = jQuery(e.currentTarget);
			var validationResult = form.validationEngine('validate');
			if (validationResult == true) {
				var invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length == 0) {
					form.find('[name="saveButton"]').attr('disabled', "disabled");
				}

				var params = jQuery(e.currentTarget).serializeFormData();
				var newValue = params.newValue;
				params.newValue = jQuery.trim(newValue);
				AppConnector.request(params).done(function (data) {
					data = data.result;
					if (data) {
						var newValue = jQuery.trim(jQuery('[name="newValue"]', container).val());
						var dragImagePath = jQuery('#dragImagePath').val();
						var newElement = '<tr class="pickListValue u-cursor-pointer"><td class="u-text-ellipsis"><img class="alignMiddle" src="' + dragImagePath + '" />&nbsp;&nbsp;' + newValue + '</td></tr>';
						var newPickListValueRow = jQuery(newElement).appendTo(jQuery('#pickListValuesTable').find('tbody'));
						newPickListValueRow.attr('data-key', newValue);
						newPickListValueRow.attr('data-key-id', data['id']);
						app.hideModalWindow();
						var params = {
							title: app.vtranslate('JS_MESSAGE'),
							text: app.vtranslate('JS_ITEM_ADDED_SUCCESSFULLY'),
							type: 'success'
						};
						Vtiger_Helper_Js.showPnotify(params);
						//update the new item in the hidden picklist values array
						var pickListValuesEle = jQuery('[name="pickListValues"]');
						var pickListValuesArray = JSON.parse(pickListValuesEle.val());
						pickListValuesArray[data['id']] = newValue;
						pickListValuesEle.val(JSON.stringify(pickListValuesArray));
					} else {
						form.find('[name="saveButton"]').attr('disabled', false);
					}
				});
			}
			e.preventDefault();
		});
	},
	/**
	 * Remene item
	 */
	registerRenameItemSaveEvent() {
		$('#renameItemForm').on('submit', (e) => {
			e.preventDefault();
			let form = $(e.currentTarget);
			if (form.validationEngine('validate')) {
				let newValue, oldValue = form.find('[name="oldValue"]').val(),
					id = form.find('[name="primaryKeyId"]').val(),
					params = $(e.currentTarget).serializeFormData();
				if (form.find('[name="newValue"]').length > 0) {
					newValue = $.trim(form.find('[name="newValue"]').val());
					params.newValue = newValue;
				}
				if (form.data('jqv').InvalidFields.length === 0) {
					form.find('[name="saveButton"]').attr('disabled', "disabled");
				}
				const progress = $.progressIndicator({position: 'html', blockInfo: {enabled: true}});
				AppConnector.request(params).done((data) => {
					progress.progressIndicator({'mode': 'hide'});
					if (typeof data.result !== "undefined") {
						app.hideModalWindow();
						Vtiger_Helper_Js.showPnotify({
							title: app.vtranslate('JS_MESSAGE'),
							text: app.vtranslate('JS_ITEM_RENAMED_SUCCESSFULLY'),
							type: 'success'
						});
						if (newValue !== '') {
							let encodedOldValue = oldValue.replace(/"/g, '\\"'),
								dragImagePath = $('#dragImagePath').val(),
								renamedElement = '<tr class="pickListValue u-cursor-pointer">' +
									'<td class="u-text-ellipsis"><img class="alignMiddle" src="' +
									dragImagePath + '" />&nbsp;&nbsp;' + data.result.newValue +
									'</td></tr>';
							$('[data-key="' + encodedOldValue + '"]').replaceWith($(renamedElement).attr('data-key', newValue).attr('data-key-id', id));
							//update the new item in the hidden picklist values array
							let pickListValuesEle = $('[name="pickListValues"]'),
								pickListValuesArray = JSON.parse(pickListValuesEle.val());
							pickListValuesArray[id] = newValue;
							pickListValuesEle.val(JSON.stringify(pickListValuesArray));
						}
					} else {
						form.find('[name="saveButton"]').attr('disabled', false);
					}
				}).fail(function (data, err) {
					app.errorLog(data, err);
					progress.progressIndicator({'mode': 'hide'});
				});
			}
		});
	},

	showDeleteItemForm: function (params) {
		var thisInstance = this;
		AppConnector.request(params).done(function (data) {
			app.showModalWindow(data, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			});
		});

		var callBackFunction = function (data) {
			var form = data.find('#deleteItemForm');
			thisInstance.registerScrollForNonEditablePicklistValues(form);
			var maximumSelectionSize = jQuery('#pickListValuesCount').val() - 1;
			App.Fields.Picklist.changeSelectElementView(jQuery('[name="delete_value[]"]'), 'select2', {
				maximumSelectionLength: maximumSelectionSize,
				dropdownCss: {'z-index': 100001}
			});
			Settings_Picklist_Js.registerDeleteOptionEvent();

			var validationParams = app.getvalidationEngineOptions(true);
			validationParams.onValidationComplete = function (form, valid) {
				if (valid) {
					var selectElement = jQuery('[name="delete_value[]"]');
					var select2Element = app.getSelect2ElementFromSelect(selectElement);
					var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(selectElement);
					if (result !== true) {
						select2Element.validationEngine('showPrompt', result, 'error', 'topLeft', true);
						return;
					} else {
						select2Element.validationEngine('hide');
						form.find('[name="saveButton"]').attr('disabled', "disabled");
					}
					var deleteValues = jQuery('[name="delete_value[]"]').val();
					AppConnector.request(form.serializeFormData()).done(function (data) {
						if (typeof data.result !== "undefined") {
							app.hideModalWindow();
							//delete the item in the hidden picklist values array
							var pickListValuesEle = jQuery('[name="pickListValues"]');
							var pickListValuesArray = JSON.parse(pickListValuesEle.val());
							jQuery.each(deleteValues, function (i, e) {
								var encodedOldValue = e.replace(/"/g, '\\"');
								jQuery('[data-key-id="' + encodedOldValue + '"]').remove();
								delete pickListValuesArray[e];
							});
							pickListValuesEle.val(JSON.stringify(pickListValuesArray));
							Vtiger_Helper_Js.showPnotify({
								title: app.vtranslate('JS_MESSAGE'),
								text: app.vtranslate('JS_ITEMS_DELETED_SUCCESSFULLY'),
								type: 'success'
							});
						}
					});
				}
				return false;
			}
			form.validationEngine(validationParams);
		}
	},

	registerSelectPickListValueEvent: function () {
		jQuery("#pickListValuesTable").on('click', '.pickListValue', function (event) {
			var currentRow = jQuery(event.currentTarget);
			var currentRowTd = currentRow.find('td');
			event.preventDefault();

			if (event.ctrlKey) {
				currentRowTd.toggleClass('selectedListItem');
			} else {
				jQuery(".pickListValue").find('td').not(currentRowTd).removeClass("selectedListItem");
				currentRowTd.toggleClass('selectedListItem');
			}
		});
	},

	registerPickListValuesSortableEvent: function () {
		var tbody = jQuery("tbody", jQuery('#pickListValuesTable'));
		tbody.sortable({
			'helper': function (e, ui) {
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function (index, element) {
					element = jQuery(element);
					element.width(element.width());
				})
				return ui;
			},
			'containment': tbody,
			'revert': true,
			update: function (e, ui) {
				jQuery('#saveSequence').removeAttr('disabled');
			}
		});
	},

	registerSaveSequenceClickEvent: function () {
		jQuery('#saveSequence').on('click', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true,
					'elementToBlock': jQuery('.tab-content')
				}
			});
			var pickListValuesSequenceArray = {}
			var pickListValues = jQuery('#pickListValuesTable').find('.pickListValue');
			jQuery.each(pickListValues, function (i, element) {
				pickListValuesSequenceArray[jQuery(element).data('key-id')] = ++i;
			});
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'saveOrder',
				picklistValues: pickListValuesSequenceArray,
				picklistName: jQuery('[name="picklistName"]').val()
			}
			AppConnector.request(params).done(function (data) {
				if (typeof data.result !== "undefined") {
					jQuery('#saveSequence').attr('disabled', 'disabled');
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_SEQUENCE_UPDATED_SUCCESSFULLY'),
						type: 'success'
					});
				}
			});
		});
	},

	registerAssingValueToRoleTabClickEvent: function () {
		jQuery('#assignedToRoleTab').on('click', function (e) {
			jQuery('#rolesList').trigger('change');
		});
	},

	registerItemActions: function () {
		Settings_Picklist_Js.registerAddItemEvent();
		Settings_Picklist_Js.registerRenameItemEvent();
		Settings_Picklist_Js.registerDeleteItemEvent();
		Settings_Picklist_Js.registerSelectPickListValueEvent();
		Settings_Picklist_Js.registerAssingValueToRuleEvent();
		Settings_Picklist_Js.registerChangeRoleEvent();
		Settings_Picklist_Js.registerAssingValueToRoleTabClickEvent();
		Settings_Picklist_Js.registerPickListValuesSortableEvent();
		Settings_Picklist_Js.registerSaveSequenceClickEvent();
	},

	registerEvents: function () {
		Settings_Picklist_Js.registerModuleChangeEvent();
		Settings_Picklist_Js.registerModulePickListChangeEvent();
		Settings_Picklist_Js.registerItemActions();
		Settings_Picklist_Js.registerEnablePickListValueClickEvent();
	}
}

jQuery(document).ready(function () {
	Settings_Picklist_Js.registerEvents();
})

Vtiger_Base_Validator_Js("Vtiger_FieldLabel_Validator_Js", {

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function (field, rules, i, options) {
		var instance = new Vtiger_FieldLabel_Validator_Js();
		instance.setElement(field);
		var response = instance.validate();
		if (response != true) {
			return instance.getError();
		}
	}

}, {
	/**
	 * Function to validate the field label
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function () {
		var fieldValue = this.getFieldValue();
		return this.validateValue(fieldValue);
	},

	validateValue: function (fieldValue) {
		let specialChars = /[\<\>\"\,\#]/;
		if (specialChars.test(fieldValue)) {
			this.setError(app.vtranslate('JS_SPECIAL_CHARACTERS') + " < > \" , # " + app.vtranslate('JS_NOT_ALLOWED'));
			return false;
		}
		return true;
	}
});
