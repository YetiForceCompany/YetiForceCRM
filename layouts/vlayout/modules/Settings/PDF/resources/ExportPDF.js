/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Settings_PDF_ExportPDF_Js", {
	/*
	 * Function to register the click event for generate button
	 */
	registerPreSubmitEvent: function (container) {
		container.find('#generate_pdf').on('click', function (e) {
			var templateId = container.find('#pdf_template').val();
			var url = jQuery(this).data('url');
			jQuery(this).prop('href', url + templateId);
		});

	},
	registerEvents: function () {
		var container = jQuery('div.modal-content');
		this.registerPreSubmitEvent(container);
	}
});

jQuery(function () {
	var exportObject = new Settings_PDF_ExportPDF_Js();
	exportObject.registerEvents();
});
