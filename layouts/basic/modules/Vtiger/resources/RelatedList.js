/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

jQuery.Class("Vtiger_RelatedList_Js", {}, {
	selectedRelatedTabElement: false,
	parentRecordId: false,
	parentModuleName: false,
	relatedModulename: false,
	relatedTabsContainer: false,
	detailViewContainer: false,
	relatedContentContainer: false,
	listSearchInstance: false,
	setSelectedTabElement: function (tabElement) {
		this.selectedRelatedTabElement = tabElement;
	},
	getSelectedTabElement: function () {
		return this.selectedRelatedTabElement;
	},
	getParentId: function () {
		return this.parentRecordId;
	},
	getRelatedContainer: function () {
		return this.relatedContentContainer;
	},
	loadRelatedList: function (params) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		if (typeof this.relatedModulename == "undefined" || this.relatedModulename.length <= 0) {
			var currentInstance = Vtiger_Detail_Js.getInstance();
			currentInstance.loadWidgets();
			return aDeferred.promise();
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var completeParams = this.getCompleteParams();
		var activeTabsReference = thisInstance.relatedTabsContainer.find('li.active').data('reference');
		/*
		 var activeTabsContainer = thisInstance.relatedTabsContainer.find('li.active').data('url');
		 if( activeTabsContainer != undefined){
		 completeParams = activeTabsContainer;
		 }else{
		 jQuery.extend(completeParams,params);
		 }*/
		jQuery.extend(completeParams, params);
		AppConnector.request(completeParams).then(
				function (responseData) {
					progressIndicatorElement.progressIndicator({
						'mode': 'hide'
					})
					var currentInstance = Vtiger_Detail_Js.getInstance();
					currentInstance.loadWidgets();
					if (activeTabsReference != 'ProductsAndServices') {
						thisInstance.relatedTabsContainer.find('li').removeClass('active');
						thisInstance.selectedRelatedTabElement.addClass('active');
						thisInstance.relatedContentContainer.html(responseData);
						responseData = thisInstance.relatedContentContainer.html();
						Vtiger_Helper_Js.showHorizontalTopScrollBar();
						jQuery('.pageNumbers', thisInstance.relatedContentContainer).tooltip();
						jQuery('body').trigger(jQuery.Event('LoadRelatedRecordList.PostLoad'), {response: responseData, params: completeParams});
						app.showBtnSwitch(jQuery('body').find('.switchBtn'));
						thisInstance.registerUnreviewedCountEvent();
						if (thisInstance.listSearchInstance) {
							thisInstance.listSearchInstance.registerBasicEvents();
						}
					}
					aDeferred.resolve(responseData);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);
		return aDeferred.promise();
	},
	triggerDisplayTypeEvent: function () {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if (widthType) {
			var elements = jQuery('.listViewEntriesTable').find('td,th');
			elements.attr('class', widthType);
		}
	},
	showSelectRelationPopup: function (extendParams) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var popupInstance = Vtiger_Popup_Js.getInstance();
		var mainParams = this.getPopupParams()
		$.extend(mainParams, extendParams);
		popupInstance.show(mainParams, function (responseString) {
			var responseData = JSON.parse(responseString);
			var relatedIdList = Object.keys(responseData);
			thisInstance.addRelations(relatedIdList).then(
					function (data) {
						var selectedTab = thisInstance.getSelectedTabElement();
						if (selectedTab.data('link-key') == 'LBL_RECORD_SUMMARY') {
							var detail = Vtiger_Detail_Js.getInstance();
							detail.loadWidgets();
						} else {
							var relatedCurrentPage = thisInstance.getCurrentPageNum();
							var params = {'page': relatedCurrentPage};
							thisInstance.loadRelatedList(params).then(function (data) {
								aDeferred.resolve(data);
							});
						}
					}
			);
		}
		);
		return aDeferred.promise();
	},
	addRelations: function (idList) {
		var aDeferred = jQuery.Deferred();
		var sourceRecordId = this.parentRecordId;
		var sourceModuleName = this.parentModuleName;
		var relatedModuleName = this.relatedModulename;

		var params = {};
		params['mode'] = "addRelation";
		params['module'] = sourceModuleName;
		params['action'] = 'RelationAjax';

		params['related_module'] = relatedModuleName;
		params['src_record'] = sourceRecordId;
		params['related_record_list'] = jQuery.isArray(idList) ? JSON.stringify(idList) : idList;

		AppConnector.request(params).then(
				function (responseData) {
					var detail = Vtiger_Detail_Js.getInstance();
					detail.registerRelatedModulesRecordCount();
					aDeferred.resolve(responseData);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);
		return aDeferred.promise();
	},
	getPopupParams: function () {
		var parameters = {};
		var parameters = {
			'module': this.relatedModulename,
			'src_module': this.parentModuleName,
			'src_record': this.parentRecordId,
			'multi_select': true
		}
		return parameters;
	},
	deleteRelation: function (relatedIdList) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = "deleteRelation";
		params['module'] = this.parentModuleName;
		params['action'] = 'RelationAjax';

		params['related_module'] = this.relatedModulename;
		params['src_record'] = this.parentRecordId;
		params['related_record_list'] = JSON.stringify(relatedIdList);

		AppConnector.request(params).then(
				function (responseData) {
					var detail = Vtiger_Detail_Js.getInstance();
					detail.registerRelatedModulesRecordCount();
					aDeferred.resolve(responseData);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);

		return aDeferred.promise();
	},
	getCurrentPageNum: function () {
		return jQuery('input[name="currentPageNum"]', this.relatedContentContainer).val();
	},
	setCurrentPageNumber: function (pageNumber) {
		jQuery('input[name="currentPageNum"]').val(pageNumber);
	},
	/**
	 * Function to get Order by
	 */
	getOrderBy: function () {
		return jQuery('#orderBy').val();
	},
	/**
	 * Function to get Sort Order
	 */
	getSortOrder: function () {
		return jQuery("#sortOrder").val();
	},
	getCompleteParams: function () {
		var params = {
			view: 'Detail',
			module: this.parentModuleName,
			record: this.getParentId(),
			relatedModule: this.relatedModulename,
			sortorder: this.getSortOrder(),
			orderby: this.getOrderBy(),
			page: this.getCurrentPageNum(),
			mode: 'showRelatedList'
		};
		if ($('.pagination').length) {
			params['totalCount'] = $('.pagination').data('totalCount');
		}
		if (this.relatedModulename == 'Calendar') {
			if (this.relatedContentContainer.find('.switchBtn').is(':checked'))
				params['time'] = 'current';
			else
				params['time'] = 'history';
		}

		if (this.listSearchInstance) {
			var searchValue = this.listSearchInstance.getAlphabetSearchValue();
			params.search_params = JSON.stringify(this.listSearchInstance.getListSearchParams());
		}
		if ((typeof searchValue != "undefined") && (searchValue.length > 0)) {
			params['search_key'] = this.listSearchInstance.getAlphabetSearchField();
			params['search_value'] = searchValue;
			params['operator'] = 's';
		}
		if (this.relatedModulename == 'Calendar') {
			var switchBtn = this.getRelatedContainer().find('.switchBtn');
			if (switchBtn.length) {
				params.time = switchBtn.prop('checked') ? 'current' : 'history';
			}
		}
		return params;
	},
	/**
	 * Function to handle Sort
	 */
	sortHandler: function (headerElement) {
		var aDeferred = jQuery.Deferred();
		var fieldName = headerElement.data('fieldname');
		var sortOrderVal = headerElement.data('nextsortorderval');
		if (typeof sortOrderVal === 'undefined')
			return;
		var sortingParams = {
			"orderby": fieldName,
			"sortorder": sortOrderVal,
			"tab_label": this.selectedRelatedTabElement.data('label-key')
		}
		this.loadRelatedList(sortingParams).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to handle next page navigation
	 */
	nextPageHandler: function () {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var pageLimit = jQuery('#pageLimit').val();
		var noOfEntries = jQuery('#noOfEntries').val();
		if (noOfEntries == pageLimit) {
			var pageNumber = this.getCurrentPageNum();
			var nextPage = parseInt(pageNumber) + 1;
			var nextPageParams = {
				'page': nextPage
			}
			this.loadRelatedList(nextPageParams).then(
					function (data) {
						thisInstance.setCurrentPageNumber(nextPage);
						aDeferred.resolve(data);
					},
					function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					}
			);
		}
		return aDeferred.promise();
	},
	/**
	 * Function to handle next page navigation
	 */
	previousPageHandler: function () {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var pageNumber = this.getCurrentPageNum();
		if (pageNumber > 1) {
			var previousPage = parseInt(pageNumber) - 1;
			var previousPageParams = {
				'page': previousPage
			}
			this.loadRelatedList(previousPageParams).then(
					function (data) {
						thisInstance.setCurrentPageNumber(previousPage);
						aDeferred.resolve(data);
					},
					function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					}
			);
		}
		return aDeferred.promise();
	},
	/**
	 * Function to handle select page jump in related list
	 */
	selectPageHandler: function (pageNumber) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var selectPage = {
			'page': pageNumber,
		}
		/**
		 * Added a condition, because there's a switch button with
		 * the option used in the list in the related calendar module
		 */
		if (this.relatedModulename == 'Calendar') {
			var time = jQuery('.switchBtn').is(':checked')
			if (time)
				selectPage['time'] = 'current';
			else
				selectPage['time'] = 'history';
		}
		this.loadRelatedList(selectPage).then(
				function (data) {
					thisInstance.setCurrentPageNumber(pageNumber);
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);

		return aDeferred.promise();
	},
	/**
	 * Function to handle page jump in related list
	 */
	pageJumpHandler: function (e) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		if (e.which == 13) {
			var element = jQuery(e.currentTarget);
			var response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
			if (typeof response != "undefined") {
				element.validationEngine('showPrompt', response, '', "topLeft", true);
				e.preventDefault();
			} else {
				element.validationEngine('hideAll');
				var jumpToPage = parseInt(element.val());
				var totalPages = parseInt(jQuery('#totalPageCount').text());
				if (jumpToPage > totalPages) {
					var error = app.vtranslate('JS_PAGE_NOT_EXIST');
					element.validationEngine('showPrompt', error, '', "topLeft", true);
				}
				var invalidFields = element.parent().find('.formError');
				if (invalidFields.length < 1) {
					var currentPage = jQuery('input[name="currentPageNum"]').val();
					if (jumpToPage == currentPage) {
						var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + " " + jumpToPage;
						var params = {
							text: message,
							type: 'info'
						};
						Vtiger_Helper_Js.showMessage(params);
						e.preventDefault();
						return false;
					}
					var jumptoPageParams = {
						'page': jumpToPage
					}
					if (this.relatedModulename == 'Calendar') {
						var time = jQuery('.switchBtn').is(':checked')
						if (time)
							jumptoPageParams['time'] = 'current';
						else
							jumptoPageParams['time'] = 'history';
					}
					this.loadRelatedList(jumptoPageParams).then(
							function (data) {
								thisInstance.setCurrentPageNumber(jumpToPage);
								aDeferred.resolve(data);
							},
							function (textStatus, errorThrown) {
								aDeferred.reject(textStatus, errorThrown);
							}
					);
				} else {
					e.preventDefault();
				}
			}
		}
		return aDeferred.promise();
	},
	/**
	 * Function to add related record for the module
	 */
	addRelatedRecord: function (element, callback) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var referenceModuleName = this.relatedModulename;
		var parentId = this.getParentId();
		var parentModule = this.parentModuleName;
		var quickCreateParams = {};
		var relatedParams = {};
		var relatedField = element.data('name');
		var fullFormUrl = element.data('url');
		relatedParams[relatedField] = parentId;
		var eliminatedKeys = new Array('view', 'module', 'mode', 'action');

		var preQuickCreateSave = function (data) {
			var index, queryParam, queryParamComponents;

			//To handle switch to task tab when click on add task from related list of activities
			//As this is leading to events tab intially even clicked on add task
			if (typeof fullFormUrl != 'undefined' && fullFormUrl.indexOf('?') !== -1) {
				var urlSplit = fullFormUrl.split('?');
				var queryString = urlSplit[1];
				var queryParameters = queryString.split('&');
				for (index = 0; index < queryParameters.length; index++) {
					queryParam = queryParameters[index];
					queryParamComponents = queryParam.split('=');
					if (queryParamComponents[0] == 'mode' && queryParamComponents[1] == 'Calendar') {
						data.find('a[data-tab-name="Task"]').trigger('click');
					}
				}
			}
			jQuery('<input type="hidden" name="sourceModule" value="' + parentModule + '" />').appendTo(data);
			jQuery('<input type="hidden" name="sourceRecord" value="' + parentId + '" />').appendTo(data);
			jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);

			if (typeof relatedField != "undefined") {
				var field = data.find('[name="' + relatedField + '"]');
				//If their is no element with the relatedField name,we are adding hidden element with
				//name as relatedField name,for saving of record with relation to parent record
				if (field.length == 0) {
					jQuery('<input type="hidden" name="' + relatedField + '" value="' + parentId + '" />').appendTo(data);
				}
			}
			for (index = 0; index < queryParameters.length; index++) {
				queryParam = queryParameters[index];
				queryParamComponents = queryParam.split('=');
				if (jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1' && data.find('[name="' + queryParamComponents[0] + '"]').length == 0) {
					jQuery('<input type="hidden" name="' + queryParamComponents[0] + '" value="' + queryParamComponents[1] + '" />').appendTo(data);
				}
			}
			if (typeof callback !== 'undefined') {
				callback();
			}
		}
		var postQuickCreateSave = function (data) {
			thisInstance.loadRelatedList().then(
					function (data) {
						aDeferred.resolve(data);
					})
		}

		//If url contains params then seperate them and make them as relatedParams
		if (typeof fullFormUrl != 'undefined' && fullFormUrl.indexOf('?') !== -1) {
			var urlSplit = fullFormUrl.split('?');
			var queryString = urlSplit[1];
			var queryParameters = queryString.split('&');
			for (var index = 0; index < queryParameters.length; index++) {
				var queryParam = queryParameters[index];
				var queryParamComponents = queryParam.split('=');
				if (jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1') {
					relatedParams[queryParamComponents[0]] = queryParamComponents[1];
				}
			}
		}

		quickCreateParams['data'] = relatedParams;
		quickCreateParams['callbackFunction'] = postQuickCreateSave;
		quickCreateParams['callbackPostShown'] = preQuickCreateSave;
		quickCreateParams['noCache'] = true;
		Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, quickCreateParams);
		return aDeferred.promise();
	},
	getRelatedPageCount: function () {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['action'] = "RelationAjax";
		params['module'] = this.parentModuleName;
		params['record'] = this.getParentId();
		params['relatedModule'] = this.relatedModulename;
		params['tab_label'] = this.selectedRelatedTabElement.data('label-key');
		params['mode'] = "getRelatedListPageCount";

		var element = jQuery('#totalPageCount');
		var totalCountElem = jQuery('#totalCount');
		var totalPageNumber = element.text();
		if (totalPageNumber == "") {
			element.progressIndicator({});
			AppConnector.request(params).then(
					function (data) {
						var pageCount = data['result']['page'];
						var numberOfRecords = data['result']['numberOfRecords'];
						totalCountElem.val(numberOfRecords);
						element.text(pageCount);
						element.progressIndicator({'mode': 'hide'});
						aDeferred.resolve();
					},
					function (error, err) {

					}
			);
		} else {
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	favoritesRelation: function (relcrmId, state) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['action'] = "RelationAjax";
		params['module'] = this.parentModuleName;
		params['record'] = this.getParentId();
		params['relcrmid'] = relcrmId;
		params['relatedModule'] = this.relatedModulename;
		params['mode'] = "updateFavoriteForRecord";
		params['actionMode'] = state ? 'delete' : 'add';

		if (relcrmId) {
			AppConnector.request(params).then(
					function (data) {
						if (data.result)
							aDeferred.resolve(true);
					},
					function (error, err) {
					}
			);
		} else {
			aDeferred.reject(false);
		}
		return aDeferred.promise();
	},
	registerUnreviewedCountEvent: function (container) {
		var thisInstance = this;
		var ids = [];
		var listViewRelatedContentDiv = container == undefined ? this.relatedContentContainer : container;
		var isUnreviewedActive = listViewRelatedContentDiv.find('.unreviewed').length;
		listViewRelatedContentDiv.find('tr.listViewEntries').each(function () {
			var id = jQuery(this).data('id');
			if (id) {
				ids.push(id);
			}
		});
		if (!ids || isUnreviewedActive < 1) {
			return;
		}
		var actionParams = {
			action: 'ChangesReviewedOn',
			mode: 'getUnreviewed',
			module: 'ModTracker',
			sourceModule: this.relatedModulename,
			recordsId: ids
		};
		AppConnector.request(actionParams).then(function (appData) {
			var data = appData.result;
			$.each(data, function (id, value) {
				if (value.a > 0) {
					listViewRelatedContentDiv.find('tr[data-id="' + id + '"] .unreviewed .badge.all').text(value.a);
				}
				if (value.m > 0) {
					listViewRelatedContentDiv.find('tr[data-id="' + id + '"] .unreviewed .badge.mail').text(value.m);
				}
			});
		});
	},
	init: function (parentId, parentModule, selectedRelatedTabElement, relatedModuleName) {
		this.selectedRelatedTabElement = selectedRelatedTabElement;
		this.parentRecordId = parentId;
		this.parentModuleName = parentModule;
		this.relatedModulename = relatedModuleName;
		this.relatedTabsContainer = selectedRelatedTabElement.closest('div.related');
		this.detailViewContainer = this.relatedTabsContainer.closest('div.detailViewContainer');
		this.relatedContentContainer = jQuery('div.contents', this.detailViewContainer);
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		app.showPopoverElementView(this.relatedContentContainer.find('.popoverTooltip'));
		this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(this.relatedContentContainer);
	}
})
