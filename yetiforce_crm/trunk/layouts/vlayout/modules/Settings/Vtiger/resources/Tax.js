/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_Vtiger_Tax_Js",{},{
	
	//Stored history of TaxName and duplicate check result
	duplicateCheckCache : {},
	
	/**
	 * This function will show the model for Add/Edit tax
	 */
	editTax : function(url, currentTrElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		
		AppConnector.request(url).then(
			function(data) {
				var callBackFunction = function(data) {
					//cache should be empty when modal opened 
					thisInstance.duplicateCheckCache = {};
					var form = jQuery('#editTax');
					
					var params = app.validationEngineOptions;
					params.onValidationComplete = function(form, valid){
						if(valid) {
							thisInstance.saveTaxDetails(form, currentTrElement);
							return valid;
						}
					}
					form.validationEngine(params);
					
					form.submit(function(e) {
						e.preventDefault();
					})
				}
				
				progressIndicatorElement.progressIndicator({'mode':'hide'});
				app.showModalWindow(data,function(data){
					if(typeof callBackFunction == 'function'){
						callBackFunction(data);
					}
				}, {'width':'500px'});
			},
			function(error) {
				//TODO : Handle error
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
	
	/*
	 * Function to Save the Tax Details
	 */
	saveTaxDetails : function(form, currentTrElement) {
		var thisInstance = this;
		var params = form.serializeFormData();

		if(typeof params == 'undefined' ) {
			params = {};
		}
		thisInstance.validateTaxName(params).then(
			function(data) {
				var progressIndicatorElement = jQuery.progressIndicator({
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});

				params.module = app.getModuleName();
				params.parent = app.getParentModuleName();
				params.action = 'TaxAjax';
				AppConnector.request(params).then(
					function(data) {
						progressIndicatorElement.progressIndicator({'mode':'hide'});
						app.hideModalWindow();
						//Adding or update the tax details in the list
						if(form.find('.addTaxView').val() == "true") {
							thisInstance.addTaxDetails(data['result']);
						} else {
							thisInstance.updateTaxDetails(data['result'], currentTrElement);
						}
						//show notification after tax details saved
						var params = {
							text: app.vtranslate('JS_TAX_SAVED_SUCCESSFULLY')
						};
						Settings_Vtiger_Index_Js.showMessage(params);
					}
				);
			},
			function(data,err) {
			}
		);
	},
	
	/*
	 * Function to add the Tax Details in the list after saving
	 */
	addTaxDetails : function(details) {
		var container = jQuery('#TaxCalculationsContainer');
		
		//Based on tax type, we will add the tax details row
		if(details.type == '0') {
			var taxTable = jQuery('.inventoryTaxTable', container);
		} else {
			var taxTable = jQuery('.shippingTaxTable', container);
		}
		
		var trElementForTax = 
				jQuery('<tr class="opacity" data-taxid="'+details.taxid+'" data-taxtype="'+details.type+'">\n\
					<td style="border-left: none;" class="textAlignCenter '+details.row_type+'"><label class="taxLabel">'+details.taxlabel+'</label></td>\n\
					<td style="border-left: none;" class="textAlignCenter '+details.row_type+'"><span class="taxPercentage">'+details.percentage+'%</span></td>\n\
					<td style="border-left: none;" class="textAlignCenter '+details.row_type+'"><input class="editTaxStatus" type="checkbox" checked>\n\
						<div class="pull-right actions">\n\
							<a class="editTax cursorPointer" data-url="'+details._editurl+'">\n\
								<i class="icon-pencil alignBottom" title="'+app.vtranslate('JS_EDIT')+'"></i>\n\
							</a>\n\
						</div>\n\
					</td></tr>');
		taxTable.append(trElementForTax);
	},
	
	/*
	 * Function to update the tax details in the list after edit
	 */
	updateTaxDetails : function(data, currentTrElement) {
		currentTrElement.find('.taxLabel').text(data['taxlabel']);
		currentTrElement.find('.taxPercentage').text(data['percentage']+'%');
		if(data['deleted'] == '0') {
			currentTrElement.find('.editTaxStatus').attr('checked', 'true');
		} else {
			currentTrElement.find('.editTaxStatus').removeAttr('checked');
		}
	},
	
	/*
	 * Function to validate the TaxName to avoid duplicates
	 */
	validateTaxName : function(data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		
		var taxName = data.taxlabel;
		var form = jQuery('#editTax');
		var taxLabelElement = form.find('[name="taxlabel"]');
		
		if(!(taxName in thisInstance.duplicateCheckCache)) {
			thisInstance.checkDuplicateName(data).then(
				function(data){
					thisInstance.duplicateCheckCache[taxName] = data['success'];
					aDeferred.resolve();
				},
				function(data, err){
					thisInstance.duplicateCheckCache[taxName] = data['success'];
					thisInstance.duplicateCheckCache['message'] = data['message'];
					taxLabelElement.validationEngine('showPrompt', data['message'] , 'error','bottomLeft',true);
					aDeferred.reject(data);
				}
			);
		} else {
			if(thisInstance.duplicateCheckCache[taxName] == true){
				var result = thisInstance.duplicateCheckCache['message'];
				taxLabelElement.validationEngine('showPrompt', result , 'error','bottomLeft',true);
				aDeferred.reject();
			} else {
				aDeferred.resolve();
			}
		}
		return aDeferred.promise();
	},
	
	/*
	 * Function to check Duplication of Tax Name
	 */
	checkDuplicateName : function(details) {
		var aDeferred = jQuery.Deferred();
		var taxName = details.taxlabel;
		var taxId = details.taxid;
		var moduleName = app.getModuleName();
		var params = {
			'module' : moduleName,
			'parent' : app.getParentModuleName(),
			'action' : 'TaxAjax',
			'mode' : 'checkDuplicateName',
			'taxlabel' : taxName,
			'taxid' : taxId,
			'type' : details.type
		}
		
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var result = response['success'];
				if(result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	/*
	 * Function to update tax status as enabled or disabled 
	 */
	updateTaxStatus : function(currentTarget) {
		var aDeferred = jQuery.Deferred();
		
		var currentTrElement = currentTarget.closest('tr');
		var taxId = currentTrElement.data('taxid');
		var taxType = currentTrElement.data('taxtype');
		var deleted = currentTarget.is(':checked') ? 0 : 1;
		
		var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
		
		var params = {
		'module' : app.getModuleName(),
		'parent' : app.getParentModuleName(),
		'action' : 'TaxAjax',
		'taxid' : taxId,
		'type' : taxType,
		'deleted' : deleted
		}
		
		AppConnector.request(params).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				aDeferred.resolve(data);
			},
			function(error,err) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				//TODO : Handle error
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
	
	/*
	 * Function to register all actions in the Tax List
	 */
	registerActions : function() {
		var thisInstance = this;
		var container = jQuery('#TaxCalculationsContainer');
		
		//register click event for Add New Tax button
		container.find('.addTax').click(function(e) {
			var addTaxButton = jQuery(e.currentTarget);
			var createTaxUrl = addTaxButton.data('url')+'&type='+addTaxButton.data('type');
			thisInstance.editTax(createTaxUrl);
		});
		
		//register event for edit tax icon
		container.on('click', '.editTax', function(e) {
			var editTaxButton = jQuery(e.currentTarget);
			var currentTrElement = editTaxButton.closest('tr');
			thisInstance.editTax(editTaxButton.data('url'), currentTrElement);
		});
		
		//register event for checkbox to change the tax Status
		container.on('click', '.editTaxStatus', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			
			thisInstance.updateTaxStatus(currentTarget).then(
				function(data){
					var params = {};
					if(currentTarget.is(':checked')) {
						params.text = app.vtranslate('JS_TAX_ENABLED');
					} else {
						params.text = app.vtranslate('JS_TAX_DISABLED');
					}
					Settings_Vtiger_Index_Js.showMessage(params);
				},
				function(error){
					//TODO: Handle Error
				}
			);
		});
		
	},
	
	registerEvents: function() {
		this.registerActions();
	}

});

jQuery(document).ready(function(e){
	var taxInstance = new Settings_Vtiger_Tax_Js();
	taxInstance.registerEvents();
})