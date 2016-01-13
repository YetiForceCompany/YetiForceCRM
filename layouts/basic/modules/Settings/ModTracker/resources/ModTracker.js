/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class('Settings_ModTracker_Js', {}, {
	registerActiveEvent : function() {
		var modTrackerContainer = jQuery('#modTrackerContainer');
		modTrackerContainer.on('change', '.activeModTracker', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var tr = currentTarget.closest('tr');
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'Save';
			params['mode'] = 'changeActiveStatus';
			params['id'] = tr.data('id');
			params['status'] = currentTarget.attr('checked') == 'checked';

			AppConnector.request(params).then(
				function(data) {
					var params = {};
					params['text'] = data.result.message;
					Settings_Vtiger_Index_Js.showMessage(params);
				},
				function(error) {
					var params = {};
					params['text'] = error;
					Settings_Vtiger_Index_Js.showMessage(params);
				}
			);
		})
	},
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		this.registerActiveEvent();
	}
})
jQuery(document).ready(function(){
	var settingModTrackerInstance = new Settings_ModTracker_Js();
	settingModTrackerInstance.registerEvents();
})