/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class('Settings_CustomRecordNumbering_Js', {}, {
	
	form : false,
	getForm : function(){
		if(this.form == false){
			this.form = jQuery('#EditView');
		}
		return this.form;
	},
	
	/**
	 * Function to register change event for source module field
	 */
	registerOnChangeEventOfSourceModule :function(){
		var editViewForm = this.getForm();
		editViewForm.find('[name="sourceModule"]').on('change',function(e){
			jQuery('.saveButton').removeAttr('disabled');
			var element = jQuery(e.currentTarget);
			var params = {};
			var sourceModule = element.val();

			params = {
				'module' : app.getModuleName(),
				'parent' : app.getParentModuleName(),
				'action' : "CustomRecordNumberingAjax",
				'mode' : "getModuleCustomNumberingData",
				'sourceModule' : sourceModule
			}
			
			AppConnector.request(params).then(
					function(data){
						if(data){
							editViewForm.find('[name="prefix"]').val(data.result.prefix);
							editViewForm.find('[name="sequenceNumber"]').val(data.result.sequenceNumber);
							editViewForm.find('[name="sequenceNumber"]').data('oldSequenceNumber',data.result.sequenceNumber);
						}
					},
					function(jqXHR,textStatus, errorThrown){
			})
		})
	},
	
	/**
	 * Function to register event for saving module custom numbering
	 */
	saveModuleCustomNumbering : function(){
		if(jQuery('.saveButton').attr("disabled")){
			return;
		}
		var editViewForm = this.getForm();
		var params = {}
		var sourceModule = editViewForm.find('[name="sourceModule"]').val();
		var sourceModuleLabel = editViewForm.find('option[value="'+sourceModule+'"]').text();
		var prefix = editViewForm.find('[name="prefix"]');
		var currentPrefix = jQuery.trim(prefix.val());
		var oldPrefix = prefix.data('oldPrefix');
		var sequenceNumberElement = editViewForm.find('[name="sequenceNumber"]');
		var sequenceNumber = sequenceNumberElement.val();
		var oldSequenceNumber = sequenceNumberElement.data('oldSequenceNumber');

		if((sequenceNumber < oldSequenceNumber) && (currentPrefix == oldPrefix)){
			var errorMessage = app.vtranslate('JS_SEQUENCE_NUMBER_MESSAGE')+" "+oldSequenceNumber;
			sequenceNumberElement.validationEngine('showPrompt', errorMessage , 'error','topLeft',true);
			return;
		}

		params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : "CustomRecordNumberingAjax",
			'mode' : "saveModuleCustomNumberingData",
			'sourceModule' : sourceModule,
			'prefix' : currentPrefix,
			'sequenceNumber' : sequenceNumber
		}
		
		jQuery('.saveButton').attr("disabled","disabled");
		AppConnector.request(params).then(
				function(data){
					var params;
					var successfullSaveMessage = app.vtranslate('JS_RECORD_NUMBERING_SAVED_SUCCESSFULLY_FOR')+" "+sourceModuleLabel;
					if(data.success == true){
						params = {
							text: successfullSaveMessage
						};
						Settings_Vtiger_Index_Js.showMessage(params);
					}else{
						var errorMessage = currentPrefix+" "+app.vtranslate(data.error.message);
						params = {
							text: errorMessage,
							type: 'error'
						};
						Settings_Vtiger_Index_Js.showMessage(params);
					}
				},
				function(jqXHR,textStatus, errorThrown){
		})
	},
	
	/**
	 * Function to handle update record with the given sequence number
	 */
	registerEventToUpdateRecordsWithSequenceNumber : function(){
		var editViewForm = this.getForm();
		editViewForm.find('[name="updateRecordWithSequenceNumber"]').on('click',function(){
			var params = {};
			var sourceModule = editViewForm.find('[name="sourceModule"]').val();
			var sourceModuleLabel = editViewForm.find('option[value="'+sourceModule+'"]').text();
			
			params = {
				'module' : app.getModuleName(),
				'parent' : app.getParentModuleName(),
				'action' : "CustomRecordNumberingAjax",
				'mode' : "updateRecordsWithSequenceNumber",
				'sourceModule' : sourceModule
			}
			
			AppConnector.request(params).then(
					function(data){
						var successfullSaveMessage = app.vtranslate('JS_RECORD_NUMBERING_UPDATED_SUCCESSFULLY_FOR')+" "+sourceModuleLabel;
						if(data.success == true){
							var params = {
								text: successfullSaveMessage
							};
							Settings_Vtiger_Index_Js.showMessage(params);
						}else{
							Settings_Vtiger_Index_Js.showMessage(data.error.message);
						}
					},
					function(jqXHR,textStatus, errorThrown){
			})
		})
	},
	
	/**
	 * Function to register change event for prefix and sequence number
	 */
	registerChangeEventForPrefixAndSequenceNumber : function() {
		var editViewForm = this.getForm();
		editViewForm.find('[name="prefix"],[name="sequenceNumber"]').on('change',function(){
			jQuery('.saveButton').removeAttr('disabled');
		})
	},
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		var thisInstance = this;
		var editViewForm = this.getForm();
		this.registerOnChangeEventOfSourceModule();
		this.registerEventToUpdateRecordsWithSequenceNumber();
		this.registerChangeEventForPrefixAndSequenceNumber();
	
		var params = app.validationEngineOptions;
		params.onValidationComplete = function(editViewForm, valid){
			if(valid) {
				thisInstance.saveModuleCustomNumbering();
			}
			return false;
		}
		editViewForm.validationEngine('detach');
		editViewForm.validationEngine('attach',params);
	}
})
jQuery(document).ready(function() {
	var customRecordNumberingInstance = new Settings_CustomRecordNumbering_Js();
	customRecordNumberingInstance.registerEvents();
});