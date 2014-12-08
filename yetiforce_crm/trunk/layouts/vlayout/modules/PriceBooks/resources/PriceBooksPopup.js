/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Popup_Js("PriceBook_Products_Popup_Js",{

},{
	/**
	 * Function to register event for enabling list price
	 */
	checkBoxChangeHandler : function(e){
			this._super(e);
			var elem = jQuery(e.currentTarget);
			var parentRow = elem.closest('tr');
			if(elem.is(':checked')) {
				jQuery('input[name=listPrice]',parentRow).removeClass('invisible');
			}else{
				jQuery('input[name=listPrice]',parentRow).addClass('invisible');
			}
	},

	/**
	 * Function to register event for add to pricebook button in the popup
	 */

	registerSelectButton : function(){
		var popupPageContentsContainer = jQuery('#popupPage')
		var thisInstance = this;
		popupPageContentsContainer.on('jqv.form.result', function(e){
			e.preventDefault();
			var tableEntriesElement = popupPageContentsContainer.find('table.listViewEntriesTable');
			var selectedRecords = jQuery('input.entryCheckBox', tableEntriesElement).filter(':checked');
			if((selectedRecords.length) == 0){
				var message = app.vtranslate("JS_PLEASE_SELECT_ONE_RECORD");
				Vtiger_Helper_Js.showConfirmationBox({'message' : message})
				return;
			}
			var invalidFields = popupPageContentsContainer.data('jqv').InvalidFields;
			if((invalidFields.length) == 0){
				var selectedRecordDetails = new Array();
				selectedRecords.each(function(index, checkBoxElement){
					var checkBoxJqueryObject = jQuery(checkBoxElement)
					var row = checkBoxJqueryObject.closest('tr');
					var rowListPrice = row.find('input[name=listPrice]');
					var listPrice = rowListPrice.val();
					var id = row.data('id');
					selectedRecordDetails.push({'id' : id,'price' : listPrice});
				});
				thisInstance.done(selectedRecordDetails, thisInstance.getEventName());
			}

		});
	},
	/**
	 * Function to handle select all in the popup
	 */

	selectAllHandler : function(e){
		this._super(e);
		var currentElement = jQuery(e.currentTarget);
		var isMainCheckBoxChecked = currentElement.is(':checked');
		var tableElement = currentElement.closest('table');
		if(isMainCheckBoxChecked) {
			jQuery('input.entryCheckBox', tableElement).closest('tr').find('input[name="listPrice"]').removeClass('invisible');
		}else {
			jQuery('input.entryCheckBox', tableElement).closest('tr').find('input[name="listPrice"]').addClass('invisible');
		}
	},

	/**
	 * Function to register event for actions buttons
	 */
	registerEventForActionsButtons : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','a.cancelLink',function(e){
			thisInstance.done();
		})
	},

	/**
	 * Function to get Page Records
	 */
	getPageRecords : function(params){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		this._super(params).then(
			function(data){
				thisInstance.popupSlimScroll();
				var form = jQuery('#popupPage');
				form.validationEngine('detach');
				form.validationEngine('attach');
				aDeferred.resolve(data);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to handle sort
	 */
	sortHandler : function(headerElement){
		var thisInstance = this;
		//Listprice column should not be sorted so checking for class noSorting
		if(headerElement.hasClass('noSorting')){
			return;
		}
		this._super(headerElement).then(
			function(data){
				thisInstance.popupSlimScroll();
				var form = jQuery('#popupPage');
				form.validationEngine('detach');
				form.validationEngine('attach');
			},

			function(textStatus, errorThrown){

			}
		);
	},

	/**
	 * Function to handle slim scroll for popup
	 */
	popupSlimScroll : function(){
		var popupPageContentsContainer = this.getPopupPageContainer();
		var element = popupPageContentsContainer.find('.popupEntriesDiv');
		app.showScrollBar(element, {"height" : '400px'});
	},

     /**
     * Function which will register event when user clicks on the row
     */
    registerEventForListViewEntries : function() {
        //To Make sure we will not close the window once he clicks on the row,
        //which is default behaviour in normal popup
        return true;
    },

	registerEventForListViewEntries : function(){
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','.listViewEntries',function(e){
		    return;
		});
	},
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		this._super();
		this.registerEventForActionsButtons();
		this.popupSlimScroll();
		jQuery('#popupPage').validationEngine({promptPosition : "topRight"});
	}
});