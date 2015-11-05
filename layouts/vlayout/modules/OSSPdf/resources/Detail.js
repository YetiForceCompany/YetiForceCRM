/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("OSSPdf_Detail_Js",{},{
	
	editFormContents: function(){
		jQuery('.btn input[type="checkbox"]').on('change',function(){
			element = jQuery(this).closest('label');
			element.toggleClass('active');
			blockId = 'DOC_'+element.data('block');
			jQuery('#'+blockId).toggleClass('hide');
		});
	},
	/**
	 * Function which will register all the events
	 */
    registerEvents : function() {
		this._super();
		this.editFormContents();
	}
})
