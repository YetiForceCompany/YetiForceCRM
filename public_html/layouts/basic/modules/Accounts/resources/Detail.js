/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_Detail_Js("Accounts_Detail_Js", {}, {
	//It stores the Account Hierarchy response data
	accountHierarchyResponseCache: {},
	/*
	 * function to get the AccountHierarchy response data
	 */
	getAccountHierarchyResponseData: function (params) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		//Check in the cache
		if (!(jQuery.isEmptyObject(thisInstance.accountHierarchyResponseCache))) {
			aDeferred.resolve(thisInstance.accountHierarchyResponseCache);
		} else {
			AppConnector.request(params).done(
				function (data) {
					//store it in the cache, so that we dont do multiple request
					thisInstance.accountHierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.accountHierarchyResponseCache);
				}
			);
		}
		return aDeferred.promise();
	},
	registerButtons: function (container) {
		container.find('.toChangeBtn').on('click', function (e) {
			var currentTarget = $(e.currentTarget);
			var fieldname = currentTarget.data('fieldname');
			var params = {
				value: currentTarget.hasClass('btn-success') ? 0 : 1,
				field: fieldname,
				record: currentTarget.data('recordId'),
				module: app.getModuleName(),
				action: 'SaveAjax'
			};
			AppConnector.request(params).done(
				function (data) {
					if (currentTarget.hasClass('btn-warning')) {
						currentTarget.removeClass('btn-warning');
						currentTarget.addClass('btn-success');
					} else {
						currentTarget.addClass('btn-warning');
						currentTarget.removeClass('btn-success');
					}
					currentTarget.html(data.result[fieldname].display_value);
					var params = {
						title: app.vtranslate('JS_LBL_PERMISSION'),
						text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
						type: 'success',
					};
					Vtiger_Helper_Js.showMessage(params);
				}
			);
		});
	},
	/*
	 * function to display the AccountHierarchy response data
	 */
	displayAccountHierarchyResponseData: function (data) {
		var thisInstance = this;
		app.showModalWindow(data, function (container) {
			var bodyModal = container.find('.maxHeightModal');
			app.showScrollBar(bodyModal, {
				height: bodyModal.height,
				railVisible: true,
				size: '6px'
			});
			thisInstance.registerButtons(container);
		});
	},
	registerHierarchyRecordCount: function () {
		var hierarchyButton = $('.detailViewTitle .hierarchy');
		if (hierarchyButton.length) {
			var params = {
				module: app.getModuleName(),
				action: 'RelationAjax',
				record: app.getRecordId(),
				mode: 'getHierarchyCount',
			};
			AppConnector.request(params).done(function (response) {
				if (response.success) {
					$('.detailViewTitle .hierarchy .badge').html(response.result);
				}
			});
		}
	},
	registerShowHierarchy: function () {
		var thisInstance = this;
		var hierarchyButton = $('.detailViewTitle');
		var url = "index.php?module=Accounts&view=AccountHierarchy&record=" + app.getRecordId();
		hierarchyButton.on('click', '.js-detail__icon, .recordLabelValue', function (e) {
			thisInstance.getAccountHierarchyResponseData(url).done(function (data) {
				thisInstance.displayAccountHierarchyResponseData(data);
			});
		});
	},
	registerEvents: function () {
		this._super();
		this.registerHierarchyRecordCount();
		this.registerShowHierarchy();
	}
});
