/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

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
	getCompleteParams: function () {
		var params = this._super();
		var container = this.getRelatedContainer();
		params['selectedIds'] = container.find('#selectedIds').data('selectedIds');
		params['excludedIds'] = container.find('#excludedIds').data('excludedIds');
		return params;
	},
	changeCustomFilterElementView: function () {
		var filterSelectElement = this.content.find('#recordsFilter');
		if (filterSelectElement.length > 0) {
			App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
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
			select2Instance.$dropdown.append(this.content.find('span.filterActionsDiv'));
		}
	},
	registerChangeCustomFilterEvent: function () {
		var thisInstance = this;
		var relatedContainer = thisInstance.getRelatedContainer();
		var filterSelectElement = relatedContainer.find('.loadFormFilterButton');
		var recordsFilter = relatedContainer.find('#recordsFilter');
		filterSelectElement.on('click', function (e) {
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_ADD_THIS_FILTER');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function () {
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
				AppConnector.request(params).done(function (responseData) {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					if (responseData.result === false) {
						var message = app.vtranslate('JS_NO_RECORDS_RELATED_TO_THIS_FILTER');
						var params = {
							text: message,
							type: 'info'
						};
						Vtiger_Helper_Js.showMessage(params);
					} else {
						Vtiger_Detail_Js.reloadRelatedList();
					}
				}).fail(function () {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
				});
			});
		});
	},
	/**
	 * Function to edit related status for email enabled modules of campaigns
	 */
	registerEventToEditRelatedStatus: function () {
		var thisInstance = this;
		var relatedContainer = thisInstance.getRelatedContainer();
		relatedContainer.find('.currentStatus').on('click', function (e) {
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			element.addClass('open');
		});
		var statusDropdown = relatedContainer.find('.currentStatus').find('.dropdown-menu');
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
			element.progressIndicator();
			AppConnector.request(params).done(function (responseData) {
				if (responseData.result[0]) {
					element.progressIndicator({'mode': 'hide'});
					currentStatus.find('.statusValue').text(selectedStatusValue);
					currentStatus.removeClass('open');
				}
			}).fail(function () {
				element.progressIndicator({mode: 'hide'});
			});
		});
	},
	registerPostLoadEvents: function () {
		this._super();
		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerEventToEditRelatedStatus();
	}
});
