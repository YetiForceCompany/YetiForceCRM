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
	registerChangeVal: function(content) {
		var thisInstance = this;
		content.find('.configField').change(function(e) {
			console.log('configField');
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if(target.attr('type') == 'checkbox'){
				params['val'] = this.checked;
			}else{
				params['val'] = target.val();
			}
			app.saveAjax('updateConfig', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			});
		});
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
		var content = $('#supportProcessesContainer');
		this.registerChangeVal(content);
		this.registerSave();
	}
});

