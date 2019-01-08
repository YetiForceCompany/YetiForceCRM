/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_PDF_Edit_Js("Settings_PDF_Edit1_Js", {}, {
	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer: function () {
		return this.step1Container;
	},
	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step1Container = element;
		return this;
	},
	/**
	 * Function  to intialize the reports step1
	 */
	initialize: function (container) {
		if (typeof container === "undefined") {
			container = jQuery('#pdf_step1');
		}
		if (container.is('#pdf_step1')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#pdf_step1'));
		}
	},
	submit: function () {
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		formData['async'] = false;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		const saveData = form.serializeFormData();
		saveData['action'] = 'Save';
		saveData['step'] = 1;
		saveData['async'] = false;
		if (typeof saveData['metatags_status'] === 'undefined') {
			saveData['metatags_status'] = 0;
		}
		AppConnector.request(saveData).done(function (data) {
			data = JSON.parse(data);
			if (data.success == true) {
				Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});
				var pdfRecordElement = jQuery('[name="record"]', form);
				if (pdfRecordElement.val() === '') {
					pdfRecordElement.val(data.result.id);
					formData['record'] = data.result.id;
				}

				formData['record'] = data.result.id;
				AppConnector.request(formData).done(function (data) {
					form.hide();
					progressIndicatorElement.progressIndicator({
						'mode': 'hide'
					})
					aDeferred.resolve(data);
				}).fail(function (error, err) {
					app.errorLog(error, err);
				});
			}
		}).fail(function (error, err) {
			app.errorLog(error, err);
		});

		return aDeferred.promise();
	},
	registerCancelStepClickEvent: function (form) {
		jQuery('button.cancelLink', form).on('click', function () {
			window.history.back();
		});
	},
	registerMarginCheckboxClickEvent: function (container) {
		container.find('#margin_chkbox').on('change', function () {
			var status = jQuery(this).is(':checked');

			if (status) {
				container.find('.margin_inputs').addClass('d-none');
			} else {
				container.find('.margin_inputs').removeClass('d-none');
			}
		});
	},
	/**
	 * Register module change event - load proper variable panel for specified module
	 * @param {jQuery} container
	 */
	registerModuleChangeEvent(container) {
		container.find('[name="module_name"]').on('change', function () {
			const progressIndicator = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {'enabled': true}
			});
			AppConnector.request({
				module: 'PDF',
				parent: 'Settings',
				view: 'VariablePanel',
				record: container.find('[name="record"]').val(),
				type: 'pdf',
				selectedModule: $(this).val()
			}).done((response) => {
				container.find('.js-variable-panel').html(response);
				App.Tools.VariablesPanel.registerRefreshCompanyVariables(container);
				App.Fields.Text.registerCopyClipboard(container);
				progressIndicator.progressIndicator({'mode': 'hide'});
			}).fail((error, err) => {
				progressIndicator.progressIndicator({'mode': 'hide'});
				app.errorLog(error, err);
			});
		});
	},
	registerWatermarkTypeChange: function (container) {
		var watermarkType = container.find('#watermark_type');

		watermarkType.on('change', function () {
			container.find('.watertext').toggleClass('d-none')
			container.find('.waterimage').toggleClass('d-none')
		});
	},
	registerUploadButton: function (form) {
		form.find('#uploadWM').on('click', function (e) {
			e.preventDefault();
			var fileSelect = form.find('#watermark_image');
			// Get the selected files from the input.
			var files = fileSelect[0].files;

			// Create a new FormData object.
			var formData = new FormData();
			// Loop through each of the selected files.
			for (var i = 0; i < files.length; i++) {
				var file = files[i];

				// Check the file type.
				if (!file.type.match('image.*')) {
					continue;
				}

				// Add the file to the request.
				formData.append('watermark[]', file, file.name);
			}
			formData.append('template_id', form.find('[name="record"]').val());
			// Set up the request.
			var xhr = new XMLHttpRequest();

			// Open the connection.
			xhr.open('POST', 'index.php?module=PDF&parent=Settings&action=Watermark&mode=upload', true);

			// Set up a handler for when the request finishes.
			xhr.onload = function () {
				if (xhr.status === 200) {
					const response = JSON.parse(xhr.response);
					form.find('#watermark').html('<img src="' + response.result.base64 + '" class="col-md-9" />');
					form.find('[name="watermark_image"]').val(response.result.fileName);
					form.find('#deleteWM').removeClass('d-none');
				}
			};

			// Send the Data.
			xhr.send(formData);
		});
	},
	registerDeleteUploadButton: function (form) {
		form.find('#deleteWM').on('click', function (e) {
			e.preventDefault();
			var params = {};
			params.data = {
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				action: 'Watermark',
				mode: 'delete',
				id: form.find('[name="record"]').val()
			};
			params.dataType = 'json';
			AppConnector.request(params).done(function (data) {
				var response = data['result'];
				if (response) {
					form.find('#watermark').html('');
					form.find('[name="watermark_image"]').val('');
					form.find('#deleteWM').addClass('d-none');
				}
			}).fail(function (data, err) {
				app.errorLog(data, err);
			});
		});
	},
	/**
	 * Register events
	 */
	registerEvents() {
		const container = this.getContainer();
		//After loading 1st step only, we will enable the Next button
		container.find('[type="submit"]').removeAttr('disabled');
		const opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function (form, valid) {
			//returns the valid status
			return valid;
		};
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		this.registerCancelStepClickEvent(container);
		this.registerMarginCheckboxClickEvent(container);
		this.registerModuleChangeEvent(container);
		this.registerWatermarkTypeChange(container);
		this.registerUploadButton(container);
		this.registerDeleteUploadButton(container);
		App.Tools.VariablesPanel.registerRefreshCompanyVariables(container);
		App.Fields.Text.registerCopyClipboard(container);
		App.Tools.VariablesPanel.refreshCompanyVariables(container);
	}
});
