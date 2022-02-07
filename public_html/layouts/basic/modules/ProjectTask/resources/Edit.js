/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Edit_Js(
	'ProjectTask_Edit_Js',
	{},
	{
		/**
		 * Function to get popup params
		 */
		getRecordsListParams: function (container) {
			let params = this._super(container);
			let sourceFieldElement = jQuery('input[class="sourceField"]', container);
			let form, parentIdElement, closestContainer;
			if (sourceFieldElement.attr('name') == 'projectmilestoneid') {
				form = this.getForm();
				parentIdElement = form.find('[name="projectid"]');
				if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
					closestContainer = parentIdElement.closest('.fieldValue');
					params['related_parent_id'] = parentIdElement.val();
					params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
			if (sourceFieldElement.attr('name') == 'parentid') {
				form = this.getForm();
				parentIdElement = form.find('[name="projectmilestoneid"]');
				if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
					closestContainer = parentIdElement.closest('.fieldValue');
					params['related_parent_id'] = parentIdElement.val();
					params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
			return params;
		}
	}
);
