/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Vtiger_Mobile_Js",{
    self: false,
    getInstance: function() {
        if (this.self != false) {
            return this.self;
        }
        this.self = new Vtiger_Mobile_Js();
        return this.self;
    },
	registerOutboundCall : function( phoneNumber, record ) {
		if(phoneNumber != undefined){
			Vtiger_Mobile_Js.performCall( phoneNumber, record )
		}
	},
	registerOutboundCallToUser : function( elmnt, phoneNumber, record ) {
		$( elmnt ).popover('toggle');
		$('.popoverCallOK').click(function(e) {
			var currentTdElement = jQuery(e.currentTarget);
			var user = currentTdElement.closest('.popover-content').find('.sesectedUser').val();
			Vtiger_Mobile_Js.performCall( phoneNumber, record , user)
			$( elmnt ).popover('hide');
		});
		$('.popoverCallCancel').click(function() {
			$( elmnt ).popover('hide');
		});
	},
	performCall : function( phoneNumber, record , user) {
		var params = {
		'module'	: 'Vtiger',
		'action'	: "Mobile",
		'mode'		: 'performCall',
		'record'	: record,
		'phoneNumber' : phoneNumber
		}
		if(user != undefined){
			params.user = user;
		}
        AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var params = {
					text: app.vtranslate('JS_MOBILE_PERFORM_CALL_OK'),
					animation: 'show',
					type: 'info'
				};
				if(response != true){
					params.type = 'error';
					params.text = app.vtranslate('JS_MOBILE_PERFORM_CALL_ERROR');
				}
				Vtiger_Helper_Js.showPnotify(params);
			}
        );
	},
	
},{
	registerEvents: function() {
		var thisInstance = this;
	},
});
jQuery(document).ready(function() {
    Vtiger_Mobile_Js.getInstance().registerEvents();
});