/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_RelatedList_Js("Campaigns_RelatedList_Js",{
	
	/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail : function(massActionUrl, module){
		var params = {"relatedLoad" : true};
		//To get the current module
		params['sourceModule'] = app.getModuleName();
		//to get current campaign id 
		params['sourceRecord'] = jQuery('#recordId').val();
		Vtiger_List_Js.triggerSendEmail(massActionUrl, module, params);
	}
},{
	
	loadRelatedList : function(params){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		this._super(params).then(function(data){
			thisInstance.registerEvents();
			var moduleName = app.getModuleName();
			var className = moduleName+"_List_Js";
			var listInstance = new window[className]();
			listInstance.registerEvents();
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	
	getCompleteParams : function(){
		var params = {};
		params['view'] = "Detail";
		params['module'] = this.parentModuleName;
		params['record'] = this.getParentId(),
		params['relatedModule'] = this.relatedModulename,
		params['sortorder'] =  this.getSortOrder(),
		params['orderby'] =  this.getOrderBy(),
		params['page'] = this.getCurrentPageNum();
		params['mode'] = "showRelatedList",
		params['selectedIds'] = jQuery('#selectedIds').data('selectedIds');
		params['excludedIds'] = jQuery('#excludedIds').data('excludedIds');
		
		return params;
	},
	
	changeCustomFilterElementView : function() {
		var filterSelectElement = jQuery('#recordsFilter');
		if(filterSelectElement.length > 0){
			app.showSelect2ElementView(filterSelectElement,{
				formatSelection : function(data){
					var resultContainer = jQuery('<span></span>');
					resultContainer.append(jQuery(jQuery('.filterImage').clone().get(0)).show());
					resultContainer.append(data.text);
					return resultContainer;
				}
			});

			var select2Instance = filterSelectElement.data('select2');
			select2Instance.dropdown.append(jQuery('span.filterActionsDiv'));
		}
	},
	/**
	 * Function to register change event for custom filter
	 */
	
	registerChangeCustomFilterEvent : function(){
		var filterSelectElement = jQuery('#recordsFilter');
		filterSelectElement.change(function(e){
			var element = jQuery(e.currentTarget);
			var cvId = element.find('option:selected').data('id');
			var relatedModuleName = jQuery('.relatedModuleName').val();
			var params = {
				'sourceRecord' : jQuery('#recordId').val(),
				'relatedModule' :relatedModuleName,
				'viewId' : cvId,
				'module' : app.getModuleName(),
				'action': "RelationAjax",
				'mode' : 'addRelationsFromRelatedModuleViewId'
			}
			
			var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			AppConnector.request(params).then(
				function(responseData){
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					})
					if(responseData != null){
						var message = app.vtranslate('JS_NO_RECORDS_RELATED_TO_THIS_FILTER');
						var params = {
							text: message,
							type: 'info'
						};
						Vtiger_Helper_Js.showMessage(params);
					} else {
						Vtiger_Detail_Js.reloadRelatedList();
					}
				},

				function(textStatus, errorThrown){
				}
			);
		})
		
	},
	
	/**
	 * Function to edit related status for email enabled modules of campaigns
	 */
	registerEventToEditRelatedStatus : function(){
		var thisInstance = this;
		jQuery('.currentStatus').on('click',function(e){
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			element.addClass('open');
		});
		var statusDropdown = jQuery('.currentStatus').find('.dropdown-menu');
		statusDropdown.on('click','a',function(e){
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var liContainer = element.closest('li');
			var currentStatus = element.closest('.currentStatus');
			var selectedStatusId = liContainer.attr('id');
			var selectedStatusValue = liContainer.data('status');
			var relatedRecordId = element.closest('tr').data('id');
			var params = {
				'relatedModule' : thisInstance.relatedModulename,
				'relatedRecord' : relatedRecordId,
				'status' : selectedStatusId,
				'module' : app.getModuleName(),
				'action' : 'RelationAjax',
				'sourceRecord' : thisInstance.parentRecordId,
				'mode' : 'updateStatus'
			}
			element.progressIndicator({});
			AppConnector.request(params).then(
				function(responseData){
					if(responseData.result[0]){
						element.progressIndicator({'mode': 'hide'});
						currentStatus.find('.statusValue').text(selectedStatusValue);
						currentStatus.removeClass('open');
					}
				},

				function(textStatus, errorThrown){
				}
			);
		});
	},
	
	registerEvents : function(){
		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerEventToEditRelatedStatus();
	}
})