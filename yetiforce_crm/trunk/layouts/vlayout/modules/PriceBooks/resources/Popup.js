/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Popup_Js("PriceBooks_Popup_Js",{},{
	
	/**
	 * Function to pass params for request
	 */
	getCompleteParams : function(){
		var params = this._super();
		params['currency_id'] = jQuery('#currencyId').val();
		return params;
	},
	
	registerEvents: function(){
		this._super();
	}
});

