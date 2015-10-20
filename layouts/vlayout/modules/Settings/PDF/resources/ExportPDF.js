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
			if (templateIds.length > 1 || (app.getUrlVar('view') === 'List' && JSON.parse(container.find('[name="validRecords"]').val()).length > 0)) {
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
		container.find('#generate_pdf, #single_pdf, #email_pdf').on('click', function (e) {
			var templateIds = new Array();
			var i = 0;
			container.find('[name="pdf_template[]"]').each(function () {
				if (jQuery(this).is(':checked')) {
					templateIds[i] = jQuery(this).val();
					i++;
				}
			});
			var url = jQuery(this).data('url');
			if (app.getUrlVar('view') === 'List') {
				url = url.replace('&record=&', '&record=' + container.find('[name="validRecords"]').val() + '&');
			}

			window.open(url + JSON.stringify(templateIds), '_blank');
		});
	},
	registerValidateSubmit: function (container) {
		var thisInstance = this;
		thisInstance.validateSubmit(container);

		container.find('[name="pdf_template[]"]').on('change', function () {
			thisInstance.validateSubmit(container);
		});
	},
	registerListViewCheckRecords: function (container) {
		var thisInstance = this;
		container.find('[name="pdf_template[]"]').on('change', function () {
			document.progressLoader = jQuery.progressIndicator({
				'message': app.vtranslate('JS_RECALCULATING'),
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			var selectedRecords = container.find('[name="selectedRecords"]').val();
			var selectedTemplates = [];
			container.find('[name="pdf_template[]"]:checked').each(function (i) {
				selectedTemplates[i] = jQuery(this).val();
			});

			var params = {};
			params.data = {
				parent: 'Settings',
				module: 'PDF',
				action: 'ValidateRecords',
				records: selectedRecords,
				view: app.getUrlVar('view'),
				for_module: app.getUrlVar('module'),
				templates: selectedTemplates
			};
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (data['success']) {
							container.find('[name="validRecords"]').val(JSON.stringify(response.valid_records));
							container.find('#recordsInfo').text(response.message);
							setTimeout(function () {
								document.progressLoader.progressIndicator({'mode': 'hide'})
							}, 500);
							thisInstance.validateSubmit(container);
						}
					},
					function (data, err) {
						app.errorLog(data, err);
					}
			);
		});
	},
	countSelectedRecords: function (container) {
		var selectedRecords = JSON.parse(container.find('[name="selectedRecords"]').val());

		return selectedRecords.length;
	},
	registerEvents: function () {
		var container = jQuery('div.modal-content');
		this.registerPreSubmitEvent(container);
		this.registerValidateSubmit(container);

		if (app.getUrlVar('view') === 'List') {
			this.registerListViewCheckRecords(container);
			var recordsInput = '<input type="hidden" name="selectedRecords" value="" />';
			container.find('div.modal-footer').prepend(recordsInput);
			var validInput = '<input type="hidden" name="validRecords" value="" />';
			container.find('div.modal-footer').prepend(validInput);
			recordsInput = container.find('[name="selectedRecords"]');
			validInput = container.find('[name="validRecords"]');
			container.find('div.modal-header').append('<p id="recordsInfo">records info</p>');

			var selectedRecords = [];
			if (container.find('#all_records').val() !== '') {
				selectedRecords = JSON.parse(container.find('#all_records').val());
			} else {
				jQuery('.listViewEntriesCheckBox:checked').each(function (i) {
					selectedRecords[i] = jQuery(this).val();
				});
			}

			recordsInput.val(JSON.stringify(selectedRecords));
			validInput.val(JSON.stringify(selectedRecords));
			jQuery('#recordsInfo').text(selectedRecords.length + ' from ' + selectedRecords.length + ' are valid for chosen template.');
			// recalculate valid records
			container.find('[name="pdf_template[]"]').first().trigger('change');
		}
	}
});

jQuery(function () {
	var exportObject = new Settings_PDF_ExportPDF_Js();
	exportObject.registerEvents();
});
