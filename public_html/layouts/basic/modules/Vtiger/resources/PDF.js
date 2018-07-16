/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Vtiger_PDF_Js", {
	validateSubmit: function (container) {
		var templateIds = [];
		var i = 0;
		container.find('[name="pdf_template[]"]').each(function () {
			if (jQuery(this).is(':checked')) {
				templateIds[i] = jQuery(this).val();
				i++;
			}
		});
		if (templateIds.length > 0) {
			container.find('#generate_pdf').attr('disabled', false);
			var view = app.getUrlVar('view');
			if (templateIds.length > 1 || (view && view.replace('#', '') === 'List' && JSON.parse(container.find('[name="validRecords"]').val()).length > 0)) {
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
			document.progressLoader = jQuery.progressIndicator({
				message: app.vtranslate('JS_PDF_GENERATING'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var templateIds = [];
			var i = 0;
			container.find('[name="pdf_template[]"]').each(function () {
				if (jQuery(this).is(':checked')) {
					templateIds[i] = jQuery(this).val();
					i++;
				}
			});
			var view = app.getUrlVar('view');
			if (view && view.replace('#', '') === 'List') {
				container.find('[name="record"]').val(container.find('[name="validRecords"]').val());
			}

			container.find('[name="template"]').val(templateIds);
			switch (jQuery(this).attr('id')) {
				case 'generate_pdf':
					break;
				case 'single_pdf':
					container.find('[name="single_pdf"]').val(1);
					container.find('#pdfExportModal').submit();
					break;
				case 'email_pdf':
					container.find('[name="email_pdf"]').val(1);
					container.find('#pdfExportModal').submit();
					break;
			}
			document.progressLoader.progressIndicator({'mode': 'hide'});
			app.hideModalWindow();
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
				message: app.vtranslate('JS_PDF_RECALCULATING'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var selectedRecords = container.find('[name="selectedRecords"]').val();
			var selectedTemplates = [];
			container.find('[name="pdf_template[]"]:checked').each(function (i) {
				selectedTemplates[i] = jQuery(this).val();
			});

			var params = {};
			params.data = {
				module: app.getModuleName(),
				action: 'PDF',
				mode: 'validateRecords',
				records: selectedRecords,
				view: app.getViewName(),
				templates: selectedTemplates
			};
			params.dataType = 'json';
			AppConnector.request(params).done(function (data) {
				var response = data['result'];
				if (data['success']) {
					container.find('[name="validRecords"]').val(JSON.stringify(response.valid_records));
					container.find('#recordsInfo').text(response.message);
					setTimeout(function () {
						document.progressLoader.progressIndicator({'mode': 'hide'})
					}, 500);
					thisInstance.validateSubmit(container);
				}
			}).fail(function (data, err) {
				app.errorLog(data, err);
			});
		});
	},
	countSelectedRecords: function (container) {
		var selectedRecords = JSON.parse(container.find('[name="selectedRecords"]').val());

		return selectedRecords.length;
	},
	registerEvents: function () {
		var container = jQuery('div.modal-content');
		this.registerPreSubmitEvent(container);

		if (app.getViewName() === 'Detail') {
			this.registerValidateSubmit(container);
		}
		if (app.getViewName() === 'List') {
			this.validateSubmit(container);
			this.registerListViewCheckRecords(container);
			var selectedRecords = JSON.parse(container.find('#all_records').val());
			var recordsInput = container.find('[name="selectedRecords"]');
			var validInput = container.find('[name="validRecords"]');
			container.find('div.modal-body').append('<p id="recordsInfo">' + app.vtranslate('JS_RECORD_INFO'), +'</p>');
			recordsInput.val(JSON.stringify(selectedRecords));
			validInput.val(JSON.stringify(selectedRecords));
			jQuery('#recordsInfo').text(selectedRecords.length + ' from ' + selectedRecords.length + ' are valid for chosen template.');
			this.validateSubmit(container);
		}
	}
});
jQuery(function () {
	var instance = new Vtiger_PDF_Js();
	instance.registerEvents();
});
