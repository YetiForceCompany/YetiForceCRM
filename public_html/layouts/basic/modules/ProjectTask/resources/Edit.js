/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Vtiger_Edit_Js("ProjectTask_Edit_Js", {}, {

	/**
	 * Function to get popup params
	 */
	getPopUpParams: function (container) {
		var params = this._super(container);
		var sourceFieldElement = jQuery('input[class="sourceField"]', container);
		var form, parentIdElement, closestContainer;
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
	},

	registerEvents: function () {
		this._super();
	}
});


