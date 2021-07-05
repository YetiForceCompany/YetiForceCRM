/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'OSSEmployees_Detail_Js',
	{
		employeeHierarchyResponseCache: {},
		triggerEmployeeHierarchy: function (HierarchyUrl) {
			OSSEmployees_Detail_Js.getEmployeeHierarchyResponseData(HierarchyUrl).done(function (data) {
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
			});
		},
		getEmployeeHierarchyResponseData: function (params) {
			var aDeferred = jQuery.Deferred();
			if (!jQuery.isEmptyObject(OSSEmployees_Detail_Js.employeeHierarchyResponseCache)) {
				aDeferred.resolve(OSSEmployees_Detail_Js.employeeHierarchyResponseCache);
			} else {
				AppConnector.request(params)
					.done(function (data) {
						OSSEmployees_Detail_Js.employeeHierarchyResponseCache = data;
						aDeferred.resolve(OSSEmployees_Detail_Js.employeeHierarchyResponseCache);
					})
					.fail(function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					});
			}
			return aDeferred.promise();
		}
	},
	{
		registerEvents: function () {
			this._super();
		}
	}
);
