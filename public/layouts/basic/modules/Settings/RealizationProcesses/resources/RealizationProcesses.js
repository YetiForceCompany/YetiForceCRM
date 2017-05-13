/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
 
jQuery.Class('Settings_RealizationProcesses_Js', {
}, {
	/**
	 * Saves config to database
	 */
	saveConfig : function() {
		jQuery('.projectStatus').on('change',function() {
			var status = jQuery(this).val();
			var params = {};
			params.data = {
				module: 'RealizationProcesses',
				parent: 'Settings',
				action: 'SaveGeneral',
				status: status,
				moduleId: jQuery(this).data('moduleid'),
				mode: 'save'
			
			};
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
				function(data) {
				var response = data['result'];
				if ( response['success']) {
					var params = {
						text: app.vtranslate(response.message),
						animation: 'show',
						type: 'success'
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
				}
			);
		});
	},


});

jQuery(document).ready(function() {
	var instance = new Settings_RealizationProcesses_Js();
	instance.saveConfig();
})
