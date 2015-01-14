/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_Mobile_Js', {}, {
	//This will store the MenuEditor Container
	mobileContainer : false,
	
	/**
	 * Function to get the MenuEditor container
	 */
	getContainer : function() {
		if(this.mobileContainer == false) {
			this.mobileContainer = jQuery('#MobileKeysContainer');
		}
		return this.mobileContainer;
	},
	
	addKey: function(e) {
		var container = jQuery('#MobileKeysContainer');
		var editColorModal = container.find('.addKeyContainer');
		var clonedContainer = editColorModal.clone(true, true);
		
		var callBackFunction = function(data) {	
			data.find('.addKeyContainer').removeClass('hide');
			data.find('.select').addClass('chzn-select');
			app.changeSelectElementView(data); // chzn-select select2
			data.find('[name="saveButton"]').click(function(e) {
				var form = data.find('form');
				var formData = form.serializeFormData();
				var progress = $.progressIndicator({
					'message' : app.vtranslate('Adding a Key'),
					'blockInfo' : {
						'enabled' : true
					}
				});
				var settingMobileInstance = new Settings_Mobile_Js();
				settingMobileInstance.registerSaveEvent('addKey',formData, true);
				progress.progressIndicator({'mode': 'hide'});
			});
		}
		app.showModalWindow(clonedContainer,function(data) {
			if(typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width':'1000px'});
	},
	deleteKey: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var settingMobileInstance = new Settings_Mobile_Js();
		settingMobileInstance.registerSaveEvent('deleteKey',{
			'user': closestTrElement.data('user'),
			'service': closestTrElement.data('service'),
		});
		closestTrElement.remove();
	},
	registerSaveEvent: function(mode, data, reload) {
		var params = {}
		params.data = {
			module: app.getModuleName(), 
			parent: app.getParentModuleName(), 
			action: 'SaveAjax', 
			mode: mode,
			params: data
		}
		params.async = false;
		params.dataType = 'json';
        AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var params = {
					text: response['message'],
					animation: 'show',
					type: 'success'
				};
				Vtiger_Helper_Js.showPnotify(params);
				if(reload == true && response.success == true){
					window.location.reload();
				}
			},
			function(data, err) {}
        );
	},
	
	registerEvents : function(e){
		var thisInstance = this;
		var container = thisInstance.getContainer();
		container.find('.addKey').click(thisInstance.addKey);
		container.find('.deleteKey').click(thisInstance.deleteKey);
	}
});
jQuery(document).ready(function(){
	var settingMobileInstance = new Settings_Mobile_Js();
	settingMobileInstance.registerEvents();
})