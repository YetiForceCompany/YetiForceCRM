/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_EmailPreview_Js",{},{
	
	/**
	 * Function to get email actions params
	 */
	getEmailActionsParams : function(mode){
		var parentRecord = new Array();
		var parentRecordId = jQuery('[name="parentRecord"]').val();
		parentRecord.push(parentRecordId);
		var recordId = jQuery('[name="recordId"]').val();
		var params = {};
		params['module'] = "Emails";
		params['view'] = "ComposeEmail";
		params['selected_ids'] = parentRecord;
		params['record'] = recordId;
		params['mode'] = mode;
		params['parentId'] = parentRecordId;
		params['relatedLoad'] = true;
		
		return params;
	},
	
	/**
	 * Function to register events for action buttons of email preview
	 */
	registerEventsForActionButtons : function(){
		var thisInstance = this;
		jQuery('[name="previewForward"],[name="previewEdit"], [name="previewPrint"]').on('click',function(e){
			var module = "Emails";
			Vtiger_Helper_Js.checkServerConfig(module).then(function(data){
				if(data == true){
					var mode = jQuery(e.currentTarget).data('mode');
					var params = thisInstance.getEmailActionsParams(mode);
					var urlString = (typeof params == 'string')? params : jQuery.param(params);
					var url = 'index.php?'+urlString;
					self.location.href = url;
				} else {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
				}
			})
		})
	},
	
	registerEvents : function(){
		this.registerEventsForActionButtons();
	}
})

//On Page Load
jQuery(document).ready(function() {
	var emailPreviewInstance  =  new Vtiger_EmailPreview_Js();
	emailPreviewInstance.registerEvents();
	var documentHeight = (jQuery(document).height())+'px';
	jQuery('.SendEmailFormStep2').css('height',documentHeight);
});
