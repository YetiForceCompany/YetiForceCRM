/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js("Settings_Workflows_List_Js",{
    
    triggerCreate : function(url) {
        var selectedModule = jQuery('#moduleFilter').val();
        if(selectedModule.length > 0) {
            url += '&source_module='+selectedModule
        }
        window.location.href = url;
    }
},{
    
	registerFilterChangeEvent : function() {
		var thisInstance = this;
		jQuery('#moduleFilter').on('change',function(e){
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var params = {
				module : app.getModuleName(),
				parent : app.getParentModuleName(),
				sourceModule : jQuery(e.currentTarget).val()
			}
			//Make the select all count as empty
			jQuery('#recordsCount').val('');
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(params).then(
				function(data){
					thisInstance.updatePagination();
				}
			);
		});
	},
	
	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function(){
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.listViewEntries',function(e){
			var editUrl = jQuery(e.currentTarget).find('.icon-pencil').closest('a').attr('href');
			window.location.href = editUrl;
		});
	},
	
	getDefaultParams : function() {
		var pageNumber = jQuery('#pageNumber').val();
		var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var params = {
			'module': module,
			'parent' : parent,
			'page' : pageNumber,
			'view' : "List",
			sourceModule : jQuery('#moduleFilter').val()
		}

		return params;
	},
	
	registerEvents : function() {
		this._super();
		this.registerFilterChangeEvent();
	}
});