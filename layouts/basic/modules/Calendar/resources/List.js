/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/


Vtiger_List_Js("Calendar_List_Js",{

	triggerMassEdit : function(massEditUrl) {
		Vtiger_List_Js.triggerMassAction(massEditUrl, function(container){
			var massEditForm = container.find('#massEdit');
			massEditForm.validationEngine(app.validationEngineOptions);
			var listInstance = Vtiger_List_Js.getInstance();
			var editInstance = Vtiger_Edit_Js.getInstance();
			editInstance.registerBasicEvents(jQuery(container));
			listInstance.postMassEdit(container);
		});
	},

	triggerImportAction : function (importUrl) {
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(importUrl).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				if(data) {
					app.showModalWindow(data, function(data){
						jQuery('#ical_import').validationEngine(app.validationEngineOptions);
					});
				}
			}
		);
	},

	triggerExportAction : function (importUrl) {
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(importUrl).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				if(data) {
					app.showModalWindow(data, function(data){
					});
				}
			}
		);
	}

},{});
