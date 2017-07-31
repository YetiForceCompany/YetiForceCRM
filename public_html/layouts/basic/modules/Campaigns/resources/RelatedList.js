/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_RelatedList_Js("Campaigns_RelatedList_Js", {
	/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail: function () {
		Vtiger_List_Js.triggerSendEmail({
			relatedLoad: true,
			sourceModule: app.getModuleName(),
			sourceRecord: app.getRecordId(),
			module: jQuery('.relatedModuleName').val(),
			cvid: jQuery('#recordsFilter').val(),
		});
	}
}, {
	loadRelatedList: function (params) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		this._super(params).then(function (data) {
			thisInstance.registerEvents();
			var moduleName = app.getModuleName();
			var className = moduleName + "_List_Js";
			var listInstance = new window[className]();
			listInstance.registerEvents();
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	getCompleteParams: function () {
		var params = {};
		params['view'] = "Detail";
		params['module'] = this.parentModuleName;
		params['record'] = this.getParentId();
		params['relatedModule'] = this.relatedModulename;
		params['sortorder'] = this.getSortOrder();
		params['orderby'] = this.getOrderBy();
		params['page'] = this.getCurrentPageNum();
		params['mode'] = "showRelatedList";
		params['selectedIds'] = jQuery('#selectedIds').data('selectedIds');
		params['excludedIds'] = jQuery('#excludedIds').data('excludedIds');

		if (this.listSearchInstance) {
			var searchValue = this.listSearchInstance.getAlphabetSearchValue();
			params.search_params = JSON.stringify(this.listSearchInstance.getListSearchParams());
		}
		if ((typeof searchValue != "undefined") && (searchValue.length > 0)) {
			params['search_key'] = this.listSearchInstance.getAlphabetSearchField();
			params['search_value'] = searchValue;
			params['operator'] = 's';
		}
		return params;
	},
	changeCustomFilterElementView: function () {
		var filterSelectElement = jQuery('#recordsFilter');
		if (filterSelectElement.length > 0) {
			app.showSelect2ElementView(filterSelectElement, {
				templateSelection: function (data) {
					var resultContainer = jQuery('<span></span>');
					resultContainer.append(jQuery(jQuery('.filterImage').detach().get(0)).show());
					resultContainer.append(data.text);
					return resultContainer;
				},
				customSortOptGroup: true,
				closeOnSelect: true
			});
			var select2Instance = filterSelectElement.data('select2');
			select2Instance.$dropdown.append(jQuery('span.filterActionsDiv'));
		}
	},
	/**
	 * Function to register change event for custom filter
	 */

	registerChangeCustomFilterEvent: function () {
		var thisInstance = this;
		var relatedContainer = thisInstance.getRelatedContainer();
		var filterSelectElement = relatedContainer.find('.loadFormFilterButton');
		var recordsFilter = relatedContainer.find('#recordsFilter');
		filterSelectElement.click(function (e) {
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_ADD_THIS_FILTER');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function () {
						var cvId = recordsFilter.val();
						var relatedModuleName = relatedContainer.find('.relatedModuleName').val();
						var params = {
							sourceRecord: app.getRecordId(),
							relatedModule: relatedModuleName,
							viewId: cvId,
							module: app.getModuleName(),
							action: "RelationAjax",
							mode: 'addRelationsFromRelatedModuleViewId'
						};

						var progressIndicatorElement = jQuery.progressIndicator({
							position: 'html',
							blockInfo: {
								enabled: true
							}
						});
						AppConnector.request(params).then(
								function (responseData) {
									progressIndicatorElement.progressIndicator({
										'mode': 'hide'
									});
									if (responseData != null) {
										var message = app.vtranslate('JS_NO_RECORDS_RELATED_TO_THIS_FILTER');
										var params = {
											text: message,
											type: 'info'
										};
										Vtiger_Helper_Js.showMessage(params);
									} else {
										Vtiger_Detail_Js.reloadRelatedList();
									}
								},
								function (textStatus, errorThrown) {
								}
						);
					},
					function (error, err) {
					}
			);
		});
	},
	/**
	 * Function to edit related status for email enabled modules of campaigns
	 */
	registerEventToEditRelatedStatus: function () {
		var thisInstance = this;
		jQuery('.currentStatus').on('click', function (e) {
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			element.addClass('open');
		});
		var statusDropdown = jQuery('.currentStatus').find('.dropdown-menu');
		statusDropdown.on('click', 'a', function (e) {
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var liContainer = element.closest('li');
			var currentStatus = element.closest('.currentStatus');
			var selectedStatusId = liContainer.attr('id');
			var selectedStatusValue = liContainer.data('status');
			var relatedRecordId = element.closest('tr').data('id');
			var params = {
				'relatedModule': thisInstance.relatedModulename,
				'relatedRecord': relatedRecordId,
				'status': selectedStatusId,
				'module': app.getModuleName(),
				'action': 'RelationAjax',
				'sourceRecord': thisInstance.parentRecordId,
				'mode': 'updateStatus'
			};
			element.progressIndicator({});
			AppConnector.request(params).then(
					function (responseData) {
						if (responseData.result[0]) {
							element.progressIndicator({'mode': 'hide'});
							currentStatus.find('.statusValue').text(selectedStatusValue);
							currentStatus.removeClass('open');
						}
					},
					function (textStatus, errorThrown) {
					}
			);
		});
	},
	registerEvents: function () {
		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerEventToEditRelatedStatus();
	}
});
