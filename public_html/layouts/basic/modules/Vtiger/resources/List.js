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

jQuery.Class("Vtiger_List_Js", {
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
				moduleClassName = parentModule + "_" + module + "_List_Js";
				if (typeof window[moduleClassName] === "undefined") {
					moduleClassName = module + "_List_Js";
				}
				fallbackClassName = parentModule + "_Vtiger_List_Js";
				if (typeof window[fallbackClassName] === "undefined") {
					fallbackClassName = "Vtiger_List_Js";
				}
			} else {
				moduleClassName = module + "_List_Js";
				fallbackClassName = "Vtiger_List_Js";
			}
			if (typeof window[moduleClassName] !== "undefined") {
				instance = new window[moduleClassName]();
			} else {
				instance = new window[fallbackClassName]();
			}
			Vtiger_List_Js.listInstance = instance;
			return instance;
		}
		return Vtiger_List_Js.listInstance;
	},
	/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail: function (params) {
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult !== true) {
			var postData = listInstance.getSearchParams();
			delete postData.parent;
			postData.view = 'SendMailModal';
			postData.cvid = listInstance.getCurrentCvId();
			if (params) {
				jQuery.extend(postData, params);
			}
			AppConnector.request(postData).done(function (response) {
				app.showModalWindow(response, function (data) {
					data.find('[name="saveButton"]').on('click', function (e) {
						if (data.find('form').validationEngine('validate')) {
							jQuery.extend(postData, {
								field: data.find('#field').val(),
								template: data.find('#template').val(),
								action: 'Mail',
								mode: 'sendMails',
							});
							delete postData.view;
							AppConnector.request(postData).done(function (response) {
								if (response.result == true) {
									app.hideModalWindow();
								}
							}).fail(function (data, err) {
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
	/*
	 * function to trigger Send Sms
	 * @params: send email url , module name.
	 */
	triggerSendSms: function (massActionUrl, module) {
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			Vtiger_List_Js.triggerMassAction(massActionUrl);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},
	triggerTransferOwnership: function (massActionUrl) {
		var thisInstance = this;
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			var progressIndicatorElement = jQuery.progressIndicator();
			thisInstance.getRelatedModulesContainer = false;
			var actionParams = {
				"type": "POST",
				"url": massActionUrl,
				"dataType": "html",
				"data": {}
			};
			AppConnector.request(actionParams).done(
				function (data) {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					if (data) {
						var callback = function (data) {
							var params = app.validationEngineOptions;
							params.onValidationComplete = function (form, valid) {
								if (valid) {
									thisInstance.transferOwnershipSave(form)
								}
								return false;
							}
							data.find('#changeOwner').validationEngine(app.validationEngineOptions);
						}
						app.showModalWindow(data, function (data) {
							var selectElement = thisInstance.getRelatedModuleContainer();
							App.Fields.Picklist.changeSelectElementView(selectElement, 'select2');
							if (typeof callback == 'function') {
								callback(data);
							}
						});
					}
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerQuickExportToExcel: function (module) {
		var massActionUrl = "index.php";
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			var progressIndicatorElement = jQuery.progressIndicator();
			var actionParams = {
				type: "POST",
				url: massActionUrl,
				dataType: "application/x-msexcel",
				data: listInstance.getSearchParams()
			};
			//can't use AppConnector to get files with a post request so we add a form to the body and submit it
			var form = $('<form method="POST" action="' + massActionUrl + '">');
			form.append($('<input />', {name: "module", value: module}));
			form.append($('<input />', {name: "action", value: "QuickExport"}));
			form.append($('<input />', {name: "mode", value: "exportToExcel"}));
			if (typeof csrfMagicName !== "undefined") {
				form.append($('<input />', {name: csrfMagicName, value: csrfMagicToken}));
			}
			$.each(actionParams.data, function (k, v) {
				form.append($('<input />', {name: k, value: v}));
			});
			$('body').append(form);
			form.submit();
			Vtiger_Helper_Js.showMessage({text: app.vtranslate('JS_STARTED_GENERATING_FILE'), type: 'info'})

			progressIndicatorElement.progressIndicator({mode: 'hide'});
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	transferOwnershipSave: function (form) {
		const listInstance = Vtiger_List_Js.getInstance();
		let transferOwner = jQuery('#transferOwnerId').val(),
			relatedModules = jQuery('#related_modules').val(),
			params = {
				'module': app.getModuleName(),
				'action': 'TransferOwnership',
				'transferOwnerId': transferOwner,
				'related_modules': relatedModules
			};
		params = $.extend(params, listInstance.getSearchParams());
		delete params.view;
		AppConnector.request(params).done(
			(response) => {
				app.hideModalWindow();
				listInstance.getListViewRecords();
				Vtiger_List_Js.clearList();
				 if (response.result.success) {
					Vtiger_Helper_Js.showMessage({
						title: app.vtranslate("JS_MESSAGE"),
						text: app.vtranslate("JS_RECORDS_TRANSFERRED_SUCCESSFULLY"),
						type: "info"
					});
				} else {
					Vtiger_Helper_Js.showMessage(response.result.notify);
				}
			}
		);
	},
	/*
	 * Function to get the related module container
	 */
	getRelatedModuleContainer: function () {
		if (this.getRelatedModulesContainer == false) {
			this.getRelatedModulesContainer = jQuery('#related_modules');
		}
		return this.getRelatedModulesContainer;
	},
	triggerMassAction: function (massActionUrl, callBackFunction, beforeShowCb, css) {
		if (typeof beforeShowCb === "undefined") {
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
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			var progressIndicatorElement = jQuery.progressIndicator();
			var actionParams = {
				"type": "POST",
				"url": massActionUrl,
				"dataType": "html",
				"data": listInstance.getSearchParams()
			};
			if (typeof css === "undefined") {
				css = {};
			}
			css = jQuery.extend({'text-align': 'left'}, css);
			AppConnector.request(actionParams).done(function (data) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				if (data) {
					var result = beforeShowCb(data);
					if (!result) {
						return;
					}
					app.showModalWindow(data, function (data) {
						app.event.trigger('MassEditModal.AfterLoad', data, massActionUrl);
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
							//listInstance.triggerDisplayTypeEvent();
						}
					}, css);
					//register inactive fields for massedit modal
					if ($('#massEditContainer').length) {
						listInstance.inactiveFieldsValidation($('#massEditContainer').find('form'));
					}
				}
			}).fail(function (error, err) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				Vtiger_Helper_Js.showPnotify({
					title: app.vtranslate('JS_MESSAGE'),
					text: err,
					type: 'error'
				});
			});
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerMassEdit: function (massEditUrl) {
		var selectedCount = this.getSelectedRecordCount();
		if (selectedCount > jQuery('#listMaxEntriesMassEdit').val()) {
			var params = {
				title: app.vtranslate('JS_MESSAGE'),
				text: app.vtranslate('JS_MASS_EDIT_LIMIT'),
				type: 'error'
			};
			Vtiger_Helper_Js.showPnotify(params);
			return;
		}
		Vtiger_List_Js.triggerMassAction(massEditUrl, function (container) {
			var massEditForm = container.find('#massEdit');
			massEditForm.validationEngine(app.validationEngineOptions);
			var listInstance = Vtiger_List_Js.getInstance();
			listInstance.registerEventForTabClick(massEditForm);
			var editInstance = Vtiger_Edit_Js.getInstance();
			editInstance.registerBasicEvents(massEditForm);
			listInstance.postMassEdit(container);
			listInstance.registerSlimScrollMassEdit();
		}, {'width': '65%'});
	},
	getSelectedRecordCount: function () {
		var count;
		var listInstance = Vtiger_List_Js.getInstance();
		var cvId = listInstance.getCurrentCvId();
		var selectedIdObj = jQuery('#selectedIds').data(cvId + 'Selectedids');
		if (selectedIdObj != undefined) {
			if (selectedIdObj != 'all') {
				count = selectedIdObj.length;
			} else {
				var excludedIdsCount = jQuery('#excludedIds').data(cvId + 'Excludedids').length;
				var totalRecords = jQuery('#recordsCount').val();
				count = totalRecords - excludedIdsCount;
			}
		}
		return count;
	},
	/*
	 * function to trigger export action
	 * returns UI
	 */
	triggerExportAction: function (exportActionUrl) {
		let listInstance = Vtiger_List_Js.getInstance();
		let params = listInstance.getSearchParams();
		if ('undefined' === typeof params.viewname)
			exportActionUrl += '&selected_ids=' + params.selected_ids + '&excluded_ids=' + params.excluded_ids + '&page=' + params.page;
		else
			exportActionUrl += '&selected_ids=' + params.selected_ids + '&excluded_ids=' + params.excluded_ids + '&viewname=' + params.viewname
				+ '&page=' + params.page + '&entityState=' + params.entityState;
		if (listInstance.getListSearchInstance()) {
			exportActionUrl += "&search_params=" + params.search_params;
			if ((typeof params.search_value !== "undefined") && (params.search_value.length > 0)) {
				exportActionUrl += '&search_key=' + params.search_key;
				exportActionUrl += '&search_value=' + params.search_value;
				exportActionUrl += '&operator=s';
			}
		}
		window.location.href = exportActionUrl;
	},
	/**
	 * Function to reload list
	 */
	clearList: function () {
		jQuery('#deSelectAllMsg').trigger('click');
		jQuery("#selectAllMsgDiv").hide();
	},
	triggerListSearch: function () {
		var listInstance = Vtiger_List_Js.getInstance();
		var listViewContainer = listInstance.getListViewContentContainer();
		listViewContainer.find('[data-trigger="listSearch"]').trigger("click");
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
		var selected = Vtiger_List_Js.getSelectedRecordsParams();
		if (selected === false) {
			return false;
		}
		selected.view = 'GenerateModal';
		selected.fromview = 'List';
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		app.showModalWindow(null, 'index.php?' + jQuery.param(selected), function () {
			progressIndicatorElement.progressIndicator({mode: 'hide'})
		});
	},
	showMap: function () {
		var selectedParams = Vtiger_List_Js.getSelectedRecordsParams(false);
		var url = 'index.php?module=OpenStreetMap&view=MapModal&srcModule=' + app.getModuleName();
		app.showModalWindow(null, url, function (container) {
			var mapView = new OpenStreetMap_Map_Js();
			mapView.setSelectedParams(selectedParams);
			mapView.registerModalView(container);

		});
	},
	triggerReviewChanges: function (reviewUrl) {
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			var message = app.vtranslate('JS_MASS_REVIEWING_CHANGES_CONFIRMATION');
			var title = '<i class="fa fa-check-circle"></i> ' + app.vtranslate('JS_LBL_REVIEW_CHANGES');
			Vtiger_Helper_Js.showConfirmationBox({'message': message, 'title': title}).done(function (e) {
				let params = listInstance.getSearchParams();
				var url = reviewUrl + '&viewname=' + params.viewname + '&selected_ids=' + params.selected_ids
					+ '&excluded_ids=' + params.excluded_ids + '&entityState=' + params.entityState;
				if (listInstance.getListSearchInstance()) {
					url += "&search_params=" + params.search_params;
					if ((typeof searchValue !== "undefined") && (params.search_value.length > 0)) {
						url += '&search_key=' + params.search_key;
						url += '&search_value=' + params.search_value;
						url += '&operator=s';
					}
				}
				var deleteMessage = app.vtranslate('JS_LOADING_PLEASE_WAIT');
				var progressIndicatorElement = jQuery.progressIndicator({
					'message': deleteMessage,
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
				AppConnector.request(url).done(
					function (data) {
						progressIndicatorElement.progressIndicator({
							'mode': 'hide'
						});
						if (data.result) {
							var params = {
								text: data.result,
								type: 'info'
							}
							Vtiger_Helper_Js.showPnotify(params);
						} else {
							listInstance.getListViewRecords();
						}
					}).fail(function (error, err) {
					app.errorLog(error, err);
				});
			}).fail(function (error, err) {
				Vtiger_List_Js.clearList();
			});
		} else {
			listInstance.noRecordSelectedAlert();
		}
	}
}, {
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
		if (this.listSearchInstance == false && (this.getListViewContainer().find('.searchField').length || this.getListViewContainer().find('.picklistSearchField').length)) {
			this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(this.getListViewContainer(), this.noEventsListSearch);
		}
		return this.listSearchInstance;
	},
	getListViewContainer: function () {
		if (this.listViewContainer == false) {
			this.listViewContainer = jQuery('div.listViewPageDiv');
		}
		return this.listViewContainer;
	},
	getListViewTopMenuContainer: function () {
		if (this.listViewTopMenuContainer == false) {
			this.listViewTopMenuContainer = jQuery('.listViewTopMenuDiv');
		}
		return this.listViewTopMenuContainer;
	},
	getListViewContentContainer: function () {
		if (this.listViewContentContainer == false) {
			this.listViewContentContainer = jQuery('.listViewContentDiv');
		}
		return this.listViewContentContainer;
	},
	getFilterBlock: function () {
		if (this.filterBlock == false) {
			var filterSelectElement = this.getFilterSelectElement();
			if (filterSelectElement.length <= 0) {
				this.filterBlock = jQuery();
			} else if (filterSelectElement.is('select')) {
				this.filterBlock = filterSelectElement.data('select2').$dropdown;
			}
		}
		return this.filterBlock;
	},
	getFilterSelectElement: function () {

		if (this.filterSelectElement == false) {
			this.filterSelectElement = jQuery('#customFilter');
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
			page: jQuery('#pageNumber').val(),
			view: app.getViewName(),
			viewname: this.getCurrentCvId(),
			orderby: jQuery('#orderBy').val(),
			sortorder: jQuery("#sortOrder").val(),
			entityState: jQuery("#entityState").val(),
		};
		if (app.getParentModuleName()) {
			params.parent = app.getParentModuleName();
		}
		var listSearchInstance = this.getListSearchInstance();
		if (listSearchInstance !== false) {
			var searchValue = listSearchInstance.getAlphabetSearchValue();
			params.search_params = JSON.stringify(listSearchInstance.getListSearchParams(true));
			if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
				params.search_key = listSearchInstance.getAlphabetSearchField();
				params.search_value = searchValue;
				params.operator = 's';
			}
		}
		return params;
	},

	/*
	 * Function which will give you all the list view params
	 */
	getListViewRecords: function (urlParams) {
		var aDeferred = $.Deferred();
		if (typeof urlParams === "undefined") {
			urlParams = {};
		}
		var thisInstance = this;
		var listViewContentsContainer = $('#listViewContents');
		var loadingMessage = jQuery('.listViewLoadingMsg').text();
		var progressIndicatorElement = $.progressIndicator({
			'message': loadingMessage,
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var defaultParams = this.getDefaultParams();
		urlParams = $.extend(defaultParams, urlParams);
		AppConnector.requestPjax(urlParams).done(function (data) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			listViewContentsContainer.html(data);
			app.event.trigger("RecordListView.AfterLoad", data, thisInstance);
			thisInstance.calculatePages().done(function (data) {
				aDeferred.resolve(data);
				// Let listeners know about page state change.
				app.notifyPostAjaxReady();
			});
			thisInstance.postLoadListViewRecordsEvents(listViewContentsContainer);
			thisInstance.massUpdatePagination(urlParams);
			Vtiger_List_Js.clearList();
		}).fail(function (textStatus, errorThrown) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			Vtiger_Helper_Js.showPnotify({
				text: app.vtranslate('JS_NOT_ALLOWED_VALUE'),
				type: 'error'
			});
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	postLoadListViewRecordsEvents: function (container) {
		const self = this;
		self.registerListScroll(container);
		self.registerFixedThead(container);
		App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'));
		App.Fields.Picklist.changeSelectElementView(container);
		var searchInstance = self.getListSearchInstance();
		if (searchInstance !== false) {
			searchInstance.registerBasicEvents();
		}
		Vtiger_Index_Js.registerMailButtons(container);
		//self.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		var selectedIds = self.readSelectedIds();
		if (selectedIds != '') {
			if (selectedIds == 'all') {
				$('.listViewEntriesCheckBox').each(function (index, element) {
					$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
				});
				$('#deSelectAllMsgDiv').show();
				var excludedIds = self.readExcludedIds();
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
		var aDeferred = jQuery.Deferred();
		var element = jQuery('#totalPageCount');
		var totalPageNumber = element.text();
		if (totalPageNumber == "") {
			var totalRecordCount = jQuery('#totalCount').val();
			if (totalRecordCount != '') {
				var pageLimit = jQuery('#pageLimit').val();
				if (pageLimit == '0')
					pageLimit = 1;
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
	/*
	 * Function to return alerts if no records selected.
	 */
	noRecordSelectedAlert: function (text = 'JS_PLEASE_SELECT_ONE_RECORD') {
		return Vtiger_Helper_Js.showPnotify({text: app.vtranslate(text)});
	},
	massActionSave: function (form, isMassEdit) {
		if (typeof isMassEdit === "undefined") {
			isMassEdit = false;
		}
		var aDeferred = jQuery.Deferred();
		if (isMassEdit) {
			var massEditPreSaveEvent = jQuery.Event(Vtiger_List_Js.massEditPreSave);
			form.trigger(massEditPreSaveEvent);
			if (massEditPreSaveEvent.isDefaultPrevented()) {
				form.find('[name="saveButton"]').removeAttr('disabled');
				aDeferred.reject();
				return aDeferred.promise();
			}
			form.find('[id^="selectRow"]').each(function (index, checkbox) {
				checkbox = jQuery(checkbox);
				if (!checkbox.prop('checked')) {
					checkbox.closest('.js-form-row-container').find('.fieldValue [name]').each(function (index, element) {
						element = jQuery(element);
						element.attr('data-element-name', element.attr('name')).removeAttr('name');
					});
				}
			});
		}
		var massActionUrl = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(massActionUrl).done(
			function (data) {
				progressIndicatorElement.progressIndicator({
					'mode': 'hide'
				});
				app.hideModalWindow();
				if (!(data.result)) {
					var params = {
						text: app.vtranslate('JS_MASS_EDIT_NOT_SUCCESSFUL'),
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
				aDeferred.resolve(data);
			}).fail(
			function (error, err) {
				app.hideModalWindow();
				app.errorLog(error, err);
				aDeferred.reject(error, err);
			}
		);
		return aDeferred.promise();
	},
	checkSelectAll: function () {
		var state = true;
		jQuery('.listViewEntriesCheckBox').each(function (index, element) {
			if (jQuery(element).is(':checked')) {
				state = true;
			} else {
				state = false;
				return false;
			}
		});
		if (state == true) {
			jQuery('#listViewEntriesMainCheckBox').prop('checked', true);
		} else {
			jQuery('#listViewEntriesMainCheckBox').prop('checked', false);
		}
	},
	getRecordsCount: function () {
		var aDeferred = jQuery.Deferred();
		var recordCountVal = jQuery("#recordsCount").val();
		if (recordCountVal != '') {
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var params = this.getDefaultParams();
			params.view = 'ListAjax';
			params.mode = 'getRecordsCount';
			AppConnector.request(params).done(function (data) {
				var response = JSON.parse(data);
				jQuery("#recordsCount").val(response['result']['count']);
				count = response['result']['count'];
				aDeferred.resolve(count);
			});
		}

		return aDeferred.promise();
	},
	getSelectOptionFromChosenOption: function (liElement) {
		var id = liElement.attr("id");
		var idArr = id.split("-");
		var currentOptionId = '';
		if (idArr.length > 0) {
			currentOptionId = idArr[idArr.length - 1];
		} else {
			return false;
		}
		return jQuery('#filterOptionId_' + currentOptionId);
	},
	readSelectedIds: function (decode) {
		var cvId = this.getCurrentCvId();
		var selectedIdsElement = jQuery('#selectedIds');
		var selectedIdsDataAttr = cvId + 'Selectedids';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes)) {
			var selectedIds = [];
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
		var cvId = this.getCurrentCvId();
		var exlcudedIdsElement = jQuery('#excludedIds');
		var excludedIdsDataAttr = cvId + 'Excludedids';
		var excludedIdsElementDataAttributes = exlcudedIdsElement.data();
		if (!(excludedIdsDataAttr in excludedIdsElementDataAttributes)) {
			var excludedIds = [];
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
		var cvId = this.getCurrentCvId();
		if (!Array.isArray(selectedIds)) {
			selectedIds = [selectedIds];
		}
		jQuery('#selectedIds').data(cvId + 'Selectedids', selectedIds);
	},
	writeExcludedIds: function (excludedIds) {
		var cvId = this.getCurrentCvId();
		jQuery('#excludedIds').data(cvId + 'Excludedids', excludedIds);
	},
	getCurrentCvId: function () {
		return jQuery('#customFilter').find('option:selected').data('id');
	},
	getAlphabetSearchField: function () {
		return jQuery("#alphabetSearchKey").val();
	},
	getAlphabetSearchValue: function () {
		return jQuery("#alphabetValue").val();
	},
	/**
	 * Function to check whether atleast minNumberOfRecords is checked
	 * @param {number} minNumberOfRecords
	 * @returns {boolean}
	 */
	checkListRecordSelected(minNumberOfRecords = 1) {
		let selectedIds = this.readSelectedIds();
		if (typeof selectedIds === 'object' && selectedIds.length < minNumberOfRecords) {
			return true;
		}
		return false;
	},
	inactiveFieldValidation: function (field) {
		field.validationEngine('hide');
		var form = field.closest('form');
		var invalidFields = form.data('jqv').InvalidFields;
		var fields = [field.get(0)];
		var validationVal = field.attr('data-validation-engine');
		field.attr('data-invalid-validation-engine', validationVal ? validationVal : 'validate[]');
		field.removeAttr('data-validation-engine');

		if (field.is('select') && field.hasClass('select2')) {
			let selectElement = app.getSelect2ElementFromSelect(field);
			selectElement.validationEngine('hide');
			fields.push(selectElement.get(0));
		}
		for (var i in fields) {
			var response = jQuery.inArray(fields[i], invalidFields);
			if (response != '-1') {
				invalidFields.splice(response, 1);
			}
		}
	},
	activeFieldValidation: function (field) {
		var validationVal = field.attr('data-invalid-validation-engine');
		if (typeof validationVal === 'undefined')
			return;
		field.attr('data-validation-engine', validationVal ? validationVal : 'validate[]');
		field.removeAttr('data-invalid-validation-engine');
	},
	postMassEdit: function (massEditContainer) {
		var thisInstance = this;
		var editInstance = Vtiger_Edit_Js.getInstance();
		massEditContainer.find('.selectRow').on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var blockElement = element.closest('.js-form-row-container').find('.fieldValue');
			var fieldElement = blockElement.find('[data-validation-engine],[data-invalid-validation-engine]');
			var fieldInfo = fieldElement.data('fieldinfo');
			if (element.prop('checked')) {
				thisInstance.activeFieldValidation(fieldElement);
			} else {
				thisInstance.inactiveFieldValidation(fieldElement);
			}
			if (fieldInfo !== undefined && fieldInfo.type === 'reference') {
				var mapFields = editInstance.getMappingRelatedField(fieldInfo.name, editInstance.getReferencedModuleName(blockElement), massEditContainer);
				$.each(mapFields, function (key, value) {
					var checkboxElement = massEditContainer.find('[id="selectRow' + key + '"]');
					if (checkboxElement.length && checkboxElement.prop('disabled')) {
						checkboxElement.prop('disabled', false);
						checkboxElement.trigger('click');
						checkboxElement.prop('disabled', true);
					}
				});
			}
		})
		massEditContainer.find('form').on('submit', function (e) {
			var form = jQuery(e.currentTarget);
			if (typeof form.data('submit') !== "undefined") {
				return false;
			}
			if (form.validationEngine('validate')) {
				e.preventDefault();
				if (!form.find('input[id^="selectRow"]:checked').length) {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('NONE_OF_THE_FIELD_VALUES_ARE_CHANGED_IN_MASS_EDIT'));
					return;
				}
				var invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length == 0) {
					form.find('[name="saveButton"]').prop('disabled', true);
				} else {
					return;
				}
				thisInstance.massActionSave(form, true).done(function (data) {
					thisInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
				}).fail(function (error, err) {
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
		self.getListViewRecords({
			orderby: listViewPageDiv.find('#orderBy').val(),
			sortorder: listViewPageDiv.find('#sortOrder').val(),
			viewname: self.getCurrentCvId()
		}).done(function (data) {
			aDeferred.resolve();
		}).fail(function (textStatus, errorThrown) {
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
		listViewPageDiv.find('.js-page--jump-drop-down').on('click', 'li', (e) => {
			e.stopImmediatePropagation();
		}).on('keypress', '.js-page-jump', (e) => {
			this.jumpToPage(e);
		});
	},
	/**
	 * Jump to next page
	 * @param {jQuery} element
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
	 * @param {jQuery} element
	 */
	jumpToClickedPage(element) {
		if (element.hasClass('disabled')) {
			return false;
		}
		this.paginationGoToPage(element.data('id'));
	},
	/**
	 * Jump to page function
	 * @param {jQuery.Event} e
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
		var aDeferred = jQuery.Deferred();
		var pageCountParams = this.getPageJumpParams();
		AppConnector.request(pageCountParams).done(function (data) {
			var response;
			if (typeof data != "object") {
				response = JSON.parse(data);
			} else {
				response = data;
			}
			aDeferred.resolve(response);
		}).fail(function (error, err) {

		});
		return aDeferred.promise();
	},
	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams: function () {
		var params = this.getDefaultParams();
		params.view = "ListAjax";
		params.mode = "getPageCount";

		return params;
	},
	/**
	 * Function to update Pagining status
	 */
	updatePagination: function (pageNumber) {
		pageNumber = typeof pageNumber !== "undefined" ? pageNumber : 1;
		AppConnector.request(Object.assign(this.getDefaultParams(), {
			module: app.getModuleName(),
			view: 'Pagination',
			page: pageNumber,
			mode: 'getPagination',
			sourceModule: jQuery('#moduleFilter').val(),
			totalCount: $('.pagination').data('totalCount'),
			noOfEntries: jQuery('#noOfEntries').val()
		})).done((data) => {
			jQuery('.paginationDiv').html(data);
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
			type: 'info',
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
	/*
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

		if (typeof selectOption === "object") {
			textOption = selectOption.text();
		}

		$('#select2-customFilter-container span').contents().last().replaceWith(textOption);
		app.setMainParams('pageNumber', '1');
		app.setMainParams('pageToJump', '1');
		app.setMainParams('orderBy', selectOption.data('orderby'));
		app.setMainParams('sortOrder', selectOption.data('sortorder'));
		let urlParams = {
			"viewname": selectOption.val(),
			//to make alphabetic search empty
			"search_key": this.getAlphabetSearchField(),
			"search_value": "",
			"search_params": ""
		};
		//Make the select all count as empty
		jQuery('#recordsCount').val('');
		//Make total number of pages as empty
		jQuery('#totalPageCount').text("");
		$('.pagination').data('totalCount', 0);
		this.getListViewRecords(urlParams).done(() => {
			this.breadCrumbsFilter(selectOption.text());
			this.ListViewPostOperation();
			this.updatePagination(1);
		});
		event.stopPropagation();
	},
	/*
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
		this.getListViewTopMenuContainer().find('.js-filter-tab').on('click', (e) => {
			const cvId = $(e.currentTarget).data('cvid');
			let selectOption = filterSelect.find(`[value=${cvId}]`);
			selectOption.trigger('click');
			$('#select2-customFilter-container span').contents().last().replaceWith(selectOption.text());
			filterSelect.val(cvId).trigger('change');
		});
	},
	breadCrumbsFilter: function (text) {
		var breadCrumbs = jQuery('.breadcrumbsContainer');
		var breadCrumbsLastSpan = breadCrumbs.last('span');
		var filterExist = breadCrumbsLastSpan.find('.breadCrumbsFilter');
		if (filterExist.length && text != undefined) {
			filterExist.text(' [' + app.vtranslate('JS_FILTER') + ': ' + text + ']');
		} else if (filterExist.length < 1) {
			text = (text == undefined) ? this.getFilterSelectElement().find(':selected').text() : text;
			if (breadCrumbsLastSpan.hasClass('breadCrumbsFilter')) {
				breadCrumbsLastSpan.text(': ' + text);
			} else {
				breadCrumbs.append('<small class="breadCrumbsFilter hideToHistory p-1 js-text-content u-text-ellipsis--no-hover" data-js="text"> [' + app.vtranslate('JS_FILTER') + ': ' + text + ']</small>');
			}
		}
	},
	ListViewPostOperation: function () {
		return true;
	},
	/*
	 * Function to register the click event for list view main check box.
	 */
	registerMainCheckBoxClickEvent: function () {
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click', '#listViewEntriesMainCheckBox', function () {
			var selectedIds = thisInstance.readSelectedIds();
			var excludedIds = thisInstance.readExcludedIds();
			if (jQuery('#listViewEntriesMainCheckBox').is(":checked")) {
				var recordCountObj = thisInstance.getRecordsCount();
				recordCountObj.done(function (data) {
					jQuery('#totalRecordsCount').text(data);
					if (jQuery("#deSelectAllMsgDiv").css('display') == 'none') {
						jQuery("#selectAllMsgDiv").show();
					}
				});

				jQuery('.listViewEntriesCheckBox').each(function (index, element) {
					jQuery(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						if ((jQuery.inArray(jQuery(element).val(), excludedIds)) != -1) {
							excludedIds.splice(jQuery.inArray(jQuery(element).val(), excludedIds), 1);
						}
					} else if ((jQuery.inArray(jQuery(element).val(), selectedIds)) == -1) {
						selectedIds.push(jQuery(element).val());
					}
				});
			} else {
				jQuery("#selectAllMsgDiv").hide();
				jQuery('.listViewEntriesCheckBox').each(function (index, element) {
					jQuery(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.push(jQuery(element).val());
						selectedIds = 'all';
					} else {
						selectedIds.splice(jQuery.inArray(jQuery(element).val(), selectedIds), 1);
					}
				});
			}
			thisInstance.writeSelectedIds(selectedIds);
			thisInstance.writeExcludedIds(excludedIds);

		});
	},
	/*
	 * Function  to register click event for list view check box.
	 */
	registerCheckBoxClickEvent: function () {
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click', '.listViewEntriesCheckBox', function (e) {
			var selectedIds = thisInstance.readSelectedIds();
			var excludedIds = thisInstance.readExcludedIds();
			var elem = jQuery(e.currentTarget);
			if (elem.is(':checked')) {
				elem.closest('tr').addClass('highlightBackgroundColor');
				if (selectedIds == 'all') {
					excludedIds.splice(jQuery.inArray(elem.val(), excludedIds), 1);
				} else if ((jQuery.inArray(elem.val(), selectedIds)) == -1) {
					selectedIds.push(elem.val());
				}
			} else {
				elem.closest('tr').removeClass('highlightBackgroundColor');
				if (selectedIds == 'all') {
					excludedIds.push(elem.val());
					selectedIds = 'all';
				} else {
					selectedIds.splice(jQuery.inArray(elem.val(), selectedIds), 1);
				}
			}
			thisInstance.checkSelectAll();
			thisInstance.writeSelectedIds(selectedIds);
			thisInstance.writeExcludedIds(excludedIds);
		});
	},
	/*
	 * Function to register the click event for select all.
	 */
	registerSelectAllClickEvent: function () {
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click', '#selectAllMsg', function () {
			jQuery('#selectAllMsgDiv').hide();
			jQuery("#deSelectAllMsgDiv").show();
			jQuery('#listViewEntriesMainCheckBox').prop('checked', true);
			jQuery('.listViewEntriesCheckBox').each(function (index, element) {
				jQuery(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
			});
			thisInstance.writeSelectedIds('all');
		});
	},
	/*
	 * Function to register the click event for deselect All.
	 */
	registerDeselectAllClickEvent: function () {
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click', '#deSelectAllMsg', function () {
			jQuery('#deSelectAllMsgDiv').hide();
			jQuery('#listViewEntriesMainCheckBox').prop('checked', false);
			jQuery('.listViewEntriesCheckBox').each(function (index, element) {
				jQuery(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
			});
			var excludedIds = [];
			var selectedIds = [];
			thisInstance.writeSelectedIds(selectedIds);
			thisInstance.writeExcludedIds(excludedIds);
		});
	},
	/*
	 * Function to register the click event for listView headers
	 */
	registerHeadersClickEvent: function () {
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click', '.js-listview_header', function (e) {
			var fieldName = jQuery(e.currentTarget).data('columnname');
			var sortOrderVal = jQuery(e.currentTarget).data('nextsortorderval');
			if (typeof sortOrderVal === "undefined")
				return;
			var cvId = thisInstance.getCurrentCvId();
			var urlParams = {
				"orderby": fieldName,
				"sortorder": sortOrderVal,
				"viewname": cvId
			}
			thisInstance.getListViewRecords(urlParams);
		});
	},
	/*
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
					sorceModuleName: app.getModuleName(),
				};
				if (currentOptionElement.data('featured') === 1) {
					params.actions = 'remove'
				} else {
					params.actions = 'add'
				}
				AppConnector.request(params).done(function (data) {
					window.location.reload();
				});
				event.stopPropagation();
			});
		}
	},
	/*
	 * Function to register the click event for duplicate filter
	 */
	registerDuplicateFilterClickEvent: function () {
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if (listViewFilterBlock != false) {
			listViewFilterBlock.on('mouseup', '.js-filter-duplicate', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var editUrl = currentOptionElement.data('duplicateurl');
				new CustomView(editUrl);
				event.stopPropagation();
			});
		}
	},
	/*
	 * Function to register the click event for edit filter
	 */
	registerEditFilterClickEvent: function () {
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if (listViewFilterBlock != false) {
			listViewFilterBlock.on('mouseup', '.js-filter-edit', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var editUrl = currentOptionElement.data('editurl');
				new CustomView(editUrl);
				event.stopPropagation();
			});
		}
	},
	/*
	 * Function to register the click event for delete filter
	 */
	registerDeleteFilterClickEvent: function () {
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if (listViewFilterBlock != false) {
			//used mouseup event to stop the propagation of customfilter select change event.
			listViewFilterBlock.on('mouseup', '.js-filter-delete', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE_FILTER');
				Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
					var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					var deleteUrl = currentOptionElement.data('deleteurl');
					var newEle = '<form action=' + deleteUrl + ' method="POST">';
					if (typeof csrfMagicName !== "undefined") {
						newEle += '<input type = "hidden" name ="' + csrfMagicName + '"  value=\'' + csrfMagicToken + '\'>';
					}
					newEle += '</form>';
					var formElement = jQuery(newEle);
					formElement.appendTo('body').submit();
				});
				event.stopPropagation();
			});
		}
	},
	/*
	 * Function to register the click event for approve filter
	 */
	registerApproveFilterClickEvent: function () {
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();

		if (listViewFilterBlock != false) {
			listViewFilterBlock.on('mouseup', '.js-filter-approve', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var approveUrl = currentOptionElement.data('approveurl');
				var newEle = '<form action=' + approveUrl + ' method="POST">';
				if (typeof csrfMagicName !== "undefined") {
					newEle += '<input type = "hidden" name ="' + csrfMagicName + '"  value=\'' + csrfMagicToken + '\'>';
				}
				newEle += '</form>';
				var formElement = jQuery(newEle);
				formElement.appendTo('body').submit();
				event.stopPropagation();
			});
		}
	},
	/*
	 * Function to register the click event for deny filter
	 */
	registerDenyFilterClickEvent: function () {
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if (listViewFilterBlock != false) {
			listViewFilterBlock.on('mouseup', '.js-filter-deny', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var denyUrl = currentOptionElement.data('denyurl');
				var form = '<form action=' + denyUrl + ' method="POST">';
				if (typeof csrfMagicName !== "undefined") {
					form += '<input type = "hidden" name ="' + csrfMagicName + '"  value=\'' + csrfMagicToken + '\'>';
				}
				form += '</form>';
				jQuery(form).appendTo('body').submit();
				event.stopPropagation();
			});
		}
	},
	/*
	 * Function to generate filter actions template
	 */
	appendFilterActionsTemplate: function (liElement) {
		let currentOptionElement = this.getSelectOptionFromChosenOption(liElement);
		let template = $(`<span class="js-filter-actions o-filter-actions noWrap float-right">
					<span ${currentOptionElement.data('featured') === 1 ? 'title="' + app.vtranslate('JS_REMOVE_TO_FAVORITES') + '"' : 'title="' + app.vtranslate('JS_ADD_TO_FAVORITES') + '"'} data-value="favorites" data-js="click"
						  class=" mr-1 js-filter-favorites ${currentOptionElement.data('featured') === 1 ? 'fas fa-star' : 'far fa-star'}"></span>
					<span title="${app.vtranslate('JS_DUPLICATE')}" data-value="duplicate" data-js="click"
						  class="fas fa-retweet mr-1 js-filter-duplicate ${$("#createFilter").length !== 0 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_EDIT')}" data-value="edit" data-js="click"
						  class="fas fa-pencil-alt mr-1 js-filter-edit ${currentOptionElement.data('editable') === 1 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_DELETE')}" data-value="delete" data-js="click"
						  class="fas fa-trash-alt mr-1 js-filter-delete ${currentOptionElement.data('deletable') === 1 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_DENY')}" data-value="deny" data-js="click"
						  class="fas fa-exclamation-circle mr-1 js-filter-deny ${currentOptionElement.data('public') === 1 ? '' : 'd-none'}"></span>
					<span title="${app.vtranslate('JS_APPROVE')}" data-value="approve" data-js="click"
						  class="fas fa-check mr-1 js-filter-approve ${currentOptionElement.data('pending') === 1 ? '' : 'd-none'}"></span>
				</span>`);
		template.appendTo(liElement.find('.js-filter__title'));
	},
	/*
	 * Function to register the hover event for customview filter options
	 */
	registerCustomFilterOptionsHoverEvent: function () {
		var filterBlock = this.getFilterBlock();
		if (filterBlock != false) {
			filterBlock.on('mouseenter mouseleave', 'li.select2-results__option[role="treeitem"]', (event) => {
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
	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.listViewEntries', function (e) {
			if (jQuery(e.target).closest('div').hasClass('actions'))
				return;
			if (jQuery(e.target).is('button') || jQuery(e.target).parent().is('button'))
				return;
			if (jQuery(e.target).closest('a').hasClass('noLinkBtn'))
				return;
			if (jQuery(e.target, jQuery(e.currentTarget)).is('td:first-child'))
				return;
			if (jQuery(e.target).is('input[type="checkbox"]'))
				return;
			if ($.contains(jQuery(e.currentTarget).find('td:last-child').get(0), e.target))
				return;
			if ($.contains(jQuery(e.currentTarget).find('td:first-child').get(0), e.target))
				return;
			var elem = jQuery(e.currentTarget);
			var recordUrl = elem.data('recordurl');
			if (typeof recordUrl === "undefined") {
				return;
			}
			window.location.href = recordUrl;
		});
	},
	registerRecordEvents: function () {
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.recordEvent', function (event) {
			var target = $(this);
			var recordId = target.closest('tr').data('id');
			var params = {};
			if (target.data('confirm')) {
				params.message = target.data('confirm');
				params.title = target.html() + ' ' + target.data('content');
			} else {
				params.message = target.data('content');
			}
			Vtiger_Helper_Js.showConfirmationBox(params).done(function (e) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(target.data('url') + '&sourceView=List&record=' + recordId).done(function (data) {
					progressIndicatorElement.progressIndicator({
						mode: 'hide'
					});
					if (data && data.success) {
						if (data.result.notify) {
							Vtiger_Helper_Js.showMessage(data.result.notify);
						}
						var paginationObject = $('.pagination');
						var totalCount = paginationObject.data('totalCount');
						if (totalCount != '') {
							totalCount--;
							paginationObject.data('totalCount', totalCount);
						}
						var orderBy = jQuery('#orderBy').val();
						var sortOrder = jQuery("#sortOrder").val();
						var pageNumber = parseInt($('#pageNumber').val());
						if ($('#noOfEntries').val() == 1 && pageNumber != 1) {
							pageNumber--;
						}
						var urlParams = {
							viewname: data.result.viewname,
							orderby: orderBy,
							sortorder: sortOrder,
							page: pageNumber,
						};
						$('#recordsCount').val('');
						$('#totalPageCount').text('');
						thisInstance.getListViewRecords(urlParams).done(function () {
							thisInstance.updatePagination(pageNumber);
						});
					} else {
						Vtiger_Helper_Js.showPnotify({
							text: app.vtranslate(data.error.message),
							title: app.vtranslate('JS_LBL_PERMISSION')
						});
					}
				});
			});
			event.stopPropagation();
		});
	},
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
					let params = {};
					if (target.data('confirm')) {
						params.message = target.data('confirm');
						params.title = target.html();
					} else {
						params.message = target.html();
					}
					Vtiger_Helper_Js.showConfirmationBox(params).done(function (e) {
						let progressIndicatorElement = jQuery.progressIndicator(),
						dataParams =  self.getSearchParams();
						delete dataParams.view;
						AppConnector.request({
							type: 'POST',
							url: target.data('url'),
							data: dataParams
						}).done(function (data) {
							progressIndicatorElement.progressIndicator({mode: 'hide'});
							if (data && data.result && data.result.notify) {
								Vtiger_Helper_Js.showMessage(data.result.notify);
							}
							self.getListViewRecords();
						}).fail(function (error, err) {
							progressIndicatorElement.progressIndicator({mode: 'hide'});
						});
					});
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
	/*
	 * Function to register the click event of email field
	 */
	registerEmailFieldClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.emailField', function (e) {
			e.stopPropagation();
		})
	},
	/*
	 * Function to register the click event of phone field
	 */
	registerPhoneFieldClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.phoneField', function (e) {
			e.stopPropagation();
		})
	},
	/*
	 * Function to register the click event of url field
	 */
	registerUrlFieldClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.urlField', function (e) {
			e.stopPropagation();
		})
	},
	/**
	 * Function to inactive field for validation in a form
	 * this will remove data-validation-engine attr of all the elements
	 * @param Accepts form as a parameter
	 */
	inactiveFieldsValidation: function (form) {
		var massEditFieldList = jQuery('#massEditFieldsNameList').data('value');
		for (var fieldName in massEditFieldList) {
			var fieldInfo = massEditFieldList[fieldName];

			var fieldElement = form.find('[name="' + fieldInfo.name + '"]');
			if (fieldInfo.type == "reference") {
				//get the element which will be shown which has "_display" appended to actual field name
				fieldElement = form.find('[name="' + fieldInfo.name + '_display"]');
			} else if (fieldInfo.type == "multipicklist" || fieldInfo.type == "sharedOwner") {
				fieldElement = form.find('[name="' + fieldInfo.name + '[]"]');
			}

			//Not all the fields will be enabled for mass edit
			if (fieldElement.length == 0) {
				continue;
			}

			var elemData = fieldElement.data();

			//Blank validation by default
			var validationVal = "validate[]"
			if ('validationEngine' in elemData) {
				validationVal = elemData.validationEngine;
				delete elemData.validationEngine;
			}
			fieldElement.attr('data-invalid-validation-engine', validationVal);
			fieldElement.removeAttr('data-validation-engine');
		}
	},
	registerEventForTabClick: function (form) {
		var ulContainer = form.find('.massEditTabs');
		ulContainer.on('click', 'a[data-toggle="tab"]', function (e) {
			form.validationEngine('validate');
			var invalidFields = form.data('jqv').InvalidFields;
			if (invalidFields.length > 0) {
				e.stopPropagation();
			}
		});
	},
	registerSlimScrollMassEdit: function () {
		app.showScrollBar(jQuery('div[name="massEditContent"]'), {'height': app.getScreenHeight(70) + 'px'});
	},
	/*
	 * Function to register the submit event for mass Actions save
	 */
	registerMassActionSubmitEvent: function () {
		$('body').on('submit', '#massSave', (e) => {
			let form = jQuery(e.currentTarget),
				commentContent = form.find('#commentcontent'),
				commentContentValue = commentContent.html();
			if (commentContentValue === "") {
				var errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY')
				commentContent.validationEngine('showPrompt', errorMsg, 'error', 'bottomLeft', true);
				e.preventDefault();
				return;
			}
			commentContent.validationEngine('hide');
			jQuery(form).find('[name=saveButton]').attr('disabled', 'disabled');
			this.massActionSave(form).done(function (data) {
				Vtiger_List_Js.clearList();
			});
			e.preventDefault();
		});
	},
	changeCustomFilterElementView: function () {
		const thisInstance = this;
		let filterSelectElement = this.getFilterSelectElement();
		if (filterSelectElement.length > 0 && filterSelectElement.is("select")) {
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
							additionalText = '<div class="u-max-w-lg-100 u-text-ellipsis--no-hover d-inline-block small">' + actualElement.data('option') + '</div>';
						}
						return '<div class="js-filter__title d-flex justify-content-between" data-js="appendTo"><div class="u-text-ellipsis--no-hover">' + actualElement.text() + '</div></div>' + additionalText;
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
			$('.filterActionsDiv').appendTo(select2Instance.$dropdown.find('.select2-dropdown:last')).removeClass('d-none').on('click', function (e) {
				thisInstance.createFilterClickEvent(e);
			});
		}
	},
	triggerDisplayTypeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if (widthType) {
			var elements = jQuery('.listViewEntriesTable').find('td,th');
			elements.attr('class', widthType);
		}
	},
	/**
	 * Function to show total records count in listview on hover
	 * of pageNumber text
	 */
	registerEventForTotalRecordsCount: function () {
		var thisInstance = this;
		jQuery('.totalNumberOfRecords').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var totalRecordsElement = jQuery('#totalCount');
			var totalNumberOfRecords = totalRecordsElement.val();
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
			element.parent().progressIndicator({mode: 'hide'});
		})
	},
	showPagingInfo: function () {
		var totalNumberOfRecords = jQuery('#totalCount').val();
		var pageNumberElement = jQuery('.pageNumbersText');
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange + " (" + totalNumberOfRecords + ")";
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		if (listViewEntriesCount != 0) {
			jQuery('.pageNumbersText').html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText').html("");
		}
	},
	registerUnreviewedCountEvent: function () {
		let ids = [],
			listViewContentDiv = this.getListViewContentContainer(),
			isUnreviewedActive = listViewContentDiv.find('.unreviewed').length;
		listViewContentDiv.find('tr.listViewEntries').each(function () {
			var id = jQuery(this).data('id');
			if (id) {
				ids.push(id);
			}
		})
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
					listViewContentDiv.find('tr[data-id="' + id + '"] .unreviewed .badge.all').text(value.a).parent().removeClass('d-none');
				}
				if (value.m > 0) {
					listViewContentDiv.find('tr[data-id="' + id + '"] .unreviewed .badge.mail').text(value.m).parent().removeClass('d-none');
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
			let id = jQuery(this).data('id');
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
			var data = appData.result;
			$.each(data, function (id, value) {
				if (value.type) {
					listViewContentDiv.find('tr[data-id="' + id + '"] .timeLineIconList').addClass(value.color + ' userIcon-' + value.type).removeClass('d-none')
						.on('click', function (e) {
							var element = jQuery(e.currentTarget);
							var url = element.data('url');
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
		var thisInstance = this;
		$('.dropdownEntityState a').on('click', function (e) {
			var element = $(this);
			element.closest('ul').find('a').removeClass('active');
			element.addClass('active');
			$('#entityState').val(element.data('value'));
			app.setMainParams('pageNumber', '1');
			app.setMainParams('pageToJump', '1');
			$('#recordsCount').val('');
			$('#totalPageCount').text("");
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
			var button = $(this);
			var calculateValue = button.closest('td').find('.calculateValue');
			var params = self.getSearchParams();
			var progress = $.progressIndicator({
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
				progress.progressIndicator({mode: 'hide'});
			});
		});
	},
	registerListScroll: function (container) {
		const containerH = container.height(),
			containerOffsetTop = container.offset().top,
			footerH = $('.js-footer').height(),
			windowH = $(window).height();
		//	if list is bigger than window fit its height to it
		if ((containerH + containerOffsetTop + footerH) > windowH) {
			container.height(windowH - (containerOffsetTop + footerH));
		}
		container.find('.js-fixed-thead').floatThead('destroy');
		container.siblings('.floatThead-container').remove();
		app.showNewScrollbarTopBottomRight(container);
		app.registerMiddleClickScroll(container);
	},
	registerFixedThead(container) {
		this.listFloatThead = container.find('.js-fixed-thead');
		this.listFloatThead.floatThead('destroy');
		this.listFloatThead.floatThead({
			scrollContainer: function () {
				return container;
			}
		});
		this.listFloatThead.floatThead('reflow');
	},
	getFloatTheadContainer(container = this.getListViewContentContainer()) {
		if (this.listFloatThead === false) {
			this.listFloatThead = container.find('.js-fixed-thead')
		}
		return this.listFloatThead;
	},
	reflowThead() {
		if ($(window).width() > app.breakpoints.sm) {
			this.getFloatTheadContainer().floatThead('reflow');
		}
	},
	registerMassActionsBtnEvents() {
		this.getListViewContainer().on('click', '.js-mass-action', (e) => {
			e.preventDefault();
			const url = $(e.currentTarget).data('url');
			if (typeof url != 'undefined') {
				if (this.checkListRecordSelected() !== true) {
					Vtiger_List_Js.triggerMassAction(url);
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
			if (typeof url !== "undefined") {
				if (this.checkListRecordSelected(2) !== true) {
					Vtiger_List_Js.triggerMassAction(url);
				} else {
					this.noRecordSelectedAlert('JS_SELECT_ATLEAST_TWO_RECORD_FOR_MERGING');
				}
			}
		});
	},
	registerMassActionModalEvents() {
		app.event.on('MassEditModal.AfterLoad', (data, container) => {
			if (container.hasClass('js-add-comment__container') || container.hasClass('js-send-sms__container')) {
				new App.Fields.Text.Completions(container.find('.js-completions'));
			}
		});
	},
	/**
	 * Register desktop events
	 * @param {jQuery} listViewContainer
	 */
	registerDesktopEvents(listViewContainer) {
		if ($(window).width() > app.breakpoints.sm) {
			this.registerListScroll(listViewContainer);
			this.registerFixedThead(listViewContainer);
		}
	},
	registerEvents: function () {
		this.registerRowClickEvent();
		this.registerPageNavigationEvents();
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
		this.registerRecordEvents();
		this.registerMassRecordsEvents();
		this.registerMassActionsBtnMergeEvents();
		this.registerHeadersClickEvent();
		this.registerMassActionSubmitEvent();
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
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		this.registerMassActionModalEvents();
		this.registerMassActionsBtnEvents();
		//this.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.registerUrlFieldClickEvent();
		this.registerEventForTotalRecordsCount();
		this.registerSummationEvent();
		//Just reset all the checkboxes on page load: added for chrome issue.
		var listViewContainer = this.getListViewContentContainer();
		listViewContainer.find('#listViewEntriesMainCheckBox,.listViewEntriesCheckBox').prop('checked', false);
		this.getListSearchInstance(false);
		this.registerDesktopEvents(listViewContainer);
		this.registerUnreviewedCountEvent();
		this.registerLastRelationsEvent();
		Vtiger_Index_Js.registerMailButtons(listViewContainer);
	},
	/**
	 * Function that executes after the mass delete action
	 */
	postMassDeleteRecords: function () {
		var aDeferred = jQuery.Deferred();
		var listInstance = Vtiger_List_Js.getInstance();
		app.hideModalWindow();
		listInstance.getListViewRecords().done(function (data) {
			jQuery('#recordsCount').val('');
			jQuery('#totalPageCount').text('');
			//listInstance.triggerDisplayTypeEvent();
			jQuery('#deSelectAllMsg').trigger('click');
			listInstance.calculatePages().done(function () {
				listInstance.updatePagination();
			});
			aDeferred.resolve();
		});
		jQuery('#recordsCount').val('');
		return aDeferred.promise();
	}
});
