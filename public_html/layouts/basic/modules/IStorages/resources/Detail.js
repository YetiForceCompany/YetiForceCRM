/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js("IStorages_Detail_Js", {}, {
	//It stores the IStorages Hierarchy response data
	hierarchyResponseCache: {},
	/*
	 * function to get the IStoragesHierarchy response data
	 */
	getHierarchyResponseData: function (params) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		//Check in the cache
		if (!(jQuery.isEmptyObject(thisInstance.hierarchyResponseCache))) {
			aDeferred.resolve(thisInstance.hierarchyResponseCache);
		} else {
			AppConnector.request(params).done(function (data) {
				//store it in the cache, so that we dont do multiple request
				thisInstance.hierarchyResponseCache = data;
				aDeferred.resolve(thisInstance.hierarchyResponseCache);
			});
		}
		return aDeferred.promise();
	},
	/*
	 * function to display the IStorages Hierarchy response data
	 */
	displayHierarchyResponseData: function (data) {
		var callbackFunction = function (data) {
			app.showScrollBar(jQuery('#hierarchyScroll'), {
				height: '300px',
				railVisible: true,
				size: '6px'
			});
		};
		app.showModalWindow(data, function (data) {
			if (typeof callbackFunction == 'function' && jQuery('#hierarchyScroll').height() > 300) {
				callbackFunction(data);
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
		var url = "index.php?module=IStorages&view=Hierarchy&record=" + app.getRecordId();
		hierarchyButton.on('click', '.js-detail__icon', function (e) {
			thisInstance.getHierarchyResponseData(url).done(function (data) {
				thisInstance.displayHierarchyResponseData(data);
			});
		});
	},
	registerEvents: function () {
		this._super();
		this.registerHierarchyRecordCount();
		this.registerShowHierarchy();
	}
});
