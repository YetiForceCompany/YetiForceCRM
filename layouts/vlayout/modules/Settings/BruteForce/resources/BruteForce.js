/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_BruteForce_Js', {
	runAppConnector : function(params) {
		  AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				if ( response['success']) {
					var params = {
						text: app.vtranslate(response.message),
						animation: 'show',
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
				else {
					var params = {
						text: app.vtranslate(response.message),
						animation: 'show',
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			},
			function(data,err){
				var params = {
					text: app.vtranslate('Could not finnish reaction.'),
					animation: 'show',
					type: 'error'
				};
				Vtiger_Helper_Js.showPnotify(params);                
			}
		);      
	},
	registerEvents : function() {
		var instance = this;
		jQuery('#saveConfig').click(function() {
			var attempsNumber = jQuery('[name="attempsnumber"]').val();
			var timeLock = jQuery('[name="timelock"]').val();
			var active = $("[name='active']").is(':checked');
			var selectedUsers = $("[name='selectedUsers']").val();

			validation = instance.fieldsValidation(attempsNumber, timeLock);
			if(false == validation){
				return false;
			}

			if (!attempsNumber.length && !timeLock) {
				var params = {
					text: app.vtranslate('Complete the fields'),
					animation: 'show',
					type: 'warning'
				};
				Vtiger_Helper_Js.showPnotify(params);    
				return false;
			}
			var params = {}
			params.data = {
				module: 'BruteForce',
				action: 'SaveConfig',
				parent: app.getParentModuleName(),
				number: attempsNumber,
				timelock: timeLock,
				active: active,
				selectedUsers: selectedUsers
			};
			params.async = false;
			params.dataType = 'json';
			instance.runAppConnector(params);
			return false;
		});
		
		$( "#brutalforce_tab_btn_1" ).click(function() {
			$("#brutalforce_tab_btn_2").attr('class', '');
			$("#brutalforce_tab_btn_1").attr('class', 'active');
			$( "#brutalforce_tab_2" ).hide(); 
			$( "#brutalforce_tab_1" ).show();      
		});
		$( "#brutalforce_tab_btn_2" ).click(function() {
			$("#brutalforce_tab_btn_1").attr('class', '');
			$("#brutalforce_tab_btn_2").attr('class', 'active');
			$( "#brutalforce_tab_1" ).hide(); 
			$( "#brutalforce_tab_2" ).show();      
		});   
		
		jQuery("#unblock").click(function(){
			jQuery(this).parents('tr').hide();
			ip = jQuery(this).attr('data-ip');
			var params = {}
			params.data = {
				module: 'BruteForce',
				action: 'UnBlock',
				parent: app.getParentModuleName(),
				ip: ip,
			};
			params.async = false;
			params.dataType = 'json';
			instance.runAppConnector(params);          
			return false;
		});
	},

	fieldsValidation : function(attempsNumber, timeLock){
		var result = true;
		if((2 >= attempsNumber || attempsNumber >= 100) || isNaN(attempsNumber)){
			var params = {
				text: app.vtranslate('JS_WRONG_ATTEMPS_NUMBER'),
				animation: 'show',
				type: 'error'
			};
			Vtiger_Helper_Js.showPnotify(params);
			result = false;
		}
		if(isNaN(timeLock)){
			var params = {
				text: app.vtranslate('JS_WRONG_TIME_LOCK_FORMAT'),
				animation: 'show',
				type: 'error'
			};
			Vtiger_Helper_Js.showPnotify(params);
			result = false;

		}

		return result;
	}
});
jQuery(document).ready(function(){
	var currencyInstance = new Settings_BruteForce_Js();
    currencyInstance.registerEvents();
})
