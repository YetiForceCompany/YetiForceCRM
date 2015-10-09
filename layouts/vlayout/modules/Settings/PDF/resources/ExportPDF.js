/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Settings_PDF_ExportPDF_Js", {
	validateSubmit: function (container) {
		var templateIds = new Array();
		var i = 0;
		container.find('[name="pdf_template[]"]').each(function () {
			if (jQuery(this).is(':checked')) {
				templateIds[i] = jQuery(this).val();
				i++;
			}
		});

		if (templateIds.length > 0) {
			container.find('#generate_pdf').attr('disabled', false);
			if (templateIds.length > 1) {
				container.find('#single_pdf').show();
			} else {
				container.find('#single_pdf').hide();
			}
		} else {
			container.find('#generate_pdf').attr('disabled', true);
			container.find('#single_pdf').hide();
		}
	},
	/*
	 * Function to register the click event for generate button
	 */
	registerPreSubmitEvent: function (container) {
		container.find('#generate_pdf, #single_pdf').on('click', function (e) {
			var templateIds = new Array();
			var i = 0;
			container.find('[name="pdf_template[]"]').each(function () {
				if (jQuery(this).is(':checked')) {
					templateIds[i] = jQuery(this).val();
					i++;
				}
			});
			var url = jQuery(this).data('url');
			jQuery(this).prop('href', url + JSON.stringify(templateIds));
		});
	},
	registerValidateSubmit: function (container) {
		var thisInstance = this;
		thisInstance.validateSubmit(container); 

		container.find('[name="pdf_template[]"]').on('change', function() {
			thisInstance.validateSubmit(container);
		});
	},
	registerEvents: function () {
		var container = jQuery('div.modal-content');
		this.registerPreSubmitEvent(container);
		this.registerValidateSubmit(container);
	}
});

jQuery(function () {
	var exportObject = new Settings_PDF_ExportPDF_Js();
	exportObject.registerEvents();
});
