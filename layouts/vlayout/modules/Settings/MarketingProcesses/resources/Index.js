/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Settings_MarketingProcesses_Index_Js",{},{	

	/**
	 * Saves config to database
	 */
	registerSaveConversionState : function() {
		jQuery('#saveConversionState').on('click',function(e){
			var state = $("[name='conversiontoaccount']").is(':checked');
			var params = {
			'module' : 'Leads',
			'parent' : app.getParentModuleName(),
			'action' : 'ConvertToAccountSave',
			'state' : state,
			'mode': 'save',
		}
		AppConnector.request(params).then(
			function(data){
				if(true == data.result['success']){
					var param = {text:app.vtranslate('JS_CONVERSION_STATE_SUCCES')};
					Vtiger_Helper_Js.showMessage(param);
					
				}else{
					var param = {text:app.vtranslate('JS_CONVERSION_STATE_FAILURE')};
					Vtiger_Helper_Js.showPnotify(param);
				}
			},
			function(jqXHR,textStatus, errorThrown){
			})
		});
		
		return false;
	},
	registerSave : function() {
		jQuery('.saveButton').on('click',function(e){
			saveButton = jQuery(this);
			saveButton.attr('disabled', 'disabled');
			form = jQuery(this).closest('form');
			paramsForm = form.serializeFormData();
			var state = $("[name='conversiontoaccount']").is(':checked');
			var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'Save',
			'params' : paramsForm,
			'mode': 'save',
		}
		AppConnector.request(params).then(
			function(data){
				if(true == data.result['success']){
					var param = {text:app.vtranslate('LBL_SAVE_CONFIG_OK')};
					Vtiger_Helper_Js.showMessage(param);
					saveButton.removeAttr('disabled');
				}else{
					var param = {text:app.vtranslate('LBL_SAVE_CONFIG_ERROR')};
					Vtiger_Helper_Js.showPnotify(param);
					saveButton.removeAttr('disabled');
				}
			},
			function(jqXHR,textStatus, errorThrown){
			})
		});
		
		return false;
	},
	registerEvents: function() {
		this.registerSaveConversionState();
		this.registerSave();
	}
});

