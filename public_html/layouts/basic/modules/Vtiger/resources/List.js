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
	'Vtiger_List_Js',
	{
		listInstance: false,
		getRelatedModulesContainer: false,
		massEditPreSave: 'Vtiger.MassEdit.PreSave',
		getInstance: function () {
			if (Vtiger_List_Js.listInstance === false) {
				let module = app.getModuleName(),
					parentModule = app.getParentModuleName(),
					moduleClassName,
					fallbackClassName,
					instance;
				if (parentModule == 'Settings') {
					moduleClassName = parentModule + '_' + module + '_List_Js';
					if (typeof window[moduleClassName] === 'undefined') {
						moduleClassName = module + '_List_Js';
					}
					fallbackClassName = parentModule + '_Vtiger_List_Js';
					if (typeof window[fallbackClassName] === 'undefined') {
						fallbackClassName = 'Vtiger_List_Js';
					}
				} else {
					moduleClassName = module + '_List_Js';
					fallbackClassName = 'Vtiger_List_Js';
				}
				if (typeof window[moduleClassName] !== 'undefined') {
					instance = new window[moduleClassName]();
				} else {
					instance = new window[fallbackClassName]();
				}
				Vtiger_List_Js.listInstance = instance;
				return instance;
			}
			return Vtiger_List_Js.listInstance;
		},
		/**
		 * function to trigger send Email
		 * @param {Object} params - a split object.
		 * @param {function} callBackFunction - a split object.
		 * @param {$} row - current container for reference.
		 */
		triggerSendEmail: function (params, callBackFunction, row) {
			let listInstance = Vtiger_List_Js.getInstance();
			if (row) {
				listInstance.listViewContentContainer = row.closest('.js-list__form');
			}
			if ((params && params['selected_ids']) || listInstance.checkListRecordSelected() !== true) {
				let postData = listInstance.getSearchParams();
				delete postData.parent;
				delete postData.mode;
				postData.view = 'SendMailModal';
				postData.cvid = listInstance.getCurrentCvId();
				if (params) {
					$.extend(postData, params);
				}
				AppConnector.request(postData).done(function (response) {
					app.showModalWindow(response, function (data) {
						data.find('[name="saveButton"]').on('click', function (e) {
							if (data.find('form').validationEngine('validate')) {
								$.extend(postData, {
									field: data.find('#field').val(),
									template: data.find('#template').val(),
									mailNotes: data.find('#mail_notes').val(),
									action: 'Mail',
									mode: 'sendMails'
								});
								delete postData.view;
								AppConnector.request(postData)
									.done(function (response) {
										if (response.result == true) {
											app.hideModalWindow();
											if (typeof callBackFunction == 'function') {
												callBackFunction(response);
											}
										}
									})
									.fail(function () {
										app.hideModalWindow();
									});
							}
						});
					});
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		triggerMassQuickCreate: function (moduleName, data) {
			let listInstance = Vtiger_List_Js.getInstance();
			if (listInstance.checkListRecordSelected() != true) {
				let progress = $.progressIndicator({ blockInfo: { enabled: true } });
				let params = {
					callbackFunction: function () {},
					noCache: true,
					data: $.extend(data, listInstance.getSearchParams(), { sourceView: 'ListView' })
				};
				App.Components.QuickCreate.getForm(
					'index.php?module=' + moduleName + '&view=MassQuickCreateModal&sourceModule=' + app.getModuleName(),
					moduleName,
					params
				).done((data) => {
					progress.progressIndicator({
						mode: 'hide'
					});
					App.Components.QuickCreate.showModal(data, params);
					app.registerEventForClockPicker();
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		triggerTransferOwnership: function (massActionUrl) {
			let thisInstance = this;
			let listInstance = Vtiger_List_Js.getInstance();
			let validationResult = listInstance.checkListRecordSelected();
			if (validationResult != true) {
				let progressIndicatorElement = $.progressIndicator();
				thisInstance.getRelatedModulesContainer = false;
				let actionParams = {
					type: 'POST',
					url: massActionUrl,
					dataType: 'html',
					data: {}
				};
				AppConnector.request(actionParams).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (data) {
						let callback = function (data) {
							let params = { ...app.validationEngineOptions };
							params.onValidationComplete = function (form, valid) {
								if (valid) {
									thisInstance.transferOwnershipSave(form);
								}
								return false;
							};
							data.find('#changeOwner').validationEngine(params);
						};
						app.showModalWindow(data, function (data) {
							let selectElement = thisInstance.getRelatedModuleContainer();
							App.Fields.Picklist.changeSelectElementView(selectElement, 'select2');
							if (typeof callback == 'function') {
								callback(data);
							}
						});
					}
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		triggerQuickExport: function (module) {
			const progressIndicatorElement = $.progressIndicator();
			let url = 'index.php?module=' + module + '&view=ExportRecords';
			app.showModalWindow(null, url, function (container) {
				container.find('.js-modal__save').on('click', (_e) => {
					let formData = container.find('.js-modal-form').serializeFormData();
					const listInstance = Vtiger_List_Js.getInstance();
					$.extend(formData, listInstance.getSearchParams());
					AppConnector.requestForm('index.php', formData);
					Vtiger_Helper_Js.showMessage({
						text: app.vtranslate('JS_STARTED_GENERATING_FILE'),
						type: 'info'
					});
					app.hideModalWindow();
				});
			});
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		},
		transferOwnershipSave: function (form) {
			const listInstance = Vtiger_List_Js.getInstance();
			let transferOwner = $('#transferOwnerId').val(),
				relatedModules = $('#related_modules').val(),
				params = {
					module: app.getModuleName(),
					action: 'TransferOwnership',
					transferOwnerId: transferOwner,
					related_modules: relatedModules
				};
			params = $.extend(params, listInstance.getSearchParams());
			delete params.view;
			AppConnector.request(params).done((response) => {
				app.hideModalWindow();
				listInstance.getListViewRecords();
				Vtiger_List_Js.clearList();
				if (response.result.success) {
					Vtiger_Helper_Js.showMessage({
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
						type: 'info'
					});
				} else {
					Vtiger_Helper_Js.showMessage(response.result.notify);
				}
			});
		},
		/**
		 * Function to get the related module container
		 */
		getRelatedModuleContainer: function () {
			if (this.getRelatedModulesContainer == false) {
				this.getRelatedModulesContainer = $('#related_modules');
			}
			return this.getRelatedModulesContainer;
		},
		triggerMassAction: function (massActionUrl, callBackFunction, beforeShowCb, css) {
			if (typeof beforeShowCb === 'undefined') {
				beforeShowCb = function () {
					return true;
				};
			}
			if (typeof beforeShowCb == 'object') {
				css = beforeShowCb;
				beforeShowCb = function () {
					return true;
				};
			}
			let progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let actionParams = {
				type: 'POST',
				url: massActionUrl,
				dataType: 'html',
				data: Vtiger_List_Js.getInstance().getSearchParams()
			};
			if (typeof css === 'undefined') {
				css = {};
			}
			css = $.extend({ 'text-align': 'left' }, css);
			AppConnector.request(actionParams)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (data) {
						let result = beforeShowCb(data);
						if (!result) {
							return;
						}
						app.showModalWindow(
							data,
							function (data) {
								if (typeof callBackFunction == 'function') {
									callBackFunction(data);
								}
							},
							css
						);
					}
				})
				.fail(function (error, err) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify({
						title: app.vtranslate('JS_MESSAGE'),
						text: err,
						type: 'error'
					});
				});
		},
		triggerMassEdit: function (massEditUrl) {
			let listInstance = Vtiger_List_Js.getInstance();
			if (listInstance.checkListRecordSelected() !== true) {
				let selectedCount = this.getSelectedRecordCount();
				if (selectedCount > $('#listMaxEntriesMassEdit').val()) {
					let params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_MASS_EDIT_LIMIT'),
						type: 'error'
					};
					app.showNotify(params);
					return;
				}
				Vtiger_List_Js.triggerMassAction(
					massEditUrl,
					function (container) {
						app.event.trigger('MassEditModal.AfterLoad', container, massEditUrl);
						let massEditForm = container.find('#massEdit');
						massEditForm.validationEngine(app.validationEngineOptions);
						listInstance.registerEventForTabClick(massEditForm);
						let editInstance = Vtiger_Edit_Js.getInstance();
						editInstance.registerBasicEvents(massEditForm);
						listInstance.postMassEdit(container);
						listInstance.registerSlimScrollMassEdit();
						if ($('#massEditContainer').length) {
							listInstance.inactiveFieldsValidation($('#massEditContainer').find('form'));
						}
					},
					{ width: '65%' }
				);
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		getSelectedRecordCount: function () {
			let count;
			let listInstance = Vtiger_List_Js.getInstance();
			let cvId = listInstance.getCurrentCvId();
			let selectedIdObj = $('#selectedIds').data(cvId + 'selectedIds');
			if (selectedIdObj != undefined) {
				if (selectedIdObj != 'all') {
					count = selectedIdObj.length;
				} else {
					let excludedIdsCount = $('#excludedIds').data(cvId + 'Excludedids').length;
					let totalRecords = $('#recordsCount').val();
					count = totalRecords - excludedIdsCount;
				}
			}
			return count;
		},
		/**
		 * function to trigger export action
		 * returns UI
		 */
		triggerExportAction: function (exportActionUrl, newTab) {
			let formAttr = {};
			if (newTab) {
				formAttr['target'] = '_blank';
			}
			let params = Vtiger_List_Js.getInstance().getSearchParams();
			delete params.view;
			AppConnector.requestForm(exportActionUrl, params, formAttr);
		},
		/**
		 * Function to reload list
		 */
		clearList: function () {
			$('#deSelectAllMsg').trigger('click');
			$('#selectAllMsgDiv').hide();
		},
		triggerListSearch: function () {
			let listInstance = Vtiger_List_Js.getInstance();
			let listViewContainer = listInstance.getListViewContentContainer();
			listViewContainer.find('[data-trigger="listSearch"]').trigger('click');
		},
		getSelectedRecordsParams: function (checkList) {
			let listInstance = Vtiger_List_Js.getInstance();
			if (checkList == false || listInstance.checkListRecordSelected() !== true) {
				return listInstance.getSearchParams();
			} else {
				listInstance.noRecordSelectedAlert();
			}
			return false;
		},
		triggerGenerateRecords: function () {
			let selected = Vtiger_List_Js.getSelectedRecordsParams();
			if (selected === false) {
				return false;
			}
			selected.view = 'GenerateModal';
			selected.fromview = 'List';
			let progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			app.showModalWindow(null, 'index.php?' + $.param(selected), function () {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
			});
		},
		showMap: function () {
			let selectedParams = Vtiger_List_Js.getSelectedRecordsParams(false);
			let url = 'index.php?module=OpenStreetMap&view=MapModal&srcModule=' + app.getModuleName();
			app.showModalWindow(null, url, function (container) {
				let mapView = new OpenStreetMap_Map_Js();
				mapView.setSelectedParams(selectedParams);
				mapView.registerModalView(container);
			});
		},
		triggerReviewChanges: function (reviewUrl) {
			let listInstance = Vtiger_List_Js.getInstance();
			let validationResult = listInstance.checkListRecordSelected();
			if (validationResult !== true) {
				app.showConfirmModal({
					icon: 'fa fa-check-circle',
					title: app.vtranslate('JS_LBL_REVIEW_CHANGES'),
					text: app.vtranslate('JS_MASS_REVIEWING_CHANGES_CONFIRMATION'),
					confirmedCallback: () => {
						let params = { ...listInstance.getSearchParams() };
						let deleteMessage = app.vtranslate('JS_LOADING_PLEASE_WAIT');
						let progressIndicatorElement = $.progressIndicator({
							message: deleteMessage,
							position: 'html',
							blockInfo: {
								enabled: true
							}
						});
						delete params.view;
						delete params.module;
						delete params.parent;
						AppConnector.request({
							url: reviewUrl,
							data: params
						})
							.done(function (data) {
								progressIndicatorElement.progressIndicator({
									mode: 'hide'
								});
								if (data.result) {
									let params = {
										text: data.result,
										type: 'info'
									};
									app.showNotify(params);
								} else {
									listInstance.getListViewRecords();
								}
							})
							.fail(function (error, err) {
								app.errorLog(error, err);
							});
					},
					rejectedCallback: () => {
						Vtiger_List_Js.clearList();
					}
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		/**
		 * Function to register the submit event for mass comment
		 */
		triggerMassComment: function (massActionUrl) {
			let listInstance = Vtiger_List_Js.getInstance();
			if (!listInstance.checkListRecordSelected()) {
				Vtiger_List_Js.triggerMassAction(massActionUrl, (data) => {
					new App.Fields.Text.Completions($(data).find('.js-completions').eq(0));
					$(data).on('submit', '#massSave', (e) => {
						e.preventDefault();
						let form = $(e.currentTarget),
							commentContent = form.find('.js-comment-content'),
							commentContentValue = commentContent.html();
						if (commentContentValue === '') {
							let errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY');
							commentContent.validationEngine('showPrompt', errorMsg, 'error', 'bottomLeft', true);
							return;
						}
						form.find('.js-comment-value').val(commentContentValue);
						commentContent.validationEngine('hide');
						form.find('[name=saveButton]').attr('disabled', 'disabled');
						listInstance.massActionSave(form).done(function () {
							Vtiger_List_Js.clearList();
						});
					});
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		}
	},
	{
		//contains the List View element.
		listViewContainer: false,
		//Contains list view top menu element
		listViewTopMenuContainer: false,
		//Contains list view content element
		listViewContentContainer: false,
		//Contains filter Block Element
		filterBlock: false,
		filterSelectElement: false,
		listSearchInstance: false,
		noEventsListSearch: true,
		//Contains float table head
		listFloatThead: false,
		getListSearchInstance: function (events) {
			if (events != undefined) {
				this.noEventsListSearch = events;
			}
			if (
				this.listSearchInstance == false &&
				(this.getListViewContainer().find('.searchField').length ||
					this.getListViewContainer().find('.picklistSearchField').length)
			) {
				this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(
					this.getListViewContainer(),
					this.noEventsListSearch
				);
			}
			return this.listSearchInstance;
		},
		getListViewContainer: function () {
			if (this.listViewContainer == false) {
				this.listViewContainer = $('div.listViewPageDiv');
			}
			return this.listViewContainer;
		},
		getListViewTopMenuContainer: function () {
			if (this.listViewTopMenuContainer == false) {
				this.listViewTopMenuContainer = $('.listViewTopMenuDiv');
			}
			return this.listViewTopMenuContainer;
		},
		getListViewContentContainer: function () {
			if (this.listViewContentContainer == false) {
				this.listViewContentContainer = $('.listViewContentDiv');
			}
			return this.listViewContentContainer;
		},
		getFilterBlock: function () {
			if (this.filterBlock == false) {
				let filterSelectElement = this.getFilterSelectElement();
				if (filterSelectElement.length <= 0) {
					this.filterBlock = $();
				} else if (filterSelectElement.is('select')) {
					this.filterBlock = filterSelectElement.data('select2').$dropdown;
				}
			}
			return this.filterBlock;
		},
		getFilterSelectElement: function () {
			if (this.filterSelectElement == false) {
				this.filterSelectElement = $('#customFilter');
			}
			return this.filterSelectElement;
		},
		getSearchParams() {
			let params = this.getDefaultParams();
			if (this.checkListRecordSelected() !== true) {
				params.selected_ids = this.readSelectedIds(true);
				params.excluded_ids = this.readExcludedIds(true);
			}
			return params;
		},
		getDefaultParams: function () {
			let params = {
				module: app.getModuleName(),
				view: app.getViewName(),
				viewname: this.getCurrentCvId(),
				page: $('#pageNumber').val(),
				orderby: $('#orderBy').val(),
				entityState: $('#entityState').val()
			};
			if (app.getUrlVar('mid')) {
				params.mid = app.getUrlVar('mid');
			}
			if (app.getParentModuleName()) {
				params.parent = app.getParentModuleName();
			}
			if ($('#sortOrder').length) {
				params.sortorder = $('#sortOrder').val();
			}
			const advancedConditions = $('#listViewContents .js-custom-view-adv-cond');
			if (advancedConditions.length) {
				params.advancedConditions = advancedConditions.val();
			}
			const listSearchInstance = this.getListSearchInstance();
			if (listSearchInstance !== false) {
				let searchValue = listSearchInstance.getAlphabetSearchValue();
				params.search_params = listSearchInstance.getListSearchParams(true);
				if (typeof searchValue !== 'undefined' && searchValue.length > 0) {
					params.search_key = listSearchInstance.getAlphabetSearchField();
					params.search_value = searchValue;
					params.operator = 's';
				}
				listSearchInstance.parseConditions(params);
				params.search_params = JSON.stringify(params.search_params);
			}
			return params;
		},

		/**
		 * Function which will give you all the list view params
		 */
		getListViewRecords: function (urlParams) {
			let aDeferred = $.Deferred();
			if (typeof urlParams === 'undefined') {
				urlParams = {};
			}
			let thisInstance = this;
			let listViewContentsContainer = $('#listViewContents');
			let loadingMessage = $('.listViewLoadingMsg').text();
			let progressIndicatorElement = $.progressIndicator({
				message: loadingMessage,
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let defaultParams = this.getDefaultParams();
			urlParams = $.extend(defaultParams, urlParams);
			AppConnector.requestPjax(urlParams)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					listViewContentsContainer.html(data);
					app.event.trigger('RecordListView.AfterLoad', data, thisInstance);
					thisInstance.calculatePages().done(function (data) {
						aDeferred.resolve(data);
						// Let listeners know about page state change.
						app.notifyPostAjaxReady();
					});
					thisInstance.postLoadListViewRecordsEvents(listViewContentsContainer);
					thisInstance.massUpdatePagination(urlParams);
					Vtiger_List_Js.clearList();
				})
				.fail(function (textStatus, errorThrown) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: app.vtranslate('JS_NOT_ALLOWED_VALUE'),
						type: 'error'
					});
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		postLoadListViewRecordsEvents: function (container) {
			const self = this;
			this.registerPostLoadDesktopEvents(container);
			App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'));
			App.Fields.Picklist.changeSelectElementView(container);
			let searchInstance = self.getListSearchInstance();
			if (searchInstance !== false) {
				searchInstance.registerBasicEvents();
			}
			Vtiger_Index_Js.registerMailButtons(container);
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
			let selectedIds = self.readSelectedIds();
			if (selectedIds != '') {
				if (selectedIds == 'all') {
					$('.listViewEntriesCheckBox').each(function (index, element) {
						$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
					});
					$('#deSelectAllMsgDiv').show();
					let excludedIds = self.readExcludedIds();
					if (excludedIds != '') {
						$('#listViewEntriesMainCheckBox').prop('checked', false);
						$('.listViewEntriesCheckBox').each(function (index, element) {
							if ($.inArray($(element).val(), excludedIds) != -1) {
								$(element).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
							}
						});
					}
				} else {
					$('.listViewEntriesCheckBox').each(function (index, element) {
						if ($.inArray($(element).val(), selectedIds) != -1) {
							$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
						}
					});
				}
				self.checkSelectAll();
			}
			self.registerUnreviewedCountEvent();
			self.registerLastRelationsEvent();
		},
		/**
		 * Function to calculate number of pages
		 */
		calculatePages: function () {
			let aDeferred = $.Deferred();
			let element = $('#totalPageCount');
			let totalPageNumber = element.text();
			if (totalPageNumber == '') {
				let totalRecordCount = $('#totalCount').val();
				if (totalRecordCount != '') {
					let pageLimit = $('#pageLimit').val();
					if (pageLimit == '0') pageLimit = 1;
					let pageCount = Math.ceil(totalRecordCount / pageLimit);
					if (pageCount == 0) {
						pageCount = 1;
					}
					element.text(pageCount);
					aDeferred.resolve();
					return aDeferred.promise();
				}
				aDeferred.resolve();
			} else {
				aDeferred.resolve();
			}
			return aDeferred.promise();
		},
		/**
		 * Function to return alerts if no records selected.
		 */
		noRecordSelectedAlert: function (text = 'JS_PLEASE_SELECT_ONE_RECORD') {
			app.showNotify({
				text: app.vtranslate(text),
				type: 'error'
			});
		},
		massActionSave: function (form, isMassEdit) {
			if (typeof isMassEdit === 'undefined') {
				isMassEdit = false;
			}
			let aDeferred = $.Deferred();
			if (isMassEdit) {
				let massEditPreSaveEvent = $.Event(Vtiger_List_Js.massEditPreSave);
				form.trigger(massEditPreSaveEvent);
				if (massEditPreSaveEvent.isDefaultPrevented()) {
					form.find('[name="saveButton"]').removeAttr('disabled');
					aDeferred.reject();
					return aDeferred.promise();
				}
				form.find('[id^="selectRow"]').each(function (index, checkbox) {
					checkbox = $(checkbox);
					if (!checkbox.prop('checked')) {
						checkbox
							.closest('.js-form-row-container')
							.find('.fieldValue [name]')
							.each(function (index, element) {
								element = $(element);
								element.attr('data-element-name', element.attr('name')).removeAttr('name');
							});
					}
				});
			}
			let massActionUrl = form.serializeFormData();
			let progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(massActionUrl)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({
						mode: 'hide'
					});
					app.hideModalWindow();
					if (!data.result) {
						let params = {
							text: app.vtranslate('JS_MASS_EDIT_NOT_SUCCESSFUL'),
							type: 'info'
						};
						app.showNotify(params);
					}
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {
					app.hideModalWindow();
					app.errorLog(error, err);
					aDeferred.reject(error, err);
				});
			return aDeferred.promise();
		},
		checkSelectAll: function () {
			let state = true;
			$('.listViewEntriesCheckBox').each(function (index, element) {
				if ($(element).is(':checked')) {
					state = true;
				} else {
					state = false;
					return false;
				}
			});
			if (state == true) {
				$('#listViewEntriesMainCheckBox').prop('checked', true);
			} else {
				$('#listViewEntriesMainCheckBox').prop('checked', false);
			}
		},
		getRecordsCount: function () {
			let aDeferred = $.Deferred();
			let recordCountVal = $('#recordsCount').val();
			if (recordCountVal != '') {
				aDeferred.resolve(recordCountVal);
			} else {
				let count = '';
				let params = this.getDefaultParams();
				params.view = 'ListAjax';
				params.mode = 'getRecordsCount';
				AppConnector.request(params).done(function (data) {
					let response = JSON.parse(data);
					$('#recordsCount').val(response['result']['count']);
					count = response['result']['count'];
					aDeferred.resolve(count);
				});
			}

			return aDeferred.promise();
		},
		getSelectOptionFromChosenOption: function (liElement) {
			let id = liElement.attr('id');
			let idArr = id.split('-');
			let currentOptionId = '';
			if (idArr.length > 0) {
				currentOptionId = idArr[idArr.length - 1];
			} else {
				return false;
			}
			return $('#filterOptionId_' + currentOptionId);
		},
		readSelectedIds: function (decode) {
			let cvId = this.getCurrentCvId();
			let selectedIdsElement = $('#selectedIds');
			if (selectedIdsElement.length <= 0) {
				return '';
			}
			let selectedIdsDataAttr = cvId + 'selectedIds';
			let selectedIdsElementDataAttributes = selectedIdsElement.data();
			let selectedIds = [];
			if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes)) {
				this.writeSelectedIds(selectedIds);
			} else {
				selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
			}
			if (decode == true) {
				if (typeof selectedIds == 'object') {
					return JSON.stringify(selectedIds);
				}
			}
			return selectedIds;
		},
		readExcludedIds: function (decode) {
			let cvId = this.getCurrentCvId();
			let excludedIds = [];
			let exlcudedIdsElement = $('#excludedIds');
			let excludedIdsDataAttr = cvId + 'Excludedids';
			let excludedIdsElementDataAttributes = exlcudedIdsElement.data();
			if (!(excludedIdsDataAttr in excludedIdsElementDataAttributes)) {
				this.writeExcludedIds(excludedIds);
			} else {
				excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
			}
			if (decode == true) {
				if (typeof excludedIds == 'object') {
					return JSON.stringify(excludedIds);
				}
			}
			return excludedIds;
		},
		writeSelectedIds: function (selectedIds) {
			let cvId = this.getCurrentCvId();
			if (!Array.isArray(selectedIds)) {
				selectedIds = [selectedIds];
			}
			$('#selectedIds').data(cvId + 'selectedIds', selectedIds);
		},
		writeExcludedIds: function (excludedIds) {
			let cvId = this.getCurrentCvId();
			$('#excludedIds').data(cvId + 'Excludedids', excludedIds);
		},
		getCurrentCvId: function () {
			return $('#customFilter').find('option:selected').data('id');
		},
		getAlphabetSearchField: function () {
			return $('#alphabetSearchKey').val();
		},
		getAlphabetSearchValue: function () {
			return $('#alphabetValue').val();
		},
		/**
		 * Function to check whether atleast minNumberOfRecords is checked
		 * @param {number} minNumberOfRecords
		 * @returns {boolean}
		 */
		checkListRecordSelected(minNumberOfRecords = 1) {
			if (
				(this.listViewContentContainer != false &&
					this.listViewContentContainer.length != 0 &&
					this.listViewContentContainer.find('#selectedIds').length == 0) ||
				((this.listViewContentContainer == false || this.listViewContentContainer.length == 0) &&
					$('#selectedIds').length == 0)
			) {
				return true;
			}
			let selectedIds = this.readSelectedIds();
			if (typeof selectedIds === 'object' && selectedIds.length < minNumberOfRecords) {
				return true;
			}
			return false;
		},
		inactiveFieldValidation: function (field) {
			field.validationEngine('hide');
			let form = field.closest('form');
			let invalidFields = form.data('jqv').InvalidFields;
			let fields = [field.get(0)];
			field.attr('data-invalid-validation-engine', field.attr('data-validation-engine'));
			field.removeAttr('data-validation-engine');

			if (field.is('select') && field.hasClass('select2')) {
				let selectElement = app.getSelect2ElementFromSelect(field);
				selectElement.validationEngine('hide');
				fields.push(selectElement.get(0));
			}
			for (let i in fields) {
				let response = $.inArray(fields[i], invalidFields);
				if (response != '-1') {
					invalidFields.splice(response, 1);
				}
			}
		},
		activeFieldValidation: function (field) {
			let validationVal = field.attr('data-invalid-validation-engine');
			if (typeof validationVal === 'undefined') return;
			field.attr('data-validation-engine', validationVal);
			field.removeAttr('data-invalid-validation-engine');
		},
		postMassEdit: function (massEditContainer) {
			let thisInstance = this;
			let editInstance = Vtiger_Edit_Js.getInstance();
			massEditContainer.find('.selectRow').on('change', function (e) {
				let element = $(e.currentTarget);
				let blockElement = element.closest('.js-form-row-container').find('.fieldValue');
				let fieldElement = blockElement.find('[data-validation-engine],[data-invalid-validation-engine]');
				let fieldInfo = fieldElement.data('fieldinfo');
				if (element.prop('checked')) {
					thisInstance.activeFieldValidation(fieldElement);
				} else {
					thisInstance.inactiveFieldValidation(fieldElement);
				}
				if (fieldInfo !== undefined && fieldInfo.type === 'reference') {
					let mapFields = editInstance.getMappingRelatedField(
						fieldInfo.name,
						editInstance.getReferencedModuleName(blockElement),
						massEditContainer
					);
					$.each(mapFields, function (key, value) {
						let checkboxElement = massEditContainer.find('[id="selectRow' + key + '"]');
						if (checkboxElement.length && checkboxElement.prop('disabled')) {
							checkboxElement.prop('disabled', false);
							checkboxElement.trigger('click');
							checkboxElement.prop('disabled', true);
						}
					});
				}
			});
			massEditContainer.find('form').on('submit', function (e) {
				let form = $(e.currentTarget);
				if (typeof form.data('submit') !== 'undefined') {
					return false;
				}
				if (form.validationEngine('validate')) {
					e.preventDefault();
					if (!form.find('input[id^="selectRow"]:checked').length) {
						app.showNotify({
							text: app.vtranslate('JS_NONE_FIELD_MARKED_IN_MASS_EDIT'),
							type: 'error'
						});
						return;
					}
					let invalidFields = form.data('jqv').InvalidFields;
					if (invalidFields.length == 0) {
						form.find('[name="saveButton"]').prop('disabled', true);
					} else {
						return;
					}
					thisInstance
						.massActionSave(form, true)
						.done(function () {
							thisInstance.getListViewRecords();
							Vtiger_List_Js.clearList();
						})
						.fail(function (error, err) {
							app.errorLog(error, err);
						});
				} else {
					form.removeData('submit');
					app.formAlignmentAfterValidation(form);
				}
			});
		},
		/**
		 * Function to go to page
		 * @param {int} page
		 */
		paginationGoToPage(page) {
			const self = this,
				listViewPageDiv = self.getListViewContainer();
			let aDeferred = $.Deferred(),
				pageNumber = listViewPageDiv.find('#pageNumber');
			pageNumber.val(page);
			listViewPageDiv.find('.js-page-jump').val(page);
			self
				.getListViewRecords({
					orderby: listViewPageDiv.find('#orderBy').val(),
					sortorder: listViewPageDiv.find('#sortOrder').val(),
					viewname: self.getCurrentCvId()
				})
				.done(function () {
					aDeferred.resolve();
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
		},
		/**
		 * Function to register List view Page Navigation
		 */
		registerPageNavigationEvents() {
			const listViewPageDiv = this.getListViewContainer();
			listViewPageDiv.find('.js-next-page').on('click', (e) => {
				this.jumpToNextPage(e);
			});
			listViewPageDiv.find('.js-page--previous').on('click', () => {
				this.jumpToPreviousPage();
			});
			listViewPageDiv.find('.pageNumber').on('click', (e) => {
				this.jumpToClickedPage($(e.currentTarget));
			});
			listViewPageDiv.find('.js-count-number-records').on('click', () => {
				this.updatePaginationAjax(true);
			});
			listViewPageDiv
				.find('.js-page--jump-drop-down')
				.on('click', 'li', (e) => {
					e.stopImmediatePropagation();
				})
				.on('keypress', '.js-page-jump', (e) => {
					this.jumpToPage(e);
				});
		},
		/**
		 * Jump to next page
		 * @param {$} element
		 */
		jumpToNextPage(element) {
			if ($(element.currentTarget).hasClass('disabled')) {
				return;
			}
			const listViewPageDiv = this.getListViewContainer();
			if (listViewPageDiv.find('#noOfEntries').val() === listViewPageDiv.find('#pageLimit').val()) {
				this.paginationGoToPage(parseInt(listViewPageDiv.find('#pageNumber').val()) + 1);
			}
		},
		/**
		 * Jump to previous page
		 */
		jumpToPreviousPage() {
			let pageNumber = this.getListViewContainer().find('#pageNumber');
			if (pageNumber.val() > 1) {
				this.paginationGoToPage(parseInt(pageNumber.val()) - 1);
			}
		},
		/**
		 * Jump to clicked page function
		 * @param {$} element
		 */
		jumpToClickedPage(element) {
			if (element.hasClass('disabled')) {
				return false;
			}
			this.paginationGoToPage(element.data('id'));
		},
		/**
		 * Jump to page function
		 * @param {$.Event} e
		 * @returns {boolean}
		 */
		jumpToPage(e) {
			const self = this,
				listViewPageDiv = this.getListViewContainer();
			if (13 === e.which) {
				e.stopImmediatePropagation();
				let element = $(e.currentTarget),
					response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
				if (typeof response !== 'undefined') {
					element.validationEngine('showPrompt', response, '', 'topLeft', true);
				} else {
					element.validationEngine('hideAll');
					let currentPageElement = listViewPageDiv.find('#pageNumber'),
						currentPageNumber = parseInt(currentPageElement.val()),
						newPageNumber = parseInt(element.val()),
						totalPages = parseInt(listViewPageDiv.find('.js-page--total').text());
					if (newPageNumber > totalPages) {
						element.validationEngine('showPrompt', app.vtranslate('JS_PAGE_NOT_EXIST'), '', 'topLeft', true);
						return;
					}
					if (newPageNumber === currentPageNumber) {
						Vtiger_Helper_Js.showMessage({
							text: app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + ' ' + newPageNumber,
							type: 'info'
						});
						return;
					}
					currentPageElement.val(newPageNumber);
					self.getListViewRecords();
				}
				return false;
			}
		},
		/**
		 * Function to get page count and total number of records in list
		 */
		getPageCount: function () {
			let aDeferred = $.Deferred();
			let pageCountParams = this.getPageJumpParams();
			AppConnector.request(pageCountParams)
				.done(function (data) {
					let response;
					if (typeof data != 'object') {
						response = JSON.parse(data);
					} else {
						response = data;
					}
					aDeferred.resolve(response);
				})
				.fail(function (error, err) {});
			return aDeferred.promise();
		},
		/**
		 * Function to get Page Jump Params
		 */
		getPageJumpParams: function () {
			let params = this.getDefaultParams();
			params.view = 'ListAjax';
			params.mode = 'getPageCount';

			return params;
		},
		/**
		 * Function to update Pagining status
		 */
		updatePagination: function (pageNumber) {
			pageNumber = typeof pageNumber !== 'undefined' ? pageNumber : 1;
			AppConnector.request(
				Object.assign(this.getDefaultParams(), {
					module: app.getModuleName(),
					view: 'Pagination',
					page: pageNumber,
					mode: 'getPagination',
					sourceModule: $('#moduleFilter').val(),
					totalCount: $('.pagination').data('totalCount'),
					noOfEntries: $('#noOfEntries').val()
				})
			).done((data) => {
				$('.paginationDiv').html(data);
				this.registerPageNavigationEvents();
			});
		},
		/**
		 * Function to update pagination page numer
		 * @param {boolean} force
		 */
		updatePaginationAjax(force = false) {
			const self = this,
				listViewPageDiv = this.getListViewContainer();
			let params = self.getDefaultParams(),
				container = listViewPageDiv.find('.paginationDiv');
			Vtiger_Helper_Js.showMessage({
				title: app.vtranslate('JS_LBL_PERMISSION'),
				text: app.vtranslate('JS_GET_PAGINATION_INFO'),
				type: 'info'
			});
			if (container.find('.js-pagination-list').data('total-count') > 0 || force) {
				params.totalCount = -1;
				params.view = 'Pagination';
				params.mode = 'getPagination';
				AppConnector.request(params).done(function (data) {
					container.html(data);
					self.registerPageNavigationEvents();
				});
			}
		},
		/**
		 * Function to register the event for changing the custom Filter
		 */
		registerChangeCustomFilterEvent: function (event) {
			let target = $(event.currentTarget);
			let selectOption = '';
			let selectOptionId = '';
			let textOption = '';
			if (target.is('option')) {
				selectOption = target;
			} else if (event.type === 'select2:selecting') {
				selectOptionId = event.params.args.data.id;
				selectOption = $(`#filterOptionId_${selectOptionId}`);
			} else if (event.type === 'mouseup') {
				selectOptionId = event.currentTarget.id.split('-').pop();
				selectOption = $(`#filterOptionId_${selectOptionId}`);
				this.getFilterSelectElement().val(event.currentTarget.id.split('-').pop()).trigger('change');
			}

			if ($(`.nav-item[data-cvid='${selectOptionId}'] .nav-link`).tab('show').length === 0) {
				$('.js-filter-tab .active').removeClass('active');
			}

			if (typeof selectOption === 'object') {
				textOption = selectOption.text();
			}

			$('#select2-customFilter-container span').contents().last().replaceWith(textOption);
			app.setMainParams('pageNumber', '1');
			app.setMainParams('pageToJump', '1');
			app.setMainParams('orderBy', selectOption.data('orderby'));
			app.setMainParams('sortOrder', selectOption.data('sortorder'));
			let urlParams = {
				viewname: selectOption.val(),
				//to make alphabetic search empty
				search_key: this.getAlphabetSearchField(),
				search_value: '',
				search_params: '',
				advancedConditions: ''
			};
			let tileSelect = this.getListViewTopMenuContainer().find('.js-selected-tile-size');
			if (tileSelect.length > 0) {
				urlParams.tile_size = tileSelect.attr('data-selected-tile-size');
			}
			//Make the select all count as empty
			$('#recordsCount').val('');
			//Make total number of pages as empty
			$('#totalPageCount').text('');
			$('.pagination').data('totalCount', 0);
			this.getListViewRecords(urlParams).done(() => {
				this.breadCrumbsFilter(selectOption.text());
				this.ListViewPostOperation();
				this.updatePagination(1);
				if (tileSelect.length > 0) {
					const tileInstance = new Vtiger_Tiles_Js();
					tileInstance.contentContainer = this.getListViewContainer();
					tileInstance.setHeightOfTiles();
				}
			});
			event.stopPropagation();
		},
		/**
		 * Function to register the event listeners for changing the custom Filter
		 */
		registerChangeCustomFilterEventListeners() {
			let filterSelect = this.getFilterSelectElement();
			filterSelect.on('select2:selecting', (event) => {
				//prevent default select2 event if it isn't keyboard event
				if (!$(':focus').length) {
					event.preventDefault();
					filterSelect.select2('close');
					return false;
				}
				this.registerChangeCustomFilterEvent(event);
			});
			// select change event must be replaced by click to avoid triggering while clicking on options' buttons
			filterSelect.on('click', 'option', this.registerChangeCustomFilterEvent.bind(this));
			// event triggered by tab filter click
			this.getFilterBlock().on('mouseup', '.select2-results__option', this.registerChangeCustomFilterEvent.bind(this));
			this.getListViewTopMenuContainer()
				.find('.js-filter-tab')
				.on('click', (e) => {
					const cvId = $(e.currentTarget).data('cvid');
					let selectOption = filterSelect.find(`[value=${cvId}]`);
					selectOption.trigger('click');
					$('#select2-customFilter-container span').contents().last().replaceWith(selectOption.text());
					filterSelect.val(cvId).trigger('change');
				});
		},
		breadCrumbsFilter: function (text) {
			let breadCrumbs = $('.breadcrumbsContainer');
			let breadCrumbsLastSpan = breadCrumbs.last('span');
			let filterExist = breadCrumbsLastSpan.find('.breadCrumbsFilter');
			if (filterExist.length && text != undefined) {
				filterExist.text(' [' + app.vtranslate('JS_FILTER') + ': ' + text + ']');
			} else if (filterExist.length < 1) {
				text = text == undefined ? this.getFilterSelectElement().find(':selected').text() : text;
				if (breadCrumbsLastSpan.hasClass('breadCrumbsFilter')) {
					breadCrumbsLastSpan.text(': ' + text);
				} else {
					breadCrumbs.append(
						'<small class="breadCrumbsFilter hideToHistory p-1 js-text-content u-text-ellipsis--no-hover" data-js="text"> [' +
							app.vtranslate('JS_FILTER') +
							': ' +
							text +
							']</small>'
					);
				}
			}
		},
		ListViewPostOperation: function () {
			return true;
		},
		/**
		 * Function to register the click event for list view main check box.
		 */
		registerMainCheckBoxClickEvent: function () {
			let listViewPageDiv = this.getListViewContainer();
			let thisInstance = this;
			listViewPageDiv.on('click', '#listViewEntriesMainCheckBox', function () {
				let selectedIds = thisInstance.readSelectedIds();
				let excludedIds = thisInstance.readExcludedIds();
				if ($('#listViewEntriesMainCheckBox').is(':checked')) {
					let recordCountObj = thisInstance.getRecordsCount();
					recordCountObj.done(function (data) {
						$('#totalRecordsCount').text(data);
						if ($('#deSelectAllMsgDiv').css('display') == 'none') {
							$('#selectAllMsgDiv').show();
						}
					});

					$('.listViewEntriesCheckBox').each(function (index, element) {
						$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
						if (selectedIds == 'all') {
							if ($.inArray($(element).val(), excludedIds) != -1) {
								excludedIds.splice($.inArray($(element).val(), excludedIds), 1);
							}
						} else if ($.inArray($(element).val(), selectedIds) == -1) {
							selectedIds.push($(element).val());
						}
					});
				} else {
					$('#selectAllMsgDiv').hide();
					$('.listViewEntriesCheckBox').each(function (index, element) {
						$(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
						if (selectedIds == 'all') {
							excludedIds.push($(element).val());
							selectedIds = 'all';
						} else {
							selectedIds.splice($.inArray($(element).val(), selectedIds), 1);
						}
					});
				}
				thisInstance.writeSelectedIds(selectedIds);
				thisInstance.writeExcludedIds(excludedIds);
			});
		},
		/**
		 * Function  to register click event for list view check box.
		 */
		registerCheckBoxClickEvent: function () {
			let listViewPageDiv = this.getListViewContainer();
			let thisInstance = this;
			listViewPageDiv.on('click', '.listViewEntriesCheckBox', function (e) {
				let selectedIds = thisInstance.readSelectedIds();
				let excludedIds = thisInstance.readExcludedIds();
				let elem = $(e.currentTarget);
				if (elem.is(':checked')) {
					elem.closest('tr').addClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.splice($.inArray(elem.val(), excludedIds), 1);
					} else if ($.inArray(elem.val(), selectedIds) == -1) {
						selectedIds.push(elem.val());
					}
				} else {
					elem.closest('tr').removeClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.push(elem.val());
						selectedIds = 'all';
					} else {
						selectedIds.splice($.inArray(elem.val(), selectedIds), 1);
					}
				}
				thisInstance.checkSelectAll();
				thisInstance.writeSelectedIds(selectedIds);
				thisInstance.writeExcludedIds(excludedIds);
			});
		},
		/**
		 * Function to register the click event for select all.
		 */
		registerSelectAllClickEvent: function () {
			let listViewPageDiv = this.getListViewContainer();
			let thisInstance = this;
			listViewPageDiv.on('click', '#selectAllMsg', function () {
				$('#selectAllMsgDiv').hide();
				$('#deSelectAllMsgDiv').show();
				$('#listViewEntriesMainCheckBox').prop('checked', true);
				$('.listViewEntriesCheckBox').each(function (index, element) {
					$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
				});
				thisInstance.writeSelectedIds('all');
			});
		},
		/**
		 * Function to register the click event for deselect All.
		 */
		registerDeselectAllClickEvent: function () {
			let listViewPageDiv = this.getListViewContainer();
			let thisInstance = this;
			listViewPageDiv.on('click', '#deSelectAllMsg', function () {
				$('#deSelectAllMsgDiv').hide();
				$('#listViewEntriesMainCheckBox').prop('checked', false);
				$('.listViewEntriesCheckBox').each(function (index, element) {
					$(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
				});
				let excludedIds = [];
				let selectedIds = [];
				thisInstance.writeSelectedIds(selectedIds);
				thisInstance.writeExcludedIds(excludedIds);
			});
		},
		/**
		 * Function to register the click event for listView headers
		 */
		registerHeadersClickEvent: function () {
			YetiForce_ListSearch_Js.registerSearch(this.getListViewContainer(), (data) => {
				this.getListViewRecords(data);
			});
		},
		/**
		 * function to register the click event event for create filter
		 */
		createFilterClickEvent: function (event) {
			//to close the dropdown
			this.getFilterSelectElement().data('select2').close();
			new CustomView($(event.currentTarget).find('#createFilter').data('createurl'));
		},
		registerFeaturedFilterClickEvent: function () {
			let thisInstance = this;
			let listViewFilterBlock = this.getFilterBlock();
			if (listViewFilterBlock != false) {
				listViewFilterBlock.on('mouseup', '.js-filter-favorites', function (event) {
					let liElement = $(this).closest('.select2-results__option');
					let currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					let params = {
						cvid: currentOptionElement.attr('value'),
						module: 'CustomView',
						action: 'Featured',
						sorceModuleName: app.getModuleName()
					};
					if (currentOptionElement.data('featured') === 1) {
						params.actions = 'remove';
					} else {
						params.actions = 'add';
					}
					AppConnector.request(params).done(function (data) {
						window.location.reload();
					});
					event.stopPropagation();
				});
			}
		},
		/**
		 * Function to register the click event for duplicate filter
		 */
		registerDuplicateFilterClickEvent: function () {
			let thisInstance = this;
			let listViewFilterBlock = this.getFilterBlock();
			if (listViewFilterBlock != false) {
				listViewFilterBlock.on('mouseup', '.js-filter-duplicate', function (event) {
					//to close the dropdown
					thisInstance.getFilterSelectElement().data('select2').close();
					let liElement = $(event.currentTarget).closest('.select2-results__option');
					let currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					let editUrl = currentOptionElement.data('duplicateurl');
					new CustomView(editUrl);
					event.stopPropagation();
				});
			}
		},
		/**
		 * Function to register the click event for edit filter
		 */
		registerEditFilterClickEvent: function () {
			let thisInstance = this;
			let listViewFilterBlock = this.getFilterBlock();
			if (listViewFilterBlock != false) {
				listViewFilterBlock.on('mouseup', '.js-filter-edit', function (event) {
					//to close the dropdown
					thisInstance.getFilterSelectElement().data('select2').close();
					let liElement = $(event.currentTarget).closest('.select2-results__option');
					let currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					let editUrl = currentOptionElement.data('editurl');
					new CustomView(editUrl);
					event.stopPropagation();
				});
			}
		},
		/**
		 * Function to register the click event for delete filter
		 */
		registerDeleteFilterClickEvent: function () {
			const self = this;
			const listViewFilterBlock = this.getFilterBlock();
			if (listViewFilterBlock != false) {
				//used mouseup event to stop the propagation of customfilter select change event.
				listViewFilterBlock.on('mouseup', '.js-filter-delete', (event) => {
					//to close the dropdown
					self.getFilterSelectElement().data('select2').close();
					const liElement = $(event.currentTarget).closest('.select2-results__option');
					app.showConfirmModal({
						title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE_FILTER'),
						confirmedCallback: () => {
							AppConnector.requestForm(self.getSelectOptionFromChosenOption(liElement).data('deleteurl'));
						}
					});
					event.stopPropagation();
				});
			}
		},
		/**
		 * Function to register the click event for approve filter
		 */
		registerApproveFilterClickEvent: function () {
			const thisInstance = this;
			const listViewFilterBlock = this.getFilterBlock();
			if (listViewFilterBlock != false) {
				listViewFilterBlock.on('mouseup', '.js-filter-approve', (event) => {
					//to close the dropdown
					thisInstance.getFilterSelectElement().data('select2').close();
					const liElement = $(event.currentTarget).closest('.select2-results__option');
					AppConnector.requestForm(thisInstance.getSelectOptionFromChosenOption(liElement).data('approveurl'));
					event.stopPropagation();
				});
			}
		},
		/**
		 * Function to register the click event for deny filter
		 */
		registerDenyFilterClickEvent: function () {
			const thisInstance = this;
			const listViewFilterBlock = this.getFilterBlock();
			if (listViewFilterBlock != false) {
				listViewFilterBlock.on('mouseup', '.js-filter-deny', (event) => {
					//to close the dropdown
					thisInstance.getFilterSelectElement().data('select2').close();
					const liElement = $(event.currentTarget).closest('.select2-results__option');
					AppConnector.requestForm(thisInstance.getSelectOptionFromChosenOption(liElement).data('denyurl'));
					event.stopPropagation();
				});
			}
		},
		/**
		 * Function to generate filter actions template
		 */
		appendFilterActionsTemplate: function (liElement) {
			let currentOptionElement = this.getSelectOptionFromChosenOption(liElement);
			let template = $(`<span class="js-filter-actions o-filter-actions noWrap float-right">
					<span ${
						currentOptionElement.data('featured') === 1
							? 'title="' + app.vtranslate('JS_REMOVE_TO_FAVORITES') + '"'
							: 'title="' + app.vtranslate('JS_ADD_TO_FAVORITES') + '"'
					} data-value="favorites" data-js="click"
						  class=" mr-1 js-filter-favorites ${currentOptionElement.data('featured') === 1 ? 'fas fa-star' : 'far fa-star'}
						  		 ${currentOptionElement.data('featured') === undefined ? 'd-none' : ''}""></span>
					<span title="${app.vtranslate('JS_DUPLICATE')}" data-value="duplicate" data-js="click"
						  class="fas fa-retweet mr-1 js-filter-duplicate ${$('#createFilter').length !== 0 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_EDIT')}" data-value="edit" data-js="click"
						  class="fas fa-pencil-alt mr-1 js-filter-edit ${currentOptionElement.data('editable') === 1 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_DELETE')}" data-value="delete" data-js="click"
						  class="fas fa-trash-alt mr-1 js-filter-delete ${currentOptionElement.data('deletable') === 1 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_DENY')}" data-value="deny" data-js="click"
						  class="fas fa-exclamation-circle mr-1 js-filter-deny ${
								currentOptionElement.data('public') === 1 ? '' : 'd-none'
							}"></span>
					<span title="${app.vtranslate('JS_APPROVE')}" data-value="approve" data-js="click"
						  class="fas fa-check mr-1 js-filter-approve ${currentOptionElement.data('pending') === 1 ? '' : 'd-none'}"></span>
				</span>`);
			template.appendTo(liElement.find('.js-filter__title'));
		},
		/**
		 * Function to register the hover event for customview filter options
		 */
		registerCustomFilterOptionsHoverEvent: function () {
			let filterBlock = this.getFilterBlock();
			if (filterBlock != false) {
				filterBlock.on('mouseenter mouseleave', 'li.select2-results__option[role="option"]', (event) => {
					let liElement = $(event.currentTarget);
					let liFilterImages = liElement.find('.js-filter-actions');
					if (liElement.hasClass('group-result')) {
						return;
					}
					if (event.type === 'mouseenter' && liFilterImages.length === 0) {
						this.appendFilterActionsTemplate(liElement);
					}
				});
			}
		},
		/**
		 * Function to register the list view row click event
		 */
		registerRowClickEvent: function () {
			let listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.listViewEntries', function (e) {
				if ($(e.target).hasClass('js-no-link')) return;
				if ($(e.target).closest('div').hasClass('actions')) return;
				if ($(e.target).is('button') || $(e.target).parent().is('button')) return;
				if ($(e.target).closest('a').hasClass('noLinkBtn')) return;
				if ($(e.target).is('a')) return;
				if ($(e.target, $(e.currentTarget)).is('td:first-child')) return;
				if ($(e.target).is('input[type="checkbox"]')) return;
				if ($.contains($(e.currentTarget).find('td:last-child').get(0), e.target)) return;
				if ($.contains($(e.currentTarget).find('td:first-child').get(0), e.target)) return;
				let elem = $(e.currentTarget);
				let recordUrl = elem.data('recordurl');
				if (typeof recordUrl === 'undefined') {
					return;
				}
				window.location.href = recordUrl;
			});
		},
		/**
		 * Register mass records events.
		 */
		registerMassRecordsEvents: function () {
			const self = this;
			this.getListViewContainer().on('click', '.js-mass-record-event', function () {
				let target = $(this);
				let listInstance = Vtiger_List_Js.getInstance();
				if (listInstance.checkListRecordSelected() != true) {
					if (target.data('type') === 'modal') {
						let vars = {};
						target.data('url').replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
							vars[key] = value;
						});
						AppConnector.request({
							type: 'POST',
							url: target.data('url'),
							data: $.extend(self.getSearchParams(), vars)
						}).done(function (modal) {
							app.showModalWindow(modal);
						});
					} else {
						let params = {
							icon: false,
							confirmedCallback: () => {
								let progressIndicatorElement = $.progressIndicator(),
									dataParams = self.getSearchParams();
								delete dataParams.view;
								AppConnector.request({
									type: 'POST',
									url: target.data('url'),
									data: dataParams
								})
									.done(function (data) {
										progressIndicatorElement.progressIndicator({ mode: 'hide' });
										if (data && data.result && data.result.notify) {
											Vtiger_Helper_Js.showMessage(data.result.notify);
										}
										self.getListViewRecords();
									})
									.fail(function (error, err) {
										progressIndicatorElement.progressIndicator({ mode: 'hide' });
									});
							}
						};
						if (target.data('confirm')) {
							params.text = target.data('confirm');
							params.title = target.html();
						} else {
							params.text = target.html();
						}
						app.showConfirmModal(params);
					}
				} else {
					listInstance.noRecordSelectedAlert();
				}
			});
		},
		/**
		 * Update pagination row
		 * @param {Array} urlParams
		 */
		massUpdatePagination(urlParams = []) {
			const self = this,
				listViewPageDiv = this.getListViewContainer();
			let paginationObject = listViewPageDiv.find('.js-pagination-list'),
				totalCount = paginationObject.data('totalCount'),
				pageNumber = parseInt(listViewPageDiv.find('#pageNumber').val()),
				tempPageNumber = pageNumber,
				selectedIds = self.readSelectedIds(false);
			if ('all' === selectedIds[0]) {
				pageNumber = 1;
				totalCount = 0;
			} else {
				if ('' !== totalCount && 0 !== totalCount) {
					totalCount = totalCount - selectedIds.length;
				}
				if (listViewPageDiv.find('#noOfEntries').val() <= 0 && pageNumber !== 1) {
					pageNumber--;
				}
			}
			paginationObject.data('totalCount', totalCount);
			self.updatePagination(pageNumber);
			if (tempPageNumber !== pageNumber) {
				if (!$.isEmptyObject(urlParams)) {
					urlParams['page'] = pageNumber;
				}
				self.getListViewRecords(urlParams);
			}
			self.writeSelectedIds([]);
		},
		/**
		 * Function to register the click event of email field
		 */
		registerEmailFieldClickEvent: function () {
			let listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.emailField', function (e) {
				e.stopPropagation();
			});
		},
		/**
		 * Function to register the click event of phone field
		 */
		registerPhoneFieldClickEvent: function () {
			let listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.phoneField', function (e) {
				e.stopPropagation();
			});
		},
		/**
		 * Function to register the click event of url field
		 */
		registerUrlFieldClickEvent: function () {
			let listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.urlField', function (e) {
				e.stopPropagation();
			});
		},
		/**
		 * Function to inactive field for validation in a form
		 * this will remove data-validation-engine attr of all the elements
		 * @param Accepts form as a parameter
		 */
		inactiveFieldsValidation: function (form) {
			form.find('.fieldValue [data-validation-engine][data-fieldinfo]').each((_, e) => {
				let fieldElement = $(e);
				let fieldInfo = fieldElement.data('fieldinfo');
				if (fieldInfo.type == 'reference') {
					//get the element which will be shown which has "_display" appended to actual field name
					fieldElement = form.find('[name="' + fieldInfo.name + '_display"]');
				} else if (fieldInfo.type == 'multipicklist' || fieldInfo.type == 'sharedOwner') {
					fieldElement = form.find('[name="' + fieldInfo.name + '[]"]');
				}
				//Not all the fields will be enabled for mass edit
				if (fieldElement.length == 0) {
					return;
				}
				let elemData = fieldElement.data();
				//Blank validation by default
				let validationVal = 'validate[]';
				if ('validationEngine' in elemData) {
					validationVal = elemData.validationEngine;
					delete elemData.validationEngine;
				}
				fieldElement.attr('data-invalid-validation-engine', validationVal);
				fieldElement.removeAttr('data-validation-engine');
			});
		},
		registerEventForTabClick: function (form) {
			let ulContainer = form.find('.massEditTabs');
			ulContainer.on('click', 'a[data-toggle="tab"]', function (e) {
				form.validationEngine('validate');
				let invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length > 0) {
					e.stopPropagation();
				}
			});
		},
		registerSlimScrollMassEdit: function () {
			app.showScrollBar($('div[name="massEditContent"]'), {
				height: app.getScreenHeight(70) + 'px'
			});
		},
		changeCustomFilterElementView: function () {
			const thisInstance = this;
			let filterSelectElement = this.getFilterSelectElement();
			if (filterSelectElement.length > 0 && filterSelectElement.is('select')) {
				App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
					templateSelection: function (data) {
						let resultContainer = $('<span></span>');
						resultContainer.append($($('.filterImage').clone().get(0)).show());
						resultContainer.append(data.text);
						return resultContainer;
					},
					customSortOptGroup: true,
					templateResult: function (data) {
						let actualElement = $(data.element);
						if (actualElement.is('option')) {
							let additionalText = '';
							if (actualElement.data('option') !== undefined) {
								additionalText =
									'<div class="u-max-w-lg-100 u-text-ellipsis--no-hover d-inline-block small">' +
									actualElement.data('option') +
									'</div>';
							}
							return (
								'<div class="js-filter__title d-flex justify-content-between" data-js="appendTo"><div class="u-text-ellipsis--no-hover">' +
								actualElement.text() +
								'</div></div>' +
								additionalText
							);
						} else {
							return actualElement.attr('label');
						}
					},
					escapeMarkup: function (markup) {
						return markup;
					},
					closeOnSelect: true
				});

				let select2Instance = filterSelectElement.data('select2');
				$('.filterActionsDiv')
					.appendTo(select2Instance.$dropdown.find('.select2-dropdown:last'))
					.removeClass('d-none')
					.on('click', function (e) {
						thisInstance.createFilterClickEvent(e);
					});
			}
		},
		/**
		 * Function to show total records count in listview on hover
		 * of pageNumber text
		 */
		registerEventForTotalRecordsCount: function () {
			let thisInstance = this;
			$('.totalNumberOfRecords').on('click', function (e) {
				let element = $(e.currentTarget);
				let totalRecordsElement = $('#totalCount');
				let totalNumberOfRecords = totalRecordsElement.val();
				element.addClass('d-none');
				element.parent().progressIndicator({});
				if (totalNumberOfRecords == '') {
					thisInstance.getPageCount().done(function (data) {
						totalNumberOfRecords = data['result']['numberOfRecords'];
						totalRecordsElement.val(totalNumberOfRecords);
						thisInstance.showPagingInfo();
					});
				} else {
					thisInstance.showPagingInfo();
				}
				element.parent().progressIndicator({ mode: 'hide' });
			});
		},
		showPagingInfo: function () {
			let totalNumberOfRecords = $('#totalCount').val();
			let pageNumberElement = $('.pageNumbersText');
			let pageRange = pageNumberElement.text();
			let newPagingInfo = pageRange + ' (' + totalNumberOfRecords + ')';
			let listViewEntriesCount = parseInt($('#noOfEntries').val());
			if (listViewEntriesCount != 0) {
				$('.pageNumbersText').html(newPagingInfo);
			} else {
				$('.pageNumbersText').html('');
			}
		},
		registerUnreviewedCountEvent: function () {
			let ids = [],
				listViewContentDiv = this.getListViewContentContainer(),
				isUnreviewedActive = listViewContentDiv.find('.unreviewed').length;
			listViewContentDiv.find('tr.listViewEntries').each(function () {
				let id = $(this).data('id');
				if (id) {
					ids.push(id);
				}
			});
			if (!ids || isUnreviewedActive < 1) {
				return;
			}
			AppConnector.request({
				action: 'ChangesReviewedOn',
				mode: 'getUnreviewed',
				module: 'ModTracker',
				sourceModule: app.getModuleName(),
				recordsId: ids
			}).done((appData) => {
				let data = appData.result;
				$.each(data, function (id, value) {
					if (value.a > 0) {
						listViewContentDiv
							.find('tr[data-id="' + id + '"] .unreviewed .badge.all')
							.text(value.a)
							.parent()
							.removeClass('d-none');
					}
					if (value.m > 0) {
						listViewContentDiv
							.find('tr[data-id="' + id + '"] .unreviewed .badge.mail')
							.text(value.m)
							.parent()
							.removeClass('d-none');
					}
				});
				this.reflowThead();
			});
		},
		registerLastRelationsEvent: function () {
			let ids = [],
				listViewContentDiv = this.getListViewContentContainer(),
				isTimeLineActive = listViewContentDiv.find('.timeLineIconList').length;
			listViewContentDiv.find('tr.listViewEntries').each(function () {
				let id = $(this).data('id');
				if (id) {
					ids.push(id);
				}
			});
			if (!ids || isTimeLineActive < 1) {
				return;
			}
			AppConnector.request({
				action: 'LastRelation',
				module: 'ModTracker',
				sourceModule: app.getModuleName(),
				recordsId: ids
			}).done((appData) => {
				let data = appData.result;
				$.each(data, function (id, value) {
					if (value.type) {
						listViewContentDiv
							.find('tr[data-id="' + id + '"] .timeLineIconList')
							.addClass(value.color + ' yfm-' + value.type)
							.removeClass('d-none')
							.on('click', function (e) {
								let element = $(e.currentTarget);
								let url = element.data('url');
								app.showModalWindow(null, url, function (data) {
									Vtiger_Index_Js.registerMailButtons(data);
								});
							});
					}
				});
				this.reflowThead();
			});
		},
		registerChangeEntityStateEvent: function () {
			let thisInstance = this;
			$('.dropdownEntityState a').on('click', function (e) {
				let element = $(this);
				element.closest('ul').find('a').removeClass('active');
				element.addClass('active');
				$('#entityState').val(element.data('value'));
				app.setMainParams('pageNumber', '1');
				app.setMainParams('pageToJump', '1');
				$('#recordsCount').val('');
				$('#totalPageCount').text('');
				$('.pagination').data('totalCount', 0);
				$('#dropdownEntityState').find('.js-icon').attr('class', element.find('.js-icon').attr('class'));
				thisInstance.getListViewRecords().done(function (data) {
					thisInstance.calculatePages().done(function () {
						thisInstance.updatePagination();
					});
				});
			});
		},
		registerSummationEvent: function () {
			let self = this;
			let listContainer = this.getListViewContentContainer();
			listContainer.on('click', '.listViewSummation button', function () {
				let button = $(this);
				let calculateValue = button.closest('td').find('.calculateValue');
				let params = self.getSearchParams();
				let progress = $.progressIndicator({
					message: app.vtranslate('JS_CALCULATING_IN_PROGRESS'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				params.action = 'List';
				params.mode = 'calculate';
				params.fieldName = button.data('field');
				params.calculateType = button.data('operator');
				delete params.view;
				app.hidePopover(button);
				let scrollLeft = listContainer.scrollLeft();
				let scrollTop = listContainer.scrollTop();
				AppConnector.request(params).done((response) => {
					if (response.success) {
						calculateValue.html(response.result);
					} else {
						calculateValue.html('');
					}
					self.registerFixedThead(listContainer);
					listContainer.scrollLeft(scrollLeft);
					listContainer.scrollTop(scrollTop);
					progress.progressIndicator({ mode: 'hide' });
				});
			});
		},
		registerListScroll: function (container) {
			const containerH = container.height(),
				containerOffsetTop = container.offset().top,
				footerH = $('.js-footer').height(),
				windowH = $(window).height();
			//	if list is bigger than window fit its height to it
			if (containerH + containerOffsetTop + footerH > windowH) {
				container.height(windowH - (containerOffsetTop + footerH));
			}
			container.find('.js-fixed-thead').floatThead('destroy');
			container.siblings('.floatThead-container').remove();
			app.showNewScrollbarTopBottomRight(container);
			app.registerMiddleClickScroll(container);
		},
		registerFixedThead(container) {
			if (!Quasar.plugins.Platform.is.desktop) {
				this.listFloatThead = container.find('.js-fixed-thead');
				this.listFloatThead.floatThead('destroy');
				this.listFloatThead.floatThead({
					scrollContainer: function () {
						return container;
					}
				});
				this.listFloatThead.floatThead('reflow');
			}
		},
		getFloatTheadContainer(container = this.getListViewContentContainer()) {
			if (this.listFloatThead === false) {
				this.listFloatThead = container.find('.js-fixed-thead');
			}
			return this.listFloatThead;
		},
		reflowThead() {
			if (Quasar.plugins.Platform.is.desktop) {
				this.getFloatTheadContainer().floatThead('reflow');
			}
		},
		registerMassActionsBtnEvents() {
			this.getListViewContainer().on('click', '.js-mass-action', (e) => {
				e.preventDefault();
				let element = $(e.currentTarget);
				let url = element.data('url');
				if (typeof url != 'undefined') {
					if (
						(element.data('checkSelected') !== undefined && element.data('checkSelected') == 0) ||
						this.checkListRecordSelected() !== true
					) {
						switch (element.data('type')) {
							case 'modal':
								Vtiger_List_Js.triggerMassAction(url);
								break;
							case 'formRedirect':
								Vtiger_List_Js.triggerExportAction(url, element.data('tab') === 'new');
								Vtiger_List_Js.clearList();
								break;
							case 'reload':
								let params = self.getSearchParams();
								delete params.view;
								delete params.action;
								params.sourceModule = params.module;
								delete params.module;
								AppConnector.request({
									type: 'POST',
									url: url,
									data: params
								}).done((response) => {
									self.getListViewRecords();
									Vtiger_List_Js.clearList();
									if (response.result) {
										Vtiger_Helper_Js.showMessage(response.result.message);
									}
								});
								break;
						}
					} else {
						this.noRecordSelectedAlert();
					}
				}
				e.stopPropagation();
			});
		},
		registerMassActionsBtnMergeEvents() {
			this.getListViewContainer().on('click', '.js-mass-action--merge', (e) => {
				let url = $(e.target).data('url');
				if (typeof url !== 'undefined') {
					if (this.checkListRecordSelected(2) !== true) {
						Vtiger_List_Js.triggerMassAction(url);
					} else {
						this.noRecordSelectedAlert('JS_SELECT_ATLEAST_TWO_RECORD_FOR_MERGING');
					}
				}
			});
		},
		/**
		 * Register desktop events
		 * @param {$} listViewContainer
		 */
		registerDesktopEvents(listViewContainer) {
			if (Quasar.plugins.Platform.is.desktop && listViewContainer.length) {
				this.registerListScroll(listViewContainer);
				this.registerFixedThead(listViewContainer);
			}
		},
		registerPostLoadDesktopEvents(listViewContainer) {
			if (Quasar.plugins.Platform.is.desktop) {
				new PerfectScrollbar(listViewContainer[0]).destroy();
				listViewContainer.find('.js-fixed-thead').floatThead('destroy');
				listViewContainer.siblings('.floatThead-container').remove();
				new PerfectScrollbar(listViewContainer[0]);
				this.registerFixedThead(listViewContainer);
			}
		},
		/**
		 * Register keyboard shortcuts events
		 */
		registerKeyboardShortcutsEvent: function () {
			document.addEventListener('keydown', (event) => {
				if (event.shiftKey && event.ctrlKey && event.code === 'KeyA' && $('.js-add-record').length) {
					window.location.href = 'index.php?module=' + app.getModuleName() + '&view=Edit';
				}
			});
		},
		/**
		 * Function that executes after the mass delete action
		 */
		postMassDeleteRecords: function () {
			let aDeferred = $.Deferred();
			let listInstance = Vtiger_List_Js.getInstance();
			app.hideModalWindow();
			listInstance.getListViewRecords().done(function (data) {
				$('#recordsCount').val('');
				$('#totalPageCount').text('');
				$('#deSelectAllMsg').trigger('click');
				listInstance.calculatePages().done(function () {
					listInstance.updatePagination();
				});
				aDeferred.resolve();
			});
			$('#recordsCount').val('');
			return aDeferred.promise();
		},
		/**
		 * Function to register events
		 */
		registerEvents: function () {
			this.registerRowClickEvent();
			this.registerPageNavigationEvents();
			this.registerMainCheckBoxClickEvent();
			this.registerCheckBoxClickEvent();
			this.registerSelectAllClickEvent();
			this.registerDeselectAllClickEvent();
			this.registerMassRecordsEvents();
			this.registerMassActionsBtnMergeEvents();
			this.registerHeadersClickEvent();
			this.changeCustomFilterElementView();
			this.registerFeaturedFilterClickEvent();
			this.registerChangeCustomFilterEventListeners();
			this.registerChangeEntityStateEvent();
			this.registerDuplicateFilterClickEvent();
			this.registerEditFilterClickEvent();
			this.registerDeleteFilterClickEvent();
			this.registerApproveFilterClickEvent();
			this.registerDenyFilterClickEvent();
			this.registerCustomFilterOptionsHoverEvent();
			if (typeof CustomView !== 'undefined') {
				CustomView.registerCustomViewAdvCondEvents(this.getListViewContainer());
			}
			this.registerEmailFieldClickEvent();
			this.registerPhoneFieldClickEvent();
			this.registerMassActionsBtnEvents();
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
			this.registerUrlFieldClickEvent();
			this.registerEventForTotalRecordsCount();
			this.registerSummationEvent();
			//Just reset all the checkboxes on page load: added for chrome issue.
			let listViewContainer = this.getListViewContentContainer();
			listViewContainer.find('#listViewEntriesMainCheckBox,.listViewEntriesCheckBox').prop('checked', false);
			this.getListSearchInstance(false);
			this.registerDesktopEvents(listViewContainer);
			this.registerUnreviewedCountEvent();
			this.registerLastRelationsEvent();
			this.registerKeyboardShortcutsEvent();
			Vtiger_Index_Js.registerMailButtons(listViewContainer);
		}
	}
);
