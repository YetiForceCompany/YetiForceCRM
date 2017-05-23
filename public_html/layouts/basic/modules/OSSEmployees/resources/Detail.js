/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */

Vtiger_Detail_Js("OSSEmployees_Detail_Js", {
	employeeHierarchyResponseCache: {},
	triggerEmployeeHierarchy: function (HierarchyUrl) {
		OSSEmployees_Detail_Js.getEmployeeHierarchyResponseData(HierarchyUrl).then(
				function (data) {
					app.showModalWindow(data);
				}
		);
	},
	getEmployeeHierarchyResponseData: function (params) {
		var aDeferred = jQuery.Deferred();
		if (!(jQuery.isEmptyObject(OSSEmployees_Detail_Js.employeeHierarchyResponseCache))) {
			aDeferred.resolve(OSSEmployees_Detail_Js.employeeHierarchyResponseCache);
		} else {
			AppConnector.request(params).then(
					function (data) {
						OSSEmployees_Detail_Js.employeeHierarchyResponseCache = data;
						aDeferred.resolve(OSSEmployees_Detail_Js.employeeHierarchyResponseCache);
					}
			);
		}
		return aDeferred.promise();
	}
}, {
	registerEvents: function () {
		this._super();
		this.registerHoliday();
	}
});
