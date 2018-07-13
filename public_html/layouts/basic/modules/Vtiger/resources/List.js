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
		if (Vtiger_List_Js.listInstance == false) {
			var module = app.getModuleName();
			var parentModule = app.getParentModuleName();
			if (parentModule == 'Settings') {
				var moduleClassName = parentModule + "_" + module + "_List_Js";
				if (typeof window[moduleClassName] === "undefined") {
					moduleClassName = module + "_List_Js";
				}
				var fallbackClassName = parentModule + "_Vtiger_List_Js";
				if (typeof window[fallbackClassName] === "undefined") {
					fallbackClassName = "Vtiger_List_Js";
				}
			} else {
				moduleClassName = module + "_List_Js";
				fallbackClassName = "Vtiger_List_Js";
			}
			if (typeof window[moduleClassName] !== "undefined") {
				var instance = new window[moduleClassName]();
			} else {
				var instance = new window[fallbackClassName]();
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
		if (validationResult != true) {
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var postData = listInstance.getDefaultParams();
			delete postData.parent;
			postData.module = app.getModuleName();
			postData.view = 'SendMailModal';
			postData.selected_ids = selectedIds;
			postData.excluded_ids = excludedIds;
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
		var thisInstance = this;
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			var progressIndicatorElement = jQuery.progressIndicator();
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				viewname: cvId,
				selected_ids: selectedIds,
				excluded_ids: excludedIds
			};
			var searchValue = listInstance.getListSearchInstance().getAlphabetSearchValue();
			postData.search_params = JSON.stringify(listInstance.getListSearchInstance().getListSearchParams());
			if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
				postData['search_key'] = listInstance.getListSearchInstance().getAlphabetSearchField();
				postData['search_value'] = searchValue;
				postData['operator'] = 's';
			}

			var actionParams = {
				type: "POST",
				url: massActionUrl,
				dataType: "application/x-msexcel",
				data: postData
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
		var listInstance = Vtiger_List_Js.getInstance();
		var selectedIds = listInstance.readSelectedIds(true);
		var excludedIds = listInstance.readExcludedIds(true);
		var cvId = listInstance.getCurrentCvId();
		var transferOwner = jQuery('#transferOwnerId').val();
		var relatedModules = jQuery('#related_modules').val();

		var params = {
			'module': app.getModuleName(),
			'action': 'TransferOwnership',
			"viewname": cvId,
			"selected_ids": selectedIds,
			"excluded_ids": excludedIds,
			'transferOwnerId': transferOwner,
			'related_modules': relatedModules
		}
		AppConnector.request(params).done(
			function (data) {
				if (data.success) {
					app.hideModalWindow();
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
					listInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
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
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"viewname": cvId,
				"selected_ids": selectedIds,
				"excluded_ids": excludedIds
			};
			var listViewInstance = Vtiger_List_Js.getInstance();
			if (listViewInstance.getListSearchInstance()) {
				var searchValue = listViewInstance.getListSearchInstance().getAlphabetSearchValue();
				postData.search_params = JSON.stringify(listViewInstance.getListSearchInstance().getListSearchParams());
				if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
					postData['search_key'] = listViewInstance.getListSearchInstance().getAlphabetSearchField();
					postData['search_value'] = searchValue;
					postData['operator'] = 's';
				}
			}
			var actionParams = {
				"type": "POST",
				"url": massActionUrl,
				"dataType": "html",
				"data": postData
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
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
							//listInstance.triggerDisplayTypeEvent();
						}
					}, css)
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
		var listInstance = Vtiger_List_Js.getInstance();
		// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
		var selectedIds = listInstance.readSelectedIds(true);
		var excludedIds = listInstance.readExcludedIds(true);
		var cvId = listInstance.getCurrentCvId();
		var pageNumber = jQuery('#pageNumber').val();
		if ('undefined' === typeof cvId)
			exportActionUrl += '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds + '&page=' + pageNumber;
		else
			exportActionUrl += '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds + '&viewname=' + cvId + '&page=' + pageNumber;
		var listViewInstance = Vtiger_List_Js.getInstance();
		if (listViewInstance.getListSearchInstance()) {
			var searchValue = listViewInstance.getListSearchInstance().getAlphabetSearchValue();
			exportActionUrl += "&search_params=" + JSON.stringify(listViewInstance.getListSearchInstance().getListSearchParams());
			if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
				exportActionUrl += '&search_key=' + listViewInstance.getListSearchInstance().getAlphabetSearchField();
				exportActionUrl += '&search_value=' + searchValue;
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
	getSelectedRecordsParams: function (checkList, urlSearchParams) {
		var listInstance = Vtiger_List_Js.getInstance();
		if (checkList == false || listInstance.checkListRecordSelected() != true) {
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				viewname: cvId,
				selected_ids: selectedIds,
				excluded_ids: excludedIds
			};

			var searchValue = listInstance.getListSearchInstance().getAlphabetSearchValue();
			postData.search_params = JSON.stringify(listInstance.getListSearchInstance().getListSearchParams());
			if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
				postData['search_key'] = listInstance.getListSearchInstance().getAlphabetSearchField();
				postData['search_value'] = searchValue;
				postData['operator'] = 's';
			}
			return postData;
		} else {
			listInstance.noRecordSelectedAlert();
		}
		return false;
	},
	triggerGenerateRecords: function (url) {
		var selected = Vtiger_List_Js.getSelectedRecordsParams();
		if (selected === false) {
			return false;
		}
		var params = {};
		jQuery.extend(params, selected);
		url += '&' + jQuery.param(params);
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		app.showModalWindow(null, url, function () {
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
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var message = app.vtranslate('JS_MASS_REVIEWING_CHANGES_CONFIRMATION');
			var title = '<i class="fa fa-check-circle"></i> ' + app.vtranslate('JS_LBL_REVIEW_CHANGES');
			Vtiger_Helper_Js.showConfirmationBox({'message': message, 'title': title}).done(function (e) {
				var url = reviewUrl + '&viewname=' + cvId + '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds;
				if (listInstance.getListSearchInstance()) {
					var searchValue = listInstance.getListSearchInstance().getAlphabetSearchValue();
					url += "&search_params=" + JSON.stringify(listInstance.getListSearchInstance().getListSearchParams());
					if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
						url += '&search_key=' + listInstance.getListSearchInstance().getAlphabetSearchField();
						url += '&search_value=' + searchValue;
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
	getDefaultParams: function () {
		var params = {
			module: app.getModuleName(),
			page: jQuery('#pageNumber').val(),
			view: app.getViewName(),
			viewname: this.getCurrentCvId(),
			orderby: jQuery('#orderBy').val(),
			sortorder: jQuery("#sortOrder").val(),
			entityState: jQuery("#entityState").val(),
		}
		if (app.getParentModuleName()) {
			params.parent = app.getParentModuleName();
		}
		var listSearchInstance = this.getListSearchInstance();
		if (listSearchInstance !== false) {
			var searchValue = this.getListSearchInstance().getAlphabetSearchValue();
			params.search_params = JSON.stringify(this.getListSearchInstance().getListSearchParams(true));
			if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
				params['search_key'] = this.getListSearchInstance().getAlphabetSearchField();
				params['search_value'] = searchValue;
				params['operator'] = 's';
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
		}).fail(function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	postLoadListViewRecordsEvents: function (container) {
		const self = this;
		self.registerListScroll(container);
		App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'));
		App.Fields.Picklist.changeSelectElementView(container);
		app.showPopoverElementView(container.find('.js-popover-tooltip'));
		self.registerListViewSpecialOption();
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
				pageCount = Math.ceil(totalRecordCount / pageLimit);
				if (pageCount == 0) {
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
				return aDeferred.promise();
			}
			/*
			 this.getPageCount().done(function (data) {
			 var pageCount = data['result']['page'];
			 if (pageCount == 0) {
			 pageCount = 1;
			 }
			 element.text(pageCount);
			 aDeferred.resolve();
			 });*/
			aDeferred.resolve();
		} else {
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	/*
	 * Function to return alerts if no records selected.
	 */
	noRecordSelectedAlert: function () {
		return alert(app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'));
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
	/*
	 * Function to check whether atleast one record is checked
	 */
	checkListRecordSelected: function () {
		var selectedIds = this.readSelectedIds();
		if (typeof selectedIds == 'object' && selectedIds.length <= 0) {
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

		if (field.is('select') && field.hasClass('chzn-select')) {
			var chosenElement = app.getChosenElementFromSelect(field);
			chosenElement.validationEngine('hide');
			fields.push(chosenElement.get(0));
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
	/*
	 * Function to register List view Page Navigation
	 */
	registerPageNavigationEvents: function () {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		jQuery('#listViewNextPageButton').on('click', function (e) {
			if ($(this).hasClass('disabled')) {
				return;
			}
			var pageLimit = jQuery('#pageLimit').val();
			var noOfEntries = jQuery('#noOfEntries').val();
			if (noOfEntries == pageLimit) {
				var orderBy = jQuery('#orderBy').val();
				var sortOrder = jQuery("#sortOrder").val();
				var cvId = thisInstance.getCurrentCvId();
				var urlParams = {
					orderby: orderBy,
					sortorder: sortOrder,
					viewname: cvId
				}
				var pageNumber = jQuery('#pageNumber').val();
				var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
				jQuery('#pageNumber').val(nextPageNumber);
				jQuery('#pageToJump').val(nextPageNumber);
				thisInstance.getListViewRecords(urlParams).done(function (data) {
					thisInstance.updatePagination(nextPageNumber);
					aDeferred.resolve();
				}).fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			}
			return aDeferred.promise();
		});
		jQuery('#listViewPreviousPageButton').on('click', function () {
			var aDeferred = jQuery.Deferred();
			var pageNumber = jQuery('#pageNumber').val();
			if (pageNumber > 1) {
				var orderBy = jQuery('#orderBy').val();
				var sortOrder = jQuery("#sortOrder").val();
				var cvId = thisInstance.getCurrentCvId();
				var urlParams = {
					"orderby": orderBy,
					"sortorder": sortOrder,
					"viewname": cvId
				}
				var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
				jQuery('#pageNumber').val(previousPageNumber);
				jQuery('#pageToJump').val(previousPageNumber);
				thisInstance.getListViewRecords(urlParams).done(function (data) {
					thisInstance.updatePagination(previousPageNumber);
					aDeferred.resolve();
				}).fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			}
		});

		jQuery('.pageNumber').on('click', function () {
			var disabled = $(this).hasClass("disabled")
			if (disabled)
				return false;
			var pageNumber = $(this).data("id");
			var orderBy = jQuery('#orderBy').val();
			var sortOrder = jQuery("#sortOrder").val();
			var cvId = thisInstance.getCurrentCvId();
			var urlParams = {
				"orderby": orderBy,
				"sortorder": sortOrder,
				"viewname": cvId,
				"page": pageNumber
			}
			var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
			jQuery('#pageNumber').val(previousPageNumber);
			jQuery('#pageToJump').val(previousPageNumber);
			thisInstance.getListViewRecords(urlParams).done(function (data) {
				thisInstance.updatePagination(pageNumber);
			}).fail(function (textStatus, errorThrown) {
			});
		});
		$('#totalCountBtn').on('click', function () {
			app.hidePopover(jQuery(this));
			var paramsNotifier = {
				title: app.vtranslate('JS_LBL_PERMISSION'),
				text: app.vtranslate('JS_GET_PAGINATION_INFO'),
				type: 'info',
			};
			Vtiger_Helper_Js.showMessage(paramsNotifier);
			var params = thisInstance.getDefaultParams();
			params.totalCount = -1;
			params.view = 'Pagination';
			params.mode = 'getPagination';
			AppConnector.request(params).done(function (data) {
				jQuery('.paginationDiv').html(data);
				thisInstance.registerPageNavigationEvents();
			});
		});
		jQuery('#listViewPageJump').on('click', function (e) {
			if (typeof Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(jQuery('#pageToJump')) !== "undefined") {
				var pageNo = jQuery('#pageNumber').val();
				jQuery("#pageToJump").val(pageNo);
			}
			jQuery('#pageToJump').validationEngine('hideAll');
			var element = jQuery('#totalPageCount');
			var totalPageNumber = element.text();
			if (totalPageNumber == "") {
				var totalCountElem = jQuery('#totalCount');
				var totalRecordCount = totalCountElem.val();
				if (totalRecordCount != '') {
					var recordPerPage = jQuery('#pageLimit').val();
					if (recordPerPage == '0')
						recordPerPage = 1;
					pageCount = Math.ceil(totalRecordCount / recordPerPage);
					if (pageCount == 0) {
						pageCount = 1;
					}
					element.text(pageCount);
					return;
				}
				element.progressIndicator({});
				thisInstance.getPageCount().done(function (data) {
					var pageCount = data['result']['page'];
					totalCountElem.val(data['result']['numberOfRecords']);
					if (pageCount == 0) {
						pageCount = 1;
					}
					element.text(pageCount);
					element.progressIndicator({mode: 'hide'});
				});
			}
		})

		jQuery('#listViewPageJumpDropDown').on('click', 'li', function (e) {
			e.stopImmediatePropagation();
		}).on('keypress', '#pageToJump', function (e) {
			if (e.which == 13) {
				e.stopImmediatePropagation();
				var element = jQuery(e.currentTarget);
				var response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
				if (typeof response !== "undefined") {
					element.validationEngine('showPrompt', response, '', "topLeft", true);
				} else {
					element.validationEngine('hideAll');
					var currentPageElement = jQuery('#pageNumber');
					var currentPageNumber = currentPageElement.val();
					var newPageNumber = parseInt(jQuery(e.currentTarget).val());
					var totalPages = parseInt(jQuery('#totalPageCount').text());
					if (newPageNumber > totalPages) {
						var error = app.vtranslate('JS_PAGE_NOT_EXIST');
						element.validationEngine('showPrompt', error, '', "topLeft", true);
						return;
					}
					if (newPageNumber == currentPageNumber) {
						var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + " " + newPageNumber;
						var params = {
							text: message,
							type: 'info'
						};
						Vtiger_Helper_Js.showMessage(params);
						return;
					}
					currentPageElement.val(newPageNumber);
					thisInstance.getListViewRecords().done(function (data) {
						thisInstance.updatePagination(newPageNumber);
						element.closest('.btn-group ').removeClass('open');
					}).fail(function (textStatus, errorThrown) {
					});
				}
				return false;
			}
		});
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
		params['view'] = "ListAjax";
		params['mode'] = "getPageCount";

		return params;
	},
	/**
	 * Function to update Pagining status
	 */
	updatePagination: function (pageNumber) {
		pageNumber = typeof pageNumber !== "undefined" ? pageNumber : 1;
		var thisInstance = this;
		var cvId = thisInstance.getCurrentCvId();
		var params = {};
		params['module'] = app.getModuleName();
		if ('Settings' == app.getParentModuleName())
			params['parent'] = 'Settings';
		params['view'] = 'Pagination';
		params['viewname'] = cvId;
		params['page'] = pageNumber;
		params['mode'] = 'getPagination';
		params['sourceModule'] = jQuery('#moduleFilter').val();
		params['totalCount'] = $('.pagination').data('totalCount');
		var searchInstance = this.getListSearchInstance();
		if (searchInstance !== false) {
			var searchValue = searchInstance.getAlphabetSearchValue();
			params.search_params = JSON.stringify(this.getListSearchInstance().getListSearchParams());
			if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
				params['search_key'] = this.getListSearchInstance().getAlphabetSearchField();
				params['search_value'] = searchValue;
				params['operator'] = 's';
			}
		}
		params['noOfEntries'] = jQuery('#noOfEntries').val();
		AppConnector.request(params).done(function (data) {
			jQuery('.paginationDiv').html(data);
			thisInstance.registerPageNavigationEvents();

		});
	},
	/*
	 * Function to register the event for changing the custom Filter
	 */
	registerChangeCustomFilterEvent: function () {
		var thisInstance = this;
		this.getFilterSelectElement().on('change', function (event) {
			$(`.nav-item[data-cvid='${thisInstance.getCurrentCvId()}'] .nav-link`).tab('show');
			var currentTarget = jQuery(event.currentTarget);
			var selectOption = currentTarget.find(':selected');
			app.setMainParams('pageNumber', '1');
			app.setMainParams('pageToJump', '1');
			app.setMainParams('orderBy', selectOption.data('orderby'));
			app.setMainParams('sortOrder', selectOption.data('sortorder'));
			var urlParams = {
				"viewname": jQuery(this).val(),
				//to make alphabetic search empty
				"search_key": thisInstance.getAlphabetSearchField(),
				"search_value": "",
				"search_params": ""
			}
			//Make the select all count as empty
			jQuery('#recordsCount').val('');
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			$('.pagination').data('totalCount', 0);
			thisInstance.getListViewRecords(urlParams).done(function () {
				thisInstance.breadCrumbsFilter(selectOption.text());
				thisInstance.ListViewPostOperation();
				thisInstance.updatePagination(1);
			});
			event.stopPropagation();
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
				breadCrumbs.append('<small class="breadCrumbsFilter hideToHistory p-1 js-text-content" data-js="text"> [' + app.vtranslate('JS_FILTER') + ': ' + text + ']</small>');
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
		listViewPageDiv.on('click', '.listViewHeaderValues', function (e) {
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
	registerCreateFilterClickEvent: function (event) {
		var thisInstance = this;
		//to close the dropdown
		thisInstance.getFilterSelectElement().data('select2').close();
		var currentElement = jQuery(event.currentTarget);
		var liElement = currentElement.find('#createFilter');
		var createUrl = liElement.data('createurl');
		Vtiger_CustomView_Js.loadFilterView(createUrl);
	},
	/*
	 * Function to register the click event for duplicate filter
	 */
	registerDuplicateFilterClickEvent: function () {
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if (listViewFilterBlock != false) {
			listViewFilterBlock.on('mouseup', 'li [data-fa-i2svg].duplicateFilter', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var editUrl = currentOptionElement.data('duplicateurl');
				Vtiger_CustomView_Js.loadFilterView(editUrl);
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
			listViewFilterBlock.on('mouseup', 'li [data-fa-i2svg].editFilter', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var editUrl = currentOptionElement.data('editurl');
				Vtiger_CustomView_Js.loadFilterView(editUrl);
				var cvId = jQuery(this).data('cvid');
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
			listViewFilterBlock.on('mouseup', 'li [data-fa-i2svg].deleteFilter', function (event) {
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE_FILTER');
				Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(
					function (e) {
						var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
						var deleteUrl = currentOptionElement.data('deleteurl');
						var newEle = '<form action=' + deleteUrl + ' method="POST">';
						if (typeof csrfMagicName !== "undefined") {
							newEle += '<input type = "hidden" name ="' + csrfMagicName + '"  value=\'' + csrfMagicToken + '\'>';
						}
						newEle += '</form>';
						var formElement = jQuery(newEle);
						formElement.appendTo('body').submit();
					},
					function (error, err) {
					}
				);
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
			listViewFilterBlock.on('mouseup', 'li [data-fa-i2svg].approveFilter', function (event) {
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
			listViewFilterBlock.on('mouseup', 'li [data-fa-i2svg].denyFilter', function (event) {
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
	 * Function to register the hover event for customview filter options
	 */
	registerCustomFilterOptionsHoverEvent: function () {
		var thisInstance = this;
		var listViewTopMenuDiv = this.getListViewTopMenuContainer();
		var filterBlock = this.getFilterBlock()
		if (filterBlock != false) {
			filterBlock.on('mouseenter mouseleave', 'li.select2-results__option[role="treeitem"]', function (event) {
				var liElement = $(event.currentTarget);
				var liFilterImages = liElement.find('.filterActionImgs');
				if (liElement.hasClass('group-result')) {
					return;
				}

				if (event.type === 'mouseenter') {
					if (liFilterImages.length > 0) {
						liFilterImages.show();
					} else {
						thisInstance.performFilterImageActions(liElement);
					}

				} else {
					liFilterImages.hide();
				}
			});
		}
	},
	performFilterImageActions: function (liElement) {
		jQuery('.filterActionImages').clone(true, true).removeClass('filterActionImages').addClass('filterActionImgs').appendTo(liElement).removeClass('d-none');
		var currentOptionElement = this.getSelectOptionFromChosenOption(liElement);
		var deletable = currentOptionElement.data('deletable');
		if (deletable != '1') {
			liElement.find('.deleteFilter').remove();
		}
		var editable = currentOptionElement.data('editable');
		if (editable != '1') {
			liElement.find('.editFilter').remove();
		}
		var pending = currentOptionElement.data('pending');
		if (pending != '1') {
			liElement.find('.approveFilter').remove();
		}
		var approve = currentOptionElement.data('public');
		if (approve != '1') {
			liElement.find('.denyFilter').remove();
		}
		if ($("#createFilter").length == 0) {
			liElement.find('.duplicateFilter').remove();
		}
	},
	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function () {
		var thisInstance = this;
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
		var thisInstance = this;
		jQuery('.listViewActionsDiv').on('click', '.massRecordEvent', function (event) {
			var target = $(this);
			var listInstance = Vtiger_List_Js.getInstance();
			var validationResult = listInstance.checkListRecordSelected();
			if (validationResult != true) {
				var params = {};
				if (target.data('confirm')) {
					params.message = target.data('confirm');
					params.title = target.html();
				} else {
					params.message = target.html();
				}
				Vtiger_Helper_Js.showConfirmationBox(params).done(function (e) {
					var progressIndicatorElement = jQuery.progressIndicator();
					var postData = {
						viewname: listInstance.getCurrentCvId(),
						selected_ids: listInstance.readSelectedIds(true),
						excluded_ids: listInstance.readExcludedIds(true)
					};
					var listViewInstance = Vtiger_List_Js.getInstance();
					if (listViewInstance.getListSearchInstance()) {
						var searchValue = listViewInstance.getListSearchInstance().getAlphabetSearchValue();
						postData.search_params = JSON.stringify(listViewInstance.getListSearchInstance().getListSearchParams());
						if ((typeof searchValue !== "undefined") && (searchValue.length > 0)) {
							postData['search_key'] = listViewInstance.getListSearchInstance().getAlphabetSearchField();
							postData['search_value'] = searchValue;
							postData['operator'] = 's';
						}
					}
					AppConnector.request({
						type: "POST",
						url: target.data('url'),
						data: postData
					}).done(function (data) {
						progressIndicatorElement.progressIndicator({mode: 'hide'});
						if (data && data.result && data.result.notify) {
							Vtiger_Helper_Js.showMessage(data.result.notify);
						}
						thisInstance.getListViewRecords();
						thisInstance.writeSelectedIds([]);
					}).fail(function (error, err) {
						progressIndicatorElement.progressIndicator({mode: 'hide'});
					});
				});
			} else {
				listInstance.noRecordSelectedAlert();
			}
		});
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
		var thisInstance = this;
		jQuery('body').on('submit', '#massSave', function (e) {
			var form = jQuery(e.currentTarget);
			var commentContent = form.find('#commentcontent')
			var commentContentValue = commentContent.val();
			if (commentContentValue == "") {
				var errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY')
				commentContent.validationEngine('showPrompt', errorMsg, 'error', 'bottomLeft', true);
				e.preventDefault();
				return;
			}
			commentContent.validationEngine('hide');
			jQuery(form).find('[name=saveButton]').attr('disabled', 'disabled');
			thisInstance.massActionSave(form).done(function (data) {
				Vtiger_List_Js.clearList();
			});
			e.preventDefault();
		});
	},
	changeCustomFilterElementView: function () {
		var thisInstance = this;
		var filterSelectElement = this.getFilterSelectElement();
		if (filterSelectElement.length > 0 && filterSelectElement.is("select")) {
			App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
				templateSelection: function (data) {
					var resultContainer = jQuery('<span></span>');
					resultContainer.append(jQuery(jQuery('.filterImage').clone().get(0)).show());
					resultContainer.append(data.text);
					return resultContainer;
				},
				customSortOptGroup: true,
				closeOnSelect: true
			});

			var select2Instance = filterSelectElement.data('select2');
			jQuery('.filterActionsDiv').appendTo(select2Instance.$dropdown.find('.select2-dropdown:last')).removeClass('d-none').on('click', function (e) {
				thisInstance.registerCreateFilterClickEvent(e);
			});
		}
	},
	registerFeaturedElementsEvent: function () {
		var thisInstance = this;
		var listViewTopMenuDiv = this.getListViewTopMenuContainer();
		listViewTopMenuDiv.on('click', '.featuredLabel', function (e) {
			var cvId = jQuery(this).data('cvid');
			thisInstance.getFilterSelectElement().val(cvId).trigger('change');
		});
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
		var thisInstance = this;
		var ids = [];
		var listViewContentDiv = this.getListViewContentContainer();
		var isUnreviewedActive = listViewContentDiv.find('.unreviewed').length;
		listViewContentDiv.find('tr.listViewEntries').each(function () {
			var id = jQuery(this).data('id');
			if (id) {
				ids.push(id);
			}
		})
		if (!ids || isUnreviewedActive < 1) {
			return;
		}
		var actionParams = {
			action: 'ChangesReviewedOn',
			mode: 'getUnreviewed',
			module: 'ModTracker',
			sourceModule: app.getModuleName(),
			recordsId: ids
		};
		AppConnector.request(actionParams).done(function (appData) {
			var data = appData.result;
			$.each(data, function (id, value) {
				if (value.a > 0) {
					listViewContentDiv.find('tr[data-id="' + id + '"] .unreviewed .badge.all').text(value.a).parent().removeClass('d-none');
				}
				if (value.m > 0) {
					listViewContentDiv.find('tr[data-id="' + id + '"] .unreviewed .badge.mail').text(value.m).parent().removeClass('d-none');
				}
			});
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
		});
	},
	registerLastRelationsEvent: function () {
		var thisInstance = this;
		var ids = [];
		var listViewContentDiv = this.getListViewContentContainer();
		var isTimeLineActive = listViewContentDiv.find('.timeLineIconList').length;
		listViewContentDiv.find('tr.listViewEntries').each(function () {
			var id = jQuery(this).data('id');
			if (id) {
				ids.push(id);
			}
		})
		if (!ids || isTimeLineActive < 1) {
			return;
		}
		var actionParams = {
			action: 'LastRelation',
			module: 'ModTracker',
			sourceModule: app.getModuleName(),
			recordsId: ids
		};
		AppConnector.request(actionParams).done(function (appData) {
			var data = appData.result;
			$.each(data, function (id, value) {
				if (value.type) {
					listViewContentDiv.find('tr[data-id="' + id + '"] .timeLineIconList').addClass(value.color + ' userIcon-' + value.type).parent().removeClass('d-none')
						.on('click', function (e) {
							var element = jQuery(e.currentTarget);
							var url = element.data('url');
							app.showModalWindow(null, url, function (data) {
								Vtiger_Index_Js.registerMailButtons(data);
							});
						});
				}
			});
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
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
			$('#dropdownEntityState').find('[data-fa-i2svg]').attr('class', element.find('[data-fa-i2svg]').attr('class'));
			thisInstance.getListViewRecords().done(function (data) {
				thisInstance.calculatePages().done(function () {
					thisInstance.updatePagination();
				});
			});
		});
	},
	registerSummationEvent: function () {
		var thisInstance = this;
		this.getListViewContentContainer().on('click', '.listViewSummation button', function () {
			var button = $(this);
			var calculateValue = button.closest('td').find('.calculateValue');
			var params = thisInstance.getDefaultParams();
			var progress = $.progressIndicator({
				message: app.vtranslate('JS_CALCULATING_IN_PROGRESS'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			params['action'] = 'List';
			params['mode'] = 'calculate';
			params['fieldName'] = button.data('field');
			params['calculateType'] = button.data('operator');
			if (thisInstance.checkListRecordSelected() != true) {
				params['selected_ids'] = thisInstance.readSelectedIds(true);
				params['excluded_ids'] = thisInstance.readExcludedIds(true);
			}
			delete params['view'];
			app.hidePopover(button);
			AppConnector.request(params).done(function (response) {
				if (response.success) {
					calculateValue.html(response.result);
				} else {
					calculateValue.html('');
				}
				progress.progressIndicator({mode: 'hide'});
			});
		});
	},
	registerListScroll: function (container) {
		if (CONFIG.view !== 'ListPreview') {
			const containerH = container.height(),
				containerOffsetTop = container.offset().top,
				footerH = $('.js-footer').height(),
				windowH = $(window).height();
			if ($(window).width() > app.breakpoints.sm) {
				//if list is bigger than window fit its height to it
				if ((containerH + containerOffsetTop + footerH) > windowH) {
					container.height(windowH - (containerOffsetTop + footerH));
				}
				app.showNewScrollbarTopBottomRight(container);
			}
		}
	},
	registerMassActionsBtnEvents: function () {
		var thisInstance = this;
		this.getListViewContainer().on('click', '.js-mass-action', function (e) {
			e.preventDefault();
			var currentElement = $(this);
			var url = currentElement.data('url');
			if (typeof url !== "undefined") {
				if (thisInstance.checkListRecordSelected() != true) {
					Vtiger_List_Js.triggerMassAction(url);
				} else {
					thisInstance.noRecordSelectedAlert();
				}
			}
			e.stopPropagation();
		});
	},
	registerEvents: function () {
		this.breadCrumbsFilter();
		this.registerRowClickEvent();
		this.registerPageNavigationEvents();
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
		this.registerRecordEvents();
		this.registerMassRecordsEvents();
		this.registerMassActionsBtnEvents();
		this.registerHeadersClickEvent();
		this.registerMassActionSubmitEvent();
		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerChangeEntityStateEvent();
		this.registerDuplicateFilterClickEvent();
		this.registerEditFilterClickEvent();
		this.registerDeleteFilterClickEvent();
		this.registerApproveFilterClickEvent();
		this.registerDenyFilterClickEvent();
		this.registerCustomFilterOptionsHoverEvent();
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		//this.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.registerUrlFieldClickEvent();
		this.registerEventForTotalRecordsCount();
		this.registerSummationEvent();
		//Just reset all the checkboxes on page load: added for chrome issue.
		var listViewContainer = this.getListViewContentContainer();
		listViewContainer.find('#listViewEntriesMainCheckBox,.listViewEntriesCheckBox').prop('checked', false);
		this.registerListScroll(listViewContainer);
		this.getListSearchInstance(false);
		this.registerListViewSpecialOption();
		this.registerFeaturedElementsEvent();
		this.registerUnreviewedCountEvent();
		this.registerLastRelationsEvent();

		Vtiger_Index_Js.registerMailButtons(listViewContainer);
	},
	registerListViewSpecialOption: function () {
		var thisInstance = this;
		var listViewContainer = this.getListViewContentContainer();
		var box = listViewContainer.find('.listViewEntriesTable input.searchInSubcategories');
		box.on("change", function (e) {
			var element = jQuery(e.currentTarget);
			var searchContributorElement = jQuery('.listSearchContributor[name="' + element.data('columnname') + '"]');
			var searchValue = searchContributorElement.val();
			if (searchValue) {
				thisInstance.getListSearchInstance().triggerListSearch();
			}
		})
	},
	/**
	 * Function that executes after the mass delete action
	 */
	postMassDeleteRecords: function () {
		var aDeferred = jQuery.Deferred();
		var listInstance = Vtiger_List_Js.getInstance();
		app.hideModalWindow();
		var module = app.getModuleName();
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
	},
});
