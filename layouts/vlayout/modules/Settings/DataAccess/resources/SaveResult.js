/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
function SaveResult() {
	this.skipCheckData = false
	this.executeTaskStatus = true
	this.loadFormData = function(formData) {
		document.RecordValue = formData
	}
	this.checkData = function(form) {
		var thisInstnce = this;
		var params = {};
		var resp = [];
		var staus = true;
		if(this.skipCheckData == false){
			delete document.RecordValue['__vtrftk'];
			delete document.RecordValue['picklistDependency'];
			delete form['__vtrftk'];
			delete form['picklistDependency'];
			$.each( document.RecordValue, function( key, value ) {
				form['p_'+key] = value;
			});
			params.data = {
				module: 'DataAccess',
				parent: 'Settings', 
				action: 'ExecuteHandlers',
				param: form,
			};
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
				function(response) {
					resp = response['result']['data'];
					$.each( resp, function( key, object ) {
						if(typeof object.type != 'undefined'){
							staus = thisInstnce.executeTask(object)
						}
					});
					
				}
			);
		}
		return staus; // staus ==  true = ok, false = block edit
	}
	this.executeTask = function(data) {
		switch (data.type) {
			case 0:
				this.showNotify(data.info)
				this.executeTaskStatus = data.save_record;
			break;
			case 1:
				this.showQuickCreate(data.module,this.executeTaskStatus)
				view = app.getViewName();
				if( view != 'Detail'){
					this.executeTaskStatus = false
				}
			break;
			case 2:
				console.log('typ 3');
			break;
		}
		return this.executeTaskStatus; // true = ok, false = block edit
	}
	
	this.showNotify = function(info) {
		var params = {
			text: info.text,
			type: 'info',
			animation: 'show',
			width: 'auto'
		};
		if(info.ntype){
			params.type = info.ntype;
		}
		params = jQuery.extend(info,params);
		Vtiger_Helper_Js.showPnotify(params);
	}
	this.showQuickCreate = function(moduleName,orgExecuteTaskStatus) {
		var sourceRecord = jQuery('input[name="record"]').val();
		if(sourceRecord == undefined){
			sourceRecord = jQuery('#recordId').val();
		}
		var sourceModule = jQuery('input[name="module"]').val();
		var preQuickCreateSave = function(data){
			var index,queryParam,queryParamComponents;
			if(moduleName == 'Calendar'){
				jQuery('<input type="hidden" name="parent_id" value="'+sourceRecord+'">').appendTo(data);
			}
			if(sourceModule == 'HelpDesk'){
				jQuery('<input type="hidden" name="ticketid" value="'+sourceRecord+'">').appendTo(data);
			}
			if(sourceModule == 'ProjectTask'){
				jQuery('<input type="hidden" name="projecttaskid" value="'+sourceRecord+'">').appendTo(data);
			}
			if(sourceModule == 'Potentials'){
				jQuery('<input type="hidden" name="potentialid" value="'+sourceRecord+'">').appendTo(data);
			}
			jQuery('<input type="hidden" name="sourceModule" value="'+sourceModule+'" />').appendTo(data);
			jQuery('<input type="hidden" name="sourceRecord" value="'+sourceRecord+'" />').appendTo(data);
			jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);
		}
		var postQuickCreateSave  = function(data) {
			if(orgExecuteTaskStatus == true){
				skipCheckData = true;
				jQuery('#EditView .btn-success').trigger('click');
			}
		}
		var quickCreateParams = {};
		var relatedParams = {};
		var quickcreateUrl = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		relatedParams['sourceModule'] = sourceModule;
		relatedParams['sourceRecord'] = sourceRecord;
		relatedParams['relationOperation'] = true;
		if((app.getViewName() == 'Detail' || app.getViewName() == 'Edit') && moduleName == 'OSSTimeControl' ){
			relatedParams['name'] = jQuery('.recordLabel').attr('title');
		}
		quickCreateParams['callbackFunction'] = postQuickCreateSave;
		quickCreateParams['callbackPostShown'] = preQuickCreateSave;
		quickCreateParams['data'] = relatedParams;
		quickCreateParams['noCache'] = true;
		var progress = jQuery.progressIndicator();
		var headerInstance = new Vtiger_Header_Js();
		headerInstance.getQuickCreateForm(quickcreateUrl, moduleName, quickCreateParams).then(function(data) {
			headerInstance.handleQuickCreateData(data, quickCreateParams);
			progress.progressIndicator({'mode': 'hide'});
			jQuery('form[name="QuickCreate"] .cancelLinkContainer').on('click', function(e){
				if(orgExecuteTaskStatus == true){
					skipCheckData = true;
					jQuery('#EditView .btn-success').trigger('click');
				}
			});
		});
	}
}
