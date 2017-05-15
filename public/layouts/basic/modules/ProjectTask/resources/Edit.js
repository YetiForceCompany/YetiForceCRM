/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

Vtiger_Edit_Js("ProjectTask_Edit_Js",{},{
	
	/**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
		var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		var form, parentIdElement, closestContainer;
		if(sourceFieldElement.attr('name') == 'projectmilestoneid') {
			form = this.getForm();
			parentIdElement  = form.find('[name="projectid"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				closestContainer = parentIdElement.closest('.fieldValue');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			} 
		}
		if(sourceFieldElement.attr('name') == 'parentid'){
			form = this.getForm();
			parentIdElement  = form.find('[name="projectmilestoneid"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				closestContainer = parentIdElement.closest('.fieldValue');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			}
		}
		return params;
	},

	registerEvents: function(){
		this._super();
	}
});


