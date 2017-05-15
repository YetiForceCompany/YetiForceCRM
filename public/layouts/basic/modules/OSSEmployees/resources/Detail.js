/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

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
