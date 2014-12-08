/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

PriceBooks_RelatedList_Js("Products_RelatedList_Js",{},{
	
	/**
	 * Function to get params for show event invocation
	 */
	getPopupParams : function(){
		var parameters = {
			'module' : this.relatedModulename,
			'src_module' :this.parentModuleName ,
			'src_record' : this.parentRecordId,
			'view' : "ProductPriceBookPopup",
			'src_field' : 'productsRelatedList',
			'multi_select' : true
		}
		return parameters;
	}
})