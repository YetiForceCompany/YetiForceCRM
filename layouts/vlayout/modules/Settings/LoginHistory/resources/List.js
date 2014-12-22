/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js("Settings_LoginHistory_List_Js",{},{
    
	registerFilterChangeEvent : function() {
		var thisInstance = this;
		jQuery('#usersFilter').on('change',function(e){
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var params = {
				module : app.getModuleName(),
				parent : app.getParentModuleName(),
				'search_key' : 'user_name',
				'search_value' : jQuery(e.currentTarget).val(),
				'page' : 1,
                'user_name' :this.options[this.selectedIndex].getAttribute("name")
			}
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(params).then(
				function(data){
					thisInstance.updatePagination();
				}
			);
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
			'user_name' : jQuery('select[id=usersFilter] option:selected').attr('name'),
			'search_key' : 'user_name',
			'search_value' : jQuery('#usersFilter').val()
		}

		return params;
	},
	
	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var pageJumpParams = {
			'module' : module,
			'parent' : parent,
			'action' : "ListAjax",
			'mode' : "getPageCount",
			'search_value' : jQuery('#usersFilter').val(),
			'search_key' : 'user_name'
		}
		return pageJumpParams;
	},
	
	registerEvents : function() {
		this.registerFilterChangeEvent();
		this.registerPageNavigationEvents();
		this.registerEventForTotalRecordsCount();
	}
});