/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'Partners_Detail_Js',
	{},
	{
		hierarchyResponseCache: {},
		/**
		 * Get response data
		 * @param {Object} params
		 * @returns {Object}
		 */
		getHierarchyResponseData: function (params) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();
			if (!jQuery.isEmptyObject(thisInstance.hierarchyResponseCache)) {
				aDeferred.resolve(thisInstance.hierarchyResponseCache);
			} else {
				AppConnector.request(params).done(function (data) {
					thisInstance.hierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.hierarchyResponseCache);
				});
			}
			return aDeferred.promise();
		},
		/**
		 * Display hierarchy count
		 */
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
		/**
		 * Display hierarchy
		 */
		registerShowHierarchy: function () {
			var thisInstance = this;
			var hierarchyButton = $('.detailViewTitle');
			var params = {
				module: app.getModuleName(),
				view: 'Hierarchy',
				record: app.getRecordId()
			};
			hierarchyButton.on('click', '.js-detail__icon', function (e) {
				thisInstance.getHierarchyResponseData(params).done(function (data) {
					app.showModalWindow(data);
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
