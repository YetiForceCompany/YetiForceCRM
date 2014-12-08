/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

Inventory_Detail_Js("Calculations_Detail_Js",{
	HierarchyResponseCache : {},
	triggerHierarchy : function(HierarchyUrl) {
		Calculations_Detail_Js.getHierarchyResponseData(HierarchyUrl).then(
			function(data) {
				Calculations_Detail_Js.displayHierarchyResponseData(data);
			}
		);
	},
	getHierarchyResponseData : function(params) {
		var aDeferred = jQuery.Deferred();
		if(!(jQuery.isEmptyObject(Calculations_Detail_Js.HierarchyResponseCache))) {
			aDeferred.resolve(Calculations_Detail_Js.HierarchyResponseCache);
		} else {
			AppConnector.request(params).then(
				function(data) {
					Calculations_Detail_Js.HierarchyResponseCache = data;
					aDeferred.resolve(Calculations_Detail_Js.HierarchyResponseCache);
				}
			);
		}
		return aDeferred.promise();
	},
	displayHierarchyResponseData : function(data) {
        var callbackFunction = function(data) {
            app.showScrollBar(jQuery('#hierarchyScroll'), {
                height: '200px',
                railVisible: true,
                alwaysVisible: true,
                size: '6px'
            });
        }
        app.showModalWindow(data, function(data){
            if(typeof callbackFunction == 'function'){
                callbackFunction(data);
            }
        });
	}
},{});