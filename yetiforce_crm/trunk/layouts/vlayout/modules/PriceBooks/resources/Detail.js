/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("PriceBooks_Detail_Js",{},{
	
	listPriceUpdateContainer : false,
	
	/**
	 * Function to get listPrice update container
	 */
	getListPriceUpdateContainer : function(){
		return this.listPriceUpdateContainer;
	},
	
	/**
	 * Function to register event for select button click on pricebooks related list
	 */
	
	registerEventForSelectRecords : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', 'button.selectRelation', function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new PriceBooks_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.showSelectRelationPopup();
		});
	},
	
	/**
	 * Function to registerevent for updatelistprice in modal window on click of save
	 */
	
	registerEventforUpdateListPrice : function(){
		var thisInstance = this;
		var container = jQuery('#listPriceUpdate');
		container.on('submit',function(e){
			e.preventDefault();
			var invalidFields = container.data('jqv').InvalidFields;
			if((invalidFields.length) == 0){
				var idList = new Array();
				var relid = container.find('input[name="relid"]').val();
				var listPriceVal = container.find('input[name="currentPrice"]').val();
				idList.push({'id' : relid,'price' : listPriceVal});
				var relatedListInstance = new PriceBooks_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), thisInstance.getSelectedTab(), thisInstance.getRelatedModuleName());
				relatedListInstance.addRelations(idList);
				app.hideModalWindow();
				var relatedCurrentPage = relatedListInstance.getCurrentPageNum();
				var params = {'page':relatedCurrentPage};
				relatedListInstance.loadRelatedList(params);
			}
		})
	},
	/**
	 * Function to show listprice update form
	 */
	showListPriceUpdate : function(data){
		app.showModalWindow(data,{'text-align':'left'});
		jQuery('#listPriceUpdate').validationEngine(app.validationEngineOptions);
		this.registerEventforUpdateListPrice();
	},
	
	/**
	 * Function to get listprice edit form
	 */
	getListPriceEditForm : function(requestUrl){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var listPriceContainer = this.getListPriceUpdateContainer();
		if(listPriceContainer != false){
			aDeferred.resolve(listPriceContainer);
		}else{
			AppConnector.request(requestUrl).then(
				function(data){
					thisInstance.listPriceUpdateContainer = data;
					aDeferred.resolve(data);
				},
				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			);
		}
		return aDeferred.promise();
	},
	
	/**
	 * function to register event for editing the list price in related list 
	 */
	
	registerEventForEditListPrice : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', 'a.editListPrice', function(e){
			e.stopPropagation();
			var elem = jQuery(e.currentTarget);
			var requestUrl = elem.data('url');
			thisInstance.getListPriceEditForm(requestUrl).then(
				function(data){
					var relid = elem.data('relatedRecordid');
					var listPrice = elem.data('listPrice');
					var form = jQuery(data);
					form.find('input[name="relid"]').val(relid);
					form.find('input[name="currentPrice"]').val(listPrice);
					thisInstance.showListPriceUpdate(form);
				},
				function(error,err){

				}
			);
		});
	},
	/**
	 * Function to register events
	 */
	
	registerEvents : function(){
		this._super();
		this.registerEventForSelectRecords();
		this.registerEventForEditListPrice();
	}
})