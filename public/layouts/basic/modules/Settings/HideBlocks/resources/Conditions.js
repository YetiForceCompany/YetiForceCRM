/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Settings_HideBlocks_Conditions_Js",{},{
	advanceFilterInstance : false,
	registerSaveConditions : function(){
		var thisInstance = this;
		$( ".saveLink" ).click(function() {
			var form = $('.targetFieldsTableContainer form')
			var advfilterlist = thisInstance.advanceFilterInstance.getValues();
			$('.advanced_filter').val(JSON.stringify(advfilterlist));
			var formData = form.serializeFormData();
			form.submit();
		});
	},
	registerEvents : function(container) {
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer'));
		this.registerSaveConditions();
	}
});