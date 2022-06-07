/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_Detail_Js(
	'Accounts_Detail_Js',
	{},
	{
		//It stores the Account Hierarchy response data
		accountHierarchyResponseCache: {},
		/**
		 * function to get the AccountHierarchy response data
		 * @returns {Promise}
		 */
		getAccountHierarchyResponseData: function (params) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();

			//Check in the cache
			if (!jQuery.isEmptyObject(thisInstance.accountHierarchyResponseCache)) {
				aDeferred.resolve(thisInstance.accountHierarchyResponseCache);
			} else {
				AppConnector.request(params).done(function (data) {
					//store it in the cache, so that we dont do multiple request
					thisInstance.accountHierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.accountHierarchyResponseCache);
				});
			}
			return aDeferred.promise();
		},

		/*
		 * function to display the AccountHierarchy response data
		 */
		displayAccountHierarchyResponseData: function (data) {
			let callbackFunction = function () {
				app.showScrollBar($('#hierarchyScroll'), {
					height: '300px',
					railVisible: true,
					size: '6px'
				});
			};
			app.showModalWindow(data, function (modalContainer) {
				App.Components.Scrollbar.xy($('#hierarchyScroll', modalContainer));
				if (typeof callbackFunction == 'function' && $('#hierarchyScroll', modalContainer).height() > 300) {
					callbackFunction();
				}
			});
		},

		registerHierarchyRecordCount: function () {
			var hierarchyButton = $('.detailViewTitle .hierarchy');
			if (hierarchyButton.length) {
				var params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					mode: 'getHierarchyCount'
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
			var url = 'index.php?module=Accounts&view=AccountHierarchy&record=' + app.getRecordId();
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
	}
);
