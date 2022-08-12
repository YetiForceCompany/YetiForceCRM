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

$.Class(
	'Vtiger_Edit_Js',
	{
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
			if (typeof moduleName === 'undefined') {
				moduleName = app.getModuleName();
			}
			let parentModule = app.getParentModuleName(),
				moduleClassName,
				fallbackClassName,
				instance;
			if (parentModule === 'Settings') {
				moduleClassName = parentModule + '_' + moduleName + '_Edit_Js';
				if (typeof window[moduleClassName] === 'undefined') {
					moduleClassName = moduleName + '_Edit_Js';
				}
				fallbackClassName = parentModule + '_Vtiger_Edit_Js';
				if (typeof window[fallbackClassName] === 'undefined') {
					fallbackClassName = 'Vtiger_Edit_Js';
				}
			} else {
				moduleClassName = moduleName + '_Edit_Js';
				fallbackClassName = 'Vtiger_Edit_Js';
			}
			if (typeof window[moduleClassName] !== 'undefined') {
				instance = new window[moduleClassName]();
			} else {
				instance = new window[fallbackClassName]();
			}
			instance.moduleName = moduleName;
			return instance;
		},
		getInstance: function () {
			if (Vtiger_Edit_Js.editInstance == false) {
				let instance = Vtiger_Edit_Js.getInstanceByModuleName();
				Vtiger_Edit_Js.editInstance = instance;
				return instance;
			}
			return Vtiger_Edit_Js.editInstance;
		},
		/**
		 * Record save ajax
		 *
		 * `Vtiger_Edit_Js.saveAjax({
		 *		value: 'value',
		 *		field: 'field'
		 *	}).done(() => {})
		 *	.fail((error, err) => {});`
		 *
		 * @param {object} params
		 * @returns {Promise}
		 */
		saveAjax: function (params, progressIndicator = true) {
			const aDeferred = $.Deferred();
			if (typeof params === 'undefined' || $.isEmptyObject(params)) {
				aDeferred.reject();
				return aDeferred.promise();
			}
			if (!params['record'] && app.getRecordId()) {
				params['record'] = app.getRecordId();
			}
			if (!params['module']) {
				params['module'] = app.getModuleName();
			}
			params['action'] = 'SaveAjax';
			params = {
				data: params,
				async: false,
				dataType: 'json'
			};
			if (progressIndicator) {
				document.progressLoader = $.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
			}
			this.saveAjaxValidation(params).then((response) => {
				if (response === true) {
					delete params.data.mode;
					AppConnector.request(params)
						.done(function (responseData) {
							aDeferred.resolve(responseData);
							if (progressIndicator) {
								document.progressLoader.progressIndicator({ mode: 'hide' });
							}
						})
						.fail((jqXHR, textStatus, errorThrown) => {
							aDeferred.reject(jqXHR, textStatus, errorThrown);
							if (progressIndicator) {
								document.progressLoader.progressIndicator({ mode: 'hide' });
							}
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						});
				} else {
					aDeferred.resolve({ success: false });
					if (progressIndicator) {
						document.progressLoader.progressIndicator({ mode: 'hide' });
					}
				}
			});
			return aDeferred.promise();
		},
		/**
		 * Record pre save validation
		 * @param {object} params
		 * @returns {Promise}
		 */
		saveAjaxValidation: function (params) {
			const aDeferred = $.Deferred();
			if (this.getInstance().checkPreSaveValidation()) {
				let paramsTemp = JSON.parse(JSON.stringify(params));
				paramsTemp.data.mode = 'preSaveValidation';
				AppConnector.request(paramsTemp)
					.done((data) => {
						const response = data.result;
						let lock = false;
						for (let i in response) {
							if (response[i].result !== true) {
								if (response[i].type === 'confirm' && typeof response[i].hash !== 'undefined') {
									app.showConfirmModal({
										text: response[i].message || '',
										confirmedCallback: () => {
											let handlers = {};
											if (typeof params.data.skipHandlers !== 'undefined') {
												handlers = JSON.parse(params.data.skipHandlers);
											}
											handlers[i] = response[i].hash;
											params.data.skipHandlers = JSON.stringify(handlers);
											this.saveAjaxValidation(params, form).then((responsePart) => {
												aDeferred.resolve(responsePart);
											});
										},
										rejectedCallback: () => {
											aDeferred.resolve(false);
										}
									});
									lock = true;
									break;
								} else if (
									typeof response[i].showModal !== 'undefined' &&
									typeof response[i].showModal.url !== 'undefined'
								) {
									app.showModalWindow(null, response[i].showModal.url, function (modalContainer) {
										app.registerModalController(undefined, modalContainer, function (_, instance) {
											instance.formContainer = form;
										});
									});
								} else {
									app.showNotify({
										text: response[i].message ? response[i].message : app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								}
							}
						}
						if (data.result.length <= 0) {
							aDeferred.resolve(true);
						} else if (!lock) {
							aDeferred.resolve(false);
						}
					})
					.fail((textStatus, errorThrown) => {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
						aDeferred.resolve(false);
					});
			} else {
				aDeferred.resolve(true);
			}
			return aDeferred.promise();
		}
	},
	{
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
			let module;
			if ((module = $('input[name="module"]', element))) {
				this.moduleName = module.val();
			}
			return this;
		},
		getRecordsListParams: function (container) {
			let formElement = container.closest('form');
			let sourceModule = $('input[name="module"]', formElement).val();
			let popupReferenceModule = $('input[name="popupReferenceModule"]', container).val();
			let sourceFieldElement = $('input[class="sourceField"]', container);
			let sourceField = sourceFieldElement.attr('name');
			let sourceRecordElement = $('input[name="record"]', formElement);
			let sourceRecordId = '';
			if (sourceRecordElement.length > 0) {
				sourceRecordId = sourceRecordElement.val();
			}
			let isMultiple = false;
			if (sourceFieldElement.data('multiple') == true) {
				isMultiple = true;
			}
			let filterFields = {};
			let mappingRelatedField = formElement.find('input[name="mappingRelatedField"]').val();
			let mappingRelatedModule = mappingRelatedField ? JSON.parse(mappingRelatedField) : [];
			if (
				mappingRelatedModule[sourceField] != undefined &&
				mappingRelatedModule[sourceField][popupReferenceModule] != undefined
			) {
				$.each(mappingRelatedModule[sourceField][popupReferenceModule], function (index, value) {
					let mapFieldElement = formElement.find('[name="' + index + '"]');
					if (mapFieldElement.length && mapFieldElement.val() != '') {
						filterFields[index] = mapFieldElement.val();
					}
				});
			}
			let listFilterFieldsJson = formElement.find('input[name="listFilterFields"]').val();
			let listFilterFields = listFilterFieldsJson ? JSON.parse(listFilterFieldsJson) : [];
			if (
				listFilterFields[sourceField] != undefined &&
				listFilterFields[sourceField][popupReferenceModule] != undefined
			) {
				$.each(listFilterFields[sourceField][popupReferenceModule], function (index, value) {
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
				filterFields: filterFields
			};
			let searchParamsElement = $('input[name="searchParams"]', container);
			if (searchParamsElement.length > 0) {
				params['search_params'] = searchParamsElement.val();
			}
			let modalParamsElement = $('input[name="modalParams"]', container);
			if (modalParamsElement.length > 0) {
				params['modal_params'] = modalParamsElement.val();
			}
			let lockedFieldsElement = $('input[name="lockedFields"]', container);
			if (lockedFieldsElement.length > 0) {
				params['lockedFields'] = lockedFieldsElement.val();
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
			let parentElem = $(e.target).closest('.fieldValue');
			if (parentElem.length <= 0) {
				parentElem = $(e.target).closest('td');
			}
			let params = this.getRecordsListParams(parentElem);
			app.showRecordsList(params, (_modal, instance) => {
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
			let sourceFieldDisplay = sourceField + '_display';
			let fieldDisplayElement = container.find('input[name="' + sourceFieldDisplay + '"]');
			let popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();
			let selectedName = params.name;
			let id = params.id;

			container.find('.clearReferenceSelection').trigger('click');

			fieldElement.val(id);
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
					let response = (params.data = data['result']['data']);
					app.event.trigger('EditView.SelectReference', params, formElement, data);
					$.each(mappingRelatedField, function (key, value) {
						if (response[value[0]] != 0) {
							let mapFieldElement = formElement.find('[name="' + key + '"]');
							let fieldinfo = mapFieldElement.data('fieldinfo');
							if (data['result']['type'][value[0]] === 'date' || data['result']['type'][value[0]] === 'datetime') {
								mapFieldElement.val(data['result']['displayData'][value[0]]);
							} else if (data['result']['type'][value[0]] === 'multipicklist') {
								let mapFieldElementMultiselect = formElement.find('[name="' + key + '[]"]');
								if (mapFieldElementMultiselect.length > 0) {
									let multipleAttr = mapFieldElementMultiselect.attr('multiple');
									let splitValues = response[value[0]].split(' |##| ');
									if (typeof multipleAttr !== 'undefined' && multipleAttr !== false && splitValues.length > 0) {
										mapFieldElementMultiselect.val(splitValues).trigger('change');
									}
								}
							} else if (mapFieldElement.is('select')) {
								if (mapFieldElement.find('option[value="' + response[value[0]] + '"]').length) {
									mapFieldElement.val(response[value[0]]).trigger('change');
								} else if (mapFieldElement.data('fieldinfo').picklistvalues.hasOwnProperty(response[value[0]])) {
									let newOption = new Option(response[value[0]], response[value[0]], true, true);
									mapFieldElement.append(newOption).trigger('change');
								}
							} else if (mapFieldElement.length == 0) {
								$("<input type='hidden'/>").attr('name', key).attr('value', response[value[0]]).appendTo(formElement);
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
				let relationOperation = $('[name="relationOperation"]');
				if (relationOperation.length) {
					this.relationOperation = relationOperation.val();
				} else {
					this.relationOperation = false;
				}
			}
			return this.relationOperation;
		},
		proceedRegisterEvents: function () {
			return $('.recordEditView').length > 0;
		},
		referenceModulePopupRegisterEvent: function (container) {
			container.on('click', '.relatedPopup', (e) => {
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
			let aDeferred = $.Deferred();
			if (typeof params.module === 'undefined') {
				params.module = this.moduleName;
			}
			if (typeof params.action === 'undefined') {
				params.action = 'BasicAjax';
			}
			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					aDeferred.reject();
				});
			return aDeferred.promise();
		},
		/**
		 * Function to get reference search params
		 */
		getReferenceSearchParams: function (element) {
			let tdElement = $(element).closest('.fieldValue');
			let params = {};
			let searchModule = this.getReferencedModuleName(tdElement);
			params.search_module = searchModule;
			return params;
		},
		/**
		 * Function which will handle the reference auto complete event registrations
		 * @params - container <jQuery> - element in which auto complete fields needs to be searched
		 */
		registerAutoCompleteFields: function (container) {
			let thisInstance = this;
			let formElement = container.closest('form');
			container.find('input.autoComplete').autocomplete({
				delay: '600',
				minLength: '3',
				source: function (request, response) {
					//element will be array of dom elements
					//here this refers to auto complete instance
					let inputElement = $(this.element[0]);
					let searchValue = request.term;
					let params = thisInstance.getReferenceSearchParams(inputElement);
					params.search_value = searchValue;
					let sourceRecordElement = $('input[name="record"]', formElement);
					if (sourceRecordElement.length > 0 && sourceRecordElement.val()) {
						params.src_record = sourceRecordElement.val();
					}
					thisInstance.searchModuleNames(params).done(function (data) {
						let reponseDataList = [];
						let serverDataFormat = data.result;
						if (serverDataFormat.length <= 0) {
							$(inputElement).val('');
							serverDataFormat = new Array({
								label: app.vtranslate('JS_NO_RESULTS_FOUND'),
								type: 'no results'
							});
						}
						for (let id in serverDataFormat) {
							let responseData = serverDataFormat[id];
							reponseDataList.push(responseData);
						}
						response(reponseDataList);
						app.event.trigger('EditView.AfterSearch', {
							field: inputElement,
							params: params
						});
					});
				},
				select: function (event, ui) {
					let selectedItemData = ui.item;
					//To stop selection if no results is selected
					if (typeof selectedItemData.type !== 'undefined' && selectedItemData.type == 'no results') {
						return false;
					}
					selectedItemData.name = selectedItemData.value;
					let element = $(this);
					let tdElement = element.closest('.fieldValue');
					thisInstance.setReferenceFieldValue(tdElement, selectedItemData);
				},
				change: function (event, ui) {
					let element = $(this);
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
			let thisInstance = this;
			container.on('click', '.clearReferenceSelection', function (e) {
				let element = $(e.currentTarget);
				thisInstance.clearFieldValue(element);
				element.closest('.fieldValue').find('.sourceField').trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
				e.preventDefault();
			});
		},
		clearFieldValue: function (element) {
			const self = this;
			let fieldValueContener = element.closest('.fieldValue');
			let fieldNameElement = fieldValueContener.find('.sourceField');
			let fieldName = fieldNameElement.attr('name');
			let referenceModule = fieldValueContener.find('input[name="popupReferenceModule"]').val();
			let formElement = fieldValueContener.closest('form');
			if (fieldNameElement.data('fieldtype') == 'reference') {
				fieldNameElement.val(0);
			} else {
				fieldNameElement.val('');
			}
			fieldValueContener
				.find('#' + fieldName + '_display')
				.removeAttr('readonly')
				.val('');
			app.event.trigger('EditView.ClearField', {
				fieldName: fieldName,
				referenceModule: referenceModule
			});
			let mappingRelatedField = this.getMappingRelatedField(fieldName, referenceModule, formElement);
			$.each(mappingRelatedField, function (key, value) {
				let mapFieldElement = formElement.find('[name="' + key + '"]');
				if (mapFieldElement.is('select')) {
					mapFieldElement.val(mapFieldElement.find('option:first').val()).trigger('change');
				} else {
					mapFieldElement.val('');
				}
				let mapFieldDisplayElement = formElement.find('input[name="' + key + '_display"]');
				if (mapFieldDisplayElement.length > 0) {
					mapFieldDisplayElement.val('').attr('readonly', false);
					let referenceModulesList = formElement.find(
						'#' + self.moduleName + '_editView_fieldName_' + key + '_dropDown'
					);
					if (referenceModulesList.length > 0 && value[1]) {
						referenceModulesList.val(referenceModulesList.find('option:first').val()).trigger('change');
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
				let currentElement = $(e.target);
				if (e.which == 13 && !currentElement.is('textarea')) {
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
			let thisInstance = this;
			let postQuickCreateSave = function (data) {
				thisInstance.setReferenceFieldValue(container, {
					name: data.result._recordLabel,
					id: data.result._recordId
				});
			};
			let params = { callbackFunction: postQuickCreateSave };
			if (app.getViewName() === 'Edit' && !app.getRecordId()) {
				let formElement = this.getForm();
				let formData = formElement.serializeFormData();
				for (let i in formData) {
					if (!formData[i] || $.inArray(i, ['_csrf', 'action']) != -1) {
						delete formData[i];
					}
				}
				params.data = {};
				params.data.sourceRecordData = formData;
			}
			let referenceModuleName = this.getReferencedModuleName(container);
			App.Components.QuickCreate.createRecord(referenceModuleName, params);
		},
		/**
		 * Function which will register event for create of reference record
		 * This will allow users to create reference record from edit view of other record
		 */
		registerReferenceCreate: function (container) {
			let thisInstance = this;
			container.on('click', '.createReferenceRecord', function (e) {
				let element = $(e.currentTarget);
				let controlElementDiv = element.closest('.fieldValue');
				thisInstance.referenceCreateHandler(controlElementDiv);
			});
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
			LBL_ADDRESS_INFORMATION: 'a',
			LBL_ADDRESS_BILLING: 'a',
			LBL_ADDRESS_MAILING_INFORMATION: 'b',
			LBL_ADDRESS_SHIPPING: 'b',
			LBL_ADDRESS_DELIVERY_INFORMATION: 'c'
		},
		addressFieldsData: false,
		/**
		 * Function to register event for copying addresses
		 */
		registerEventForCopyAddress: function () {
			let thisInstance = this;
			this.formElement
				.find(
					'.js-toggle-panel:not(.inventoryHeader):not(.inventoryItems) .fieldValue, .js-toggle-panel:not(.inventoryHeader):not(.inventoryItems) .fieldLabel'
				)
				.each(function () {
					let block = $(this);
					let referenceModulesList = block.find('.referenceModulesList');
					if (referenceModulesList.length > 0) {
						referenceModulesList.on('change', function () {
							thisInstance.formElement
								.find('[class*="copyAddressFrom"]:not(.copyAddressFromMain, .copyAddressFromMailing)')
								.addClass('d-none');
							thisInstance.registerEventForCopyBlockAddress($(this).val(), block.find('.sourceField').attr('name'));
						});
						referenceModulesList.trigger('change');
					} else {
						let referenceFields = block.find('[name="popupReferenceModule"]');
						if (referenceFields.length > 0) {
							thisInstance.registerEventForCopyBlockAddress(
								referenceFields.val(),
								block.find('.sourceField').attr('name')
							);
						}
					}
				});
			this.formElement.find('.js-toggle-panel').each(function () {
				let hideCopyAddressLabel = true;
				$(this)
					.find('.adressAction button')
					.each(function () {
						if ($(this).hasClass('d-none') == false) {
							hideCopyAddressLabel = false;
						}
					});
				if (hideCopyAddressLabel) {
					$(this).find('.copyAddressLabel').addClass('d-none');
				}
			});
			this.formElement.find('.copyAddressFromMain').on('click', function (e) {
				let element = $(this);
				let block = element.closest('.js-toggle-panel');
				let from = element.data('label');
				let to = block.data('label');
				thisInstance.copyAddress(from, to, false, false);
			});
			this.formElement.find('.copyAddressFromMailing').on('click', function (e) {
				let element = $(this);
				let block = element.closest('.js-toggle-panel');
				let from = element.data('label');
				let to = block.data('label');
				thisInstance.copyAddress(from, to, false, false);
			});
			this.formElement.find('.copyAddressFromDelivery').on('click', function (e) {
				let element = $(this);
				let block = element.closest('.js-toggle-panel');
				let from = element.data('label');
				let to = block.data('label');
				thisInstance.copyAddress(from, to, false, false);
			});
		},
		registerEventForCopyBlockAddress: function (moduleName, fieldName) {
			const self = this;
			if (moduleName == 'Accounts') {
				self.enableCopyAddressFromModule(
					moduleName,
					self.formElement,
					'copyAddressFromAccount',
					fieldName,
					'JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS'
				);
			} else if (moduleName == 'Contacts') {
				self.enableCopyAddressFromModule(
					moduleName,
					self.formElement,
					'copyAddressFromContact',
					fieldName,
					'JS_PLEASE_SELECT_AN_CONTACT_TO_COPY_ADDRESS'
				);
			} else if (moduleName == 'Leads') {
				self.enableCopyAddressFromModule(
					moduleName,
					self.formElement,
					'copyAddressFromLead',
					fieldName,
					'JS_PLEASE_SELECT_AN_LEAD_TO_COPY_ADDRESS'
				);
			} else if (moduleName == 'Vendors') {
				self.enableCopyAddressFromModule(
					moduleName,
					self.formElement,
					'copyAddressFromVendor',
					fieldName,
					'JS_PLEASE_SELECT_AN_VENDOR_TO_COPY_ADDRESS'
				);
			}
		},
		/**
		 * Show button to copy the address details from selected module
		 */
		enableCopyAddressFromModule: function (moduleName, formElement, className, fieldName, label) {
			let thisInstance = this;
			formElement
				.find('.' + className)
				.removeClass('d-none')
				.on('click', function (e) {
					let element = $(this);
					let recordRelativeAccountId = $('[name="' + fieldName + '"]').val();
					if (recordRelativeAccountId == '' || recordRelativeAccountId == '0') {
						app.showNotify({
							text: app.vtranslate(label),
							type: 'error'
						});
					} else {
						let recordRelativeAccountName = $('#' + fieldName + '_display').val();
						let data = {
							record: recordRelativeAccountId,
							selectedName: recordRelativeAccountName,
							module: moduleName
						};
						thisInstance.copyAddressDetails(
							element.data('label'),
							element.closest('.js-toggle-panel').data('label'),
							data,
							element.closest('.js-toggle-panel')
						);
						element.attr('checked', 'checked');
					}
				});
		},
		/**
		 * Function which will copy the address details
		 */
		copyAddressDetails: function (from, to, data, container) {
			app.getRecordDetails(data).done((response) => {
				this.addressFieldsData = response['result'];
				this.copyAddress(from, to, true, data['module']);
				app.event.trigger('Edit.CopyAddress', this, from, to, response, data, container);
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
			if (relatedRecord === false || sourceModule === false) from = BlockIds[fromLabel];
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
					if (toElementLable.length > 0) toElementLable.attr('readonly', true);
					status = true;
					toElement.val(fromElement);
					toElementLable.val(fromElementLabel);
					if (toElement.is('[data-select2-id]')) {
						if (toElement.val() !== fromElement) {
							toElement.val('');
						}
						toElement.trigger('change');
					}
				} else {
					toElement.val('');
					toElement.attr('readonly', false);
				}
			}
			if (status === false) {
				let errorMsg;
				if (sourceModule === 'Accounts') {
					errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
				} else if (sourceModule === 'Contacts') {
					errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
				} else {
					errorMsg = 'JS_DOES_NOT_HAVE_AN_ADDRESS';
				}
				app.showNotify({
					text: app.vtranslate(errorMsg),
					type: 'error'
				});
			}
		},
		registerReferenceSelectionEvent: function (container) {
			let thisInstance = this;
			let relategField = container.find("input[name*='addresslevel']");
			relategField.on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
				let blockContainer = $(e.currentTarget).closest('.js-toggle-panel');
				thisInstance.copyAddressDetailsRef(data, blockContainer);
			});
		},
		copyAddressDetailsRef: function (data, container) {
			let thisInstance = this;
			app
				.getRecordDetails(data)
				.done(function (data) {
					let response = data['result'];
					thisInstance.mapAddressDetails(response, container);
				})
				.fail(function (error, err) {});
		},
		mapAddressDetails: function (result, container) {
			for (let key in result) {
				if (key.indexOf('addresslevel') != -1) {
					if (container.find('[name="' + key + '"]').length != 0) {
						container.find('[name="' + key + '"]').val(result['data'][key]);
						container.find('[name="' + key + '"]').attr('readonly', true);
						container.find('[name="' + key + '_display"]').val(result['displayData'][key]);
						container.find('[name="' + key + '_display"]').attr('readonly', true);
					}
					if (
						container.find('[name="' + key + 'a"]').length != 0 &&
						container.find('[name="' + key + 'a"]').val() == 0 &&
						result['data'][key] != 0
					) {
						container.find('[name="' + key + 'a"]').val(result['data'][key]);
						container.find('[name="' + key + 'a"]').attr('readonly', true);
						container.find('[name="' + key + 'a_display"]').val(result['displayData'][key]);
						container.find('[name="' + key + 'a_display"]').attr('readonly', true);
					}
					if (
						container.find('[name="' + key + 'b"]').length != 0 &&
						container.find('[name="' + key + 'b"]').val() == 0 &&
						result['data'][key] != 0
					) {
						container.find('[name="' + key + 'b"]').val(result['data'][key]);
						container.find('[name="' + key + 'b"]').attr('readonly', true);
						container.find('[name="' + key + 'b_display"]').val(result['displayData'][key]);
						container.find('[name="' + key + 'b_display"]').attr('readonly', true);
					}
					if (
						container.find('[name="' + key + 'c"]').length != 0 &&
						container.find('[name="' + key + 'c"]').val() == 0 &&
						result['data'][key] != 0
					) {
						container.find('[name="' + key + 'c"]').val(result['data'][key]);
						container.find('[name="' + key + 'c"]').attr('readonly', true);
						container.find('[name="' + key + 'c_display"]').val(result['displayData'][key]);
						container.find('[name="' + key + 'c_display"]').attr('readonly', true);
					}
				}
			}
		},
		registerMaskFields: function (container) {
			container.find('[data-inputmask]').inputmask();
		},
		registerSubmitEvent: function () {
			let editViewForm = this.getForm();
			editViewForm.on('submit', function (e) {
				//Form should submit only once for multiple clicks also
				if (typeof editViewForm.data('submit') !== 'undefined') {
					return false;
				} else {
					document.progressLoader = $.progressIndicator({
						message: app.vtranslate('JS_SAVE_LOADER_INFO'),
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					editViewForm.find('.js-toggle-panel').find('.js-block-content').removeClass('d-none');
					if (editViewForm.validationEngine('validate')) {
						//Once the form is submiting add data attribute to that form element
						editViewForm.data('submit', 'true');
						//on submit form trigger the recordPreSave event
						let recordPreSaveEvent = $.Event(Vtiger_Edit_Js.recordPreSave);
						editViewForm.trigger(recordPreSaveEvent, { value: 'edit' });
						if (recordPreSaveEvent.isDefaultPrevented()) {
							//If duplicate record validation fails, form should submit again
							document.progressLoader.progressIndicator({ mode: 'hide' });
							editViewForm.removeData('submit');
							e.preventDefault();
						}
					} else {
						//If validation fails, form should submit again
						document.progressLoader.progressIndicator({ mode: 'hide' });
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
			form.on(Vtiger_Edit_Js.recordPreSave, (e, data) => {
				this.preSaveValidation(form).done((response) => {
					if (response !== true) {
						e.preventDefault();
					}
				});
			});
		},
		preSaveValidation: function (form) {
			const aDeferred = $.Deferred();
			if (form.find('#preSaveValidation').val()) {
				document.progressLoader = $.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let formData = new FormData(form[0]);
				formData.append('mode', 'preSaveValidation');
				AppConnector.request({
					async: false,
					url: 'index.php',
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				})
					.done((data) => {
						document.progressLoader.progressIndicator({ mode: 'hide' });
						let response = data.result;
						for (let i in response) {
							if (response[i].result !== true) {
								if (response[i].type === 'confirm' && typeof response[i].hash !== 'undefined') {
									app.showConfirmModal({
										text: response[i].message || '',
										confirmedCallback: () => {
											let handlers = {},
												handlerElement = form.find('input[name="skipHandlers"]');
											if (handlerElement.length) {
												handlers = JSON.parse(handlerElement.val());
												handlerElement.remove();
											}
											handlers[i] = response[i].hash;
											form.append(
												$('<input>', { name: 'skipHandlers', value: JSON.stringify(handlers), type: 'hidden' })
											);
											form.submit();
										}
									});
									aDeferred.resolve(false);
									break;
								} else if (
									typeof response[i].showModal !== 'undefined' &&
									typeof response[i].showModal.url !== 'undefined'
								) {
									app.showModalWindow(null, response[i].showModal.url, function (modalContainer) {
										app.registerModalController(undefined, modalContainer, function (_, instance) {
											instance.formContainer = form;
										});
									});
								} else {
									app.showNotify({
										text: response[i].message ? response[i].message : app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								}
								if (response[i].hoverField != undefined) {
									form.find('[name="' + response[i].hoverField + '"]').focus();
								}
							}
						}
						aDeferred.resolve(data.result.length <= 0);
					})
					.fail((textStatus, errorThrown) => {
						document.progressLoader.progressIndicator({ mode: 'hide' });
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
						app.errorLog(textStatus, errorThrown);
						aDeferred.resolve(false);
					});
			} else {
				aDeferred.resolve(true);
			}

			return aDeferred.promise();
		},
		registerLeavePageWithoutSubmit: function (form) {
			if (
				typeof CKEDITOR !== 'undefined' &&
				typeof CKEDITOR.instances !== 'undefined' &&
				Object.keys(CKEDITOR.instances).length
			) {
				CKEDITOR.on('instanceReady', function (e) {
					let initialFormData = form.serialize();
					window.onbeforeunload = function (e) {
						if (initialFormData != form.serialize() && form.data('submit') != 'true') {
							return app.vtranslate('JS_CHANGES_WILL_BE_LOST');
						}
					};
				});
			} else {
				let initialFormData = form.serialize();
				window.onbeforeunload = function (e) {
					if (initialFormData != form.serialize() && form.data('submit') != 'true') {
						return app.vtranslate('JS_CHANGES_WILL_BE_LOST');
					}
				};
			}
		},
		stretchCKEditor: function () {
			let row = $('.js-editor').parents('.fieldRow');
			let td = $('.js-editor').parent();
			$(row).find('.fieldLabel').remove();
			$(td).removeClass('col-md-10');
			$(td).addClass('col-md-12');
		},
		/**
		 * Function to register event for ckeditor for description field
		 */
		registerEventForEditor: function () {
			let form = this.getForm();
			$.each(form.find('.js-editor:not(.js-inventory-item-comment)'), (key, data) => {
				this.loadEditorElement($(data));
			});
		},
		loadEditorElement: function (noteContentElement) {
			App.Fields.Text.Editor.register(noteContentElement);
		},
		registerHelpInfo: function (form) {
			if (!form) {
				form = this.getForm();
			}
			app.showPopoverElementView(form.find('.js-help-info'));
		},
		registerBlockStatusCheckOnLoad: function () {
			let blocks = this.getForm().find('.js-toggle-panel');
			let module = this.moduleName;
			blocks.each(function (index, block) {
				let currentBlock = $(block);
				if (
					currentBlock.find('.js-field-block-column').length !== 0 &&
					currentBlock.find('.js-field-block-column:not(.d-none)').length === 0
				) {
					currentBlock.addClass('d-none');
				}
				let dynamicAttr = currentBlock.attr('data-dynamic');
				if (typeof dynamicAttr !== typeof undefined && dynamicAttr !== false) {
					let headerAnimationElement = currentBlock.find('.js-block-toggle').not('.d-none');
					let bodyContents = currentBlock.find('.blockContent');
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
		/**
		 * Visibility check block
		 */
		checkVisibilityBlocks: function () {
			this.getForm()
				.find('.js-toggle-panel')
				.each(function (index, block) {
					let currentBlock = $(block);
					if (currentBlock.find('.js-field-block-column').length !== 0) {
						if (currentBlock.find('.js-field-block-column:not(.d-none)').length === 0) {
							currentBlock.addClass('d-none');
						} else {
							currentBlock.removeClass('d-none');
						}
					}
				});
		},
		registerAutoloadAddress: function () {
			const self = this;
			this.getForm()
				.find('.js-search-address')
				.each(function (_index, e) {
					let search = $(e);
					let container = search.closest('.js-block-content');
					let input = search.find('.js-autoload-address');
					input
						.autocomplete({
							source: function (request, response) {
								AppConnector.request({
									module: self.moduleName,
									action: 'Fields',
									mode: 'findAddress',
									type: search.find('.js-select-operator').val(),
									value: request.term
								})
									.done(function (requestData) {
										if (requestData.result === false) {
											app.showNotify({
												text: app.vtranslate('JS_ERROR'),
												type: 'error'
											});
										} else if (requestData.result.length) {
											response(requestData.result);
										} else {
											response([{ label: app.vtranslate('JS_NO_RESULTS_FOUND'), value: '' }]);
										}
									})
									.fail(function (_textStatus, _errorThrown, jqXHR) {
										app.showNotify({
											text: jqXHR.responseJSON.error.message,
											type: 'error',
											animation: 'show'
										});
										response([{ label: app.vtranslate('JS_NO_RESULTS_FOUND'), value: '' }]);
									});
							},
							minLength: input.data('min'),
							select: function (_event, ui) {
								$.each(ui.item.address, function (index, value) {
									let field = container.find('.fieldValue [name^=' + index + ']');
									if (field.length && value) {
										if (typeof value !== 'object') {
											value = [value];
										}
										$.each(value, function (_idx, v) {
											let select = false,
												element = false;
											if (field.prop('tagName') === 'SELECT') {
												if (typeof v === 'object') {
													$.each(v, function (idx, x) {
														element = field.find('option[data-' + idx + "='" + x + "']");
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
						})
						.autocomplete('instance')._renderItem = function (ul, item) {
						return $('<li>')
							.append(`<div><span class="fi fi-${item.countryCode} mr-2"></span>${item.label}</div>`)
							.appendTo(ul);
					};
				});
		},
		setEnabledFields: function (element) {
			let fieldValue = element.closest('.fieldValue');
			let fieldName = fieldValue.find('input.sourceField').attr('name');
			let fieldDisplay = fieldValue.find('#' + fieldName + '_display');
			fieldValue.find('button').removeAttr('disabled');
			if (fieldDisplay.val() == '') {
				fieldValue.find('input').removeAttr('readonly');
			}
			fieldValue.find('.referenceModulesListGroup').removeClass('d-none');
			let placeholder = fieldDisplay.attr('placeholderDisabled');
			fieldDisplay.removeAttr('placeholderDisabled');
			fieldDisplay.attr('placeholder', placeholder);
			fieldValue.find('.referenceModulesList').attr('required', 'required');
		},
		setDisabledFields: function (element) {
			let fieldValue = element.closest('.fieldValue');
			let fieldName = fieldValue.find('input.sourceField').attr('name');
			let fieldDisplay = fieldValue.find('#' + fieldName + '_display');
			fieldValue.find('input').attr('readonly', 'readonly');
			fieldValue.find('button').attr('disabled', 'disabled');
			fieldValue.find('.referenceModulesListGroup').addClass('d-none');
			let placeholder = fieldDisplay.attr('placeholder');
			fieldDisplay.removeAttr('placeholder');
			fieldDisplay.attr('placeholderDisabled', placeholder);
			fieldValue.find('.referenceModulesList').removeAttr('required');
		},
		getMappingRelatedField: function (sourceField, sourceFieldModule, container) {
			const mappingRelatedField = container.find('input[name="mappingRelatedField"]').val();
			const mappingRelatedModule = mappingRelatedField ? JSON.parse(mappingRelatedField) : [];
			if (
				typeof mappingRelatedModule[sourceField] !== 'undefined' &&
				typeof mappingRelatedModule[sourceField][sourceFieldModule] !== 'undefined'
			) {
				return mappingRelatedModule[sourceField][sourceFieldModule];
			}
			return [];
		},
		registerValidationsFields: function (container) {
			let params = app.validationEngineOptionsForRecord;
			container.validationEngine(params);
		},
		checkReferencesField: function (container, clear) {
			let thisInstance = this;
			let activeProcess = false,
				activeSubProcess = false;
			if (!CONFIG.fieldsReferencesDependent) {
				return false;
			}
			container.find('input[data-fieldtype="referenceLink"]').each(function (index, element) {
				element = $(element);
				let t = true;
				if (element.closest('.tab-pane').length > 0) {
					t = false;
					if (element.closest('.tab-pane.active').length > 0) {
						t = true;
					}
				}
				let referenceLink = element.val();
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

				let t = true;
				if (element.closest('.tab-pane').length > 0) {
					t = false;
					if (element.closest('.tab-pane.active').length > 0) {
						t = true;
					}
				}

				let referenceLink = element.val();
				if (t && referenceLink != '' && referenceLink != '0') {
					activeSubProcess = true;
				}
			});
			container.find('input[data-fieldtype="referenceSubProcess"]').each(function (index, element) {
				element = $(element);
				let processfieldElement = element.closest('.fieldValue');
				let length = processfieldElement.find('.referenceModulesList option[disabled!="disabled"]').length;
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
			let option = element.find('option:selected');
			if (option.data('is-quickcreate') != 1) {
				element.closest('.fieldValue').find('.createReferenceRecord').addClass('d-none');
			} else {
				element.closest('.fieldValue').find('.createReferenceRecord').removeClass('d-none');
			}
		},
		checkReferenceModulesList: function (container) {
			let thisInstance = this;
			let processfieldElement = container.find('input[data-fieldtype="referenceProcess"]').closest('.fieldValue');
			let referenceProcess = processfieldElement.find('input[name="popupReferenceModule"]').val();
			let subProcessfieldElement = container.find('input[data-fieldtype="referenceSubProcess"]').closest('.fieldValue');
			Vtiger_Helper_Js.hideOptions(subProcessfieldElement.find('.referenceModulesList'), 'parent', referenceProcess);
			let subProcessValue = subProcessfieldElement.find('.referenceModulesList').val();
			subProcessfieldElement.find('[name="popupReferenceModule"]').val(subProcessValue);
			thisInstance.checkSubProcessModulesList(subProcessfieldElement.find('.referenceModulesList'));
		},
		registerReferenceFields: function (container) {
			let thisInstance = this;
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
			container
				.find('input[data-fieldtype="referenceProcess"]')
				.closest('.fieldValue')
				.find('.referenceModulesList')
				.on('change', function () {
					thisInstance.checkReferenceModulesList(container);
				});
			container
				.find('input[data-fieldtype="referenceSubProcess"]')
				.closest('.fieldValue')
				.find('.referenceModulesList')
				.on('change', function (e) {
					thisInstance.checkSubProcessModulesList($(e.currentTarget));
				});
		},
		registerFocusFirstField: function (container, afterTimeout) {
			let elementToFocus, elementToFocusTabindex;
			if (afterTimeout === undefined && container.closest('.js-modal-container').length) {
				setTimeout((_) => {
					this.registerFocusFirstField(container, true);
				}, 500);
				return;
			}
			container
				.find(
					'.fieldValue input.form-control:not([type=hidden],.dateField,.clockPicker), .fieldValue input[type=checkbox], .select2-selection.form-control'
				)
				.each(function (i, e) {
					let element = $(e);
					if (!element.prop('readonly') && !element.prop('disabled')) {
						element = element.get(0);
						if (element.type !== 'number' && element.type !== 'checkbox' && element.value !== undefined) {
							let elemLen = element.value.length;
							element.selectionStart = elemLen;
							element.selectionEnd = elemLen;
						}
						if (i === 0 || !elementToFocus) {
							elementToFocus = element;
						}
						let tabindex = $(element).attr('tabindex');
						if (tabindex > 0 && elementToFocusTabindex === undefined) {
							elementToFocusTabindex = tabindex;
							return;
						}

						if (tabindex > 0 && tabindex < elementToFocusTabindex) {
							elementToFocusTabindex = tabindex;
							elementToFocus = element;
						}
					}
				});
			if (elementToFocus) {
				elementToFocus.focus();
			}
		},
		registerCopyValue: function (container) {
			container.find('.fieldValue [data-copy-to-field]').on('change', function (e) {
				let element = $(e.currentTarget);
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
			if (typeof Vtiger_Inventory_Js !== 'undefined') {
				this.inventoryController = Vtiger_Inventory_Js.getInventoryInstance(container);
			}
		},
		/**
		 * Register record collector modal
		 * @param {jQuery} container
		 */
		registerRecordCollectorModal: function (container) {
			const self = this;
			container.on('click', '.js-record-collector-modal', function (e) {
				e.preventDefault();
				let element = $(this);
				let formData = container.serializeFormData();
				formData['view'] = 'RecordCollector';
				formData['collectorType'] = element.data('type');
				delete formData['action'];
				AppConnector.request(formData).done(function (html) {
					app.showModalWindow(
						html,
						(container) => {
							let modalForm = container.find('form.js-record-collector__form');
							let summary = container.find('.js-record-collector__summary');
							modalForm.validationEngine(app.validationEngineOptions);
							modalForm.on('submit', function (e) {
								if (modalForm.validationEngine('validate')) {
									summary.html('');
									summary.progressIndicator({});
									e.preventDefault();
									AppConnector.request(modalForm.serializeFormData()).done(function (data) {
										summary.progressIndicator({ mode: 'hide' });
										summary.html(data);
									});
								}
							});
							let recordForm = self.getForm();
							container.on('click', '.js-record-collector__select', function () {
								container
									.find(`.js-record-collector__column[data-column="${this.dataset.column}"] input`)
									.prop('checked', true);
							});
							container.on('click', '.js-record-collector__fill_fields', function () {
								let formData = container.find('.js-record-collector__fill_form').serializeFormData();
								$.each(formData, function (key, value) {
									if (value !== '') {
										let fieldElement = recordForm.find(`[name="${key}"]`);
										if (fieldElement.length) {
											fieldElement.setValue(value);
										} else {
											recordForm.append(`<input type="hidden" name="${key}" value="${value}" />`);
										}
									}
								});
								app.hideModalWindow(null, 'collectorModal');
							});
						},
						{ modalId: 'collectorModal' }
					);
				});
			});
		},
		/**
		 * Register account name function
		 * @param {jQuery} container
		 */
		registerAccountName: function (container) {
			let first = container.find('.js-first-name');
			let firstInput = first.find('input');
			let last = container.find('.js-last-name');
			let lastInput = last.find('input');
			let full = container.find('.js-account-name');
			let fullInput = full.find('input');
			let legalForm = container.find('select[name="legal_form"]');
			let legalFormVal = legalForm.val();
			firstInput.keyup(function () {
				fullInput.val(this.value + '|##|' + lastInput.val());
			});
			lastInput.keyup(function () {
				fullInput.val(firstInput.val() + '|##|' + this.value);
			});
			legalForm.change(function () {
				if (this.value == 'PLL_NATURAL_PERSON') {
					full.addClass('d-none');
					fullInput.val(firstInput.val() + '|##|' + lastInput.val());
					first.removeClass('d-none');
					last.removeClass('d-none');
				} else if (legalFormVal == 'PLL_NATURAL_PERSON') {
					full.removeClass('d-none');
					first.addClass('d-none');
					last.addClass('d-none');
					fullInput.val('');
				}
				legalFormVal = this.value;
			});
		},
		/**
		 * Trigger record edit view events
		 * @param {object} data
		 */
		triggerRecordEditEvents: function (data) {
			const self = this,
				form = this.getForm();
			if (typeof data['changeValues'] == 'object') {
				$.each(data['changeValues'], function (_, field) {
					self.setFieldValue(field);
				});
			}
			if (typeof data['changeOptions'] != 'undefined') {
				$.each(data['changeOptions'], function (fieldName, options) {
					self.setFieldOptions(fieldName, options);
				});
			}
			if (typeof data['hoverField'] != 'undefined') {
				form.find(`[name="${data['hoverField']}"]`).focus();
			}
			if (typeof data['showNotify'] != 'undefined') {
				app.showNotify(data['showNotify']);
			}
			if (typeof data['showModal'] != 'undefined') {
				app.showModalWindow(null, data['showModal']['url']);
			}
			if (typeof data['showFields'] != 'undefined') {
				$.each(data['showFields'], function (_, fieldName) {
					form.find(`.js-field-block-column[data-field="${fieldName}"]`).removeClass('d-none');
					self.checkVisibilityBlocks();
				});
			}
			if (typeof data['hideFields'] != 'undefined') {
				$.each(data['hideFields'], function (_, fieldName) {
					form.find(`.js-field-block-column[data-field="${fieldName}"]`).addClass('d-none');
					self.checkVisibilityBlocks();
				});
			}
		},
		/**
		 * Set field value
		 * @param {object} params
		 */
		setFieldValue: function (params) {
			const fieldElement = this.getForm().find(`[name="${params['fieldName']}"]`),
				fieldInfo = fieldElement.data('fieldinfo');
			if (fieldElement.is('select')) {
				if (fieldElement.find(`option[value="${params['value']}"]`).length) {
					fieldElement.val(params['value']).trigger('change');
				} else if (fieldInfo.picklistvalues.hasOwnProperty(params['value'])) {
					fieldElement.append(new Option(params['value'], params['value'], true, true)).trigger('change');
				}
			} else {
				fieldElement.val(params['value']);
			}
		},
		/**
		 * Set field options
		 * @param {string} fieldName
		 * @param {object} options
		 */
		setFieldOptions: function (fieldName, options) {
			const fieldElement = this.getForm().find(`[name="${fieldName}"]`),
				fieldInfo = fieldElement.data('fieldinfo');
			if (fieldElement.is('select') && fieldInfo) {
				const val = fieldElement.val() ?? '',
					fieldValue = fieldElement.closest('.fieldValue'),
					currentValues = [...fieldElement.get(0).options]
						.map((o) => o.value)
						.filter((e) => e !== '')
						.sort();
				let newOptions = new $();
				if (!fieldInfo.mandatory) {
					newOptions = newOptions.add(
						new Option(app.vtranslate('JS_SELECT_AN_OPTION'), '', false, !val || !options.includes(val))
					);
				}
				$.each(options, (_, e) => {
					newOptions = newOptions.add(new Option(fieldInfo['picklistvalues'][e], e, false, val == e));
				});

				const newValues = [...newOptions.map((_, e) => e.value)].filter((e) => e !== '').sort();
				if (currentValues.length === newValues.length && currentValues.every((e, i) => e === newValues[i])) {
					return;
				}

				let selected = newOptions.filter(':selected').length > 0;
				fieldElement.html(newOptions);
				let change = val && val !== fieldElement.val();
				if ((val === '' && !selected) || change) {
					fieldElement.val(null);
				}
				if (change) {
					fieldValue.addClass('border border-info');
					fieldElement.trigger('change');
					setTimeout(function () {
						fieldValue.removeClass('border border-info');
					}, 5000);
				}
			}
		},
		/**
		 * Check if pre save validation is active
		 * @returns {bool}
		 */
		checkPreSaveValidation: function () {
			let validation = true;
			if (
				typeof app.pageController.getForm !== 'undefined' &&
				app.pageController.getForm().find('#preSaveValidation').length !== 0
			) {
				validation = app.pageController.getForm().find('#preSaveValidation').val() == 1;
			}
			return validation;
		},
		/**
		 * Register change value handler events
		 * @param {jQuery} container
		 */
		registerChangeValueHandlerEvent: function (container) {
			const event = container.find('.js-change-value-event');
			if (event.length <= 0 || event.val() === '[]') {
				return;
			}
			const fields = JSON.parse(event.val());
			$.each(fields, (_, fieldName) => {
				container
					.find(`[name="${fieldName}"],[name="${fieldName}[]"]`)
					.on(`change ${Vtiger_Edit_Js.referenceSelectionEvent} ${Vtiger_Edit_Js.referenceDeSelectionEvent}`, () => {
						this.sendChangeValueHandlerEvent(container.serializeFormData());
					});
			});
			this.sendChangeValueHandlerEvent(container.serializeFormData());
		},
		/**
		 * Send change value handler events
		 * @param {object} formData
		 */
		sendChangeValueHandlerEvent: function (formData) {
			formData['action'] = 'ChangeValueHandler';
			delete formData['view'];
			let progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			AppConnector.request(formData).done((response) => {
				$.each(response.result, (_, data) => {
					this.triggerRecordEditEvents(data);
				});
				progress.progressIndicator({ mode: 'hide' });
			});
		},
		/**
		 * Register keyboard shortcuts events
		 */
		registerKeyboardShortcutsEvent: function () {
			document.addEventListener('keydown', (event) => {
				if (event.shiftKey && event.ctrlKey && event.code === 'KeyS') {
					let form = event.target.closest('form');
					if (form) {
						$(form).trigger('submit');
					} else {
						form = $(event.target).find('form');
						if (form.length && form.hasClass('recordEditView')) {
							form.last().trigger('submit');
						}
					}
				}
			});
		},
		/**
		 * Function which will register basic events which will be used in quick create as well
		 *
		 */
		registerBasicEvents: function (container) {
			this.referenceModulePopupRegisterEvent(container);
			this.registerAutoCompleteFields(container);
			this.registerClearReferenceSelectionEvent(container);
			this.registerPreventingEnterSubmitEvent(container);
			this.registerTimeFields(container);
			this.registerRecordPreSaveEventEvent(container);
			this.registerReferenceSelectionEvent(container);
			this.registerChangeValueHandlerEvent(container);
			this.registerMaskFields(container);
			this.registerHelpInfo(container);
			this.registerReferenceFields(container);
			this.registerFocusFirstField(container);
			this.registerCopyValue(container);
			this.registerMultiImageFields(container);
			this.registerReferenceCreate(container);
			this.registerRecordCollectorModal(container);
			this.registerAccountName(container);
			this.registerKeyboardShortcutsEvent();
			App.Fields.MultiEmail.register(container);
			App.Fields.MultiDependField.register(container);
			App.Fields.Tree.register(container);
			App.Fields.MultiCurrency.register(container);
			App.Fields.MeetingUrl.register(container);
			App.Fields.ChangesJson.register(container);
			App.Fields.MultiReference.register(container);
			App.Fields.Password.register(container);
			App.Components.ActivityNotifier.register(container);
			App.Fields.MultiAttachment.register(container);
		},
		registerEvents: function () {
			let editViewForm = this.getForm();
			if (!this.proceedRegisterEvents()) {
				return;
			}
			this.registerInventoryController(editViewForm);
			app.registerBlockAnimationEvent(editViewForm);
			this.registerBlockStatusCheckOnLoad();
			this.registerEventForEditor();
			this.stretchCKEditor();
			this.registerBasicEvents(editViewForm);
			this.registerEventForCopyAddress();
			this.registerSubmitEvent();
			this.registerLeavePageWithoutSubmit(editViewForm);
			this.registerValidationsFields(editViewForm);
			this.registerAutoloadAddress();
			editViewForm.find('.js-form-submit-btn').prop('disabled', false);
		}
	}
);
