/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_Module_Manager_Js', {
}, {
	
	/*
	 * function to update the module status for the module
	 * @params: currentTarget - checkbox related to module.
	 */
	updateModuleStatus : function(currentTarget) {
		var aDeferred = jQuery.Deferred();
		var forModule = currentTarget.data('module');
		var status = currentTarget.is(':checked');
		
		var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			
		var params = {}
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['updateStatus'] = status;
		params['forModule'] = forModule
		params['action'] = 'Basic';
		params['mode'] = 'updateModuleStatus';
		
		AppConnector.request(params).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				aDeferred.resolve(data);
			},
			function(error) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				//TODO : Handle error
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
	
	//This will show the notification message using pnotify
	showNotify : function(customParams) {
		var params = {
			title : app.vtranslate('JS_MESSAGE'),
			text: customParams.text,
			animation: 'show',
			type: 'info'
		};
		Vtiger_Helper_Js.showPnotify(params);
	},
	
	registerEvents : function(e){
		var thisInstance = this;
		var container = jQuery('#moduleManagerContents');
		
		//register click event for check box to update the module status
		container.on('click', '[name="moduleStatus"]', function(e){
			var currentTarget = jQuery(e.currentTarget);
			var moduleBlock = currentTarget.closest('.moduleManagerBlock');
			var actionButtons = moduleBlock.find('.actions');
			var forModule = currentTarget.data('moduleTranslation');
			var moduleDetails = moduleBlock.find('.moduleImage, .moduleName');
			
			if(currentTarget.is(':checked')){
				//show the settings button for the module.
				actionButtons.removeClass('hide');
				
				//changing opacity of the block if the module is enabled
				moduleDetails.removeClass('dull');
				
				//update the module status as enabled
				thisInstance.updateModuleStatus(currentTarget).then(
					function(data) {
						var params = {
							text: forModule+' '+app.vtranslate('JS_MODULE_ENABLED')
						}
						thisInstance.showNotify(params);
					},
					function(error){
						//TODO: Handle Error
					}
				);
					
			} else {
				//hide the settings button for the module.
				actionButtons.addClass('hide');
				
				//changing opacity of the block if the module is disabled
				moduleDetails.addClass('dull');
				
				//update the module status as disabled
				thisInstance.updateModuleStatus(currentTarget).then(
					function(data) {
						var params = {
							text: forModule+' '+app.vtranslate('JS_MODULE_DISABLED')
						}
						thisInstance.showNotify(params);
					},
					function(error){
						//TODO: Handle Error
					}
				);
			}
			
		});
	}
});


jQuery(document).ready(function(){
	var settingModuleManagerInstance = new Settings_Module_Manager_Js();
	settingModuleManagerInstance.registerEvents();
})
