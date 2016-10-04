/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

Vtiger_Detail_Js("OSSEmployees_Detail_Js",{
	
	employeeHierarchyResponseCache : {},
	triggerEmployeeHierarchy : function(HierarchyUrl) {
		OSSEmployees_Detail_Js.getEmployeeHierarchyResponseData(HierarchyUrl).then(
			function(data) {
				OSSEmployees_Detail_Js.displayEmployeeHierarchyResponseData(data);
			}
		);
	},
	getEmployeeHierarchyResponseData : function(params) {
		var aDeferred = jQuery.Deferred();
		if(!(jQuery.isEmptyObject(OSSEmployees_Detail_Js.employeeHierarchyResponseCache))) {
			aDeferred.resolve(OSSEmployees_Detail_Js.employeeHierarchyResponseCache);
		} else {
			AppConnector.request(params).then(
				function(data) {
					OSSEmployees_Detail_Js.employeeHierarchyResponseCache = data;
					aDeferred.resolve(OSSEmployees_Detail_Js.employeeHierarchyResponseCache);
				}
			);
		}
		return aDeferred.promise();
	},
	displayEmployeeHierarchyResponseData : function(data) {
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
},{

	registerHoliday : function(sum_time) {
		var thisInstance = this;
		jQuery('select[name="year"]').change(function(){
			var year = jQuery(this).val();
			var id = jQuery('input#recordId').val();
			 var params = {}
            params.data = {module: 'OSSEmployees', action: 'GetHoliday', year: year, id: id}
            params.async = false;
            params.dataType = 'json';
            AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if ( response['success'] ) {    
                        var holiday = response.holiday;
						jQuery('span#workDay').html('<strong>'+holiday.workDay+'</strong>');
						jQuery('span#annual_holiday_entitlement').html('<strong>'+holiday.entitlement+'</strong>');
					}
                    else {
                        var params = {
                            text: app.vtranslate('message'),
                            animation: 'show',
                            type: 'error',
                            sticker: false,
                            hover_sticker: false,
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data,err){
					var parametry = {
					text: app.vtranslate('JS_ERROR_CONNECTING'),
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				}
			);
			
		});
	},
	
	/*
	registerRecordPreSaveEvent : function(form){
		var thisInstance = this;
		var userId = jQuery('[name="assigned_user_id"]').val();
		var end = true;
		var filter = /\D/;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}
		

		
		form.on(this.fieldPreSave, function(e, data) {
		//jQuery('[name="assigned_user_id"]').change( function() {
			var params = {}
			var userIdNew = jQuery('[name="assigned_user_id"]').val();
			console.log(userIdNew);
			if(end){
            params.data = {module: 'OSSEmployees', action: 'UniqueUser', userId: userIdNew}
            params.async = false;
            params.dataType = 'json';
            AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if ( response['success'] ) {  
						end = false;
                        return true;
					}
                    else {
                        var params = {
                            text: response.message,
                            animation: 'show',
                            type: 'error',
                            sticker: false,
                            hover_sticker: false,
                        };
                        Vtiger_Helper_Js.showPnotify(params);
						end = true;
						jQuery('[name="assigned_user_id"]').val(userId).trigger('chosen:updated');
						return false;
						
                    }
                },
                function(data,err){
					var parametry = {
					text: app.vtranslate('JS_ERROR_CONNECTING'),
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				}
			);
			if( !end )
				return false;
			}

		});
		//	});
	},
	*/
	registerEvents: function(){
			this._super();
			this.registerHoliday();
		//	this.registerRecordPreSaveEvent();
		}





});