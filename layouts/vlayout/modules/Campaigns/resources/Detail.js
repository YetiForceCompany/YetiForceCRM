/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Campaigns_Detail_Js",{},{
	
	loadRelatedList : function(pageNumber){
		var relatedListInstance = new Campaigns_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		var params = {'page':pageNumber};
		this.clearSelectedRecords();
		relatedListInstance.loadRelatedList(params);
	},
	
	/**
	 * Function to clear selected records
	 */
	clearSelectedRecords : function() {
		jQuery('[name="selectedIds"]').data('selectedIds',"");
		jQuery('[name="excludedIds"]').data('excludedIds',"");
	},
	
	registerEventForRelatedListPagination : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','#relatedListNextPageButton',function(e){
			var element = jQuery(e.currentTarget);
			if(element.attr('disabled') == "disabled"){
				return;
			}
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.nextPageHandler().then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEmailEnabledActions();
				}
			});
		});
		detailContentsHolder.on('click','#relatedListPreviousPageButton',function(){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.previousPageHandler().then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEmailEnabledActions();
				}
			});
		});
		detailContentsHolder.on('click','#relatedListPageJump',function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.getRelatedPageCount();
		});
		detailContentsHolder.on('click','#relatedListPageJumpDropDown > li',function(e){
			e.stopImmediatePropagation();
		}).on('keypress','#pageToJump',function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.pageJumpHandler(e).then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEmailEnabledActions();
				}
			});
		});
	},
	
	/**
	 * Function to register Event for Sorting
	 */
	registerEventForRelatedList : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','.relatedListHeaderValues',function(e){
			var element = jQuery(e.currentTarget);
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.sortHandler(element).then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEmailEnabledActions();
				}
			});
		});
		
		detailContentsHolder.on('click', 'button.selectRelation', function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.showSelectRelationPopup().then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEmailEnabledActions();
				}
			});
		});

		detailContentsHolder.on('click', 'a.relationDelete', function(e){
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var instance = Vtiger_Detail_Js.getInstance();
			var key = instance.getDeleteMessageKey();
			var message = app.vtranslate(key);
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					var row = element.closest('tr');
					var relatedRecordid = row.data('id');
					var selectedTabElement = thisInstance.getSelectedTab();
					var relatedModuleName = thisInstance.getRelatedModuleName();
					var relatedController = new Campaigns_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
					relatedController.deleteRelation([relatedRecordid]).then(function(response){
						relatedController.loadRelatedList().then(function(data){
							var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
							var listInstance = Vtiger_List_Js.getInstance();
							var selectedIds = listInstance.readSelectedIds();
							if(selectedIds != 'all'){
								relatedRecordid = relatedRecordid.toString();
								var idIndex = jQuery.inArray(relatedRecordid,selectedIds);
								if(idIndex != -1){
									selectedIds.splice(idIndex,1);
									listInstance.writeSelectedIds(selectedIds);
								}
							}
							if(emailEnabledModule){
								thisInstance.registerEmailEnabledActions();
							}
						});
					});
				},
				function(error, err){
				}
			);
		});
	},
	
	/**
	 * Function to register event for adding related record for module
	 */
	registerEventForAddingRelatedRecord : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','[name="addButton"]',function(e){
			var element = jQuery(e.currentTarget);
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
            var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ relatedModuleName +'"]');
            if(quickCreateNode.length <= 0) {
                window.location.href = element.data('url');
                return;
            }
            
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.addRelatedRecord(element).then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEmailEnabledActions();
				}
			});
		})
	},
	
	/**
	 * Function to register email enabled actions
	 */
	registerEmailEnabledActions : function() {
		var moduleName = app.getModuleName();
		var className = moduleName+"_List_Js";
		var listInstance = new window[className]();
		listInstance.registerEvents();
		listInstance.markSelectedRecords();
		this.registerRelatedListEvents();
	},
	
	registerEventForRelatedTabClick : function(){
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var detailContainer = detailContentsHolder.closest('div.detailViewInfo');
		jQuery('.related', detailContainer).on('click', 'li', function(e, urlAttributes){
			var tabElement = jQuery(e.currentTarget);
			var element = jQuery('<div></div>');
			element.progressIndicator({
				'position':'html',
				'blockInfo' : {
					'enabled' : true,
					'elementToBlock' : detailContainer
				}
			});
			var url = tabElement.data('url');
			if(typeof urlAttributes != 'undefined'){
				var callBack = urlAttributes.callback;
				delete urlAttributes.callback;
			}
			thisInstance.loadContents(url,urlAttributes).then(
				function(data){
					thisInstance.deSelectAllrelatedTabs();
					thisInstance.markTabAsSelected(tabElement);
					element.progressIndicator({'mode': 'hide'});
					var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
					if(emailEnabledModule){
						var listInstance = new Campaigns_List_Js();
						listInstance.registerEvents();
						thisInstance.registerRelatedListEvents();
					}
					if(typeof callBack == 'function'){ 
						callBack(data);
					}
					//Summary tab is clicked
					if(tabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
						thisInstance.loadWidgets();
						thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
					}
				},
				function (){
					//TODO : handle error
					element.progressIndicator({'mode': 'hide'});
				}
			);
		});
	},
	
	/**
	 * Function to register related list events
	 */
	registerRelatedListEvents : function(){
		var selectedTabElement = this.getSelectedTab();
		var relatedModuleName = this.getRelatedModuleName();
		var relatedController = new Campaigns_RelatedList_Js(this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
		relatedController.registerEvents();
	},
	
	registerEvents : function(){
		this.registerRelatedListEvents();
		this._super();
		//Calling registerevents of campaigns list to handle checkboxs click of related records
		var listInstance = Vtiger_List_Js.getInstance();
		listInstance.registerEvents();
	}
})