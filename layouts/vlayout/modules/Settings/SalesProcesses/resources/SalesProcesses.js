/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
 
jQuery.Class('Settings_SalesProcesses_Js', {
}, {

	/**
	 * Function to register save of configuration
	 */
	registerSaveConfig : function() {
		var thisInstance = this;
		jQuery('[name="productsRel2PotentialsOnly"]').on('change', function() {
			var productsRel2PotentialsOnly = jQuery(this).is(':checked') == true ? 1 : 0;
			thisInstance.saveConfig( productsRel2PotentialsOnly );
		});
	},

	/**
	 * Saves config to database
	 */
	saveConfig : function( productsRel2PotentialsOnly ) {
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});

		var params = {};
		params.data = {
			module: 'SalesProcesses',
			parent: 'Settings',
			action: 'SaveProcess',
			mode: 'save',
			prods2pot: productsRel2PotentialsOnly
		};
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				if ( response['success']) {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					var params = {
						text: response.message,
						type: 'info',
						animation: 'show'
					};
					Settings_Vtiger_Index_Js.showMessage(params);
				}
				else {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					var params = {
						text: response.message,
						type: 'error',
						animation: 'show'
					};
					Settings_Vtiger_Index_Js.showMessage(params);
				}
			},
			function(data,err){
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
			}
		);
	},

	/**
	 * register events for layout editor
	 */
	registerEvents : function() {
		var thisInstance = this;
		var container = jQuery('.contents');

		thisInstance.registerSaveConfig();
	}
});

jQuery(document).ready(function() {
	var instance = new Settings_SalesProcesses_Js();
	instance.registerEvents();
})