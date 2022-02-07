/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_Detail_Js(
	'Competition_Detail_Js',
	{},
	{
		hierarchyResponseCache: {},
		getHierarchyResponseData: function (params) {
			let thisInstance = this,
				aDeferred = jQuery.Deferred();

			if (!$.isEmptyObject(thisInstance.hierarchyResponseCache)) {
				aDeferred.resolve(thisInstance.hierarchyResponseCache);
			} else {
				AppConnector.request(params).then(function (data) {
					thisInstance.hierarchyResponseCache = data;
					aDeferred.resolve(thisInstance.hierarchyResponseCache);
				});
			}
			return aDeferred.promise();
		},
		/*
		 * function to display the hierarchy response data
		 */
		displayHierarchyResponseData: function (data) {
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
		/**
		 * Registers read count of hierarchy if it is possoble
		 */
		registerHierarchyRecordCount: function () {
			let hierarchyButton = $('.js-detail-hierarchy'),
				params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					mode: 'getHierarchyCount'
				};
			if (hierarchyButton.length) {
				AppConnector.request(params).then(function (response) {
					if (response.success) {
						$('.hierarchy .badge').html(response.result);
					}
				});
			}
		},
		registerShowHierarchy: function () {
			let thisInstance = this,
				hierarchyButton = $('.detailViewTitle'),
				params = {
					module: app.getModuleName(),
					view: 'Hierarchy',
					record: app.getRecordId()
				};
			hierarchyButton.on('click', '.js-detail-hierarchy', function () {
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				thisInstance.getHierarchyResponseData(params).then(function (data) {
					thisInstance.displayHierarchyResponseData(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
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
