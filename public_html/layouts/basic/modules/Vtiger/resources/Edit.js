/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

$.Class("Vtiger_Edit_Js", {
	//Event that will triggered when reference field is selected
	referenceSelectionEvent: 'Vtiger.Reference.Selection',
	//Event that will triggered when reference field is selected
	referenceDeSelectionEvent: 'Vtiger.Reference.DeSelection',
	//Event that will triggered before saving the record
	recordPreSave: 'Vtiger.Record.PreSave',
	editInstance: false,
	inventoryController: false,
	/**
	 * Function to get Instance by name
	 * @params moduleName:-- Name of the module to create instance
	 */
	getInstanceByModuleName: function (moduleName) {
		if (typeof moduleName === "undefined") {
			moduleName = app.getModuleName();
		}
		let parentModule = app.getParentModuleName(),
			moduleClassName,
			fallbackClassName,
			instance;
		if (parentModule === 'Settings') {
			moduleClassName = parentModule + "_" + moduleName + "_Edit_Js";
			if (typeof window[moduleClassName] === "undefined") {
				moduleClassName = moduleName + "_Edit_Js";
			}
			fallbackClassName = parentModule + "_Vtiger_Edit_Js";
			if (typeof window[fallbackClassName] === "undefined") {
				fallbackClassName = "Vtiger_Edit_Js";
			}
		} else {
			moduleClassName = moduleName + "_Edit_Js";
			fallbackClassName = "Vtiger_Edit_Js";
		}
		if (typeof window[moduleClassName] !== "undefined") {
			instance = new window[moduleClassName]();
		} else {
			instance = new window[fallbackClassName]();
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
	formElement: false,
	relationOperation: '',
	moduleName: app.getModuleName(),
	getForm: function () {
		if (this.formElement == false) {
			this.setForm($('#EditView'));
		}
		return this.formElement;
	},
	setForm: function (element) {
		this.formElement = element;
		return this;
	},
	getRecordsListParams: function (container) {
		let sourceModule = app.getModuleName();
		let popupReferenceModule = $('input[name="popupReferenceModule"]', container).val();
		let sourceFieldElement = $('input[class="sourceField"]', container);
		let sourceField = sourceFieldElement.attr('name');
		let sourceRecordElement = $('input[name="record"]');
		let sourceRecordId = '';
		if (sourceRecordElement.length > 0) {
			sourceRecordId = sourceRecordElement.val();
		}
		let isMultiple = false;
		if (sourceFieldElement.data('multiple') == true) {
			isMultiple = true;
		}
		let filterFields = {};
		let formElement = container.closest('form');
		let mappingRelatedField = formElement.find('input[name="mappingRelatedField"]').val();
		let mappingRelatedModule = mappingRelatedField ? JSON.parse(mappingRelatedField) : [];
		if (mappingRelatedModule[sourceField] != undefined && mappingRelatedModule[sourceField][popupReferenceModule] != undefined) {
			$.each(mappingRelatedModule[sourceField][popupReferenceModule], function (index, value) {
				let mapFieldElement = formElement.find('[name="' + index + '"]');
				if (mapFieldElement.length && mapFieldElement.val() != '') {
					filterFields[index] = mapFieldElement.val();
				}
			});
		}
		let params = {
			module: popupReferenceModule,
			src_module: sourceModule,
			src_field: sourceField,
			src_record: sourceRecordId,
			filterFields: filterFields,
		}
		$.each(['link', 'process'], function (index, value) {
			let fieldElement = formElement.find('[name="' + value + '"]');
			if (fieldElement.length && fieldElement.val() != '' && fieldElement.val() != 0) {
				params[value] = fieldElement.val();
			}

		});
		if (isMultiple) {
			params.multi_select = true;
		}
		return params;
	},
	/**
	 * Show records list modal
	 * @param {jQuery.Event} e
	 */
	showRecordsList: function (e) {
		var parentElem = $(e.target).closest('.fieldValue');
		if (parentElem.length <= 0) {
			parentElem = $(e.target).closest('td');
		}
		var params = this.getRecordsListParams(parentElem);
		app.showRecordsList(params, (modal, instance) => {
			instance.setSelectEvent((data) => {
				this.setReferenceFieldValue(parentElem, data);
			});
		});
	},
	setReferenceFieldValue: function (container, params) {
		const thisInstance = this;
		let sourceFieldElement = container.find('input.sourceField');
		let sourceField = sourceFieldElement.attr('name');
		let fieldElement = container.find('input[name="' + sourceField + '"]');
		let sourceFieldDisplay = sourceField + "_display";
		let fieldDisplayElement = container.find('input[name="' + sourceFieldDisplay + '"]');
		let popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();
		let selectedName = params.name;
		let id = params.id;

		container.find('.clearReferenceSelection').trigger('click');

		fieldElement.val(id)
		fieldDisplayElement.val(app.decodeHTML(selectedName)).attr('readonly', true);
		fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {
			module: popupReferenceModule,
			record: id,
			selectedName: selectedName
		});
		fieldDisplayElement.validationEngine('closePrompt', fieldDisplayElement);
		if (sourceFieldElement.data('type') == 'inventory') {
			return params;
		}
		let formElement = container.closest('form');
		let mappingRelatedField = this.getMappingRelatedField(sourceField, popupReferenceModule, formElement);
		if (typeof mappingRelatedField !== 'undefined') {
			let params = {
				module: popupReferenceModule,
				record: id
			};
			app.getRecordDetails(params).done(function (data) {
				var response = params.data = data['result']['data'];
				app.event.trigger("EditView.SelectReference", params, formElement);
				$.each(mappingRelatedField, function (key, value) {
					if (response[value[0]] != 0 && !thisInstance.getMappingValuesFromUrl(key)) {
						let mapFieldElement = formElement.find('[name="' + key + '"]');
						let fieldinfo = mapFieldElement.data('fieldinfo');
						if(data['result']['type'][value[0]]==='date' || data['result']['type'][value[0]]==='datetime'){
							mapFieldElement.val(data['result']['displayData'][value[0]]);
						}else if (mapFieldElement.is('select')) {
							if (mapFieldElement.find('option[value="' + response[value[0]] + '"]').length) {
								mapFieldElement.val(response[value[0]]).trigger("change");
							}
						} else if (mapFieldElement.length == 0) {
							$("<input type='hidden'/>").attr("name", key).attr("value", response[value[0]]).appendTo(formElement);
						} else {
							mapFieldElement.val(response[value[0]]);
						}
						let mapFieldDisplayElement = formElement.find('input[name="' + key + '_display"]');
						if (mapFieldDisplayElement.length > 0) {
							mapFieldDisplayElement.val(data['result']['displayData'][value[0]]).attr('readonly', true);
							if (fieldinfo.type === 'reference') {
								let referenceModulesList = mapFieldElement.closest('.fieldValue').find('.referenceModulesList');
								if (referenceModulesList.length > 0 && value[1]) {
									referenceModulesList.val(value[1]).trigger('change');
								}
								thisInstance.setReferenceFieldValue(mapFieldDisplayElement.closest('.fieldValue'), {
									name: data['result']['displayData'][value[0]],
									id: response[value[0]]
								});
							}
						}
					}
				});
			});
		}
	},
	getRelationOperation: function () {
		if (this.relationOperation === '') {
			var relationOperation = $('[name="relationOperation"]');
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
		if ($('.recordEditView').length > 0) {
			return true;
		} else {
			return false;
		}
	},
	treePopupRegisterEvent: function (container) {
		container.on("click", '.js-tree-modal', function (e) {
			let element = $(e.target);
			let parentElem = element.closest('.fieldValue');
			let sourceFieldElement = $('input[class="sourceField"]', parentElem);
			let fieldDisplayElement = $('input[name="' + sourceFieldElement.attr('name') + '_display"]', parentElem);
			AppConnector.request({
				module: $('input[name="module"]', element.closest('form')).val(),
				view: 'TreeModal',
				template: sourceFieldElement.data('treetemplate'),
				fieldName: sourceFieldElement.attr('name'),
				multiple: sourceFieldElement.data('multiple'),
				value: sourceFieldElement.val()
			}).done(function (requestData) {
				app.modalEvents['treeModal'] = function (modal, instance) {
					instance.setSelectEvent((responseData) => {
						sourceFieldElement.val(responseData.id);
						fieldDisplayElement.val(responseData.name).attr('readonly', true);
					});
				};
				app.showModalWindow(requestData, {modalId: 'treeModal'});
			});
		});
	},
	/**
	 * Function which will register reference field clear event
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerClearTreeSelectionEvent: function (container) {
		var thisInstance = this;
		container.find('.clearTreeSelection').on('click', function (e) {
			thisInstance.clearFieldValue($(e.currentTarget));
			e.preventDefault();
		})
	},
	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerTreeAutoCompleteFields: function (container) {
		container.find('input.treeAutoComplete').autocomplete({
			'delay': '600',
			'minLength': '3',
			'source': function (request, response) {
				//element will be array of dom elements
				//here this refers to auto complete instance
				let inputElement = $(this.element[0]);
				let searchValue = request.term.toLowerCase();
				let parentElem = inputElement.closest('.fieldValue');
				let sourceFieldElement = $('input[class="sourceField"]', parentElem);
				let fieldInfo = sourceFieldElement.data('fieldinfo');
				let allValues = fieldInfo.picklistvalues;
				let reponseDataList = [];
				for (let id in allValues) {
					if (allValues[id].toLowerCase().indexOf(searchValue) >= 0) {
						reponseDataList.push({label: allValues[id], value: id, id: id});
					}
				}
				if (reponseDataList.length <= 0) {
					$(inputElement).val('');
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
				if (typeof selectedItemData.type !== "undefined" && selectedItemData.type == "no results") {
					return false;
				}
				selectedItemData.name = selectedItemData.value;
				var element = $(this);
				var parentElem = element.closest('.fieldValue');
				var sourceField = parentElem.find('input[class="sourceField"]');
				var sourceFieldDisplay = sourceField.attr('name') + "_display";
				var fieldDisplayElement = $('input[name="' + sourceFieldDisplay + '"]', parentElem);
				sourceField.val(selectedItemData.id);
				this.value = selectedItemData.label;
				fieldDisplayElement.attr('readonly', true);
				return false;
			},
			'change': function (event, ui) {
			},
			'open': function (event, ui) {
				//To Make the menu come up in the case of quick create
				$(this).data('ui-autocomplete').menu.element.css('z-index', '100001');

			}
		});
	},
	referenceModulePopupRegisterEvent: function (container) {
		container.on("click", '.relatedPopup', (e) => {
			this.showRecordsList(e);
		});
		let moduleList = container.find('.referenceModulesList');
		App.Fields.Picklist.showSelect2ElementView(container.find('.referenceModulesList:visible'));
		moduleList.on('change', (e) => {
			let element = $(e.currentTarget);
			let parentElem = element.closest('.fieldValue');
			let popupReferenceModule = element.val();
			let referenceModuleElement = $('input[name="popupReferenceModule"]', parentElem);
			let prevSelectedReferenceModule = referenceModuleElement.val();
			referenceModuleElement.val(popupReferenceModule);
			//If Reference module is changed then we should clear the previous value
			if (prevSelectedReferenceModule != popupReferenceModule) {
				parentElem.find('.clearReferenceSelection').trigger('click');
			}
		});
	},
	getReferencedModuleName: function (parenElement) {
		return $('input[name="popupReferenceModule"]', parenElement).val();
	},
	searchModuleNames: function (params) {
		var aDeferred = $.Deferred();

		if (typeof params.module === "undefined") {
			params.module = app.getModuleName();
		}

		if (typeof params.action === "undefined") {
			params.action = 'BasicAjax';
		}

		AppConnector.request(params).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error) {
			aDeferred.reject();
		});
		return aDeferred.promise();
	},
	/**
	 * Function to get reference search params
	 */
	getReferenceSearchParams: function (element) {
		var tdElement = $(element).closest('.fieldValue');
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
				var inputElement = $(this.element[0]);
				var searchValue = request.term;
				var params = thisInstance.getReferenceSearchParams(inputElement);
				params.search_value = searchValue;
				//params.parent_id = app.getRecordId();
				//params.parent_module = app.getModuleName();
				thisInstance.searchModuleNames(params).done(function (data) {
					var reponseDataList = [];
					var serverDataFormat = data.result
					if (serverDataFormat.length <= 0) {
						$(inputElement).val('');
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
				if (typeof selectedItemData.type !== "undefined" && selectedItemData.type == "no results") {
					return false;
				}
				selectedItemData.name = selectedItemData.value;
				var element = $(this);
				var tdElement = element.closest('.fieldValue');
				thisInstance.setReferenceFieldValue(tdElement, selectedItemData);
			},
			change: function (event, ui) {
				var element = $(this);
				//if you dont have readonly attribute means the user didnt select the item
				if (element.attr('readonly') == undefined) {
					element.closest('.fieldValue').find('.clearReferenceSelection').trigger('click');
				}
			},
			open: function (event, ui) {
				//To Make the menu come up in the case of quick create
				$(this).data('ui-autocomplete').menu.element.css('z-index', '100001');
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
			var element = $(e.currentTarget);
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
		app.event.trigger('EditView.ClearField', {fieldName: fieldName, referenceModule: referenceModule});
		var mappingRelatedField = this.getMappingRelatedField(fieldName, referenceModule, formElement);
		$.each(mappingRelatedField, function (key, value) {
			var mapFieldElement = formElement.find('[name="' + key + '"]');
			if (mapFieldElement.is('select')) {
				mapFieldElement.val(mapFieldElement.find("option:first").val()).trigger('change');
			} else {
				mapFieldElement.val('');
			}
			var mapFieldDisplayElement = formElement.find('input[name="' + key + '_display"]');
			if (mapFieldDisplayElement.length > 0) {
				mapFieldDisplayElement.val('').attr('readonly', false);
				var referenceModulesList = formElement.find('#' + thisInstance.moduleName + '_editView_fieldName_' + key + '_dropDown');
				if (referenceModulesList.length > 0 && value[1]) {
					referenceModulesList.val(referenceModulesList.find("option:first").val()).trigger('change');
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
			var currentElement = $(e.target);
			if (e.which == 13 && (!currentElement.is('textarea'))) {
				e.preventDefault();
			}
		});
	},
	registerTimeFields: function (container) {
		app.registerEventForClockPicker();
		App.Fields.Date.register(container);
		App.Fields.DateTime.register(container);
	},
	referenceCreateHandler: function (container) {
		var thisInstance = this;
		var postQuickCreateSave = function (data) {
			thisInstance.setReferenceFieldValue(container, {
				name: data.result._recordLabel,
				id: data.result._recordId
			});
		}
		var params = {callbackFunction: postQuickCreateSave};
		if (app.getViewName() === 'Edit' && !app.getRecordId()) {
			var formElement = this.getForm();
			var formData = formElement.serializeFormData();
			for (var i in formData) {
				if (!formData[i] || $.inArray(i, ['__vtrftk', 'action']) != -1) {
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
			var element = $(e.currentTarget);
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
		var account_id = false;
		var contact_id = false;
		var lead_id = false;
		var vendor_id = false;
		$("#EditView .js-toggle-panel:not(.inventoryHeader):not(.inventoryItems) .fieldValue, #EditView .js-toggle-panel:not(.inventoryHeader):not(.inventoryItems) .fieldLabel").each(function (index) {
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
			$(".copyAddressFromAccount").addClass('d-none');
		} else {
			$('.copyAddressFromAccount').on('click', function (e) {
				var element = $(this);
				var block = element.closest('.js-toggle-panel');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = $('[name="' + account_id + '"]').val();

				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = $('#' + account_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'module': "Accounts"
					}

					thisInstance.copyAddressDetails(from, to, data, element.closest('.js-toggle-panel'));
					element.attr('checked', 'checked');
				}
			})
		}
		if (contact_id == false) {
			$(".copyAddressFromContact").addClass('d-none');
		} else {
			$('.copyAddressFromContact').on('click', function (e) {
				var element = $(this);
				var block = element.closest('.js-toggle-panel');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = $('[name="' + contact_id + '"]').val();
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_CONTACT_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = $('#' + contact_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Contacts"
					}
					thisInstance.copyAddressDetails(from, to, data, element.closest('.js-toggle-panel'));
					element.attr('checked', 'checked');
				}
			})
		}
		if (lead_id == false) {
			$(".copyAddressFromLead").addClass('d-none');
		} else {
			$('.copyAddressFromLead').on('click', function (e) {
				var element = $(this);
				var block = element.closest('.js-toggle-panel');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = $('[name="' + lead_id + '"]').val();
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_LEAD_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = $('#' + lead_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Leads"
					}
					thisInstance.copyAddressDetails(from, to, data, element.closest('.js-toggle-panel'));
					element.attr('checked', 'checked');
				}
			})
		}
		if (vendor_id == false) {
			$(".copyAddressFromVendor").addClass('d-none');
		} else {
			$('.copyAddressFromVendor').on('click', function (e) {
				var element = $(this);
				var block = element.closest('.js-toggle-panel');
				var from = element.data('label');
				var to = block.data('label');
				var recordRelativeAccountId = $('[name="' + vendor_id + '"]').val();
				if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_SELECT_AN_VENDOR_TO_COPY_ADDRESS'));
				} else {
					var recordRelativeAccountName = $('#' + vendor_id + '_display').val();
					var data = {
						'record': recordRelativeAccountId,
						'selectedName': recordRelativeAccountName,
						'source_module': "Vendors"
					}
					thisInstance.copyAddressDetails(from, to, data, element.closest('.js-toggle-panel'));
					element.attr('checked', 'checked');
				}
			})
		}

		$("#EditView .js-toggle-panel").each(function (index) {
			var hideCopyAddressLabel = true;
			$(this).find(".adressAction button").each(function (index) {
				if ($(this).hasClass("d-none") == false) {
					hideCopyAddressLabel = false;
				}
			});
			if (hideCopyAddressLabel) {
				$(this).find(".copyAddressLabel").addClass('d-none');
			}
		});
		$('.copyAddressFromMain').on('click', function (e) {
			var element = $(this);
			var block = element.closest('.js-toggle-panel');
			var from = element.data('label');
			var to = block.data('label');
			thisInstance.copyAddress(from, to, false, false);
		})
		$('.copyAddressFromMailing').on('click', function (e) {
			var element = $(this);
			var block = element.closest('.js-toggle-panel');
			var from = element.data('label');
			var to = block.data('label');
			thisInstance.copyAddress(from, to, false, false);
		})
		$('.copyAddressFromDelivery').on('click', function (e) {
			var element = $(this);
			var block = element.closest('.js-toggle-panel');
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
		var sourceModule = data.module;
		app.getRecordDetails(data).done(function (data) {
			var response = data['result'];
			thisInstance.addressFieldsData = response;
			thisInstance.copyAddress(from, to, true, sourceModule);
		});
	},
	/**
	 * Function to copy address between fields
	 * @param strings which accepts value as either odd or even
	 */
	copyAddress: function (fromLabel, toLabel, relatedRecord, sourceModule) {
		const thisInstance = this;
		let formElement = this.getForm(),
			status = false,
			addressMapping = this.addressFieldsMapping,
			BlockIds = this.addressFieldsMappingBlockID,
			from = BlockIds[fromLabel];
		if (relatedRecord === false || sourceModule === false)
			from = BlockIds[fromLabel];
		let to = BlockIds[toLabel],
			key,
			fromElement,
			fromElementLabel,
			nameElementFrom,
			nameElementTo;
		for (key in addressMapping) {
			nameElementFrom = addressMapping[key] + from;
			nameElementTo = addressMapping[key] + to;
			if (relatedRecord) {
				fromElement = thisInstance.addressFieldsData['data'][nameElementFrom];
				fromElementLabel = thisInstance.addressFieldsData['displayData'][nameElementFrom];
			} else {
				fromElement = formElement.find('[name="' + nameElementFrom + '"]').val();
				fromElementLabel = formElement.find('[name="' + nameElementFrom + '_display"]').val();
			}
			let toElement = formElement.find('[name="' + nameElementTo + '"]'),
				toElementLable = formElement.find('[name="' + nameElementTo + '_display"]');
			if (fromElement !== '' && fromElement !== '0' && fromElement !== undefined) {
				if (toElementLable.length > 0)
					toElementLable.attr('readonly', true);
				status = true;
				toElement.val(fromElement);
				toElementLable.val(fromElementLabel);
				if (toElement.is('[data-select2-id]')) {
					toElement.trigger('change');
				}
			} else {
				toElement.attr('readonly', false);
			}
		}
		if (status === false) {
			let errorMsg;
			if (sourceModule === "Accounts") {
				errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
			} else if (sourceModule === "Contacts") {
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
			var blockContainer = $(e.currentTarget).closest('.js-toggle-panel');
			thisInstance.copyAddressDetailsRef(data, blockContainer);
		});
	},
	copyAddressDetailsRef: function (data, container) {
		var thisInstance = this;
		app.getRecordDetails(data).done(function (data) {
			var response = data['result'];
			thisInstance.mapAddressDetails(response, container);
		}).fail(function (error, err) {

		});
	},
	mapAddressDetails: function (result, container) {
		for (var key in result) {
			if (key.indexOf("addresslevel") != -1) {
				if (container.find('[name="' + key + '"]').length != 0) {
					container.find('[name="' + key + '"]').val(result['data'][key]);
					container.find('[name="' + key + '"]').attr('readonly', true);
					container.find('[name="' + key + '_display"]').val(result['displayData'][key]);
					container.find('[name="' + key + '_display"]').attr('readonly', true);
				}
				if (container.find('[name="' + key + 'a"]').length != 0 && container.find('[name="' + key + 'a"]').val() == 0 && result['data'][key] != 0) {
					container.find('[name="' + key + 'a"]').val(result['data'][key]);
					container.find('[name="' + key + 'a"]').attr('readonly', true);
					container.find('[name="' + key + 'a_display"]').val(result['displayData'][key]);
					container.find('[name="' + key + 'a_display"]').attr('readonly', true);
				}
				if (container.find('[name="' + key + 'b"]').length != 0 && container.find('[name="' + key + 'b"]').val() == 0 && result['data'][key] != 0) {
					container.find('[name="' + key + 'b"]').val(result['data'][key]);
					container.find('[name="' + key + 'b"]').attr('readonly', true);
					container.find('[name="' + key + 'b_display"]').val(result['displayData'][key]);
					container.find('[name="' + key + 'b_display"]').attr('readonly', true);
				}
				if (container.find('[name="' + key + 'c"]').length != 0 && container.find('[name="' + key + 'c"]').val() == 0 && result['data'][key] != 0) {
					container.find('[name="' + key + 'c"]').val(result['data'][key]);
					container.find('[name="' + key + 'c"]').attr('readonly', true);
					container.find('[name="' + key + 'c_display"]').val(result['displayData'][key]);
					container.find('[name="' + key + 'c_display"]').attr('readonly', true);
				}
			}
		}
	},
	registerMaskFields: function (container) {
		container.find("[data-inputmask]").inputmask();
	},
	triggerDisplayTypeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if (widthType) {
			var elements = $('#EditView').find('.fieldValue,.fieldLabel');
			elements.addClass(widthType);
		}
	},
	registerSubmitEvent: function () {
		var editViewForm = this.getForm();
		editViewForm.on('submit', function (e) {
			//Form should submit only once for multiple clicks also
			if (typeof editViewForm.data('submit') !== "undefined") {
				return false;
			} else {
				document.progressLoader = $.progressIndicator({
					'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
				editViewForm.find('.js-toggle-panel').find('.js-block-content').removeClass('d-none');
				if (editViewForm.validationEngine('validate')) {
					//Once the form is submiting add data attribute to that form element
					editViewForm.data('submit', 'true');
					//on submit form trigger the recordPreSave event
					var recordPreSaveEvent = $.Event(Vtiger_Edit_Js.recordPreSave);
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
		form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {

		});
	},
	/**
	 * Function to register event for setting up picklistdependency
	 * for a module if exist on change of picklist value
	 */
	registerEventForPicklistDependencySetup: function (container) {
		let picklistDependcyElemnt = $('[name="picklistDependency"]', container);
		if (picklistDependcyElemnt.length <= 0) {
			return;
		}
		let picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

		let sourcePicklists = Object.keys(picklistDependencyMapping);
		if (sourcePicklists.length <= 0) {
			return;
		}

		let sourcePickListNames = [],
			i;
		for (i = 0; i < sourcePicklists.length; i++) {
			sourcePickListNames.push('[name="' + sourcePicklists[i] + '"]');
		}
		sourcePickListNames = sourcePickListNames.join(',');
		let sourcePickListElements = container.find(sourcePickListNames);

		sourcePickListElements.on('change', function (e) {
			let currentElement = $(e.currentTarget),
				configuredDependencyObject = picklistDependencyMapping[currentElement.attr('name')],
				targetObjectForSelectedSourceValue = configuredDependencyObject[currentElement.val()],
				picklistmap = configuredDependencyObject["__DEFAULT__"];

			if (typeof targetObjectForSelectedSourceValue === "undefined") {
				targetObjectForSelectedSourceValue = picklistmap;
			}
			$.each(picklistmap, function (targetPickListName, targetPickListValues) {
				let targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if (typeof targetPickListMap === "undefined") {
					targetPickListMap = targetPickListValues;
				}
				let targetPickList = $('[name="' + targetPickListName + '"]', container);
				if (targetPickList.length <= 0) {
					return;
				}

				let listOfAvailableOptions = targetPickList.data('availableOptions');
				if (typeof listOfAvailableOptions === "undefined") {
					listOfAvailableOptions = $('option', targetPickList);
					targetPickList.data('available-options', listOfAvailableOptions);
				}

				let targetOptions = new $(),
					optionSelector = [];
				optionSelector.push('');
				for (i = 0; i < targetPickListMap.length; i++) {
					optionSelector.push(targetPickListMap[i]);
				}

				$.each(listOfAvailableOptions, function (i, e) {
					if ($.inArray($(e).val(), optionSelector) !== -1) {
						targetOptions = targetOptions.add($(e));
					}
				});
				targetPickList.html(targetOptions).val(targetOptions.filter('[selected]').val()).trigger('change');
			})
		});

		//To Trigger the change on load
		sourcePickListElements.trigger('change');
	},
	registerLeavePageWithoutSubmit: function (form) {
		let InitialFormData = form.serialize();
		window.onbeforeunload = function (e) {
			if (InitialFormData != form.serialize() && form.data('submit') != "true") {
				return app.vtranslate("JS_CHANGES_WILL_BE_LOST");
			}
		};
	},
	stretchCKEditor: function () {
		var row = $('.js-editor').parents('.fieldRow');
		var td = $('.js-editor').parent();
		$(row).find('.fieldLabel').remove();
		$(td).removeClass('col-md-10');
		$(td).addClass('col-md-12');
	},
	/**
	 * Function to register event for ckeditor for description field
	 */
	registerEventForEditor: function () {
		var form = this.getForm();
		var thisInstance = this;
		$.each(form.find('.js-editor'), function (key, data) {
			thisInstance.loadEditorElement($(data));
		});
	},
	loadEditorElement: function (noteContentElement) {
		var customConfig = {};
		if (noteContentElement.is(':visible')) {
			if (noteContentElement.hasClass("js-editor--basic")) {
				customConfig.toolbar = 'Min';
			}
			if (noteContentElement.data('height')) {
				customConfig.height = noteContentElement.data('height');
			}
			new App.Fields.Text.Editor(noteContentElement, customConfig);
		}
	},
	registerHelpInfo: function () {
		var form = this.getForm();
		app.showPopoverElementView(form.find('.js-help-info'));
	},
	registerBlockAnimationEvent: function () {
		var thisInstance = this;
		var detailContentsHolder = this.getForm();
		detailContentsHolder.on('click', '.blockHeader', function (e) {
			const target = $(e.target);
			if (target.is('input') || target.is('button') || target.parents().is('button') || target.hasClass('js-stop-propagation') || target.parents().hasClass('js-stop-propagation')) {
				return false;
			}
			var currentTarget = $(e.currentTarget).find('.js-block-toggle').not('.d-none');
			var blockId = currentTarget.data('id');
			var closestBlock = currentTarget.closest('.js-toggle-panel');
			var bodyContents = closestBlock.find('.blockContent');
			var data = currentTarget.data();
			var module = app.getModuleName();
			var hideHandler = function () {
				bodyContents.addClass('d-none');
				app.cacheSet(module + '.' + blockId, 0)
			}
			var showHandler = function () {
				bodyContents.removeClass('d-none');
				thisInstance.registerEventForEditor(bodyContents);
				app.cacheSet(module + '.' + blockId, 1)
			}
			if (data.mode == 'show') {
				hideHandler();
				currentTarget.addClass('d-none');
				closestBlock.find('[data-mode="hide"]').removeClass('d-none');
			} else {
				showHandler();
				currentTarget.addClass('d-none');
				closestBlock.find("[data-mode='show']").removeClass('d-none');
			}
		});

	},
	registerBlockStatusCheckOnLoad: function () {
		let blocks = this.getForm().find('.js-toggle-panel');
		let module = app.getModuleName();
		blocks.each(function (index, block) {
			let currentBlock = $(block);
			let dynamicAttr = currentBlock.attr('data-dynamic');
			if (typeof dynamicAttr !== typeof undefined && dynamicAttr !== false) {
				let headerAnimationElement = currentBlock.find('.js-block-toggle').not('.d-none');
				let bodyContents = currentBlock.find('.blockContent')
				let blockId = headerAnimationElement.data('id');
				let cacheKey = module + '.' + blockId;
				let value = app.cacheGet(cacheKey, null);
				if (value != null) {
					if (value == 1) {
						headerAnimationElement.addClass('d-none');
						currentBlock.find("[data-mode='show']").removeClass('d-none');
						bodyContents.removeClass('d-none');
					} else {
						headerAnimationElement.addClass('d-none');
						currentBlock.find("[data-mode='hide']").removeClass('d-none');
						bodyContents.addClass('d-none');
					}
				}
			}
		});
	},
	registerAutoloadAddress: function () {
		this.getForm().find('.js-search-address').each(function (index, item) {
			let search = $(item);
			let container = search.closest('.js-block-content');
			let input = search.find('.js-autoload-address');
			search.find('.js-select-operator').on('click', function (e) {
				input.data('type', $(this).data('type'));
			});
			input.autocomplete({
				source: function (request, response) {
					AppConnector.request({
						module: app.getModuleName(),
						action: 'Fields',
						mode: 'findAddress',
						type: input.data('type'),
						value: request.term
					}).done(function (requestData) {
						if (requestData.result === false) {
							Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_ERROR'));
						} else if (requestData.result.length) {
							response(requestData.result);
						} else {
							response([{label: app.vtranslate('JS_NO_RESULTS_FOUND'), value: ''}]);
						}
					}).fail(function (textStatus, errorThrown, jqXHR) {
						Vtiger_Helper_Js.showPnotify({
							text: jqXHR.responseJSON.error.message,
							type: 'error',
							animation: 'show'
						});
						response([{label: app.vtranslate('JS_NO_RESULTS_FOUND'), value: ''}]);
					});
				},
				minLength: input.data('min'),
				select: function (event, ui) {
					$.each(ui.item.address, function (index, value) {
						let field = container.find('.fieldValue [name^=' + index + ']');
						if (field.length && value) {
							if (typeof value !== 'object') {
								value = [value];
							}
							$.each(value, function (index, v) {
								let select = false, element = false;
								if (field.prop("tagName") === 'SELECT') {
									if (typeof v === 'object') {
										$.each(v, function (index, x) {
											element = field.find("option[data-" + index + "='" + x + "']");
											if (x && element.length) {
												select = element.val();
											}
										});
									} else {
										element = field.find('option:contains(' + v + ')');
										if (v && element.length) {
											select = element.val();
										}
										element = field.find('option[value="' + v + '"]');
										if (v && element.length) {
											select = element.val();
										}
									}
								} else {
									select = v;
								}
								if (select) {
									field.val(select).change();
								}
							});
						} else {
							field.val('').change();
						}
					});
					ui.item.value = input.val();
				}
			});
		});
	},
	setEnabledFields: function (element) {
		var fieldValue = element.closest('.fieldValue');
		var fieldName = fieldValue.find('input.sourceField').attr('name');
		var fieldDisplay = fieldValue.find('#' + fieldName + '_display');
		fieldValue.find('button').removeAttr('disabled');
		if (fieldDisplay.val() == '') {
			fieldValue.find('input').removeAttr('readonly');
		}
		fieldValue.find('.referenceModulesListGroup').removeClass('d-none');
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
		fieldValue.find('.referenceModulesListGroup').addClass('d-none');
		var placeholder = fieldDisplay.attr('placeholder');
		fieldDisplay.removeAttr('placeholder');
		fieldDisplay.attr('placeholderDisabled', placeholder);
		fieldValue.find('.referenceModulesList').removeAttr('required');
	},
	getMappingRelatedField: function (sourceField, sourceFieldModule, container) {
		const mappingRelatedField = container.find('input[name="mappingRelatedField"]').val();
		const mappingRelatedModule = mappingRelatedField ? JSON.parse(mappingRelatedField) : [];
		if (typeof mappingRelatedModule[sourceField] !== 'undefined' && typeof mappingRelatedModule[sourceField][sourceFieldModule] !== 'undefined') {
			return mappingRelatedModule[sourceField][sourceFieldModule];
		}
		return [];
	},
	registerValidationsFields: function (container) {
		var params = app.validationEngineOptionsForRecord;
		container.validationEngine(params);
	},
	checkReferencesField: function (container, clear) {
		var thisInstance = this;
		var activeProcess = false, activeSubProcess = false;
		if (!CONFIG.fieldsReferencesDependent) {
			return false;
		}
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
			element.closest('.fieldValue').find('.createReferenceRecord').addClass('d-none');
		} else {
			element.closest('.fieldValue').find('.createReferenceRecord').removeClass('d-none');
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
		if (!CONFIG.fieldsReferencesDependent) {
			return false;
		}
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
		container.find('.fieldValue input.form-control:not([type=hidden],[type=checkbox],.dateField,.clockPicker)').each(function (n, e) {
			let element = $(e);
			if (!element.prop('readonly') && !element.prop('disabled')) {
				element = element.get(0);
				if (element.type !== 'number') {
					let elemLen = element.value.length;
					element.selectionStart = elemLen;
					element.selectionEnd = elemLen;
				}
				element.focus();
				return false;
			}
		});
	},
	registerCopyValue: function (container) {
		container.find('.fieldValue [data-copy-to-field]').on('change', function (e) {
			var element = $(e.currentTarget);
			container.find('[name="' + element.data('copyToField') + '"]').val(element.val());
		});
	},
	/**
	 * Register multi image upload fields
	 * @param {HTMLElement|jQuery} container
	 */
	registerMultiImageFields(container) {
		return App.Fields.MultiImage.register(container);
	},
	/**
	 * Register inventory controller
	 * @param {jQuery} container
	 */
	registerInventoryController(container) {
		if (typeof Vtiger_Inventory_Js !== "undefined") {
			this.inventoryController = Vtiger_Inventory_Js.getInventoryInstance(container);
		}
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
		this.registerCopyValue(container);
		this.registerMultiImageFields(container);
		App.Fields.MultiEmail.register(container);
		App.Fields.MultiDependField.register(container);
	},
	registerEvents: function () {
		var editViewForm = this.getForm();
		var statusToProceed = this.proceedRegisterEvents();
		if (!statusToProceed) {
			return;
		}
		this.registerInventoryController(editViewForm);
		this.registerBlockAnimationEvent();
		this.registerBlockStatusCheckOnLoad();
		this.registerEventForEditor();
		this.stretchCKEditor();
		this.registerBasicEvents(editViewForm);
		this.registerEventForCopyAddress();
		this.registerSubmitEvent();
		this.registerLeavePageWithoutSubmit(editViewForm);
		this.registerValidationsFields(editViewForm);
		this.registerReferenceCreate(editViewForm);
		this.registerAutoloadAddress();
		//this.triggerDisplayTypeEvent();
	}
});
