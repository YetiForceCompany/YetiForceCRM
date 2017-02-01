/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
jQuery.Class("Vtiger_Edit_Js", {
	//Event that will triggered when reference field is selected
	referenceSelectionEvent: 'Vtiger.Reference.Selection',
	//Event that will triggered when reference field is selected
	referenceDeSelectionEvent: 'Vtiger.Reference.DeSelection',
	//Event that will triggered before saving the record
	recordPreSave: 'Vtiger.Record.PreSave',
	refrenceMultiSelectionEvent: 'Vtiger.MultiReference.Selection',
	preReferencePopUpOpenEvent: 'Vtiger.Referece.Popup.Pre',
	editInstance: false,
	SaveResultInstance: false,
	postReferenceSelectionEvent: 'Vtiger.PostReference.Selection',
	/**
	 * Function to get Instance by name
	 * @params moduleName:-- Name of the module to create instance
	 */
	getInstanceByModuleName: function (moduleName) {
		if (typeof moduleName == "undefined") {
			moduleName = app.getModuleName();
		}
		var parentModule = app.getParentModuleName();
		if (parentModule == 'Settings') {
			var moduleClassName = parentModule + "_" + moduleName + "_Edit_Js";
			if (typeof window[moduleClassName] == 'undefined') {
				moduleClassName = moduleName + "_Edit_Js";
			}
			var fallbackClassName = parentModule + "_Vtiger_Edit_Js";
			if (typeof window[fallbackClassName] == 'undefined') {
				fallbackClassName = "Vtiger_Edit_Js";
			}
		} else {
			moduleClassName = moduleName + "_Edit_Js";
			fallbackClassName = "Vtiger_Edit_Js";
		}
		if (typeof window[moduleClassName] != 'undefined') {
			var instance = new window[moduleClassName]();
		} else {
			var instance = new window[fallbackClassName]();
		}
		instance.moduleName = moduleName;
		return instance;
	},
	getInstance: function () {
		if (Vtiger_Edit_Js.editInstance == false) {
			var instance = Vtiger_Edit_Js.getInstanceByModuleName();
			Vtiger_Edit_Js.editInstance = instance;
			return instance;
		}
		return Vtiger_Edit_Js.editInstance;
	}

}, {
	addressDataOG: [],
	addressDataGM: [],
	formElement: false,
	relationOperation: '',
	moduleName: app.getModuleName(),
	getForm: function () {
		if (this.formElement == false) {
			this.setForm(jQuery('#EditView'));
		}
		return this.formElement;
	},
	setForm: function (element) {
		this.formElement = element;
		return this;
	},
	getPopUpParams: function (container) {
		var params = {};
		var sourceModule = app.getModuleName();
		var popupReferenceModule = jQuery('input[name="popupReferenceModule"]', container).val();
		var sourceFieldElement = jQuery('input[class="sourceField"]', container);
		var sourceField = sourceFieldElement.attr('name');
		var sourceRecordElement = jQuery('input[name="record"]');
		var sourceRecordId = '';
		if (sourceRecordElement.length > 0) {
			sourceRecordId = sourceRecordElement.val();
		}

		var isMultiple = false;
		if (sourceFieldElement.data('multiple') == true) {
			isMultiple = true;
		}

		var filterFields = {};
		var formElement = container.closest('form');
		var mappingRelatedField = formElement.find('input[name="mappingRelatedField"]').val();
		var mappingRelatedModule = mappingRelatedField ? JSON.parse(mappingRelatedField) : [];
		if (mappingRelatedModule[sourceField] != undefined && mappingRelatedModule[sourceField][popupReferenceModule] != undefined) {
			$.each(mappingRelatedModule[sourceField][popupReferenceModule], function (index, value) {
				var mapFieldElement = formElement.find('[name="' + index + '"]');
				if (mapFieldElement.length && mapFieldElement.val() != '') {
					filterFields[index] = mapFieldElement.val();
				}
			});
		}
		var params = {
			module: popupReferenceModule,
			src_module: sourceModule,
			src_field: sourceField,
			src_record: sourceRecordId,
			filterFields: filterFields,
		}

		if (isMultiple) {
			params.multi_select = true;
		}
		return params;
	},
	openPopUp: function (e) {
		var thisInstance = this;
		var parentElem = jQuery(e.target).closest('.fieldValue');
		if (parentElem.length <= 0) {
			parentElem = jQuery(e.target).closest('td');
		}
		var params = this.getPopUpParams(parentElem);

		var isMultiple = false;
		if (params.multi_select) {
			isMultiple = true;
		}

		var sourceFieldElement = jQuery('input[class="sourceField"]', parentElem);

		var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
		sourceFieldElement.trigger(prePopupOpenEvent);

		if (prePopupOpenEvent.isDefaultPrevented()) {
			return;
		}

		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(params, function (response) {
			var responseData = JSON.parse(response);
			var dataList = new Array();
			for (var id in responseData) {
				var data = {
					'name': responseData[id].name,
					'id': id
				}
				dataList.push(data);
				if (!isMultiple) {
					thisInstance.setReferenceFieldValue(parentElem, data);
				}
			}
			if (isMultiple) {
				sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent, {data: dataList});
			}
			sourceFieldElement.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent, {data: responseData});
		});
	},
	setReferenceFieldValue: function (container, params) {
		var thisInstance = this;
		var sourceFieldElement = container.find('input.sourceField');
		var sourceField = sourceFieldElement.attr('name');
		var fieldElement = container.find('input[name="' + sourceField + '"]');
		var sourceFieldDisplay = sourceField + "_display";
		var fieldDisplayElement = container.find('input[name="' + sourceFieldDisplay + '"]');
		var popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();

		var selectedName = params.name;
		var id = params.id;

		fieldElement.val(id)
		fieldDisplayElement.val(selectedName).attr('readonly', true);
		fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {
			source_module: popupReferenceModule,
			record: id,
			selectedName: selectedName
		});
		fieldDisplayElement.validationEngine('closePrompt', fieldDisplayElement);
		if(sourceFieldElement.data('type') == 'inventory'){
			return params;
		}
		var formElement = container.closest('form');
		var mappingRelatedField = this.getMappingRelatedField(sourceField, popupReferenceModule, formElement);
		if (typeof mappingRelatedField != undefined) {
			var params = {
				source_module: popupReferenceModule,
				record: id
			};
			this.getRecordDetails(params).then(function (data) {
				var response = params.data = data['result']['data'];
				$.each(mappingRelatedField, function (key, value) {
					if (response[value[0]] != 0 && !thisInstance.getMappingValuesFromUrl(key)) {
						var mapFieldElement = formElement.find('[name="' + key + '"]');
						if (mapFieldElement.is('select')) {
							if (mapFieldElement.find('option[value="' + response[value[0]] + '"]').length) {
								mapFieldElement.val(response[value[0]]).trigger("chosen:updated").change();
							}
						} else if (mapFieldElement.length == 0) {
							$("<input type='hidden'/>").attr("name", key).attr("value", response[value[0]]).appendTo(formElement);
						} else {
							mapFieldElement.val(response[value[0]]);
						}
						var mapFieldDisplayElement = formElement.find('input[name="' + key + '_display"]');
						if (mapFieldDisplayElement.length > 0) {
							mapFieldDisplayElement.val(response[value[0] + '_label']).attr('readonly', true);
							var referenceModulesList = formElement.find('#' + thisInstance.moduleName + '_editView_fieldName_' + key + '_dropDown');
							if (referenceModulesList.length > 0 && value[1]) {
								referenceModulesList.val(value[1]).change().trigger("chosen:updated");
							}
							thisInstance.setReferenceFieldValue(mapFieldDisplayElement.closest('.fieldValue'), {
								name: response[value[0] + '_label'],
								id: response[value[0]]
							});
						}
					}
				});
			});
		}
	},
	getRelationOperation: function () {
		if (this.relationOperation === '') {
			var relationOperation = jQuery('[name="relationOperation"]');
			if (relationOperation.length) {
				this.relationOperation = relationOperation.val();
			} else {
				this.relationOperation = false;
			}
		}
		return this.relationOperation;
	},
	getMappingValuesFromUrl: function (key) {
		var relationOperation = this.getRelationOperation();
		if (relationOperation) {
			return app.getUrlVar(key);
		}
		return false;
	},
	proceedRegisterEvents: function () {
		if (jQuery('.recordEditView').length > 0) {
			return true;
		} else {
			return false;
		}
	},
	treePopupRegisterEvent: function (container) {
		var thisInstance = this;
		container.on("click", '.treePopup', function (e) {
			thisInstance.openTreePopUp(e);
		});
	},
	/**
	 * Function which will register reference field clear event
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerClearTreeSelectionEvent: function (container) {
		var thisInstance = this;
		container.find('.clearTreeSelection').on('click', function (e) {
			thisInstance.clearFieldValue(jQuery(e.currentTarget));
			e.preventDefault();
		})
	},
	openTreePopUp: function (e) {
		var thisInstance = this;
		var parentElem = jQuery(e.target).closest('.fieldValue');
		var form = jQuery(e.target).closest('form');
		var params = {};
		var moduleName = jQuery('input[name="module"]', form).val();
		var sourceFieldElement = jQuery('input[class="sourceField"]', parentElem);
		var sourceFieldDisplay = sourceFieldElement.attr('name') + "_display";
		var fieldDisplayElement = jQuery('input[name="' + sourceFieldDisplay + '"]', parentElem);
		var sourceRecordElement = jQuery('input[name="record"]');
		var sourceRecordId = '';
		if (sourceRecordElement.length > 0) {
			sourceRecordId = sourceRecordElement.val();
		}
		urlOrParams = 'module=' + moduleName + '&view=TreePopup&template=' + sourceFieldElement.data('treetemplate') + '&src_field=' + sourceFieldElement.attr('name') + '&src_record=' + sourceRecordId + '&multiple=' + sourceFieldElement.data('multiple');
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(urlOrParams, function (data) {
			var responseData = JSON.parse(data);
			var ids = responseData.id.split(',');
			$.each(ids, function (index, value) {
				ids[index] = 'T' + value;
			});
			ids.join();
			sourceFieldElement.val(ids);
			fieldDisplayElement.val(responseData.name).attr('readonly', true);
		});
	},
	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerTreeAutoCompleteFields: function (container) {
		var thisInstance = this;
		container.find('input.treeAutoComplete').autocomplete({
			'delay': '600',
			'minLength': '3',
			'source': function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var searchValue = request.term;
				var parentElem = inputElement.closest('.fieldValue');
				var sourceFieldElement = jQuery('input[class="sourceField"]', parentElem);
				var allValues = sourceFieldElement.data('allvalues');
				var reponseDataList = new Array();
				for (var id in allValues) {
					var name = allValues[id][0];
					if (name.toLowerCase().indexOf(searchValue) >= 0) {
						var parent = allValues[id][1];
						var label = '';
						if (parent != '')
							var label = '(' + allValues[parent][0] + ') ';
						label = label + name;
						reponseDataList.push({"label": label, "value": name, "id": id});
					}
				}
				if (reponseDataList.length <= 0) {
					jQuery(inputElement).val('');
					reponseDataList.push({
						'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
						'type': 'no results'
					});
				}
				response(reponseDataList);
			},
			'select': function (event, ui) {
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
					return false;
				}
				selectedItemData.name = selectedItemData.value;
				var element = jQuery(this);
				var parentElem = element.closest('.fieldValue');
				var sourceField = parentElem.find('input[class="sourceField"]');
				var sourceFieldDisplay = sourceField.attr('name') + "_display";
				var fieldDisplayElement = jQuery('input[name="' + sourceFieldDisplay + '"]', parentElem);

				sourceField.val(selectedItemData.id);
				fieldDisplayElement.val(selectedItemData.label).attr('readonly', true);
			},
			'change': function (event, ui) {
				var element = jQuery(this);
			},
			'open': function (event, ui) {
				//To Make the menu come up in the case of quick create
				jQuery(this).data('ui-autocomplete').menu.element.css('z-index', '100001');

			}
		});
	},
	referenceModulePopupRegisterEvent: function (container) {
		var thisInstance = this;
		container.on("click", '.relatedPopup', function (e) {
			thisInstance.openPopUp(e);
		});
		container.find('.referenceModulesList').chosen().change(function (e) {
			var element = jQuery(e.currentTarget);
			var parentElem = element.closest('.fieldValue');
			var popupReferenceModule = element.val();
			var referenceModuleElement = jQuery('input[name="popupReferenceModule"]', parentElem);
			var prevSelectedReferenceModule = referenceModuleElement.val();
			referenceModuleElement.val(popupReferenceModule);

			//If Reference module is changed then we should clear the previous value
			if (prevSelectedReferenceModule != popupReferenceModule) {
				parentElem.find('.clearReferenceSelection').trigger('click');
			}
		});
	},
	getReferencedModuleName: function (parenElement) {
		return jQuery('input[name="popupReferenceModule"]', parenElement).val();
	},
	searchModuleNames: function (params) {
		var aDeferred = jQuery.Deferred();

		if (typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if (typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		AppConnector.request(params).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (error) {
					aDeferred.reject();
				}
		)
		return aDeferred.promise();
	},
	/**
	 * Function to get reference search params
	 */
	getReferenceSearchParams: function (element) {
		var tdElement = jQuery(element).closest('.fieldValue');
		var params = {};
		var searchModule = this.getReferencedModuleName(tdElement);
		params.search_module = searchModule;
		return params;
	},
	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerAutoCompleteFields: function (container) {
		var thisInstance = this;
		container.find('input.autoComplete').autocomplete({
			delay: '600',
			minLength: '3',
			source: function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var searchValue = request.term;
				var params = thisInstance.getReferenceSearchParams(inputElement);
				params.search_value = searchValue;
				//params.parent_id = app.getRecordId();
				//params.parent_module = app.getModuleName();
				thisInstance.searchModuleNames(params).then(function (data) {
					var reponseDataList = new Array();
					var serverDataFormat = data.result
					if (serverDataFormat.length <= 0) {
						jQuery(inputElement).val('');
						serverDataFormat = new Array({
							'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type': 'no results'
						});
					}
					for (var id in serverDataFormat) {
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			select: function (event, ui) {
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
					return false;
				}
				selectedItemData.name = selectedItemData.value;
				var element = jQuery(this);
				var tdElement = element.closest('.fieldValue');
				thisInstance.setReferenceFieldValue(tdElement, selectedItemData);

				var sourceField = tdElement.find('input[class="sourceField"]').attr('name');
				var fieldElement = tdElement.find('input[name="' + sourceField + '"]');

				fieldElement.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent, {'data': selectedItemData});
			},
			change: function (event, ui) {
				var element = jQuery(this);
				//if you dont have readonly attribute means the user didnt select the item
				if (element.attr('readonly') == undefined) {
					element.closest('.fieldValue').find('.clearReferenceSelection').trigger('click');
				}
			},
			open: function (event, ui) {
				//To Make the menu come up in the case of quick create
				jQuery(this).data('ui-autocomplete').menu.element.css('z-index', '100001');
			}
		});
	},
	/**
	 * Function which will register reference field clear event
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerClearReferenceSelectionEvent: function (container) {
		var thisInstance = this;
		container.on('click', '.clearReferenceSelection', function (e) {
			var element = jQuery(e.currentTarget);
			thisInstance.clearFieldValue(element);
			element.closest('.fieldValue').find('.sourceField').trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
			e.preventDefault();
		})
	},
	clearFieldValue: function (element) {
		var thisInstance = this;
		var fieldValueContener = element.closest('.fieldValue');
		var fieldNameElement = fieldValueContener.find('.sourceField');
		var fieldName = fieldNameElement.attr('name');
		var referenceModule = fieldValueContener.find('input[name="popupReferenceModule"]').val();
		var formElement = fieldValueContener.closest('form');

		fieldNameElement.val('');
		fieldValueContener.find('#' + fieldName + '_display').removeAttr('readonly').val('');

		var mappingRelatedField = this.getMappingRelatedField(fieldName, referenceModule, formElement);
		$.each(mappingRelatedField, function (key, value) {
			var mapFieldElement = formElement.find('[name="' + key + '"]');
			if (mapFieldElement.is('select')) {
				mapFieldElement.val(mapFieldElement.find("option:first").val()).trigger("chosen:updated").change();
			} else {
				mapFieldElement.val('');
			}
			var mapFieldDisplayElement = formElement.find('input[name="' + key + '_display"]');
			if (mapFieldDisplayElement.length > 0) {
				mapFieldDisplayElement.val('').attr('readonly', false);
				var referenceModulesList = formElement.find('#' + thisInstance.moduleName + '_editView_fieldName_' + key + '_dropDown');
				if (referenceModulesList.length > 0 && value[1]) {
					referenceModulesList.val(referenceModulesList.find("option:first").val()).change().trigger("chosen:updated");
				}
			}
		});
	},
	/**
	 * Function which will register event to prevent form submission on pressing on enter
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerPreventingEnterSubmitEvent: function (container) {
		container.on('keypress', function (e) {
			//Stop the submit when enter is pressed in the form
			var currentElement = jQuery(e.target);
			if (e.which == 13 && (!currentElement.is('textarea'))) {
				e.preventDefault();
			}
		})
	},
	/**
	 * Function which will give you all details of the selected record
	 * @params - an Array of values like {'record' : recordId, 'source_module' : searchModule, 'selectedName' : selectedRecordName}
	 */
	getRecordDetails: function (params) {
		var aDeferred = jQuery.Deferred();
		var url = "index.php?module=" + app.getModuleName() + "&action=GetData&record=" + params['record'] + "&source_module=" + params['source_module'];
		if (app.getParentModuleName() == 'Settings') {
			url += '&parent=Settings';
		}
		AppConnector.request(url).then(
				function (data) {
					if (data['success']) {
						aDeferred.resolve(data);
					} else {
						aDeferred.reject(data['message']);
					}
				},
				function (error) {
					aDeferred.reject();
				}
		)
		return aDeferred.promise();
	},
	registerTimeFields: function (container) {
		app.registerEventForClockPicker();
		app.registerEventForDatePickerFields(container);
	},
	referenceCreateHandler: function (container) {
		var thisInstance = this;
		var postQuickCreateSave = function (data) {
			var params = {};
			params.name = data.result._recordLabel;
			params.id = data.result._recordId;
			thisInstance.setReferenceFieldValue(container, params);
		}
		var params = {callbackFunction: postQuickCreateSave};
		if (app.getViewName() === 'Edit' && !app.getRecordId()) {
			var formElement = this.getForm();
			var formData = formElement.serializeFormData();
			for (var i in formData) {
				if (!formData[i] || jQuery.inArray(i, ['__vtrftk', 'action']) != -1) {
					delete formData[i];
				}
			}
			params.data = {};
			params.data.sourceRecordData = formData;
		}
		var referenceModuleName = this.getReferencedModuleName(container);
		Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, params);
	},
	/**
	 * Function which will register event for create of reference record
	 * This will allow users to create reference record from edit view of other record
	 */
	registerReferenceCreate: function (container) {
		var thisInstance = this;
		container.on('click', '.createReferenceRecord', function (e) {
			var element = jQuery(e.currentTarget);
			var controlElementDiv = element.closest('.fieldValue');
			thisInstance.referenceCreateHandler(controlElementDiv);
		})
	},
	addressFieldsMapping: [
		'buildingnumber',
		'localnumber',
		'addresslevel1',
		'addresslevel2',
		'addresslevel3',
		'addresslevel4',
		'addresslevel5',
		'addresslevel6',
		'addresslevel7',
		'addresslevel8',
		'pobox'
	],
	addressFieldsMappingBlockID: {
		'LBL_ADDRESS_INFORMATION': 'a',
		'LBL_ADDRESS_MAILING_INFORMATION': 'b',
		'LBL_ADDRESS_DELIVERY_INFORMATION': 'c'
	},
	addressFieldsData: false,
	/**
	 * Function to register event for copying addresses
	 */
	registerEventForCopyAddress: function () {
		var thisInstance = this;
		var formElement = this.getForm();
		var account_id = false;
		var contact_id = false;
		var lead_id = false;
		var vendor_id = false;
		jQuery("#EditView .blockContainer:not(.inventoryHeader):not(.inventoryItems) .fieldValue, #EditView .blockContainer:not(.inventoryHeader):not(.inventoryItems) .fieldLabel").each(function (index) {
			var block = $(this);
			var referenceModulesList = false;
			var relatedField = block.find('[name="popupReferenceModule"]').val();
			if (relatedField == 'Accounts') {
				account_id = block.find('.sourceField').attr("name");
			}
			if (relatedField == 'Contacts') {
				contact_id = block.find('.sourceField').attr("name");
			}
			if (relatedField == 'Leads') {
				lead_id = block.find('.sourceField').attr("name");
			}
			if (relatedField == 'Vendors') {
				vendor_id = block.find('.sourceField').attr("name");
			}
			referenceModulesList = block.find('.referenceModulesList');
			if (referenceModulesList.length > 0) {
				$.each(referenceModulesList.find('option'), function (key, data) {
					if (data.value == 'Accounts') {
						account_id = block.find('.sourceField').attr("name");
					}
					if (data.value == 'Contacts') {
						contact_id = block.find('.sourceField').attr("name");
					}
					if (data.value == 'Leads') {
						lead_id = block.find('.sourceField').attr("name");
					}
					if (data.value == 'Vendors') {
						vendor_id = block.find('.sourceField').attr("name");
					}
				});
			}
		});

		if (account_id == false) {
			jQuery(".copyAddressFromAccount").addClass('hide');
		} else {
			jQuery('.copyAddressFromAccount').on('click', function (e) {
				var element = jQuery(this);
				var block = element.closest('.blockContainer');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = jQuery('[name="' + account_id + '"]').val();

				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = jQuery('#' + account_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Accounts"
					}

					thisInstance.copyAddressDetails(from, to, data, element.closest('.blockContainer'));
					element.attr('checked', 'checked');
				}
			})
		}
		if (contact_id == false) {
			jQuery(".copyAddressFromContact").addClass('hide');
		} else {
			jQuery('.copyAddressFromContact').on('click', function (e) {
				var element = jQuery(this);
				var block = element.closest('.blockContainer');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = jQuery('[name="' + contact_id + '"]').val();
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_CONTACT_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = jQuery('#' + contact_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Contacts"
					}
					thisInstance.copyAddressDetails(from, to, data, element.closest('.blockContainer'));
					element.attr('checked', 'checked');
				}
			})
		}
		if (lead_id == false) {
			jQuery(".copyAddressFromLead").addClass('hide');
		} else {
			jQuery('.copyAddressFromLead').on('click', function (e) {
				var element = jQuery(this);
				var block = element.closest('.blockContainer');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = jQuery('[name="' + lead_id + '"]').val();
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_LEAD_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = jQuery('#' + lead_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Leads"
					}
					thisInstance.copyAddressDetails(from, to, data, element.closest('.blockContainer'));
					element.attr('checked', 'checked');
				}
			})
		}
		if (vendor_id == false) {
			jQuery(".copyAddressFromVendor").addClass('hide');
		} else {
			jQuery('.copyAddressFromVendor').on('click', function (e) {
				var element = jQuery(this);
				var block = element.closest('.blockContainer');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = jQuery('[name="' + vendor_id + '"]').val();
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_VENDOR_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = jQuery('#' + vendor_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Vendors"
					}
					thisInstance.copyAddressDetails(from, to, data, element.closest('.blockContainer'));
					element.attr('checked', 'checked');
				}
			})
		}

		$("#EditView .blockContainer").each(function (index) {
			var hideCopyAddressLabel = true;
			$(this).find(".adressAction button").each(function (index) {
				if ($(this).hasClass("hide") == false) {
					hideCopyAddressLabel = false;
				}
			});
			if (hideCopyAddressLabel) {
				$(this).find(".copyAddressLabel").addClass('hide');
			}
		});
		jQuery('.copyAddressFromMain').on('click', function (e) {
			var element = jQuery(this);
			var block = element.closest('.blockContainer');
			var from = element.data('label');
			var to = block.data('label');
			thisInstance.copyAddress(from, to, false, false);
		})
		jQuery('.copyAddressFromMailing').on('click', function (e) {
			var element = jQuery(this);
			var block = element.closest('.blockContainer');
			var from = element.data('label');
			var to = block.data('label');
			thisInstance.copyAddress(from, to, false, false);
		})
		jQuery('.copyAddressFromDelivery').on('click', function (e) {
			var element = jQuery(this);
			var block = element.closest('.blockContainer');
			var from = element.data('label');
			var to = block.data('label');
			thisInstance.copyAddress(from, to, false, false);
		})
	},
	/**
	 * Function which will copy the address details
	 */
	copyAddressDetails: function (from, to, data, container) {
		var thisInstance = this;
		var sourceModule = data['source_module'];
		var noAddress = true;
		var errorMsg;
		thisInstance.getRecordDetails(data).then(
				function (data) {
					var response = data['result'];
					thisInstance.addressFieldsData = response['data'];
					thisInstance.copyAddress(from, to, true, sourceModule);
				},
				function (error, err) {

				}
		);
	},
	/**
	 * Function to copy address between fields
	 * @param strings which accepts value as either odd or even
	 */
	copyAddress: function (fromLabel, toLabel, reletedRecord, sourceModule) {
		var status = false;
		var thisInstance = this;
		var formElement = this.getForm();
		var addressMapping = this.addressFieldsMapping;
		var BlockIds = this.addressFieldsMappingBlockID;

		from = BlockIds[fromLabel];
		if (reletedRecord === false || sourceModule === false)
			from = BlockIds[fromLabel];
		to = BlockIds[toLabel];
		for (var key in addressMapping) {
			var nameElementFrom = addressMapping[key] + from;
			var nameElementTo = addressMapping[key] + to;
			if (reletedRecord) {
				var fromElement = thisInstance.addressFieldsData[nameElementFrom];
				var fromElementLable = thisInstance.addressFieldsData[nameElementFrom + '_label'];
			} else {
				var fromElement = formElement.find('[name="' + nameElementFrom + '"]').val();
				var fromElementLable = formElement.find('[name="' + nameElementFrom + '_display"]').val();
			}
			var toElement = formElement.find('[name="' + nameElementTo + '"]');
			var toElementLable = formElement.find('[name="' + nameElementTo + '_display"]');
			if (fromElement != '' && fromElement != '0' && fromElement != undefined) {
				if (toElementLable.length > 0)
					toElementLable.attr('readonly', true);
				status = true;
				toElement.val(fromElement);
				toElementLable.val(fromElementLable);
			} else {
				toElement.attr('readonly', false);
			}
		}
		if (status == false) {
			if (sourceModule == "Accounts") {
				errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
			} else if (sourceModule == "Contacts") {
				errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
			} else {
				errorMsg = 'JS_DOES_NOT_HAVE_AN_ADDRESS';
			}
			Vtiger_Helper_Js.showPnotify(app.vtranslate(errorMsg));
		}
	},
	registerReferenceSelectionEvent: function (container) {
		var thisInstance = this;
		var relategField = container.find("input[name*='addresslevel']");
		relategField.on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
			var blockContainer = jQuery(e.currentTarget).closest('.blockContainer');
			thisInstance.copyAddressDetailsRef(data, blockContainer);
		});
	},
	copyAddressDetailsRef: function (data, container) {
		var thisInstance = this;
		thisInstance.getRecordDetails(data).then(
				function (data) {
					var response = data['result'];
					thisInstance.mapAddressDetails(response['data'], container);
				},
				function (error, err) {

				});
	},
	mapAddressDetails: function (result, container) {
		for (var key in result) {
			if (key.indexOf("addresslevel") != -1) {

				if (container.find('[name="' + key + '"]').length != 0) {
					container.find('[name="' + key + '"]').val(result[key]);
					container.find('[name="' + key + '"]').attr('readonly', true);
					container.find('[name="' + key + '_display"]').val(result[key + '_label']);
					container.find('[name="' + key + '_display"]').attr('readonly', true);
				}
				if (container.find('[name="' + key + 'a"]').length != 0 && container.find('[name="' + key + 'a"]').val() == 0 && result[key] != 0) {
					container.find('[name="' + key + 'a"]').val(result[key]);
					container.find('[name="' + key + 'a"]').attr('readonly', true);
					container.find('[name="' + key + 'a_display"]').val(result[key + '_label']);
					container.find('[name="' + key + 'a_display"]').attr('readonly', true);
				}
				if (container.find('[name="' + key + 'b"]').length != 0 && container.find('[name="' + key + 'b"]').val() == 0 && result[key] != 0) {
					container.find('[name="' + key + 'b"]').val(result[key]);
					container.find('[name="' + key + 'b"]').attr('readonly', true);
					container.find('[name="' + key + 'b_display"]').val(result[key + '_label']);
					container.find('[name="' + key + 'b_display"]').attr('readonly', true);
				}
				if (container.find('[name="' + key + 'c"]').length != 0 && container.find('[name="' + key + 'c"]').val() == 0 && result[key] != 0) {
					container.find('[name="' + key + 'c"]').val(result[key]);
					container.find('[name="' + key + 'c"]').attr('readonly', true);
					container.find('[name="' + key + 'c_display"]').val(result[key + '_label']);
					container.find('[name="' + key + 'c_display"]').attr('readonly', true);
				}
			}
		}
	},
	registerMaskFields: function (container) {
		var thisInstance = this;
		container.find(":input").inputmask();
	},
	/**
	 * Function to register event for image delete
	 */
	registerEventForImageDelete: function () {
		var formElement = this.getForm();
		var recordId = formElement.find('input[name="record"]').val();
		formElement.find('.imageDelete').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var parentTd = element.closest('.fieldValue');
			var imageUploadElement = parentTd.find('[name="imagename[]"]');
			var fieldInfo = imageUploadElement.data('fieldinfo');
			var mandatoryStatus = fieldInfo.mandatory;
			var imageId = element.closest('div').find('img').data().imageId;
			element.closest('div').remove();
			var exisitingImages = parentTd.find('[name="existingImages"]');
			if (exisitingImages.length < 1 && mandatoryStatus) {
				formElement.validationEngine('detach');
				imageUploadElement.attr('data-validation-engine', 'validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
				formElement.validationEngine('attach');
			}

			if (formElement.find('[name=imageid]').length != 0) {
				var imageIdValue = JSON.parse(formElement.find('[name=imageid]').val());
				imageIdValue.push(imageId);
				formElement.find('[name=imageid]').val(JSON.stringify(imageIdValue));
			} else {
				var imageIdJson = [];
				imageIdJson.push(imageId);
				formElement.append('<input type="hidden" name="imgDeleted" value="true" />');
				formElement.append('<input type="hidden" name="imageid" value="' + JSON.stringify(imageIdJson) + '" />');
			}
		});
	},
	triggerDisplayTypeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if (widthType) {
			var elements = jQuery('#EditView').find('.fieldValue,.fieldLabel');
			elements.addClass(widthType);
		}
	},
	registerSubmitEvent: function () {
		var editViewForm = this.getForm();
		editViewForm.submit(function (e) {
			//Form should submit only once for multiple clicks also
			if (typeof editViewForm.data('submit') != "undefined") {
				return false;
			} else {
				document.progressLoader = jQuery.progressIndicator({
					'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});

				var module = jQuery(e.currentTarget).find('[name="module"]').val();
				if (editViewForm.validationEngine('validate')) {
					//Once the form is submiting add data attribute to that form element
					editViewForm.data('submit', 'true');
					//on submit form trigger the recordPreSave event
					var recordPreSaveEvent = jQuery.Event(Vtiger_Edit_Js.recordPreSave);
					editViewForm.trigger(recordPreSaveEvent, {'value': 'edit'});
					if (recordPreSaveEvent.isDefaultPrevented()) {
						//If duplicate record validation fails, form should submit again
						document.progressLoader.progressIndicator({'mode': 'hide'});
						editViewForm.removeData('submit');
						e.preventDefault();
					}
				} else {
					//If validation fails, form should submit again
					document.progressLoader.progressIndicator({'mode': 'hide'});
					editViewForm.removeData('submit');
					app.formAlignmentAfterValidation(editViewForm);
				}
			}
		});
	},
	/*
	 * Function to check the view permission of a record after save
	 */
	registerRecordPreSaveEventEvent: function (form) {
		if (Vtiger_Edit_Js.SaveResultInstance == false) {
			Vtiger_Edit_Js.SaveResultInstance = new SaveResult();
		}
		var formElement = this.getForm();
		var formData = formElement.serializeFormData();
		if (Vtiger_Edit_Js.SaveResultInstance.recordValue == false) {
			Vtiger_Edit_Js.SaveResultInstance.loadFormData(formData);
		}
		form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			if (Vtiger_Edit_Js.SaveResultInstance.checkData(form.serializeFormData(), form) == false) {
				e.preventDefault();
			}
		});
	},
	/**
	 * Function to register event for setting up picklistdependency
	 * for a module if exist on change of picklist value
	 */
	registerEventForPicklistDependencySetup: function (container) {
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]', container);
		if (picklistDependcyElemnt.length <= 0) {
			return;
		}
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if (sourcePicklists.length <= 0) {
			return;
		}

		var sourcePickListNames = [];
		for (var i = 0; i < sourcePicklists.length; i++) {
			sourcePickListNames.push('[name="' + sourcePicklists[i] + '"]');
		}
		sourcePickListNames = sourcePickListNames.join(',');
		var sourcePickListElements = container.find(sourcePickListNames);

		sourcePickListElements.on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var sourcePicklistname = currentElement.attr('name');

			var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
			var selectedValue = currentElement.val();
			var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
			var picklistmap = configuredDependencyObject["__DEFAULT__"];

			if (typeof targetObjectForSelectedSourceValue == 'undefined') {
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if (typeof targetPickListMap == "undefined") {
					targetPickListMap = targetPickListValues;
				}
				var targetPickList = jQuery('[name="' + targetPickListName + '"]', container);
				if (targetPickList.length <= 0) {
					return;
				}

				var listOfAvailableOptions = targetPickList.data('availableOptions');
				if (typeof listOfAvailableOptions == "undefined") {
					listOfAvailableOptions = jQuery('option', targetPickList);
					targetPickList.data('available-options', listOfAvailableOptions);
				}

				var targetOptions = new jQuery();
				var optionSelector = [];
				optionSelector.push('');
				for (var i = 0; i < targetPickListMap.length; i++) {
					optionSelector.push(targetPickListMap[i]);
				}

				jQuery.each(listOfAvailableOptions, function (i, e) {
					var picklistValue = jQuery(e).val();
					if (jQuery.inArray(picklistValue, optionSelector) != -1) {
						targetOptions = targetOptions.add(jQuery(e));
					}
				})
				var targetPickListSelectedValue = '';
				var targetPickListSelectedValue = targetOptions.filter('[selected]').val();
				targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("chosen:updated");
			})
		});

		//To Trigger the change on load
		sourcePickListElements.trigger('change');
	},
	registerLeavePageWithoutSubmit: function (form) {
		InitialFormData = form.serialize();
		window.onbeforeunload = function (e) {
			if (InitialFormData != form.serialize() && form.data('submit') != "true") {
				return app.vtranslate("JS_CHANGES_WILL_BE_LOST");
			}
		};
	},
	stretchCKEditor: function () {
		var row = jQuery('.ckEditorSource').parents('.fieldRow');
		var td = jQuery('.ckEditorSource').parent();
		jQuery(row).find('.fieldLabel').remove();
		jQuery(td).removeClass('col-md-10');
		jQuery(td).addClass('col-md-12');
	},
	/**
	 * Function to register event for ckeditor for description field
	 */
	registerEventForCkEditor: function () {
		var form = this.getForm();
		var thisInstance = this;
		$.each(form.find('.ckEditorSource'), function (key, data) {
			thisInstance.loadCkEditorElement(jQuery(data));
		});
	},
	loadCkEditorElement: function (noteContentElement) {
		var customConfig = {};
		if (noteContentElement.is(':visible')) {
			if (noteContentElement.hasClass("ckEditorBasic")) {
				customConfig.toolbar = 'Min';
			}
			if (noteContentElement.hasClass("ckEditorSmall")) {
				customConfig.height = '5em';
			}
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(noteContentElement, customConfig);
		}
	},
	registerHelpInfo: function () {
		var form = this.getForm();
		app.showPopoverElementView(form.find('.HelpInfoPopover'));
	},
	registerBlockAnimationEvent: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getForm();
		detailContentsHolder.on('click', '.blockHeader', function (e) {
			if (jQuery(e.target).is('input') || jQuery(e.target).is('button') || jQuery(e.target).parents().is('button')) {
				return false;
			}
			var currentTarget = jQuery(e.currentTarget).find('.blockToggle').not('.hide');
			var blockId = currentTarget.data('id');
			var closestBlock = currentTarget.closest('.blockContainer');
			var bodyContents = closestBlock.find('.blockContent');
			var data = currentTarget.data();
			var module = app.getModuleName();
			var hideHandler = function () {
				bodyContents.addClass('hide');
				app.cacheSet(module + '.' + blockId, 0)
			}
			var showHandler = function () {
				bodyContents.removeClass('hide');
				thisInstance.registerEventForCkEditor(bodyContents);
				app.cacheSet(module + '.' + blockId, 1)
			}
			if (data.mode == 'show') {
				hideHandler();
				currentTarget.addClass('hide');
				closestBlock.find('[data-mode="hide"]').removeClass('hide');
			} else {
				showHandler();
				currentTarget.addClass('hide');
				closestBlock.find("[data-mode='show']").removeClass('hide');
			}
		});

	},
	registerBlockStatusCheckOnLoad: function () {
		var blocks = this.getForm().find('.blockContainer');
		var module = app.getModuleName();
		blocks.each(function (index, block) {
			var currentBlock = jQuery(block);
			var headerAnimationElement = currentBlock.find('.blockToggle').not('.hide');
			var bodyContents = currentBlock.find('.blockContent')
			var blockId = headerAnimationElement.data('id');
			var cacheKey = module + '.' + blockId;
			var value = app.cacheGet(cacheKey, null);
			if (value != null) {
				if (value == 1) {
					headerAnimationElement.addClass('hide');
					currentBlock.find("[data-mode='show']").removeClass('hide');
					bodyContents.removeClass('hide');
				} else {
					headerAnimationElement.addClass('hide');
					currentBlock.find("[data-mode='hide']").removeClass('hide');
					bodyContents.addClass('hide');
				}
			}
		});
	},
	getDataFromOG: function (request, apiData) {
		var thisInstance = this;

		if (apiData["opencage_data"]) {
			return  jQuery.ajax({
				url: apiData["opencage_data"].geoCodeURL,
				data: {
					format: "json",
					q: request.term,
					pretty: '1',
					key: apiData["opencage_data"].geoCodeKey
				},
				success: function (data, textStatus, jqXHR) {
					if (data.results.length) {
						thisInstance.addressDataOG = jQuery.map(data.results, function (item) {
							return {
								label: item.formatted,
								source: 'opencage_geocoder',
								source_label: 'OpenCage Geocoder',
								value: item.components.road,
								components: item.components
							}
						});
					}
				}
			})
		}

		return [];
	},
	getDataFromGM: function (request, apiData) {
		var thisInstance = this;

		if (apiData["google_map_api"]) {
			return jQuery.ajax({
				url: apiData["google_map_api"].geoCodeURL,
				data: {
					address: request.term,
					key: apiData["google_map_api"].geoCodeKey
				},
				success: function (addressData) {

					if (0 < addressData.results.length) {
						var result = addressData.results[0].geometry.location;

						jQuery.ajax({
							url: apiData["google_map_api"].geoCodeURL,
							data: {
								latlng: result.lat + "," + result.lng,
								key: apiData["google_map_api"].geoCodeKey
							},
							success: function (data, textStatus, jqXHR) {
								thisInstance.addressDataGM = jQuery.map(data.results, function (item) {
									return {
										label: item.formatted_address,
										source: 'google_geocoding',
										source_label: 'Google Geocoding',
										value: item.formatted_address,
										components: thisInstance.mappingAddressDataFromGoogle(item.address_components)
									}
								})
							}
						})
					}
				}
			})
		}

		return [];
	},
	mappingAddressDataFromGoogle: function (address) {

		var data = {}

		for (var key in address) {
			var types = address[key]['types'];

			if ('route' === types[0]) {
				data.road = address[key]['long_name'];
			}

			if ('street_number' === types[0]) {
				var numbers = address[key]['long_name'];
				if (numbers.indexOf('/' > -1)) {
					var tab = numbers.split('/');

					data.house_number = tab[0];
					data.local_number = tab[1];

				} else {
					data.house_number = address[key]['long_name'];
				}
			}

			if ('country' === types[0] && 'political' === types[1]) {
				data.country = address[key]['long_name'];
			}

			if ('administrative_area_level_1' === types[0] && 'political' === types[1]) {
				data.state = address[key]['long_name'];
			}

			if ('administrative_area_level_2' === types[0] && 'political' === types[1]) {
				data.powiat = address[key]['long_name'];
			}

			if ('sublocality_level_1' === types[0] && 'sublocality' === types[1] && 'political' === types[2]) {
				data.region_city = address[key]['long_name'];
			}

			if ('postal_code' === types[0]) {
				data.postcode = address[key]['long_name'];
			}

			if ('locality' === types[0] && 'political' === types[1]) {
				data.city = address[key]['long_name'];
			}

		}

		return data;
	},
	registerApiAddress: function () {
		var thisInstance = this;
		var apiElement = jQuery('[name="apiAddress"]');
		var apiData = [];

		jQuery(apiElement).each(function (index, item) {
			var apiName = jQuery(item).data('api-name');
			var info = {
				geoCodeURL: jQuery(item).data('url'),
				geoCodeKey: jQuery(item).val()
			}

			apiData[apiName] = info;
			apiData["minLookupLenght"] = jQuery(item).data('lenght');
			apiData["max_num"] = jQuery(item).data('max-num');
		});

		if (!apiData) {
			return false;
		}

		jQuery('.api_address_autocomplete').each(function () {
			jQuery(this).autocomplete({
				source: function (request, response) {
					jQuery.when(
							thisInstance.getDataFromOG(request, apiData),
							thisInstance.getDataFromGM(request, apiData)

							).then(function (og, gm) {

						var result = thisInstance.addressDataOG.concat(thisInstance.addressDataGM);

						response(result.slice(0, apiData['max_num']));

					}).fail(function (e) {
						response([{label: app.vtranslate('An error has occurred. No results.'), value: ''}]);
					});
				},
				minLength: apiData.minLookupLenght,
				select: function (event, ui) {
					for (var key in ui.item.components) {
						var addressType = thisInstance.addressFieldsMappingFromApi[key];
						jQuery(this).parents('.blockContainer').find('[name^="' + addressType + '"]').val(ui.item.components[key]);
					}
				}
			}).data("ui-autocomplete")._renderItem = function (ul, item) {
				return jQuery("<li>")
						.data("item.autocomplete", item)
						.append('<a><img style="width: 24px; height: 24px;" class="alignMiddle" src="layouts/basic/skins/images/'
								+ item.source + '.png" title="' + item.source_label + '" alt="' + item.source_label + '">' + item.label + "</a>")
						.appendTo(ul);
			};
		});
	},
	addressFieldsMappingFromApi: {
		'house_number': 'buildingnumber',
		'local_number': 'localnumber',
		'country': 'addresslevel1',
		'state': 'addresslevel2',
		'powiat': 'addresslevel3',
		'county': 'addresslevel4',
		'city': 'addresslevel5',
		'region_city': 'addresslevel6',
		'postcode': 'addresslevel7',
		'road': 'addresslevel8',
		'village': 'addresslevel5'
	},
	setEnabledFields: function (element) {
		var fieldValue = element.closest('.fieldValue');
		var fieldName = fieldValue.find('input.sourceField').attr('name');
		var fieldDisplay = fieldValue.find('#' + fieldName + '_display');
		fieldValue.find('button').removeAttr('disabled');
		if (fieldDisplay.val() == '') {
			fieldValue.find('input').removeAttr('readonly');
		}
		fieldValue.find('.referenceModulesListGroup').removeClass('hide');
		var placeholder = fieldDisplay.attr('placeholderDisabled');
		fieldDisplay.removeAttr('placeholderDisabled');
		fieldDisplay.attr('placeholder', placeholder);
		fieldValue.find('.referenceModulesList').attr('required', 'required');
	},
	setDisabledFields: function (element) {
		var fieldValue = element.closest('.fieldValue');
		var fieldName = fieldValue.find('input.sourceField').attr('name');
		var fieldDisplay = fieldValue.find('#' + fieldName + '_display');
		fieldValue.find('input').attr('readonly', 'readonly');
		fieldValue.find('button').attr('disabled', 'disabled');
		fieldValue.find('.referenceModulesListGroup').addClass('hide');
		var placeholder = fieldDisplay.attr('placeholder');
		fieldDisplay.removeAttr('placeholder');
		fieldDisplay.attr('placeholderDisabled', placeholder);
		fieldValue.find('.referenceModulesList').removeAttr('required');
	},
	getMappingRelatedField: function (sourceField, sourceFieldModule, container) {
		var mappingRelatedField = container.find('input[name="mappingRelatedField"]').val();
		var mappingRelatedModule = mappingRelatedField ? JSON.parse(mappingRelatedField) : [];
		if (typeof mappingRelatedModule[sourceField] != 'undefined' && typeof mappingRelatedModule[sourceField][sourceFieldModule] != 'undefined')
			return mappingRelatedModule[sourceField][sourceFieldModule];
		return [];
	},
	registerValidationsFields: function (container) {
		var thisInstance = this;
		var params = app.validationEngineOptionsForRecord;
		container.validationEngine(params);
	},
	checkReferencesField: function (container, clear) {
		var thisInstance = this;
		var activeProcess = false, activeSubProcess = false;
		container.find('input[data-fieldtype="referenceLink"]').each(function (index, element) {
			element = $(element);
			var t = true;
			if (element.closest('.tab-pane').length > 0) {
				t = false;
				if (element.closest('.tab-pane.active').length > 0) {
					t = true;
				}
			}
			var referenceLink = element.val();
			if (t && referenceLink != '' && referenceLink != '0') {
				activeProcess = true;
			}
		});
		container.find('input[data-fieldtype="referenceProcess"]').each(function (index, element) {
			element = $(element);
			if (activeProcess) {
				thisInstance.setEnabledFields(element);
			} else {
				if (clear) {
					thisInstance.clearFieldValue(element);
				}
				thisInstance.setDisabledFields(element);
			}

			var t = true;
			if (element.closest('.tab-pane').length > 0) {
				t = false;
				if (element.closest('.tab-pane.active').length > 0) {
					t = true;
				}
			}

			var referenceLink = element.val();
			if (t && referenceLink != '' && referenceLink != '0') {
				activeSubProcess = true;
			}
		});
		container.find('input[data-fieldtype="referenceSubProcess"]').each(function (index, element) {
			element = $(element);
			var processfieldElement = element.closest('.fieldValue');
			var length = processfieldElement.find('.referenceModulesList option[disabled!="disabled"]').length;
			if (activeSubProcess && length > 0) {
				thisInstance.setEnabledFields(element);
			} else {
				if (clear) {
					thisInstance.clearFieldValue(element);
				}
				thisInstance.setDisabledFields(element);
			}
		});
	},
	checkSubProcessModulesList: function (element) {
		var option = element.find('option:selected');
		if (option.data('is-quickcreate') != 1) {
			element.closest('.fieldValue').find('.createReferenceRecord').addClass('hide');
		} else {
			element.closest('.fieldValue').find('.createReferenceRecord').removeClass('hide');
		}
	},
	checkReferenceModulesList: function (container) {
		var thisInstance = this;
		var processfieldElement = container.find('input[data-fieldtype="referenceProcess"]').closest('.fieldValue');
		var referenceProcess = processfieldElement.find('input[name="popupReferenceModule"]').val();
		var subProcessfieldElement = container.find('input[data-fieldtype="referenceSubProcess"]').closest('.fieldValue');
		Vtiger_Helper_Js.hideOptions(subProcessfieldElement.find('.referenceModulesList'), 'parent', referenceProcess);
		var subProcessValue = subProcessfieldElement.find('.referenceModulesList').val();
		subProcessfieldElement.find('[name="popupReferenceModule"]').val(subProcessValue);
		thisInstance.checkSubProcessModulesList(subProcessfieldElement.find('.referenceModulesList'));
	},
	registerReferenceFields: function (container) {
		var thisInstance = this;
		thisInstance.checkReferenceModulesList(container);
		thisInstance.checkReferencesField(container, false);
		container.find('.sourceField').on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
			thisInstance.checkReferencesField(container, true);
		});
		container.find('.sourceField').on(Vtiger_Edit_Js.referenceDeSelectionEvent, function (e) {
			thisInstance.checkReferencesField(container, true);
		});
		container.find('input[data-fieldtype="referenceProcess"]').closest('.fieldValue').find('.referenceModulesList').on('change', function () {
			thisInstance.checkReferenceModulesList(container);
		});
		container.find('input[data-fieldtype="referenceSubProcess"]').closest('.fieldValue').find('.referenceModulesList').on('change', function (e) {
			thisInstance.checkSubProcessModulesList($(e.currentTarget));
		});
	},
	registerFocusFirstField: function (container) {
		var thisInstance = this;
		container.find('.fieldValue input.form-control:not([type=hidden],[type=checkbox])').each(function (n, e) {
			var element = jQuery(e);
			if (!element.prop('readonly') && !element.prop('disabled')) {
				element = element.get(0);
				var elemLen = element.value.length;

				element.selectionStart = elemLen;
				element.selectionEnd = elemLen;
				element.focus();
				return false;
			}
		});
	},
	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerBasicEvents: function (container) {
		this.treePopupRegisterEvent(container);
		this.registerClearTreeSelectionEvent(container);
		this.registerTreeAutoCompleteFields(container);
		this.referenceModulePopupRegisterEvent(container);
		this.registerAutoCompleteFields(container);
		this.registerClearReferenceSelectionEvent(container);
		this.registerPreventingEnterSubmitEvent(container);
		this.registerTimeFields(container);
		this.registerEventForPicklistDependencySetup(container);
		this.registerRecordPreSaveEventEvent(container);
		this.registerReferenceSelectionEvent(container);
		this.registerMaskFields(container);
		this.registerHelpInfo();
		this.registerReferenceFields(container);
		this.registerFocusFirstField(container);
	},
	registerEvents: function () {
		var editViewForm = this.getForm();
		var statusToProceed = this.proceedRegisterEvents();
		if (!statusToProceed) {
			return;
		}
		this.registerBlockAnimationEvent();
		this.registerBlockStatusCheckOnLoad();
		this.registerEventForCkEditor();
		this.stretchCKEditor();
		this.registerBasicEvents(editViewForm);
		this.registerEventForCopyAddress();
		this.registerEventForImageDelete();
		this.registerSubmitEvent();
		this.registerLeavePageWithoutSubmit(editViewForm);
		this.registerValidationsFields(editViewForm);
		this.registerReferenceCreate(editViewForm);
		this.registerApiAddress();
		//this.triggerDisplayTypeEvent();
	}
});

