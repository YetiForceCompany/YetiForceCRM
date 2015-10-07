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
		} else {
			container.find('#generate_pdf').attr('disabled', true);
		}
	},
	/*
	 * Function to register the click event for generate button
	 */
	registerPreSubmitEvent: function (container) {
		container.find('#generate_pdf').on('click', function (e) {
			var templateIds = new Array();
			var i = 0;
			container.find('[name="pdf_template[]"]').each(function () {
				if (jQuery(this).is(':checked')) {
					templateIds[i] = jQuery(this).val();
					i++;
				}
			});
			var url = jQuery(this).data('url');
			if (templateIds.length === 1) {
				jQuery(this).prop('href', url + templateIds[0]);
			} else {
				alert('póki co jeden szablon');
				console.log('póki co jeden szablon');
				e.preventDefault();
			}
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
