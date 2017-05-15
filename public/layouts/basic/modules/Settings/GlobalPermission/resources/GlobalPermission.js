/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_GlobalPermission_Js = {
	savePermissions: function(e) {
		var target = jQuery(e.currentTarget);
		var parent = target.closest('tr');
		var checked = target.attr('checked')? false : true;
		var params = {
		'module' : app.getModuleName(),
		'parent' : app.getParentModuleName(),
		'action' : 'Save',
		'profileID' : parent.data('pid'),
		'globalactionid' : target.data('globalactionid'),
		'checked' : checked
		}
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var params = {
					text: response['message'],
					type: 'success',
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
			},
			function(error,err){}
		);
	},
	registerEvents : function() {
		jQuery('.GP_SAVE').change(Settings_GlobalPermission_Js.savePermissions);
	}
}
jQuery(document).ready(function(){
	Settings_GlobalPermission_Js.registerEvents();
})