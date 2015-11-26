/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Accounts_Detail_Js",{
	
	//It stores the Account Hierarchy response data
	accountHierarchyResponseCache : {},
	
	/*
	 * function to trigger Account Hierarchy action
	 * @param: Account Hierarchy Url.
	 */
	triggerAccountHierarchy : function(accountHierarchyUrl) {
		Accounts_Detail_Js.getAccountHierarchyResponseData(accountHierarchyUrl).then(
			function(data) {
				Accounts_Detail_Js.displayAccountHierarchyResponseData(data);
			}
		);
		
	},
	
	/*
	 * function to get the AccountHierarchy response data
	 */
	getAccountHierarchyResponseData : function(params) {
		var aDeferred = jQuery.Deferred();
		
		//Check in the cache
		if(!(jQuery.isEmptyObject(Accounts_Detail_Js.accountHierarchyResponseCache))) {
			aDeferred.resolve(Accounts_Detail_Js.accountHierarchyResponseCache);
		} else {
			AppConnector.request(params).then(
				function(data) {
					//store it in the cache, so that we dont do multiple request
					Accounts_Detail_Js.accountHierarchyResponseCache = data;
					aDeferred.resolve(Accounts_Detail_Js.accountHierarchyResponseCache);
				}
			);
		}
		return aDeferred.promise();
	},
	
	/*
	 * function to display the AccountHierarchy response data
	 */
	displayAccountHierarchyResponseData : function(data) {
        var callbackFunction = function(data) {
            app.showScrollBar(jQuery('#hierarchyScroll'), {
                height: '300px',
                railVisible: true,
                size: '6px'
            });
        }
        app.showModalWindow(data, function(data){
            
            if(typeof callbackFunction == 'function' && jQuery('#hierarchyScroll').height() > 300){
                callbackFunction(data);
            }
        });
        }
},{
	getDeleteMessageKey : function() {
		return 'LBL_RELATED_RECORD_DELETE_CONFIRMATION';
	},
	
	/**
	 * Number of records in hierarchy
	 * @license licenses/License.html
	 * @package YetiForce.Detail
	 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
	 */
	registerHierarchyRecordCount: function () {
		var hierarchyButton = $('.detailViewToolbar .hierarchy');
		if(hierarchyButton.length){
			var thisInstance = new Vtiger_Detail_Js();
			var params = {
				module: app.getModuleName(),
				action: 'RelationAjax',
				record: thisInstance.getRecordId(),
				mode: 'getHierarchyCount',
			}
			AppConnector.request(params).then(function (response) {
				if (response.success) {
					$('.detailViewToolbar .hierarchy').append(' <span class="badge">' + response.result + '</span>');
				}
			});
		}
	},
	registerEvents: function () {
		this._super();
		this.registerHierarchyRecordCount();
	}
});
