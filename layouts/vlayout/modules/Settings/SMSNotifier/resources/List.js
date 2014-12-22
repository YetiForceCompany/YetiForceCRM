/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js("Settings_SMSNotifier_List_Js",{
	
	/**
	 * Function to trigger edit and add new configuration for SMS server
	 */
	triggerEdit : function(event, url) {
		event.stopPropagation();
		var instance = Vtiger_List_Js.getInstance();
		instance.EditRecord(url);
	},
	
	/**
	 * Function to trigger delete SMS provider Configuration
	 */
	triggerDelete : function(event,url){
		event.stopPropagation();
		var instance = Vtiger_List_Js.getInstance();
		instance.DeleteRecord(url);
	}
	
},{
	/**
	 * Function to show the SMS Provider configuration details for edit and add new
	 */
	EditRecord : function(url) {
		var thisInstance = this;
		AppConnector.request(url).then(
			function(data) {
				
				var callBackFunction = function(data) {
					var form = jQuery('#smsConfig');
					
					thisInstance.registerProviderTypeChangeEvent(form);
					
					var params = app.getvalidationEngineOptions(true);
					params.onValidationComplete = function(form, valid){
						if(valid) {
							thisInstance.saveConfiguration(form).then(
								function(data) {
									if(data['success']) {
										var params = {};
										params['text'] = app.vtranslate('JS_CONFIGURATION_SAVED');
										Settings_Vtiger_Index_Js.showMessage(params);
										thisInstance.getListViewRecords();
									}
								},
								function(error, err) {

								}
							);
						}
						//To prevent form submit
						return false;
					}
					form.validationEngine(params);
					
				}
				
				app.showModalWindow(data,function(data) {
					if(typeof callBackFunction == 'function') {
						callBackFunction(data);
					}
				});
			},
			function(error,err){
			}
		);
	},
	
	/**
	 * Function to register change event for SMS server Provider Type
	 */
	registerProviderTypeChangeEvent : function(form) {
		var thisInstance = this;
		var contents = form.find('.configContent');
		form.find('.providerType').change(function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var selectedProviderName = currentTarget.val(); 
            jQuery('.providerFields',form).addClass('hide'); 
            jQuery('#'+selectedProviderName+'_container').removeClass('hide'); 

		})
	},
	
	/**
	 * Function to save the SMS Server Configuration Details from edit and Add new configuration 
	 */
	saveConfiguration : function(form) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		
		var params = form.serializeFormData();
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'SaveAjax';
		
		AppConnector.request(params).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				aDeferred.resolve(data);
			},
			function(error) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to delete Configuration for SMS Provider
	 */
	DeleteRecord : function(url) {
		var thisInstance = this;
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
			function(e) {
				AppConnector.request(url).then(
					function() {
						var params = {
							text: app.vtranslate('JS_RECORD_DELETED_SUCCESSFULLY')
						};
						Settings_Vtiger_Index_Js.showMessage(params);
						thisInstance.getListViewRecords();
					},
					function(error,err){
					}
				);
			},
			function(error, err){
			}
		);
	},
	
	/**
	 * Function to register all the events
	 */
	registerEvents : function() {
		//this.triggerDisplayTypeEvent();
		this.registerPageNavigationEvents();
	}
})